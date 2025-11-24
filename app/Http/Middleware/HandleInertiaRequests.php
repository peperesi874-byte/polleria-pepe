<?php

namespace App\Http\Middleware;

use App\Models\Notificacion;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Vista raíz que se carga en la primera visita.
     */
    protected $rootView = 'app';

    /**
     * Versión de los assets (opcional; útil para cache busting).
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props compartidos globalmente con Inertia.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [

            // 🔐 Autenticación
            'auth' => [
                'user' => function () use ($request) {
                    $u = $request->user();
                    if (!$u) {
                        return null;
                    }

                    return [
                        'id'      => $u->id,
                        'name'    => $u->name,
                        'email'   => $u->email,
                        'role_id' => (int) ($u->role_id ?? 0),
                        'role'    => [
                            'id'     => (int) ($u->role_id ?? 0),
                            'nombre' => $u->role->nombre ?? null,
                        ],
                    ];
                },
            ],

            // ✅ Mensajes flash
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            // 📩 Notificaciones / pedidos para la campanita (admin, vendedor, repartidor)
            'notificaciones' => function () use ($request) {
                $user = $request->user();

                if (!$user) {
                    return [
                        'unread_count'       => 0,
                        'repartidor_pedidos' => [],
                    ];
                }

                $role  = (int) ($user->role_id ?? 0);
                $base  = Notificacion::query();
                $pedidosRepartidor = [];

                // 🧑‍🍳 REPARTIDOR: notis relacionadas a sus pedidos + lista de pedidos asignados
                if ($role === 3) {
                    // Notificaciones de la tabla notificaciones
                    $base
                        ->whereIn('tipo', ['pedido.asignacion', 'pedido.estado', 'pedido.cancelado'])
                        ->where(function ($q) use ($user) {
                            $q->where('meta->repartidor_nuevo', $user->id)
                              ->orWhere('meta->repartidor_id', $user->id);
                        });

                    // Además, sus pedidos asignados (para la pestañita flotante)
                    $pedidosRepartidor = Pedido::query()
                        ->where('asignado_a', $user->id)
                        ->whereIn('estado', [
                            'pendiente',
                            'confirmado',
                            'en_preparacion',
                            'listo',
                            'en_camino',
                            'en_reparto',
                        ])
                        ->orderByDesc('created_at')
                        ->limit(5)
                        ->get([
                            'id',
                            'folio',
                            'estado',
                            'total',
                            'created_at',
                        ]);
                }
                // 🧑‍💼 ADMIN / VENDEDOR: por user_id como ya lo tenías
                elseif (in_array($role, [1, 2], true)) {
                    $base->where('user_id', $user->id);
                }
                // Cliente u otros: sin notificaciones globales por ahora
                else {
                    $base->whereRaw('1 = 0');
                }

                return [
                    'unread_count'       => (clone $base)
                        ->where('leida', false)
                        ->count(),
                    'repartidor_pedidos' => $pedidosRepartidor,
                ];
            },

            // 🔔 Notificaciones específicas para el PANEL DEL CLIENTE (campanita arriba)
            'notifications_cliente' => function () use ($request) {
                $u = $request->user();

                // Si no hay usuario logueado, no mandamos nada
                if (!$u) {
                    return null;
                }

                // 👇 Ligadas por user_id, igual que en Cliente/Notificaciones
                $baseQuery = Notificacion::query()
                    ->where('user_id', $u->id)
                    ->orderByDesc('created_at');

                // Últimas 5 notificaciones para el dropdown
                $items = (clone $baseQuery)
                    ->limit(5)
                    ->get([
                        'id',
                        'titulo',
                        'mensaje',
                        'leida',
                        'created_at',
                    ]);

                // Conteo de no leídas
                $unread = (clone $baseQuery)
                    ->where('leida', 0)
                    ->count();

                return [
                    'items'  => $items,
                    'unread' => $unread,
                ];
            },

            // 🌍 CONFIGURACIÓN GLOBAL DEL SISTEMA (horarios, zona de cobertura, umbral stock)
            'config_global' => function () {
                $cfg = \App\Services\ConfiguracionService::get();

                if (!$cfg) {
                    return null;
                }

                return [
                    'horario_apertura'  => $cfg->horario_apertura,
                    'horario_cierre'    => $cfg->horario_cierre,
                    'zona_cobertura'    => $cfg->zona_cobertura,
                    'umbral_stock_bajo' => $cfg->umbral_stock_bajo,
                    'is_open_now'       => \App\Services\ConfiguracionService::isOpenNow(),
                ];
            },

        ]);
    }
}
