<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Vista raíz que se carga en la primera visita.
     */
    protected $rootView = 'app'; // puede ser public|protected; uso protected (como el stub de Laravel)

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
            // Autenticación (lo que ya tenías)
            'auth' => [
                'user' => function () use ($request) {
                    $u = $request->user();
                    if (!$u) return null;

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

            // ✅ Mensajes flash para que Vue los lea como $page.props.flash.success / error
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
