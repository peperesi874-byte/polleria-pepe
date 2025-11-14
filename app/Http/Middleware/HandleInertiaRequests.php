<?php

namespace App\Http\Middleware;

use App\Models\Notificacion;
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

            // 📩 Notificaciones (contador global para la campanita)
            'notificaciones' => function () {
                if (!Auth::check()) {
                    return [
                        'unread_count' => 0,
                    ];
                }

                $userId = Auth::id();

                return [
                    'unread_count' => Notificacion::where('user_id', $userId)
                        ->where('leida', false)
                        ->count(),
                ];
            },
        ]);
    }
}
