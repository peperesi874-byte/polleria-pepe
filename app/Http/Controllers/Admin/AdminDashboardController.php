<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Pedido;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Usa nullsafe y defaults para que nunca truene
        $cards = [
            'productos' => (int) (Producto::count() ?? 0),
            'pedidos'   => (int) (Pedido::count() ?? 0),
            'usuarios'  => (int) (User::count() ?? 0),
        ];

        return Inertia::render('Admin/Dashboard', [
            'cards' => $cards,
        ]);
    }
}

