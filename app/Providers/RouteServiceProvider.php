<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RedirectByRole;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ Fuerza que el router conozca los alias siempre
        Route::aliasMiddleware('role', CheckRole::class);
        Route::aliasMiddleware('redirect.role', RedirectByRole::class);

        parent::boot();
    }
}
