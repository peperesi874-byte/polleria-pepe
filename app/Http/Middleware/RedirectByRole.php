<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectByRole
{
    /**
     * Redirige al usuario según su rol al iniciar sesión.
     * 
     * Roles esperados:
     * 1 = Administrador
     * 2 = Vendedor
     * 3 = Repartidor
     * 4 = Cliente (por defecto)
     */
    public function handle(Request $request, Closure $next)
    {
        // Si no está autenticado, sigue el flujo normal
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $roleId = (int)($user->role_id ?? 0);

        // Si ya está dentro de su área, continúa normalmente
        $current = $request->path();
        if (
            str_starts_with($current, 'admin') ||
            str_starts_with($current, 'vendedor') ||
            str_starts_with($current, 'repartidor') ||
            str_starts_with($current, 'catalogo')
        ) {
            return $next($request);
        }

        // Redirección según el rol
        switch ($roleId) {
            case 1: // 🧩 Administrador
                return redirect()->route('admin.dashboard');
            case 2: // 💼 Vendedor
                return redirect()->route('vendedor.dashboard');
            case 3: // 🚚 Repartidor
                // Cuando se cree su panel
                return redirect()->route('catalogo.index');
            case 4: // 👤 Cliente
            default:
                return redirect()->route('catalogo.index');
        }
    }
}
