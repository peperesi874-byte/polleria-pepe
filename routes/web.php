<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProductoController;

Route::get('/', fn () => redirect()->route('catalogo.index'));

Route::get('/catalogo', [CatalogoController::class, 'index'])
    ->name('catalogo.index');

// auth scaffolding (Breeze)
require __DIR__ . '/auth.php';

// ---------------------- ADMIN ----------------------
Route::prefix('admin')->as('admin.')->middleware(['auth', 'role:1'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/reportes', fn () => Inertia::render('Admin/Reportes/Index'))
        ->name('reportes.index');

    Route::controller(UserController::class)->group(function () {
        Route::get('/usuarios', 'index')->name('usuarios.index');
        Route::get('/usuarios/create', 'create')->name('usuarios.create');
        Route::post('/usuarios', 'store')->name('usuarios.store');
        Route::get('/usuarios/{user}/edit', 'edit')->name('usuarios.edit');
        Route::put('/usuarios/{user}', 'update')->name('usuarios.update');
        Route::delete('/usuarios/{user}', 'destroy')->name('usuarios.destroy');
    });

    // IMPORTANTE: deja que el prefijo admin. nombre las rutas
    Route::resource('productos', ProductoController::class)
        ->parameters(['productos' => 'producto']);
});
require __DIR__.'/auth.php';
