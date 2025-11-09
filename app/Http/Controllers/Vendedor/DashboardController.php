<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Fecha actual (solo día)
        $hoy = now()->toDateString();

        // ✅ Contadores solo de pedidos creados hoy
        $counts = [
            'pedidos_hoy' => Pedido::whereDate('created_at', $hoy)->count(),
            'pendientes'  => Pedido::whereDate('created_at', $hoy)->where('estado', 'pendiente')->count(),
            'en_camino'   => Pedido::whereDate('created_at', $hoy)->where('estado', 'en_camino')->count(),
            'entregados'  => Pedido::whereDate('created_at', $hoy)->where('estado', 'entregado')->count(),
        ];

        // Enviar datos a Inertia/Vue
        return Inertia::render('Vendedor/Dashboard/Index', [
            'counts' => $counts,
        ]);
    }
}
