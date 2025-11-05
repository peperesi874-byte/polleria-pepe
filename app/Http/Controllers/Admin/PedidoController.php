<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoLog;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

    /** Cambiar estado */
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
        $pedido->update(['estado' => $data['estado']]);

        PedidoLog::create([
            'pedido_id' => $pedido->id,
            'user_id'   => auth()->id(),
            'accion'    => 'estado_cambiado',
            'de'        => $anterior,
            'a'         => $data['estado'],
        ]);

        // Bitácora
        BitacoraService::add('pedidos', 'estado_cambiado', $pedido->id, [
            'de' => $anterior,
            'a'  => $data['estado'],
        ]);

        return back()->with('success', 'Estado actualizado.');
    }

    /** Alias para admin.pedidos.estado */
    public function updateEstado(Request $request, Pedido $pedido)
    {
        return $this->setEstado($request, $pedido);
    }

    /** Cancelar */
    public function cancelar(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        if ($pedido->estado === 'entregado') {
            return back()->with('error', 'No se puede cancelar un pedido entregado.');
        }

        $anterior = $pedido->estado;

        $pedido->update([
            'estado'              => 'cancelado',
            'motivo_cancelacion'  => $data['motivo'],
        ]);

        PedidoLog::create([
            'pedido_id' => $pedido->id,
            'user_id'   => auth()->id(),
            'accion'    => 'cancelado',
            'de'        => $anterior,
            'a'         => 'cancelado',
            'motivo'    => $data['motivo'],
        ]);

        // Bitácora
        BitacoraService::add('pedidos', 'cancelado', $pedido->id, [
            'de'     => $anterior,
            'a'      => 'cancelado',
            'motivo' => $data['motivo'],
        ]);

        return back()->with('success', 'Pedido cancelado.');
    }

    /** Asignar / desasignar repartidor */
    public function asignar(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'repartidor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (!empty($data['repartidor_id'])) {
            $isRepartidor = User::where('id', $data['repartidor_id'])
                ->where('role_id', 3)
                ->exists();

            if (!$isRepartidor) {
                return back()->with('error', 'El usuario seleccionado no es repartidor.');
            }
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

        // Bitácora
        BitacoraService::add('pedidos', $nuevo !== '' ? 'asignado' : 'desasignado', $pedido->id, [
            'de' => $anterior,
            'a'  => $nuevo,
        ]);

        return back()->with('success', 'Asignación actualizada.');
    }
}
