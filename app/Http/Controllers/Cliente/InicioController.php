<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pedido; // 👈 IMPORTANTE: para consultar pedidos

class InicioController extends Controller
{
    public function __invoke()
    {
        // 🛒 Leer el carrito igual que en checkout
        $cartFromSession = session('cart.items')
            ?? session('cart')
            ?? session('carrito')
            ?? session('cart_items')
            ?? [];

        // Normalizar items
        if (is_array($cartFromSession) && array_key_exists('items', $cartFromSession)) {
            $rawItems = $cartFromSession['items'];
        } else {
            $rawItems = $cartFromSession;
        }

        $items = is_array($rawItems) ? array_values($rawItems) : [];

        // Calcular subtotal y cantidad
        $subtotal = 0.0;
        $count = 0;

        foreach ($items as $it) {
            $precio = (float)($it['precio'] ?? $it['price'] ?? 0);
            $qty    = (int)($it['cantidad'] ?? $it['qty'] ?? 0);

            $subtotal += $precio * $qty;
            $count    += $qty;
        }

        $cartResumen = [
            'items'    => $items,
            'subtotal' => $subtotal,
            'count'    => $count,
        ];

        // 👤 Cliente autenticado
        $userId = auth()->id();

        // 🧾 Últimos 5 pedidos del cliente
        $recientes = Pedido::query()
            ->where('id_cliente', $userId)
            ->orderByDesc('id')
            ->limit(5)
            ->get([
                'id',
                'estado',
                'total',
                'created_at',
            ]);

        // 🧮 Resumen general de pedidos
        $resumenPedidos = [
            'total'      => Pedido::where('id_cliente', $userId)->count(),
            'pendientes' => Pedido::where('id_cliente', $userId)
                ->whereIn('estado', ['pendiente', 'confirmado', 'preparando', 'listo', 'en_camino'])
                ->count(),
            'entregados' => Pedido::where('id_cliente', $userId)
                ->where('estado', 'entregado')
                ->count(),
        ];

        return Inertia::render('Cliente/Inicio', [
            'cartResumen'     => $cartResumen,
            'recientes'       => $recientes,
            'resumenPedidos'  => $resumenPedidos,
        ]);
    }
}
