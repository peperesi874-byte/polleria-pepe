<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ReportesOperativosController extends Controller
{
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $fecha = $request->input('fecha', now()->toDateString());

        // Nombres "por defecto" de columnas
        $colEstado     = 'estado';        // pendiente, en_camino, entregado, cancelado
        $colTipoVenta  = 'tipo_pedido';   // mostrador / domicilio (si existe)
        $colTotal      = 'total';         // importe total del pedido

        // Base: pedidos del día seleccionado
        $base = Pedido::query()
            ->whereDate('created_at', $fecha);

        // 🔍 Filtro por vendedor SOLO si existe columna adecuada
        if (Schema::hasColumn('pedidos', 'user_id')) {
            $base->where('user_id', $user->id);
        } elseif (Schema::hasColumn('pedidos', 'vendedor_id')) {
            $base->where('vendedor_id', $user->id);
        }

        // Helper para clonar la consulta con todos los filtros
        $q = fn () => (clone $base);

        // --- Conteos generales ---
        $totalPedidos = $q()->count();

        // --- Mostrador / Domicilio (solo si existe columna tipo_pedido) ---
        $totalMostrador = 0;
        $totalDomicilio = 0;

        if (Schema::hasColumn('pedidos', $colTipoVenta)) {
            $totalMostrador = $q()->where($colTipoVenta, 'mostrador')->count();
            $totalDomicilio = $q()->where($colTipoVenta, 'domicilio')->count();
        }

        // --- Estados (si no existiera la columna, quedarán en 0 pero no truena) ---
        $pendientes = Schema::hasColumn('pedidos', $colEstado)
            ? $q()->where($colEstado, 'pendiente')->count()
            : 0;

        $enCamino = Schema::hasColumn('pedidos', $colEstado)
            ? $q()->where($colEstado, 'en_camino')->count()
            : 0;

        $entregados = Schema::hasColumn('pedidos', $colEstado)
            ? $q()->where($colEstado, 'entregado')->count()
            : 0;

        $cancelados = Schema::hasColumn('pedidos', $colEstado)
            ? $q()->where($colEstado, 'cancelado')->count()
            : 0;

        // --- Importe total / ticket promedio (solo si existe columna total) ---
        $totalImporte = Schema::hasColumn('pedidos', $colTotal)
            ? (float) $q()->sum($colTotal)
            : 0;

        $promedioTicket = $totalPedidos > 0 ? $totalImporte / $totalPedidos : 0;

        $resumen = [
            'total_pedidos'    => $totalPedidos,
            'total_mostrador'  => $totalMostrador,
            'total_domicilio'  => $totalDomicilio,
            'pendientes'       => $pendientes,
            'en_camino'        => $enCamino,
            'entregados'       => $entregados,
            'cancelados'       => $cancelados,
            'total_importe'    => $totalImporte,
            'promedio_ticket'  => $promedioTicket,
        ];

        return Inertia::render('Vendedor/Reportes/Operativos', [
            'fecha'   => $fecha,
            'resumen' => $resumen,
        ]);
    }
}
