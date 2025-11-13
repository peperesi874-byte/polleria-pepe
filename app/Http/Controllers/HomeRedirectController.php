<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeRedirectController extends Controller
{
    public function __invoke()
    {
        $u = Auth::user();

        // Si no hay usuario autenticado, redirige al login
        if (!$u) {
            return redirect()->route('login');
        }

        return match ($u->rol ?? $u->role ?? $u->role_id ?? 'cliente') {
            // 👑 Administrador o vendedor → dashboard de admin
            'admin', 'vendedor', 1 => redirect()->route('admin.dashboard'),

            // 🚚 Repartidor → su propio panel (si existe)
            'repartidor', 3        => redirect()->route('repartidor.inicio'),

            // 🧍‍♂️ Cliente → su panel principal
            'cliente', 4           => redirect()->route('cliente.inicio'),

            // 🔄 Cualquier otro → catálogo público
            default                => redirect()->route('catalogo.index'),
        };
    }
}
