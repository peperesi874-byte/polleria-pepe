<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $hoy  = now()->toDateString();

        // Base: pedidos asignados a este repartidor
        $base = Pedido::query()->where('asignado_a', $user->id);

        // Stats rápidas
        $stats = [
            'asignados'      => (clone $base)->count(),
            'pendientes'     => (clone $base)
                ->whereIn('estado', ['pendiente', 'confirmado', 'en_preparacion', 'listo'])
                ->count(),
            'en_camino'      => (clone $base)
                ->whereIn('estado', ['en_camino', 'en_reparto'])
                ->count(),
            'entregados_hoy' => (clone $base)
                ->where('estado', 'entregado')
                ->whereDate('updated_at', $hoy)
                ->count(),
        ];

        // Próximos por salir (los más urgentes)
        $proximos = (clone $base)
            ->whereIn('estado', ['pendiente', 'confirmado', 'en_preparacion', 'listo'])
            ->orderBy('created_at')
            ->limit(5)
            ->get([
                'id',
                'folio',
                'estado',
                'total',
                'entrega_colonia',
                'entrega_municipio',
                'created_at',
            ]);

        // En ruta actualmente
        $enRuta = (clone $base)
            ->whereIn('estado', ['en_camino', 'en_reparto'])
            ->orderBy('created_at')
            ->limit(5)
            ->get([
                'id',
                'folio',
                'estado',
                'total',
                'entrega_colonia',
                'entrega_municipio',
                'created_at',
            ]);

        // Historial rápido (entregados / cancelados)
        $historial = (clone $base)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get([
                'id',
                'folio',
                'estado',
                'total',
                'created_at',
                'updated_at',
            ]);

        return Inertia::render('Repartidor/Dashboard', [
            'stats'     => $stats,
            'proximos'  => $proximos,
            'enRuta'    => $enRuta,
            'historial' => $historial,
        ]);
    }
}
