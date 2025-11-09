<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Soportar 'role:2' o 'role:1,2,3'
        $allowed = collect($roles)
            ->flatMap(function ($r) {
                return collect(explode(',', (string)$r));
            })
            ->map(fn ($v) => (string) trim($v))
            ->filter()
            ->values();

        if ($allowed->isEmpty()) {
            abort(403);
        }

        if (!$allowed->contains((string) $user->role_id)) {
            abort(403, 'No autorizado para este módulo.');
        }

        return $next($request);
    }
}
