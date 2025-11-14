<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\BitacoraLog;
use App\Models\Pedido;               // 👈 IMPORTANTE
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BitacoraController extends Controller
{
    /**
     * Mostrar la bitácora filtrada para el vendedor actual.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Paginador base
        $paginator = BitacoraLog::query()
            ->where('user_id', $user->id)          // solo acciones de ese vendedor
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $items = collect($paginator->items());

        // IDs de pedidos para enriquecer textos
        $pedidoIds = $items
            ->filter(fn ($log) => $log->modulo === 'pedidos')
            ->pluck('ref_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Mapa id_pedido => folio (o "Pedido #id" si no hay folio)
        $pedidos = [];
        if (!empty($pedidoIds)) {
            $pedidos = Pedido::whereIn('id', $pedidoIds)
                ->get()
                ->mapWithKeys(function (Pedido $p) {
                    $folio = $p->folio
                        ?? $p->codigo
                        ?? $p->clave
                        ?? ('Pedido #' . $p->id);
                    return [$p->id => $folio];
                })
                ->all();
        }

        // Transformar para Vue
        $logs = $paginator->through(function (BitacoraLog $log) use ($pedidos) {
            $meta = is_array($log->meta) ? $log->meta : [];

            // Intentar obtener el ID / folio del pedido
            $pedidoId = $log->ref_id ?? $meta['pedido_id'] ?? null;
            $folio    = $pedidoId && isset($pedidos[$pedidoId])
                ? $pedidos[$pedidoId]
                : null;

            // Estados viejo/nuevo (por si los guardamos en meta)
            $estadoAnt   = $meta['estado_anterior'] ?? $meta['from'] ?? null;
            $estadoNuevo = $meta['estado_nuevo']    ?? $meta['to']   ?? $meta['estado'] ?? null;

            $descripcion = null;

            if ($log->modulo === 'pedidos') {
                // Para el módulo pedidos SIEMPRE recalculamos el texto
                switch ($log->accion) {
                    case 'creado':
                        $descripcion = $folio
                            ? "Se creó el pedido {$folio}."
                            : "Se creó un pedido.";
                        break;

                    case 'estado_cambiado':
                        if ($folio && $estadoAnt && $estadoNuevo) {
                            $descripcion = "Se cambió el estado del pedido {$folio} de {$estadoAnt} a {$estadoNuevo}.";
                        } elseif ($folio && $estadoNuevo) {
                            $descripcion = "Se cambió el estado del pedido {$folio} a {$estadoNuevo}.";
                        } elseif ($folio) {
                            $descripcion = "Se cambió el estado del pedido {$folio}.";
                        } else {
                            $descripcion = "Se cambió el estado de un pedido.";
                        }
                        break;

                    case 'asignado':
                        $repartidor = $meta['repartidor_nombre']
                            ?? $meta['repartidor']
                            ?? $meta['asignado_a']
                            ?? null;

                        if ($folio && $repartidor) {
                            $descripcion = "Se asignó el pedido {$folio} al repartidor {$repartidor}.";
                        } elseif ($folio) {
                            $descripcion = "Se asignó el pedido {$folio} a un repartidor.";
                        } else {
                            $descripcion = "Se asignó un pedido a un repartidor.";
                        }
                        break;

                    case 'cancelado':
                        $descripcion = $folio
                            ? "Se canceló el pedido {$folio}."
                            : "Se canceló un pedido.";
                        break;

                    default:
                        $descripcion = $folio
                            ? "Movimiento en el pedido {$folio}."
                            : "Movimiento en el módulo de pedidos.";
                        break;
                }
            } else {
                // Otros módulos: si algún día los usamos
                $descripcion = $log->descripcion
                    ?: 'Movimiento registrado en la bitácora.';
            }

            return [
                'id'          => (int) $log->id,
                'fecha'       => optional($log->created_at)->format('d/m/Y H:i'),
                'modulo'      => $log->modulo ?? '-',
                'descripcion' => $descripcion ?? '—',
            ];
        });

        return Inertia::render('Vendedor/Bitacora/Index', [
            'registros' => $logs,
        ]);
    }
}
