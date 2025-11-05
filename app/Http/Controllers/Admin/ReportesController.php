<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    /** GET admin/reportes/productos/export/csv */
    public function exportProductosCSV(): StreamedResponse
    {
        $rows = DB::table('productos')
            ->select('id', 'nombre', 'precio')
            ->orderBy('nombre')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte' => 'catalogo_productos',
        ]);

        $filename = 'catalogo_productos_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return Response::stream(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Producto', 'Precio']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->id, $r->nombre, number_format((float) ($r->precio ?? 0), 2)]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /** GET admin/reportes/pedidos-por-estado/export/csv */
    public function exportPedidosPorEstadoCSV(Request $request): StreamedResponse
    {
        $estado = (string) $request->get('estado', '');
        $desde  = (string) $request->get('desde', '');
        $hasta  = (string) $request->get('hasta', '');

        $rows = DB::table('pedidos')
            ->when($estado !== '', fn ($w) => $w->where('estado', $estado))
            ->when($desde !== '', fn ($w) => $w->whereDate('created_at', '>=', $desde))
            ->when($hasta !== '', fn ($w) => $w->whereDate('created_at', '<=', $hasta))
            ->select('id', 'folio', 'estado', 'tipo_entrega', 'total', 'created_at')
            ->orderBy('created_at')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte' => 'pedidos_por_estado',
            'estado'  => $estado,
            'desde'   => $desde,
            'hasta'   => $hasta,
        ]);

        $filename = 'pedidos_por_estado_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return Response::stream(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Folio', 'Estado', 'Tipo entrega', 'Total', 'Creado']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->folio,
                    $r->estado,
                    $r->tipo_entrega,
                    number_format((float) ($r->total ?? 0), 2),
                    (string) $r->created_at,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /** GET admin/reportes/ventas/export/csv */
    public function exportVentasCSV(Request $request): StreamedResponse
    {
        $desde = (string) $request->get('desde', '');
        $hasta = (string) $request->get('hasta', '');

        $rows = DB::table('pedidos')
            ->when($desde !== '', fn ($w) => $w->whereDate('created_at', '>=', $desde))
            ->when($hasta !== '', fn ($w) => $w->whereDate('created_at', '<=', $hasta))
            ->whereIn('estado', ['entregado', 'listo', 'en_camino', 'preparando', 'pendiente'])
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as pedidos, SUM(total) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte' => 'ventas',
            'desde'   => $desde,
            'hasta'   => $hasta,
        ]);

        $filename = 'ventas_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return Response::stream(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Pedidos', 'Total']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->fecha, (int) $r->pedidos, number_format((float) ($r->total ?? 0), 2)]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /** GET admin/reportes/movimientos-inventario/export/csv */
    public function exportMovInventarioCSV(Request $request): StreamedResponse
    {
        $productoId = (int) $request->get('producto_id', 0);
        $tipo       = (string) $request->get('tipo', '');
        $desde      = (string) $request->get('desde', '');
        $hasta      = (string) $request->get('hasta', '');

        $rows = DB::table('inventario_movimientos as im')
            ->join('productos as p', 'p.id', '=', 'im.producto_id')
            ->leftJoin('users as u', 'u.id', '=', 'im.user_id')
            ->when($productoId > 0, fn ($w) => $w->where('im.producto_id', $productoId))
            ->when(in_array($tipo, ['entrada', 'salida', 'ajuste']), fn ($w) => $w->where('im.tipo', $tipo))
            ->when($desde !== '', fn ($w) => $w->whereDate('im.created_at', '>=', $desde))
            ->when($hasta !== '', fn ($w) => $w->whereDate('im.created_at', '<=', $hasta))
            ->select([
                'im.id',
                'p.nombre as producto',
                'im.tipo',
                'im.cantidad',
                'im.delta',
                'im.stock_resultante',
                DB::raw("COALESCE(u.name,'—') as usuario"),
                'im.motivo',
                'im.created_at',
            ])
            ->orderBy('p.nombre')
            ->orderByDesc('im.id')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte'      => 'movimientos_inventario',
            'producto_id'  => $productoId,
            'tipo'         => $tipo,
            'desde'        => $desde,
            'hasta'        => $hasta,
        ]);

        $filename = 'movimientos_inventario_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return Response::stream(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Producto', 'Tipo', 'Cantidad', 'Δ', 'Stock resultante', 'Motivo', 'Usuario', 'Fecha']);
            foreach ($rows as $m) {
                fputcsv($out, [
                    $m->id,
                    $m->producto,
                    $m->tipo,
                    (int) $m->cantidad,
                    (int) $m->delta,
                    (int) $m->stock_resultante,
                    $m->motivo ?? '',
                    $m->usuario,
                    (string) $m->created_at,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /** GET admin/reportes/desempeno-repartidores/export/csv */
    public function exportDesempenoRepartidoresCSV(Request $request): StreamedResponse
    {
        $desde = (string) $request->get('desde', '');
        $hasta = (string) $request->get('hasta', '');

        $rows = DB::table('pedidos as p')
            ->join('users as u', 'u.id', '=', 'p.asignado_a')
            ->when($desde !== '', fn ($w) => $w->whereDate('p.created_at', '>=', $desde))
            ->when($hasta !== '', fn ($w) => $w->whereDate('p.created_at', '<=', $hasta))
            ->where('p.estado', 'entregado')
            ->selectRaw('u.id as repartidor_id, u.name as repartidor, COUNT(p.id) as entregas, SUM(p.total) as total')
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('entregas')
            ->get();

        BitacoraService::add('reportes', 'exportar_csv', null, [
            'reporte' => 'desempeno_repartidores',
            'desde'   => $desde,
            'hasta'   => $hasta,
        ]);

        $filename = 'desempeno_repartidores_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return Response::stream(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Repartidor ID', 'Repartidor', 'Entregas', 'Total recaudado']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->repartidor_id, $r->repartidor, (int) $r->entregas, number_format((float) ($r->total ?? 0), 2)]);
            }
            fclose($out);
        }, 200, $headers);
    }
}
