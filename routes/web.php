<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Público
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

// Vendedor
use App\Http\Controllers\Vendedor\DashboardController as VendedorDashboardController;
use App\Http\Controllers\Vendedor\ReportesOperativosController;
use App\Http\Controllers\Vendedor\ClienteRapidoController;
use App\Http\Controllers\Vendedor\PedidoCrearController;
use App\Http\Controllers\Vendedor\PedidoTicketController as VendedorPedidoTicketController;
use App\Http\Controllers\Vendedor\NotificacionesController as VendedorNotificacionesController;
use App\Http\Controllers\Vendedor\BitacoraController as VendedorBitacoraController;

// Repartidor
use App\Http\Controllers\Repartidor\DashboardController as RepartidorDashboardController;
use App\Http\Controllers\Repartidor\PedidosController as RepartidorPedidosController;
use App\Http\Controllers\Repartidor\NotificacionesController as RepartidorNotificacionesController;


// Cliente
use App\Http\Controllers\ClientePedidoController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClientePerfilController;
use App\Http\Controllers\Cliente\CheckoutController; 
use App\Http\Controllers\HomeRedirectController;




// Perfil (Auth Breeze)
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('catalogo.index'));

Route::get('/catalogo', [CatalogoController::class, 'index'])
    ->name('catalogo.index');

Route::resource('productos', ProductoController::class)
    ->names('productos');

    /*
|--------------------------------------------------------------------------
| PÁGINAS LEGALES (PÚBLICAS)
|--------------------------------------------------------------------------
*/
Route::prefix('legal')
    ->as('legal.')
    ->group(function () {
        // Términos y condiciones
        Route::inertia('/terminos', 'Legal/Terminos')
            ->name('terminos');

        // Aviso de privacidad
        Route::inertia('/privacidad', 'Legal/Privacidad')
            ->name('privacidad');
    });

/*
|--------------------------------------------------------------------------
| PERFIL (requiere login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Auth scaffolding Breeze
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| REDIRECCIÓN POR ROL
|--------------------------------------------------------------------------
*/
Route::get('/redirect-by-role', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('catalogo.index');

    $role = (int) ($user->role_id ?? 0);

    return match ($role) {
        1       => redirect()->route('admin.dashboard'),
        2       => redirect()->route('vendedor.dashboard'),
        3       => redirect()->route('repartidor.dashboard'),
        4       => redirect()->route('cliente.inicio'),
        default => redirect()->route('catalogo.index'),
    };
})->name('redirect.by.role');

Route::middleware(['auth', 'verified'])
    ->get('/dashboard', fn () => redirect()->route('redirect.by.role'))
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| PANEL ADMIN (role_id = 1)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:1'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Reportes (vista)
        Route::get('/reportes', fn () => Inertia::render('Admin/Reportes/Index'))
            ->name('reportes.index');

        // Configuración
        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])
            ->name('config.edit');

        Route::put('/configuracion', [ConfiguracionController::class, 'update'])
            ->name('config.update');

        /*
        | USERS
        */
        Route::controller(UserController::class)->group(function () {
            Route::get('/usuarios', 'index')->name('usuarios.index');
            Route::get('/usuarios/create', 'create')->name('usuarios.create');
            Route::post('/usuarios', 'store')->name('usuarios.store');
            Route::get('/usuarios/{user}/edit', 'edit')->name('usuarios.edit');
            Route::put('/usuarios/{user}', 'update')->name('usuarios.update');
            Route::delete('/usuarios/{user}', 'destroy')->name('usuarios.destroy');
        });

        // Reporte: Ingresos de ventas (solo pedidos entregados)
Route::get('/reportes/ingresos', [ReportesController::class, 'ingresos'])
    ->name('reportes.ingresos');

Route::get('/reportes/ingresos/export/csv', [ReportesController::class, 'exportIngresosCsv'])
    ->name('reportes.ingresos.csv');


        /*
        | REPORTES (CATÁLOGO, ETC.)
        */
        Route::get('/reportes/productos/export/csv', [ReportesController::class, 'exportProductosCSV'])
            ->name('reportes.productos.csv');

        /*
        | INVENTARIO
        */
        Route::get('/inventario', [InventarioController::class, 'index'])
            ->name('inventario.index');

        Route::post('/inventario/movimiento', [InventarioController::class, 'storeMovimiento'])
            ->name('inventario.movimiento');

        Route::put('/inventario/{producto}/minimo', [InventarioController::class, 'updateMinimo'])
            ->name('inventario.minimo');

        Route::get('/inventario/{producto}/movimientos', [InventarioController::class, 'movimientos'])
            ->name('inventario.movimientos');

        Route::get('/inventario/export/pdf', [InventarioController::class, 'exportInventarioPDF'])
            ->name('inventario.export.pdf');

        Route::get('/inventario/export/csv', [InventarioController::class, 'exportInventarioCSV'])
            ->name('inventario.export.csv');

        Route::get('/inventario/movimientos/export/csv', [InventarioController::class, 'exportCSV'])
            ->name('inventario.historial.csv');

        /*
        | PEDIDOS
        */
        Route::resource('pedidos', AdminPedidoController::class)
            ->only(['index', 'show'])
            ->names('pedidos');

        Route::post('/pedidos', [AdminPedidoController::class, 'store'])
            ->name('pedidos.store');

        Route::put('/pedidos/{pedido}/estado', [AdminPedidoController::class, 'setEstado'])
            ->name('pedidos.estado');

        Route::put('/pedidos/{pedido}/cancelar', [AdminPedidoController::class, 'cancelar'])
            ->name('pedidos.cancelar');

        Route::put('/pedidos/{pedido}/asignar', [AdminPedidoController::class, 'asignar'])
            ->name('pedidos.asignar');

        Route::get('/pedidos/{pedido}/ticket', [VendedorPedidoTicketController::class, 'show'])
            ->name('pedidos.ticket');

        /*
        | BITÁCORA + NOTIFICACIONES
        */
        Route::get('/bitacora', [BitacoraController::class, 'index'])
            ->name('bitacora.index');

        Route::get('/notificaciones', [NotificacionesController::class, 'index'])
            ->name('notificaciones.index');

        Route::patch('/notificaciones/{n}/leer', [NotificacionesController::class, 'markRead'])
            ->name('notificaciones.read');

        Route::patch('/notificaciones/leer-todas', [NotificacionesController::class, 'markAllRead'])
            ->name('notificaciones.read_all');

        Route::delete('/notificaciones/leidas', [NotificacionesController::class, 'deleteRead'])
            ->name('notificaciones.delete_read');

        Route::delete('/notificaciones', [NotificacionesController::class, 'deleteAll'])
            ->name('notificaciones.delete_all');
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
        Route::get('/dashboard', [VendedorDashboardController::class, 'index'])
            ->name('dashboard');

        // Reporte operativo
        Route::get('/reportes/operativos', [ReportesOperativosController::class, 'index'])
            ->name('reportes.operativos');

        // Alta rápida de cliente (desde el form de pedido)
        Route::post('/clientes/rapido', [ClienteRapidoController::class, 'store'])
            ->name('clientes.rapido.store');

        /*
        | NOTIFICACIONES
        */
        Route::get('/notificaciones', [VendedorNotificacionesController::class, 'index'])
            ->name('notificaciones.index');

        Route::post('/notificaciones/{notificacion}/leer', [VendedorNotificacionesController::class, 'marcarLeida'])
            ->name('notificaciones.leer');

        Route::post('/notificaciones/leer-todas', [VendedorNotificacionesController::class, 'marcarTodasLeidas'])
            ->name('notificaciones.leer_todas');

        /*
        | BITÁCORA DEL VENDEDOR
        */
        Route::get('/bitacora', [VendedorBitacoraController::class, 'index'])
            ->name('bitacora.index');

        /*
        | PEDIDOS DEL VENDEDOR
        */
        // Crear pedido en mostrador
        Route::get('/pedidos/create', [PedidoCrearController::class, 'create'])
            ->name('pedidos.create');

        Route::post('/pedidos', [PedidoCrearController::class, 'store'])
            ->name('pedidos.store');

        // Listado general de pedidos (usa mismo controlador que admin)
        Route::get('/pedidos', [AdminPedidoController::class, 'index'])
            ->name('pedidos.index');

        // Detalle y acciones sobre pedidos (usa mismo controlador del admin)
        Route::get('/pedidos/{pedido}', [AdminPedidoController::class, 'show'])
            ->name('pedidos.show');

        Route::put('/pedidos/{pedido}/estado', [AdminPedidoController::class, 'setEstado'])
            ->name('pedidos.estado');

        Route::put('/pedidos/{pedido}/cancelar', [AdminPedidoController::class, 'cancelar'])
            ->name('pedidos.cancelar');

        Route::put('/pedidos/{pedido}/asignar', [AdminPedidoController::class, 'asignar'])
            ->name('pedidos.asignar');

        // Ticket del pedido
        Route::get('/pedidos/{pedido}/ticket', [VendedorPedidoTicketController::class, 'show'])
            ->name('pedidos.ticket');
    });

/*
|--------------------------------------------------------------------------
| PANEL REPARTIDOR (role_id = 3)
|--------------------------------------------------------------------------
*/
Route::prefix('repartidor')
    ->as('repartidor.')
    ->middleware(['auth', 'role:3'])
    ->group(function () {

        // 🌟 Panel principal del repartidor
        Route::get('/dashboard', [RepartidorDashboardController::class, 'index'])
            ->name('dashboard');

        // 📦 Pedidos asignados al repartidor
        Route::get('/pedidos-asignados', [RepartidorPedidosController::class, 'index'])
            ->name('pedidos.index');

        // 🔄 Cambiar estado (en_reparto / entregado)
        Route::post('/pedidos/{pedido}/estado', [RepartidorPedidosController::class, 'cambiarEstado'])
            ->name('pedidos.cambiar-estado');

        // 🔔 Notificaciones del repartidor
        Route::get('/notificaciones', [RepartidorNotificacionesController::class, 'index'])
            ->name('notificaciones.index');
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

        // Inicio del panel cliente
        Route::get('/', fn () => Inertia::render('Cliente/Inicio'))
            ->name('inicio');

        // Catálogo (versión dentro del panel cliente)
        Route::get('/catalogo', [CatalogoController::class, 'index'])
            ->name('catalogo.index');

        // Pedidos del cliente
        Route::get('/pedidos', [ClientePedidoController::class, 'index'])
            ->name('pedidos.index');

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
        Route::get('/checkout', [CheckoutController::class, 'create'])
            ->name('checkout.create');

        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->name('checkout.store');

        // Confirmación de pedido
        Route::get('/checkout/confirmacion/{pedido}', [CheckoutController::class, 'confirmacion'])
            ->name('checkout.confirmacion');

        // Perfil del cliente
        Route::get('/perfil', [ClientePerfilController::class, 'edit'])
            ->name('perfil.edit');

        Route::put('/perfil', [ClientePerfilController::class, 'update'])
            ->name('perfil.update');

        // Ticket del pedido desde el panel del cliente (mismo ticket del vendedor)
        Route::get('/pedidos/{pedido}/ticket', [VendedorPedidoTicketController::class, 'show'])
            ->name('pedidos.ticket');

                  // 🔔 Notificaciones del cliente
        Route::get('/notificaciones', [\App\Http\Controllers\Cliente\NotificacionesController::class, 'index'])
            ->name('notificaciones.index');

              // 🔘 Marcar UNA notificación como leída
         Route::post(
              '/notificaciones/{id}/leer',
              [\App\Http\Controllers\Cliente\NotificacionesController::class, 'markAsRead']
              )->name('notificaciones.leer');


        // 🔘 (Opcional) Marcar TODAS como leídas
        Route::post(
            '/notificaciones/leer-todas',
            [\App\Http\Controllers\Cliente\NotificacionesController::class, 'markAllAsRead']
        )->name('notificaciones.leer_todas');

            Route::post('/cliente/pedidos/{pedido}/cancelar', [ClientePedidoController::class, 'cancelar'])
    ->name('cliente.pedidos.cancelar');

            Route::post('/pedidos/{pedido}/cancelar', [ClientePedidoController::class, 'cancelar'])
    ->name('pedidos.cancelar');



    });
