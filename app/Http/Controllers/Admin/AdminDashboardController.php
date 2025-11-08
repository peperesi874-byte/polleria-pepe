<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Inventario;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ---- Contadores base
        $cards = [
            'productos'       => 0,
            'pedidos'         => 0,
            'usuarios'        => 0,
            'inventario_bajo' => 0,
        ];

        // ---- Últimos pedidos (tabla principal)
        $ultimos = collect();

        // ---- Alertas de inventario (columna lateral)
        $alertas = collect();

        try {
            // Totales simples
            $cards['productos'] = (int) Producto::count();
            $cards['pedidos']   = (int) Pedido::count();
            $cards['usuarios']  = (int) User::count();

            // Umbral global desde la tabla configuraciones (id=1)
            $cfg     = Configuracion::find(1);
            $umbral  = $cfg?->stock_umbral; // puede ser null

            // Regla de "stock bajo":
            // - Si el producto tiene stock_minimo => stock_actual <= stock_minimo
            // - Si stock_minimo es NULL y hay umbral global => stock_actual <= umbral
            $invBajoQuery = Inventario::query()
                ->when(true, function ($q) use ($umbral) {
                    $q->where(function ($w) use ($umbral) {
                        $w->whereColumn('stock_actual', '<=', 'stock_minimo'); // cuando el producto definió mínimo
                        if (!is_null($umbral)) {
                            $w->orWhere(function ($x) use ($umbral) {
                                $x->whereNull('stock_minimo')
                                  ->where('stock_actual', '<=', (int) $umbral);
                            });
                        }
                    });
                });

            // KPI para la card
            $cards['inventario_bajo'] = (int) $invBajoQuery->count();

            // Lista de alertas (trae nombre del producto)
            $alertas = Inventario::query()
                ->with(['producto:id,nombre'])
                ->where(function ($w) use ($umbral) {
                    $w->whereColumn('stock_actual', '<=', 'stock_minimo');
                    if (!is_null($umbral)) {
                        $w->orWhere(function ($x) use ($umbral) {
                            $x->whereNull('stock_minimo')
                              ->where('stock_actual', '<=', (int) $umbral);
                        });
                    }
                })
                ->orderBy('stock_actual', 'asc')
                ->limit(6)
                ->get(['id','producto_id','stock_actual','stock_minimo'])
                ->map(function ($i) {
                    return [
                        'id'            => (int) $i->id,
                        'nombre'        => $i->producto?->nombre ?? 'Producto',
                        'stock_actual'  => (int) $i->stock_actual,
                        'stock_minimo'  => is_null($i->stock_minimo) ? null : (int) $i->stock_minimo,
                    ];
                });

            // 10 pedidos recientes
            $ultimos = Pedido::query()
                ->latest('id')
                ->withCount('items')
                ->limit(10)
                ->get(['id','folio','estado','tipo_entrega','total','created_at'])
                ->map(fn ($p) => [
                    'id'         => (int) $p->id,
                    'folio'      => (string) ($p->folio ?? ''),
                    'estado'     => (string) $p->estado,
                    'tipo'       => (string) $p->tipo_entrega,
                    'items'      => (int) $p->items_count,
                    'total'      => (float) $p->total,
                    'created_at' => optional($p->created_at)->format('Y-m-d H:i'),
                ]);

        } catch (\Throwable $e) {
            Log::warning('[Dashboard] Falló la construcción de datos: ' . $e->getMessage());
            // deja los valores por defecto para no romper la vista
        }

        return Inertia::render('Admin/Dashboard', [
            'cards'   => $cards,
            'ultimos' => $ultimos,
            'alertas' => $alertas,
            // No mandamos bitácora/notificaciones porque ya se comparten
            // globalmente desde AppServiceProvider (bitacoraTop / notifTop).
        ]);
    }
}
