<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesOperativosController extends Controller
{
    public function index(Request $request)
    {
        // Por defecto HOY. También acepta ?fecha=YYYY-MM-DD
        $fecha = $request->query('fecha', now()->toDateString());

        // Base: pedidos del día seleccionado
        $base = Pedido::query()->whereDate('created_at', $fecha);

        // Conteos por estado (del día)
        $pendientes  = (clone $base)->where('estado', 'pendiente')->count();
        $preparando  = (clone $base)->where('estado', 'preparando')->count();
        $listo       = (clone $base)->where('estado', 'listo')->count();
        $enCamino    = (clone $base)->where('estado', 'en_camino')->count();
        $entregados  = (clone $base)->where('estado', 'entregado')->count();
        $cancelados  = (clone $base)->where('estado', 'cancelado')->count();

        // Totales del día
        $totalPedidos = (clone $base)->count();
        $montoDelDia  = (clone $base)->sum('total');

        // ⚠️ SIN LÍMITE: todos los pedidos del día sin repartidor
        $sinAsignar = (clone $base)
            ->whereNull('asignado_a')
            ->orderByDesc('id')
            ->get(['id','folio','estado','total','created_at']);

        return Inertia::render('Vendedor/Reportes/Operativos', [
            'fecha'   => $fecha,
            'totales' => [
                'total_hoy'   => (int) $totalPedidos,
                'monto_hoy'   => (float) $montoDelDia,
                'pendiente'   => (int) $pendientes,
                'preparando'  => (int) $preparando,
                'listo'       => (int) $listo,
                'en_camino'   => (int) $enCamino,
                'entregado'   => (int) $entregados,
                'cancelado'   => (int) $cancelados,
                'sin_asignar' => (int) $sinAsignar->count(),
            ],
            'sin_asignar' => $sinAsignar->map(fn ($p) => [
                'id'         => (int) $p->id,
                'folio'      => (string) ($p->folio ?? '—'),
                'estado'     => (string) $p->estado,
                'total'      => (float) $p->total,
                'created_at' => optional($p->created_at)->format('Y-m-d H:i'),
            ]),
        ]);
    }
}
