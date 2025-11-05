<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Inventario;         // 👈 importa Inventario
use Illuminate\Support\Facades\Log; // 👈 para registrar si algo falla
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Contadores base
        $cards = [
            'productos'        => (int) Producto::count(),
            'pedidos'          => (int) Pedido::count(),
            'usuarios'         => (int) User::count(),
            'inventario_bajo'  => 0, // se calcula abajo con try/catch
        ];

        // Conteo de productos con stock_actual <= stock_minimo (no romper el panel si falla)
        try {
            $cards['inventario_bajo'] = (int) Inventario::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        } catch (\Throwable $e) {
            Log::warning('[Dashboard] No se pudo calcular inventario_bajo: ' . $e->getMessage());
            $cards['inventario_bajo'] = 0;
        }

        // 10 pedidos recientes para la tabla
        $ultimos = Pedido::query()
            ->latest('id')
            ->withCount('items')
            ->take(10)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'folio'      => $p->folio,
                'estado'     => $p->estado,
                'tipo'       => $p->tipo_entrega,
                'items'      => (int) $p->items_count,
                'total'      => (float) $p->total,
                'created_at' => optional($p->created_at)->format('Y-m-d H:i'),
            ]);

        // 👇 Mantengo tu componente actual
        return Inertia::render('Admin/Dashboard', [
            'cards'   => $cards,
            'ultimos' => $ultimos,
        ]);
    }
}
