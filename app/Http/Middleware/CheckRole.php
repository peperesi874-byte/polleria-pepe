<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$ids)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        // $ids vienen como ['1','2',...] desde 'role:1,2'
        if (!in_array((string) $user->role_id, array_map('strval', $ids), true)) {
            abort(403);
        }

        return $next($request);
    }
}
