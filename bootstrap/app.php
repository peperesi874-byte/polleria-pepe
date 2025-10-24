<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RedirectByRole;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
  $middleware->alias([
        'role'          => CheckRole::class,
        'redirect.role' => RedirectByRole::class,
    ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
