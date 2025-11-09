<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // 🔁 En vez de ir a "dashboard" fijo, mandamos a la ruta que
                // ya definiste para decidir según el rol:
                return redirect()->route('redirect.by.role');
            }
        }

        return $next($request);
    }
}
