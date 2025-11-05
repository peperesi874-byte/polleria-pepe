<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Controladores públicos
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ProductoController;

// Controladores Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\ReportesController;
use App\Http\Controllers\Admin\BitacoraController;

// Perfil (Breeze)
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('catalogo.index'));
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');

// CRUD de productos (vista tipo “admin ligero” / pública autenticada si así lo decides)
Route::resource('productos', ProductoController::class)->names('productos');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Importante: ruta de auth
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Redirección por rol
|--------------------------------------------------------------------------
*/
Route::get('/redirect-by-role', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('catalogo.index');

    $roleId = (int)($user->role_id ?? 0);
    return match ($roleId) {
        1       => redirect()->route('admin.dashboard'),
        2, 3, 4 => redirect()->route('catalogo.index'),
        default => redirect()->route('catalogo.index'),
    };
})->name('redirect.by.role');

/*
|--------------------------------------------------------------------------
| ADMIN (role_id = 1)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:1']) // asegúrate de tener este middleware
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Centro de reportes (vista con tarjetas)
        Route::get('/reportes', fn () => Inertia::render('Admin/Reportes/Index'))
            ->name('reportes.index');

        // Usuarios
        Route::controller(UserController::class)->group(function () {
            Route::get('/usuarios', 'index')->name('usuarios.index');
            Route::get('/usuarios/create', 'create')->name('usuarios.create');
            Route::post('/usuarios', 'store')->name('usuarios.store');
            Route::get('/usuarios/{user}/edit', 'edit')->name('usuarios.edit');
            Route::put('/usuarios/{user}', 'update')->name('usuarios.update');
            Route::delete('/usuarios/{user}', 'destroy')->name('usuarios.destroy');
        });

        // Pedidos (listado + detalle)
        Route::resource('pedidos', AdminPedidoController::class)
            ->only(['index', 'show'])
            ->names('pedidos');

        // Acciones de pedidos
        Route::put('/pedidos/{pedido}/estado',   [AdminPedidoController::class, 'setEstado'])
            ->name('pedidos.estado');
        Route::put('/pedidos/{pedido}/cancelar', [AdminPedidoController::class, 'cancelar'])
            ->name('pedidos.cancelar');
        Route::put('/pedidos/{pedido}/asignar',  [AdminPedidoController::class, 'asignar'])
            ->name('pedidos.asignar');

        // Inventario (lista + movimientos + mínimo)
        Route::get('/inventario', [InventarioController::class, 'index'])
            ->name('inventario.index');
        Route::post('/inventario/movimiento', [InventarioController::class, 'storeMovimiento'])
            ->name('inventario.movimiento');
        Route::put('/inventario/{producto}/minimo', [InventarioController::class, 'updateMinimo'])
            ->name('inventario.minimo');

        // Historial por producto
        Route::get('/inventario/{producto}/movimientos', [InventarioController::class, 'movimientos'])
            ->name('inventario.movimientos');

        // Exportaciones
        Route::get('/inventario/historial/export/csv', [InventarioController::class, 'exportCSV'])
            ->name('inventario.historial.csv');

        Route::get('/inventario/export/pdf', [InventarioController::class, 'exportInventarioPDF'])
            ->name('inventario.export.pdf');

        Route::get('/inventario/export/csv', [InventarioController::class, 'exportInventarioCSV'])
            ->name('inventario.export.csv');

        Route::get('/reportes/productos/export/csv', [ReportesController::class, 'exportProductosCSV'])
            ->name('reportes.productos.csv');

        // Bitácora
        Route::get('/bitacora', [BitacoraController::class, 'index'])
            ->name('bitacora.index');
    });
