<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectByRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Ajusta según tu esquema:
        // Asumo role_id: 1=Admin, 2=Vendedor, 3=Repartidor, 4=Cliente
        $roleId = $user->role_id
            ?? ($user->role->id ?? null);

        switch ($roleId) {
            case 1: // Administrador
                return redirect()->route('dashboard');
            case 2: // Vendedor
                return redirect()->route('ventas.index');
            case 3: // Repartidor
                return redirect()->route('entregas.index');
            case 4: // Cliente (default)
            default:
                return redirect()->route('catalogo.index');
        }
    }
}
