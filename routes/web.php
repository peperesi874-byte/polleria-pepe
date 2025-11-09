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
use App\Http\Controllers\Admin\NotificacionesController;
use App\Http\Controllers\Admin\ConfiguracionController;

// Controladores Vendedor
use App\Http\Controllers\Vendedor\DashboardController as VendedorDashboardController;
use App\Http\Controllers\Vendedor\ReportesOperativosController;
use App\Http\Controllers\Vendedor\ClienteRapidoController;
use App\Http\Controllers\Vendedor\PedidoCrearController;

// Perfil (Breeze)
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('catalogo.index'));
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::resource('productos', ProductoController::class)->names('productos');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth scaffolding (Breeze)
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
        2       => redirect()->route('vendedor.dashboard'),
        3, 4    => redirect()->route('catalogo.index'),
        default => redirect()->route('catalogo.index'),
    };
})->name('redirect.by.role');

// Redirige /dashboard al panel correcto
Route::middleware(['auth', 'verified'])
    ->get('/dashboard', fn () => redirect()->route('redirect.by.role'))
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN (role_id = 1)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'verified', 'role:1'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/reportes', fn () => Inertia::render('Admin/Reportes/Index'))->name('reportes.index');

        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('config.edit');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('config.update');

        Route::controller(UserController::class)->group(function () {
            Route::get('/usuarios', 'index')->name('usuarios.index');
            Route::get('/usuarios/create', 'create')->name('usuarios.create');
            Route::post('/usuarios', 'store')->name('usuarios.store');
            Route::get('/usuarios/{user}/edit', 'edit')->name('usuarios.edit');
            Route::put('/usuarios/{user}', 'update')->name('usuarios.update');
            Route::delete('/usuarios/{user}', 'destroy')->name('usuarios.destroy');
        });

        // Pedidos (admin)
        Route::resource('pedidos', AdminPedidoController::class)
            ->only(['index', 'show'])
            ->names('pedidos');

        Route::put('/pedidos/{pedido}/estado', [AdminPedidoController::class, 'setEstado'])->name('pedidos.estado');
        Route::put('/pedidos/{pedido}/cancelar', [AdminPedidoController::class, 'cancelar'])->name('pedidos.cancelar');
        Route::put('/pedidos/{pedido}/asignar', [AdminPedidoController::class, 'asignar'])->name('pedidos.asignar');

        // Inventario
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::post('/inventario/movimiento', [InventarioController::class, 'storeMovimiento'])->name('inventario.movimiento');
        Route::put('/inventario/{producto}/minimo', [InventarioController::class, 'updateMinimo'])->name('inventario.minimo');
        Route::get('/inventario/{producto}/movimientos', [InventarioController::class, 'movimientos'])->name('inventario.movimientos');

        // Exportaciones / reportes admin
        Route::get('/inventario/export/pdf', [InventarioController::class, 'exportInventarioPDF'])->name('inventario.export.pdf');
        Route::get('/inventario/export/csv', [InventarioController::class, 'exportInventarioCSV'])->name('inventario.export.csv');
        Route::get('/reportes/productos/export/csv', [ReportesController::class, 'exportProductosCSV'])->name('reportes.productos.csv');

        // Bitácora + notificaciones
        Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('/notificaciones', [NotificacionesController::class, 'index'])->name('notificaciones.index');
        Route::patch('/notificaciones/{n}/leer', [NotificacionesController::class, 'markRead'])->name('notificaciones.read');
        Route::patch('/notificaciones/leer-todas', [NotificacionesController::class, 'markAllRead'])->name('notificaciones.read_all');
        // dentro del grupo admin
Route::get(
    '/inventario/movimientos/export/csv',
    [InventarioController::class, 'exportMovimientosCSV']
)->name('inventario.historial.csv');

    });

/*
|--------------------------------------------------------------------------
| VENDEDOR (role_id = 2)
|--------------------------------------------------------------------------
*/
Route::prefix('vendedor')
    ->as('vendedor.')
    ->middleware(['auth', 'verified', 'role:2'])
    ->group(function () {

        // Panel principal del vendedor
        Route::get('/dashboard', [VendedorDashboardController::class, 'index'])->name('dashboard');

        // Reporte operativo
        Route::get('/reportes/operativos', [ReportesOperativosController::class, 'index'])->name('reportes.operativos');

        // Alta rápida de cliente (desde el form de pedido)
        Route::post('/clientes/rapido', [ClienteRapidoController::class, 'store'])->name('clientes.rapido.store');

        /*
        |--------------------------------------------------------------------------
        | RUTAS DE PEDIDOS
        |--------------------------------------------------------------------------
        */

        // ⚠️ Primero las rutas estáticas de creación
        Route::get('/pedidos/create', [PedidoCrearController::class, 'create'])->name('pedidos.create');
        Route::post('/pedidos', [PedidoCrearController::class, 'store'])->name('pedidos.store');

        // Listado general de pedidos
        Route::get('/pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');

        // Detalle y acciones (solo IDs numéricos)
        Route::get('/pedidos/{pedido}', [AdminPedidoController::class, 'show'])
            ->whereNumber('pedido')->name('pedidos.show');

        Route::put('/pedidos/{pedido}/estado', [AdminPedidoController::class, 'setEstado'])
            ->whereNumber('pedido')->name('pedidos.estado');

        Route::put('/pedidos/{pedido}/cancelar', [AdminPedidoController::class, 'cancelar'])
            ->whereNumber('pedido')->name('pedidos.cancelar');

        Route::put('/pedidos/{pedido}/asignar', [AdminPedidoController::class, 'asignar'])
            ->whereNumber('pedido')->name('pedidos.asignar');
    });
