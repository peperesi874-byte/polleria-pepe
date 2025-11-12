<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoCrearController extends Controller
{
    public function create(Request $request)
    {
        $clientes = User::query()
            ->where('role_id', 4) // cliente
            ->select([
                'id',
                DB::raw('name AS nombre'),
                'email',
                DB::raw('NULL AS telefono')
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
            'cliente_id'           => ['required','integer','exists:users,id'],
            'tipo_entrega'         => ['required','in:mostrador,domicilio'],
            'observaciones'        => ['nullable','string','max:255'],

            'items'                => ['required','array','min:1'],
            'items.*.producto_id'  => ['required','integer','distinct'],
            'items.*.cantidad'     => ['required','integer','min:1'],

            'dom'                  => ['nullable','array'],
            'dom.nombre'           => ['nullable','string','max:100'],
            'dom.telefono'         => ['nullable','string','max:20'],
            'dom.direccion'        => ['nullable','string','max:150'],
            'dom.colonia'          => ['nullable','string','max:80'],
            'dom.ciudad'           => ['nullable','string','max:80'],
            'dom.referencias'      => ['nullable','string','max:150'],
        ]);

        $productos = Producto::whereIn('id', collect($data['items'])->pluck('producto_id'))
            ->select('id','precio','nombre')
            ->get()->keyBy('id');

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
            $entrega_nombre      = data_get($data, 'dom.nombre');
            $entrega_telefono    = data_get($data, 'dom.telefono');
            $entrega_direccion   = trim((string) data_get($data, 'dom.direccion'));
            $entrega_colonia     = data_get($data, 'dom.colonia');
            $entrega_municipio   = data_get($data, 'dom.ciudad');
            $entrega_referencias = data_get($data, 'dom.referencias');

            $entrega_calle  = null;
            $entrega_numero = null;
            if ($entrega_direccion !== '') {
                [$entrega_calle, $entrega_numero] = array_pad(array_map('trim', explode('#', $entrega_direccion, 2)), 2, null);
            }

            if ($data['tipo_entrega'] === 'mostrador') {
                $entrega_nombre = $entrega_telefono = $entrega_calle = $entrega_numero =
                $entrega_colonia = $entrega_municipio = $entrega_referencias = null;
            }

            $pedido = Pedido::create([
                'cliente_id'          => (int) $data['cliente_id'],
                'folio'               => 'PED-'.now()->format('YmdHis').random_int(10,99),
                'estado'              => 'pendiente',
                'tipo_entrega'        => $data['tipo_entrega'],
                'observaciones'       => $data['observaciones'] ?? null,
                'total'               => (float) $total,
                'asignado_a'          => null,

                'entrega_nombre'      => $entrega_nombre,
                'entrega_telefono'    => $entrega_telefono,
                'entrega_calle'       => $entrega_calle,
                'entrega_numero'      => $entrega_numero,
                'entrega_colonia'     => $entrega_colonia,
                'entrega_municipio'   => $entrega_municipio,
                'entrega_referencias' => $entrega_referencias,

                'dom'                 => $data['dom'] ?? null,
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
                'origen' => $data['tipo_entrega'],
                'total'  => $total,
            ]);
        });

        return redirect()->route('vendedor.pedidos.show', $pedido->id)
            ->with('success', 'Pedido creado correctamente.');
    }
}
