<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Services\BitacoraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\Notify;

class InventarioController extends Controller
{
    /** GET admin/inventario */
    public function index(Request $request): InertiaResponse
    {
        $q      = trim((string) $request->get('q', ''));
        $filtro = (string) $request->get('filtro', ''); // '', 'bajo_minimo'

        $productos = Producto::query()
            ->when($q !== '', fn ($w) => $w->where('nombre', 'like', "%{$q}%"))
            ->with(['inventario:id,producto_id,stock_actual,stock_minimo'])
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($p) use ($filtro) {
                $inv = $p->inventario;
                $row = [
                    'id'           => $p->id,
                    'nombre'       => $p->nombre,
                    'precio'       => (float) ($p->precio ?? 0),
                    'stock_actual' => (int) ($inv->stock_actual ?? 0),
                    'stock_minimo' => (int) ($inv->stock_minimo ?? 0),
                ];
                if ($filtro === 'bajo_minimo' && $row['stock_actual'] >= $row['stock_minimo']) {
                    return null;
                }
                return $row;
            });

        $productos->setCollection($productos->getCollection()->filter());

        return Inertia::render('Admin/Inventario/Index', [
            'productos' => $productos,
            'filters'   => ['q' => $q, 'filtro' => $filtro],
        ]);
    }

    /**
     * GET admin/inventario/export/pdf
     */
    public function exportInventarioPDF(Request $request)
    {
        $q      = trim((string) $request->get('q', ''));
        $filtro = (string) $request->get('filtro', '');

        $rows = Producto::query()
            ->when($q !== '', fn ($w) => $w->where('nombre', 'like', "%{$q}%"))
            ->with(['inventario:id,producto_id,stock_actual,stock_minimo'])
            ->orderBy('nombre')
            ->get()
            ->map(function ($p) {
                return (object) [
                    'nombre'        => $p->nombre,
                    'precio'        => (float) ($p->precio ?? 0),
                    'stock_actual'  => (int) ($p->inventario->stock_actual ?? 0),
                    'stock_minimo'  => (int) ($p->inventario->stock_minimo ?? 0),
                ];
            });

        if ($filtro === 'bajo_minimo') {
            $rows = $rows->filter(fn ($r) => $r->stock_actual <= $r->stock_minimo)->values();
        }

        $data = [
            'titulo' => 'Reporte de Inventario',
            'fecha'  => now()->timezone(config('app.timezone', 'America/Mexico_City'))->format('d/m/Y H:i'),
            'rows'   => $rows,
        ];

        BitacoraService::add('reportes', 'exportar_pdf', null, [
            'reporte' => 'inventario',
            'filtro'  => $filtro,
            'q'       => $q,
        ]);

        // Notificación
        Notify::push(
            'Reporte de inventario (PDF)',
            'Se exportó el reporte de inventario en PDF.',
            'reporte_exportado',
            [],
            'info'
        );

        $pdf = Pdf::loadView('pdf.inventario', $data)->setPaper('letter', 'portrait');
        return $pdf->stream('inventario_' . now()->format('Ymd_His') . '.pdf');
    }

    /** POST admin/inventario/movimiento */
    public function storeMovimiento(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'tipo'        => ['required', 'in:entrada,salida,ajuste'],
            'cantidad'    => ['required', 'integer', 'min:0'],
            'motivo'      => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($data) {
            $inv = Inventario::firstOrCreate(
                ['producto_id' => $data['producto_id']],
                ['stock_actual' => 0, 'stock_minimo' => 0]
            );

            $p      = Producto::find($inv->producto_id);
            $nombre = $p?->nombre ?? ('ID ' . $inv->producto_id);

            $anterior = (int) $inv->stock_actual;
            $cant     = (int) $data['cantidad'];

            if ($data['tipo'] === 'entrada') {
                $nuevo = $anterior + $cant;
                $delta =  $cant;
            } elseif ($data['tipo'] === 'salida') {
                if ($anterior < $cant) {
                    return back()->with('error', 'No hay stock suficiente para la salida.');
                }
                $nuevo = $anterior - $cant;
                $delta = -$cant;
            } else {
                // ajuste = nuevo stock absoluto
                $nuevo = max(0, $cant);
                $delta = $nuevo - $anterior;
            }

            $inv->update(['stock_actual' => $nuevo]);

            InventarioMovimiento::create([
                'producto_id'      => $inv->producto_id,
                'user_id'          => auth()->id(),
                'tipo'             => $data['tipo'],
                'cantidad'         => $cant,
                'delta'            => $delta,
                'motivo'           => $data['motivo'] ?? null,
                'stock_resultante' => $nuevo,
            ]);

            BitacoraService::add('inventario', 'movimiento', $inv->producto_id, [
                'tipo'             => $data['tipo'],
                'cantidad'         => $cant,
                'delta'            => $delta,
                'stock_resultante' => $nuevo,
            ]);

            // Notificación del movimiento
            $titulo = [
                'entrada' => 'Entrada de inventario',
                'salida'  => 'Salida de inventario',
                'ajuste'  => 'Ajuste de inventario',
            ][$data['tipo']] ?? 'Movimiento de inventario';

            $mensaje = "{$titulo} en «{$nombre}»: {$cant} pzs "
                     . ($data['motivo'] ? "(motivo: {$data['motivo']}) " : "")
                     . "→ stock ahora {$nuevo} pzs (mínimo {$inv->stock_minimo}).";

            $meta = [
                'accion'           => 'inventario_movimiento',
                'producto_id'      => (int) $inv->producto_id,
                'producto'         => $nombre,
                'tipo'             => $data['tipo'],
                'cantidad'         => (int) $cant,
                'delta'            => (int) $delta,
                'stock_resultante' => (int) $nuevo,
                'motivo'           => $data['motivo'] ?? null,
            ];

            Notify::push(
                $titulo,
                $mensaje,
                "inventario_{$data['tipo']}",
                $meta,
                $data['tipo'] === 'salida' ? 'warning' : 'info',
                $inv->producto_id
            );

            // Estado respecto al mínimo (sin okStock/lowStock)
            $min = (int) ($inv->stock_minimo ?? 0);
            if ($min > 0 && $nuevo <= $min) {
                Notify::push(
                    'Inventario bajo',
                    "«{$nombre}» con stock bajo: {$nuevo} pzs (mínimo {$min}).",
                    'inventario_bajo',
                    [
                        'accion'       => 'inventario_bajo',
                        'producto_id'  => (int) $inv->producto_id,
                        'producto'     => $nombre,
                        'stock'        => (int) $nuevo,
                        'stock_minimo' => (int) $min,
                    ],
                    'danger',
                    $inv->producto_id
                );
            } elseif ($min > 0 && $anterior <= $min && $nuevo > $min) {
                Notify::push(
                    'Inventario recuperado',
                    "«{$nombre}» se recuperó por encima del mínimo: {$nuevo} pzs (mínimo {$min}).",
                    'inventario_ok',
                    [
                        'accion'       => 'inventario_ok',
                        'producto_id'  => (int) $inv->producto_id,
                        'producto'     => $nombre,
                        'stock'        => (int) $nuevo,
                        'stock_minimo' => (int) $min,
                    ],
                    'success',
                    $inv->producto_id
                );
            }

            return back()->with('success', 'Movimiento registrado.');
        });
    }

    /** PUT admin/inventario/{producto}/minimo */
    public function updateMinimo(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $inv = Inventario::firstOrCreate(
            ['producto_id' => $producto->id],
            ['stock_actual' => 0, 'stock_minimo' => 0]
        );

        $inv->update(['stock_minimo' => (int) $data['stock_minimo']]);

        BitacoraService::add('inventario', 'actualizar_minimo', $producto->id, [
            'stock_minimo' => (int) $data['stock_minimo'],
        ]);

        // Notificar situación contra el nuevo mínimo (sin okStock/lowStock)
        $min   = (int) $inv->stock_minimo;
        $stock = (int) $inv->stock_actual;
        $nombre = $producto->nombre ?? ('ID ' . $producto->id);

        if ($min > 0 && $stock <= $min) {
            Notify::push(
                'Inventario bajo',
                "«{$nombre}» con stock bajo: {$stock} pzs (mínimo {$min}).",
                'inventario_bajo',
                [
                    'accion'       => 'inventario_bajo',
                    'producto_id'  => (int) $producto->id,
                    'producto'     => $nombre,
                    'stock'        => (int) $stock,
                    'stock_minimo' => (int) $min,
                ],
                'danger',
                $producto->id
            );
        } elseif ($min > 0 && $stock > $min) {
            Notify::push(
                'Inventario en nivel correcto',
                "«{$nombre}» se encuentra por encima del mínimo: {$stock} pzs (mínimo {$min}).",
                'inventario_ok',
                [
                    'accion'       => 'inventario_ok',
                    'producto_id'  => (int) $producto->id,
                    'producto'     => $nombre,
                    'stock'        => (int) $stock,
                    'stock_minimo' => (int) $min,
                ],
                'success',
                $producto->id
            );
        }

        return back()->with('success', 'Stock mínimo actualizado.');
    }

    /** GET admin/inventario/{producto}/movimientos */
    public function movimientos(Request $request, Producto $producto): InertiaResponse
    {
        $tipo  = (string) $request->get('tipo', '');
        $q     = trim((string) $request->get('q', ''));
        $desde = (string) $request->get('desde', '');
        $hasta = (string) $request->get('hasta', '');

        $base = InventarioMovimiento::query()
            ->where('producto_id', $producto->id)
            ->when($tipo !== '', fn ($w) => $w->where('tipo', $tipo))
            ->when($q !== '', fn ($w) => $w->where('motivo', 'like', "%{$q}%"))
            ->when($desde !== '', fn ($w) => $w->whereDate('created_at', '>=', $desde))
            ->when($hasta !== '', fn ($w) => $w->whereDate('created_at', '<=', $hasta))
            ->with(['user:id,name']);

        $movs = (clone $base)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($m) use ($producto) {
                return [
                    'id'               => $m->id,
                    'nro'              => $m->id,
                    'producto'         => $producto->nombre,
                    'tipo'             => $m->tipo,
                    'cantidad'         => (int) $m->cantidad,
                    'delta'            => (int) $m->delta,
                    'motivo'           => $m->motivo,
                    'stock_resultante' => (int) $m->stock_resultante,
                    'by'               => $m->user?->name ?? 'Sistema',
                    'fecha'            => $m->created_at?->format('Y-m-d H:i'),
                ];
            });

        $inv = Inventario::firstOrCreate(
            ['producto_id' => $producto->id],
            ['stock_actual' => 0, 'stock_minimo' => 0]
        );

        return Inertia::render('Admin/Inventario/Movimientos', [
            'producto'    => [
                'id'     => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => (float) ($producto->precio ?? 0),
            ],
            'inventario'  => [
                'stock_actual' => (int) $inv->stock_actual,
                'stock_minimo' => (int) $inv->stock_minimo,
            ],
            'filters'     => compact('tipo', 'q', 'desde', 'hasta'),
            'movimientos' => $movs,
        ]);
    }

    /**
     * GET admin/inventario/historial/export/csv
     */
    public function exportCSV(Request $request): StreamedResponse
    {
        $productoId = (int) $request->get('producto_id', 0);
        $q          = trim((string) $request->get('q', ''));
        $tipo       = (string) $request->get('tipo', '');
        $desde      = (string) $request->get('desde', '');
        $hasta      = (string) $request->get('hasta', '');

        $rows = InventarioMovimiento::query()
            ->join('productos', 'productos.id', '=', 'inventario_movimientos.producto_id')
            ->leftJoin('users', 'users.id', '=', 'inventario_movimientos.user_id')
            ->when($productoId > 0, fn ($w) => $w->where('inventario_movimientos.producto_id', $productoId))
            ->when($q !== '', fn ($w) => $w->where(function ($q2) use ($q) {
                $q2->where('productos.nombre', 'like', "%{$q}%")
                   ->orWhere('inventario_movimientos.motivo', 'like', "%{$q}%");
            }))
            ->when(in_array($tipo, ['entrada', 'salida', 'ajuste']), fn ($w) => $w->where('inventario_movimientos.tipo', $tipo))
            ->when($desde !== '', fn ($w) => $w->whereDate('inventario_movimientos.created_at', '>=', $desde))
            ->when($hasta !== '', fn ($w) => $w->whereDate('inventario_movimientos.created_at', '<=', $hasta))
            ->select([
                'inventario_movimientos.id',
                'inventario_movimientos.producto_id',
                'productos.nombre as producto',
                'inventario_movimientos.tipo',
                'inventario_movimientos.cantidad',
                'inventario_movimientos.delta',
                'inventario_movimientos.stock_resultante',
                DB::raw("COALESCE(users.name,'—') as usuario"),
                'inventario_movimientos.motivo',
                'inventario_movimientos.created_at',
            ])
            ->orderBy('productos.id')
            ->orderByDesc('inventario_movimientos.id')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte'      => 'historial_inventario',
            'producto_id'  => $productoId,
            'tipo'         => $tipo,
            'q'            => $q,
            'desde'        => $desde,
            'hasta'        => $hasta,
        ]);

        Notify::push(
            'Historial de inventario (CSV)',
            'Se exportó el historial de movimientos en CSV.',
            'reporte_exportado',
            [],
            'info'
        );

        $agrupado = $rows->groupBy('producto_id');

        $filename = 'historial_inventario_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $tz  = config('app.timezone', 'America/Mexico_City');
        $fmt = 'd/m/Y H:i';

        return Response::stream(function () use ($agrupado, $tz, $fmt) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Producto', 'Tipo', 'Cantidad', 'Δ', 'Stock resultante', 'Motivo', 'Usuario', 'Fecha/Hora']);

            foreach ($agrupado as $movs) {
                $contador = $movs->count();
                foreach ($movs as $m) {
                    $fecha = $m->created_at ? $m->created_at->timezone($tz)->format($fmt) : '';
                    $fechaExcel = $fecha !== '' ? '="' . $fecha . '"' : '';

                    fputcsv($out, [
                        $contador--,
                        $m->producto,
                        $m->tipo,
                        (int) $m->cantidad,
                        (int) $m->delta,
                        (int) $m->stock_resultante,
                        $m->motivo ?? '',
                        $m->usuario,
                        $fechaExcel,
                    ]);
                }
            }
            fclose($out);
        }, 200, $headers);
    }

    /**
     * GET admin/inventario/export/csv
     */
    public function exportInventarioCSV(): StreamedResponse
    {
        $rows = Inventario::query()
            ->join('productos', 'productos.id', '=', 'inventarios.producto_id')
            ->select([
                'productos.id',
                'productos.nombre',
                'productos.precio',
                'inventarios.stock_actual',
                'inventarios.stock_minimo',
                DB::raw('CASE WHEN inventarios.stock_actual < inventarios.stock_minimo THEN "Bajo mínimo" ELSE "Normal" END as estado'),
            ])
            ->orderBy('productos.nombre')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte' => 'inventario',
        ]);

        Notify::push(
            'Inventario (CSV)',
            'Se exportó el inventario completo en CSV.',
            'reporte_exportado',
            [],
            'info'
        );

        $filename = 'inventario_completo_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return Response::stream(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Producto', 'Precio', 'Stock actual', 'Stock mínimo', 'Estado']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->nombre,
                    number_format((float) $r->precio, 2),
                    (int) $r->stock_actual,
                    (int) $r->stock_minimo,
                    $r->estado,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }
}
