<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\User; // 👈 IMPORTANTE
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoCrearController extends Controller
{
    public function create(Request $request)
    {
        // Si NO tienes tabla clientes, tomamos los usuarios con rol "cliente" (role_id = 4)
        // y NO pedimos 'telefono' (o lo simulamos como NULL para la vista).
        $clientes = User::query()
            ->where('role_id', 4) // ajusta si tu id de cliente es otro
            ->select([
                'id',
                DB::raw('name AS nombre'),
                'email',
                DB::raw('NULL AS telefono') // 👈 evita el error; la vista puede mostrar vacío
            ])
            ->orderBy('name')
            ->get();

        $productos = Producto::query()
            ->with(['inventario:id,producto_id,stock_actual'])
            ->select('id','nombre','precio')
            ->orderBy('nombre')
            ->get()
            ->map(function ($p) {
                return [
                    'id'     => (int) $p->id,
                    'nombre' => $p->nombre,
                    'precio' => (float) $p->precio,
                    'stock'  => (int) ($p->inventario->stock_actual ?? 0),
                ];
            });

        return Inertia::render('Vendedor/Pedidos/Create', [
            'clientes'  => $clientes,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'           => ['required','integer'],
            'tipo_entrega'         => ['required','in:mostrador,domicilio'],
            'observaciones'        => ['nullable','string','max:255'],
            'items'                => ['required','array','min:1'],
            'items.*.producto_id'  => ['required','integer','distinct'],
            'items.*.cantidad'     => ['required','integer','min:1'],
        ]);

        $productos = Producto::whereIn('id', collect($data['items'])->pluck('producto_id'))
            ->select('id','precio','nombre')
            ->get()
            ->keyBy('id');

        $total = 0;
        foreach ($data['items'] as $row) {
            $prod = $productos[$row['producto_id']] ?? null;
            if (!$prod) return back()->with('error', 'Producto inválido en la lista.');

            $cantidad = (int) $row['cantidad'];

            $inv = Inventario::firstOrCreate(
                ['producto_id' => $prod->id],
                ['stock_actual' => 0, 'stock_minimo' => 0]
            );
            if ($inv->stock_actual < $cantidad) {
                return back()->with('error', "Stock insuficiente para «{$prod->nombre}». Disponibles: {$inv->stock_actual}");
            }

            $total += (float) $prod->precio * $cantidad;
        }

        $pedido = null;
        DB::transaction(function () use (&$pedido, $data, $productos, $total) {
            $pedido = Pedido::create([
                'cliente_id'     => (int) $data['cliente_id'], // 👈 apunta a users.id si usas users como clientes
                'folio'          => $this->folio(),
                'estado'         => 'pendiente',
                'tipo_entrega'   => $data['tipo_entrega'],
                'observaciones'  => $data['observaciones'] ?? null,
                'total'          => (float) $total,
                'asignado_a'     => null,
            ]);

            foreach ($data['items'] as $row) {
                $prod     = $productos[$row['producto_id']];
                $cantidad = (int) $row['cantidad'];
                $precio   = (float) $prod->precio;

                PedidoItem::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $prod->id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal'        => $precio * $cantidad,
                ]);
            }

            BitacoraService::add('pedidos', 'creado', $pedido->id, [
                'origen' => 'mostrador',
                'total'  => $total,
            ]);
        });

        return redirect()->route('vendedor.pedidos.show', $pedido->id)
            ->with('success', 'Pedido creado correctamente.');
    }

    private function folio(): string
    {
        return 'PED-' . now()->format('YmdHis') . random_int(10,99);
    }
}
