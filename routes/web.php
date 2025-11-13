<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Público / catálogo
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ProductoController;

// Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\ReportesController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\NotificacionesController;
use App\Http\Controllers\Admin\ConfiguracionController;

// Cliente
use App\Http\Controllers\ClientePedidoController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClientePerfilController;
use App\Http\Controllers\HomeRedirectController;
use App\Http\Controllers\Cliente\CheckoutController;

use App\Http\Controllers\ProfileController;

// Vendedor
use App\Http\Controllers\Vendedor\DashboardController as VendedorDashboardController;
use App\Http\Controllers\Vendedor\ReportesOperativosController;
use App\Http\Controllers\Vendedor\ClienteRapidoController;
use App\Http\Controllers\Vendedor\PedidoCrearController;
use App\Http\Controllers\Vendedor\PedidoTicketController as VendedorPedidoTicketController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('catalogo.index'));

Route::get('/catalogo', [CatalogoController::class, 'index'])
    ->name('catalogo.index');

/* CRUD de productos (público solo para pruebas) */
Route::resource('productos', ProductoController::class)->names('productos');

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN (Laravel Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
 require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🔹 Ruta para redirigir según rol
Route::get('/redirect-by-role', [HomeRedirectController::class, '__invoke'])
    ->middleware('auth')
    ->name('redirect.by.role');


Route::get('/redirect-by-role', HomeRedirectController::class)
    ->middleware('auth')
    ->name('redirect.by.role');

/*
|--------------------------------------------------------------------------
| POST-LOGIN (redirige por rol)
|--------------------------------------------------------------------------
*/
Route::get('/home', HomeRedirectController::class)
    ->middleware('auth')
    ->name('home');

/*
|--------------------------------------------------------------------------
| PANEL ADMIN (role_id = 1)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:1'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Usuarios
        Route::controller(UserController::class)->group(function () {
            Route::get('/usuarios', 'index')->name('usuarios.index');
            Route::get('/usuarios/create', 'create')->name('usuarios.create');
            Route::post('/usuarios', 'store')->name('usuarios.store');
            Route::get('/usuarios/{user}/edit', 'edit')->name('usuarios.edit');
            Route::put('/usuarios/{user}', 'update')->name('usuarios.update');
            Route::delete('/usuarios/{user}', 'destroy')->name('usuarios.destroy');
        });

        // Módulos admin
        Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
        Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('/notificaciones', [NotificacionesController::class, 'index'])->name('notificaciones.index');

        // Inventario
        Route::controller(InventarioController::class)->group(function () {
            Route::get('/inventario', 'index')->name('inventario.index');
            Route::get('/inventario/movimientos', 'movimientos')->name('inventario.movimientos');
            Route::get('/inventario/historial', 'historial')->name('inventario.historial');
            Route::get('/inventario/pdf', 'reportePdf')->name('inventario.pdf'); // si existe
        });

        // Pedidos
        Route::controller(AdminPedidoController::class)->group(function () {
            Route::get('/pedidos', 'index')->name('pedidos.index');
            Route::get('/pedidos/{pedido}', 'show')->name('pedidos.show');
            // agrega aquí setEstado/asignaciones si existen
        });

        // Configuración
        Route::controller(ConfiguracionController::class)->group(function () {
            Route::get('/configuracion', 'edit')->name('config.edit');
            Route::put('/configuracion', 'update')->name('config.update');
        });
    });

/*
|--------------------------------------------------------------------------
| PANEL VENDEDOR (role_id = 2)
|--------------------------------------------------------------------------
*/
Route::prefix('vendedor')
    ->as('vendedor.')
    ->middleware(['auth', 'role:2'])
    ->group(function () {
        // Panel principal del vendedor
        Route::get('/dashboard', [VendedorDashboardController::class, 'index'])->name('dashboard');

        // Reporte operativo
        Route::get('/reportes/operativos', [ReportesOperativosController::class, 'index'])
            ->name('reportes.operativos');

        // Alta rápida de cliente (desde el form de pedido)
        Route::post('/clientes/rapido', [ClienteRapidoController::class, 'store'])
            ->name('clientes.rapido.store');

        // Crear pedido en mostrador
        Route::get('/pedidos/create', [PedidoCrearController::class, 'create'])->name('pedidos.create');
        Route::post('/pedidos', [PedidoCrearController::class, 'store'])->name('pedidos.store');

        // Listado general de pedidos (usa mismo controlador que admin)
        Route::get('/pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');

        // Detalle y acciones sobre pedidos
        Route::get('/pedidos/{pedido}', [AdminPedidoController::class, 'show'])
            ->whereNumber('pedido')->name('pedidos.show');

        Route::put('/pedidos/{pedido}/estado', [AdminPedidoController::class, 'setEstado'])
            ->whereNumber('pedido')->name('pedidos.estado');

        Route::put('/pedidos/{pedido}/cancelar', [AdminPedidoController::class, 'cancelar'])
            ->whereNumber('pedido')->name('pedidos.cancelar');

        Route::put('/pedidos/{pedido}/asignar', [AdminPedidoController::class, 'asignar'])
            ->whereNumber('pedido')->name('pedidos.asignar');

        // Ticket del pedido
        Route::get('/pedidos/{pedido}/ticket', [VendedorPedidoTicketController::class, 'show'])
            ->whereNumber('pedido')->name('pedidos.ticket');
    });

/*
|--------------------------------------------------------------------------
| CLIENTE (role_id = 4)
|--------------------------------------------------------------------------
*/
Route::prefix('cliente')
    ->as('cliente.')
    ->middleware(['auth', 'role:4'])
    ->group(function () {
        // Inicio
        Route::get('/', fn () => Inertia::render('Cliente/Inicio'))->name('inicio');

        // Catálogo
        Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');

        // Pedidos
        Route::get('/pedidos', [ClientePedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{id}', fn ($id) => Inertia::render('Cliente/Pedidos/Show', ['id' => $id]))
            ->name('pedidos.show');

        // Carrito
        Route::prefix('carrito')->as('carrito.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::post('/update', [CartController::class, 'update'])->name('update');
            Route::post('/remove', [CartController::class, 'remove'])->name('remove');
            Route::post('/clear', [CartController::class, 'clear'])->name('clear');
        });

        // Checkout
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // Confirmación de pedido
        Route::get('/checkout/confirmacion/{pedido}', [CheckoutController::class, 'confirmacion'])
            ->name('checkout.confirmacion');

        // Perfil
        Route::get('/perfil',  [ClientePerfilController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil',  [ClientePerfilController::class, 'update'])->name('perfil.update');
    });
