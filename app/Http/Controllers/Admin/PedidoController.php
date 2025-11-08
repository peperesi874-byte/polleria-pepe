<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoLog;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Inventario automático por PIEZAS
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use Illuminate\Support\Facades\DB;

// Notificaciones in-app
use App\Services\Notify;

class PedidoController extends Controller
{
    /** Listado con filtros */
    public function index(Request $request)
    {
        $estado   = $request->string('estado')->toString();
        $q        = $request->string('q')->toString();
        $asignado = $request->string('asignado')->toString(); // '', 'none', 'any'

        $query = Pedido::query()
            ->withCount('items')
            ->with(['repartidor:id,name']);

        if ($estado !== '') {
            $query->where('estado', $estado);
        }

        if ($asignado === 'none') {
            $query->whereNull('asignado_a');
        } elseif ($asignado === 'any') {
            $query->whereNotNull('asignado_a');
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('folio', 'like', "%{$q}%")
                  ->orWhere('observaciones', 'like', "%{$q}%");
            });
        }

        $pedidos = $query->orderByDesc('id')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($p) {
                return [
                    'id'         => $p->id,
                    'folio'      => $p->folio,
                    'estado'     => $p->estado,
                    'tipo'       => $p->tipo_entrega,
                    'total'      => (float) $p->total,
                    'items'      => (int) $p->items_count,
                    'asignado_a' => $p->asignado_a,
                    'repartidor' => $p->repartidor?->name ?? null,
                    'created_at' => $p->created_at?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Admin/Pedidos/Index', [
            'pedidos' => $pedidos,
            'filters' => ['q' => $q, 'estado' => $estado, 'asignado' => $asignado],
            'estados' => ['pendiente', 'preparando', 'listo', 'en_camino', 'entregado', 'cancelado'],
        ]);
    }

    /** Detalle */
    public function show(Pedido $pedido)
    {
        $pedido->load([
            'items.producto',
            'logs.user:id,name',
        ]);

        $repartidores = User::query()
            ->where('role_id', 3)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Pedidos/Show', [
            'pedido' => [
                'id'         => $pedido->id,
                'folio'      => $pedido->folio,
                'estado'     => $pedido->estado,
                'tipo'       => $pedido->tipo_entrega,
                'total'      => (float) $pedido->total,
                'observaciones' => $pedido->observaciones,
                'created_at' => $pedido->created_at?->format('Y-m-d H:i'),
                'asignado_a' => $pedido->asignado_a,
                'items'      => $pedido->items->map(fn ($it) => [
                    'id'        => $it->id,
                    'producto'  => $it->producto?->nombre ?? '—',
                    'cantidad'  => (int) $it->cantidad,
                    'precio'    => (float) $it->precio_unitario,
                    'subtotal'  => (float) $it->subtotal,
                ]),
                'logs'       => $pedido->logs->map(fn ($log) => [
                    'id'     => $log->id,
                    'accion' => $log->accion,
                    'de'     => $log->de,
                    'a'      => $log->a,
                    'motivo' => $log->motivo,
                    'by'     => $log->user?->name ?? 'Sistema',
                    'fecha'  => $log->created_at?->format('Y-m-d H:i'),
                ]),
            ],
            'repartidores' => $repartidores,
        ]);
    }

    /** Cambiar estado (con inventario automático por piezas + notificaciones) */
    public function setEstado(Request $request, Pedido $pedido)
    {
        $estados = ['pendiente', 'preparando', 'listo', 'en_camino', 'entregado'];

        $data = $request->validate([
            'estado' => ['required', 'in:' . implode(',', $estados)],
        ]);

        if ($pedido->estado === 'cancelado') {
            return back()->with('error', 'No se puede cambiar el estado de un pedido cancelado.');
        }

        $anterior = $pedido->estado;
        $nuevo    = $data['estado'];

        // Pre-chequeo de stock si vamos a descontar
        if (in_array($nuevo, ['listo', 'entregado'])) {
            $pedido->loadMissing('items.producto');
            foreach ($pedido->items as $it) {
                $inv = Inventario::firstOrCreate(
                    ['producto_id' => $it->producto_id],
                    ['stock_actual' => 0, 'stock_minimo' => 0]
                );
                if ($inv->stock_actual < (int) $it->cantidad) {
                    $nombre = $it->producto?->nombre ?? ('ID '.$it->producto_id);
                    return back()->with('error', "Stock insuficiente para «{$nombre}». Requeridas: {$it->cantidad}, disponibles: {$inv->stock_actual}.");
                }
            }
        }

        DB::transaction(function () use ($pedido, $anterior, $nuevo) {
            // a) Cambiar estado
            $pedido->update(['estado' => $nuevo]);

            // b) Descuento por piezas (idempotente)
            if (in_array($nuevo, ['listo', 'entregado'])) {
                $pedido->loadMissing('items.producto');
                foreach ($pedido->items as $it) {
                    $prodId = (int) $it->producto_id;
                    $pzs    = (int) $it->cantidad;

                    $yaAplicado = InventarioMovimiento::where('producto_id', $prodId)
                        ->where('tipo', 'salida')
                        ->where('motivo', 'pedido:' . $pedido->id)
                        ->exists();
                    if ($yaAplicado) continue;

                    $inv = Inventario::firstOrCreate(
                        ['producto_id' => $prodId],
                        ['stock_actual' => 0, 'stock_minimo' => 0]
                    );

                    $nuevoStock = (int) $inv->stock_actual - $pzs;
                    $inv->update(['stock_actual' => $nuevoStock]);

                    InventarioMovimiento::create([
                        'producto_id'      => $prodId,
                        'user_id'          => auth()->id(),
                        'tipo'             => 'salida',
                        'cantidad'         => $pzs,
                        'delta'            => -$pzs,
                        'motivo'           => 'pedido:' . $pedido->id,
                        'stock_resultante' => $nuevoStock,
                    ]);

                    // Si quedó bajo mínimo, notificar
                    if ($nuevoStock <= (int) ($inv->stock_minimo ?? 0)) {
                        $nombre = $it->producto?->nombre ?? ('ID '.$prodId);
                        try {
                            Notify::lowStock($prodId, $nombre, $nuevoStock, (int) ($inv->stock_minimo ?? 0));
                        } catch (\Throwable $e) {
                            \Log::warning('Notify::lowStock error: '.$e->getMessage());
                        }
                    }
                }
            }

            // c) Log del pedido
            PedidoLog::create([
                'pedido_id' => $pedido->id,
                'user_id'   => auth()->id(),
                'accion'    => 'estado_cambiado',
                'de'        => $anterior,
                'a'         => $nuevo,
            ]);

            // d) Bitácora
            BitacoraService::add('pedidos', 'estado_cambiado', $pedido->id, [
                'de'     => $anterior,
                'a'      => $nuevo,
                'unidad' => 'piezas',
            ]);

            // e) Notificación in-app (firma correcta)
            try {
                Notify::pedidoEstado($pedido, $anterior, $nuevo);
            } catch (\Throwable $e) {
                \Log::warning('Notify::pedidoEstado error: '.$e->getMessage());
            }
        });

        return back()->with('success', 'Estado actualizado.');
    }

    /** Alias para admin.pedidos.estado */
    public function updateEstado(Request $request, Pedido $pedido)
    {
        return $this->setEstado($request, $pedido);
    }

    /** Cancelar (con reposición automática por piezas + notificación) */
    public function cancelar(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        if ($pedido->estado === 'entregado') {
            return back()->with('error', 'No se puede cancelar un pedido entregado.');
        }

        $anterior = $pedido->estado;

        DB::transaction(function () use ($pedido, $data, $anterior) {
            // a) Cambiar estado → cancelado
            $pedido->update([
                'estado'             => 'cancelado',
                'motivo_cancelacion' => $data['motivo'],
            ]);

            // b) Reponer por piezas si previamente se descontó por este pedido (idempotente)
            $pedido->loadMissing('items');
            foreach ($pedido->items as $it) {
                $prodId = (int) $it->producto_id;
                $pzs    = (int) $it->cantidad;

                $seDescontoAntes = InventarioMovimiento::where('producto_id', $prodId)
                    ->where('tipo', 'salida')
                    ->where('motivo', 'pedido:' . $pedido->id)
                    ->exists();

                $yaRepuesto = InventarioMovimiento::where('producto_id', $prodId)
                    ->where('tipo', 'entrada')
                    ->where('motivo', 'cancelacion:' . $pedido->id)
                    ->exists();

                if ($seDescontoAntes && !$yaRepuesto) {
                    $inv        = Inventario::firstOrCreate(['producto_id' => $prodId], ['stock_actual' => 0, 'stock_minimo' => 0]);
                    $nuevoStock = (int) $inv->stock_actual + $pzs;
                    $inv->update(['stock_actual' => $nuevoStock]);

                    InventarioMovimiento::create([
                        'producto_id'      => $prodId,
                        'user_id'          => auth()->id(),
                        'tipo'             => 'entrada',
                        'cantidad'         => $pzs,
                        'delta'            =>  $pzs,
                        'motivo'           => 'cancelacion:' . $pedido->id,
                        'stock_resultante' => $nuevoStock,
                    ]);
                }
            }

            // c) Log del pedido
            PedidoLog::create([
                'pedido_id' => $pedido->id,
                'user_id'   => auth()->id(),
                'accion'    => 'cancelado',
                'de'        => $anterior,
                'a'         => 'cancelado',
                'motivo'    => $data['motivo'],
            ]);

            // d) Bitácora
            BitacoraService::add('pedidos', 'cancelado', $pedido->id, [
                'de'     => $anterior,
                'a'      => 'cancelado',
                'motivo' => $data['motivo'],
                'unidad' => 'piezas',
            ]);

            // e) Notificación in-app de cancelación (firma correcta)
            try {
                Notify::pedidoCancelado($pedido, $data['motivo']);
            } catch (\Throwable $e) {
                \Log::warning('Notify::pedidoCancelado error: '.$e->getMessage());
            }
        });

        return back()->with('success', 'Pedido cancelado.');
    }

    /** Asignar / desasignar repartidor */
    public function asignar(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'repartidor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $antes = $pedido->asignado_a ? User::find($pedido->asignado_a) : null;
        $despues = null;

        if (!empty($data['repartidor_id'])) {
            $isRepartidor = User::where('id', $data['repartidor_id'])
                ->where('role_id', 3)
                ->exists();

            if (!$isRepartidor) {
                return back()->with('error', 'El usuario seleccionado no es repartidor.');
            }

            $despues = User::find($data['repartidor_id']);
        }

        $anterior = (string) ($pedido->asignado_a ?? '');
        $nuevo    = (string) ($data['repartidor_id'] ?? '');

        $pedido->update(['asignado_a' => $data['repartidor_id'] ?: null]);

        PedidoLog::create([
            'pedido_id' => $pedido->id,
            'user_id'   => auth()->id(),
            'accion'    => $nuevo !== '' ? 'asignado' : 'desasignado',
            'de'        => $anterior,
            'a'         => $nuevo,
        ]);

        BitacoraService::add('pedidos', $nuevo !== '' ? 'asignado' : 'desasignado', $pedido->id, [
            'de' => $anterior,
            'a'  => $nuevo,
        ]);

        // Notificación de asignación (firma correcta)
        try {
            Notify::pedidoAsignacion($pedido, $antes, $despues);
        } catch (\Throwable $e) {
            \Log::warning('Notify::pedidoAsignacion error: '.$e->getMessage());
        }

        return back()->with('success', 'Asignación actualizada.');
    }
}
