<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoLog;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Services\Notify;

class PedidoController extends Controller
{
    /** Listado con filtros (Admin y Vendedor) */
    public function index(Request $request)
    {
        $estado   = trim((string) $request->query('estado', ''));
        $q        = trim((string) $request->query('q', ''));
        $asignado = trim((string) $request->query('asignado', '')); // '', 'none', 'any'

        $query = Pedido::query()
            ->withCount('items')
            ->with(['repartidor:id,name']);

        if ($estado !== '') $query->where('estado', $estado);
        if ($asignado === 'none') $query->whereNull('asignado_a');
        elseif ($asignado === 'any') $query->whereNotNull('asignado_a');

        if ($q !== '') {
            $qLower = mb_strtolower($q, 'UTF-8');
            $query->where(function ($w) use ($qLower) {
                $w->whereRaw('LOWER(folio) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(observaciones) LIKE ?', ['%' . $qLower . '%']);
            });
        }

        $pedidos = $query->orderByDesc('id')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($p) {
                return [
                    'id'         => (int) $p->id,
                    'folio'      => $p->folio,
                    'estado'     => $p->estado,
                    'tipo'       => $p->tipo_entrega,
                    'total'      => (float) $p->total,
                    'items'      => (int) $p->items_count,
                    'asignado_a' => $p->asignado_a,
                    'repartidor' => $p->repartidor?->name ?? null,
                    'created_at' => $p->created_at?->format('Y-m-d H:i'),
                ];
            });

        $role = $request->routeIs('vendedor.*') ? 'vendedor' : 'admin';

        return Inertia::render('Pedidos/Index', [
            'role'     => $role,
            'pedidos'  => $pedidos,
            'q'        => $q,
            'estado'   => $estado,
            'asignado' => $asignado,
            'estados'  => ['pendiente', 'preparando', 'listo', 'en_camino', 'entregado', 'cancelado'],
        ]);
    }

    /** CREAR pedido (Admin y Vendedor) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cliente'       => ['nullable', 'exists:users,id'],
            'tipo_entrega'     => ['required', 'in:mostrador,domicilio'],
            'estado'           => ['nullable', 'in:pendiente,preparando,listo,en_camino,entregado,cancelado'],
            'observaciones'    => ['nullable', 'string'],
            'total'            => ['required', 'numeric', 'min:0'],

            'entrega_nombre'      => ['nullable', 'string', 'max:100'],
            'entrega_telefono'    => ['nullable', 'string', 'max:20'],
            'entrega_calle'       => ['nullable', 'string', 'max:100'],
            'entrega_numero'      => ['nullable', 'string', 'max:20'],
            'entrega_colonia'     => ['nullable', 'string', 'max:80'],
            'entrega_municipio'   => ['nullable', 'string', 'max:80'],
            'entrega_referencias' => ['nullable', 'string', 'max:150'],

            'dom' => ['nullable', 'array'],
        ]);

        $data['estado'] = $data['estado'] ?? 'pendiente';

        if (!empty($data['dom']) && is_array($data['dom'])) {
            $data['entrega_nombre']      = $data['entrega_nombre']      ?? Arr::get($data, 'dom.nombre');
            $data['entrega_telefono']    = $data['entrega_telefono']    ?? Arr::get($data, 'dom.telefono');

            if (empty($data['entrega_calle']) && empty($data['entrega_numero'])) {
                $direccion = (string) Arr::get($data, 'dom.direccion', '');
                [$calle, $numero] = array_pad(array_map('trim', explode('#', $direccion, 2)), 2, null);
                $data['entrega_calle']  = $data['entrega_calle']  ?? $calle;
                $data['entrega_numero'] = $data['entrega_numero'] ?? $numero;
            }

            $data['entrega_colonia']     = $data['entrega_colonia']     ?? Arr::get($data, 'dom.colonia');
            $data['entrega_municipio']   = $data['entrega_municipio']   ?? Arr::get($data, 'dom.ciudad');
            $data['entrega_referencias'] = $data['entrega_referencias'] ?? Arr::get($data, 'dom.referencias');
        }

        if (($data['tipo_entrega'] ?? 'mostrador') === 'mostrador') {
            $data = array_merge($data, [
                'entrega_nombre'      => null,
                'entrega_telefono'    => null,
                'entrega_calle'       => null,
                'entrega_numero'      => null,
                'entrega_colonia'     => null,
                'entrega_municipio'   => null,
                'entrega_referencias' => null,
                'dom'                 => null,
            ]);
        }

        $pedido = Pedido::create($data);

        BitacoraService::add('pedidos', 'crear', $pedido->id, [
            'total'        => $pedido->total,
            'tipo_entrega' => $pedido->tipo_entrega,
        ]);

        return redirect()->route('admin.pedidos.show', $pedido)->with('ok', 'Pedido creado correctamente');
    }

    /** Detalle (Admin y Vendedor) */
    public function show(Request $request, Pedido $pedido)
    {
        $pedido->load([
            'items.producto',
            'logs.user:id,name',
        ]);

        $repartidores = User::query()
            ->where('role_id', 3)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $role = $request->routeIs('vendedor.*') ? 'vendedor' : 'admin';

        $dom = is_array($pedido->dom) ? $pedido->dom
            : ($pedido->dom ? json_decode((string) $pedido->dom, true) : null);

        $entregaDireccion = trim(
            trim((string) ($pedido->entrega_calle ?? '')) .
            (isset($pedido->entrega_numero) && $pedido->entrega_numero !== '' ? ' #' . trim((string) $pedido->entrega_numero) : '')
        );

        // Bloque agrupado para la vista
        $envio = [
            'nombre'      => $pedido->entrega_nombre      ?? ($dom['nombre']      ?? null),
            'telefono'    => $pedido->entrega_telefono    ?? ($dom['telefono']    ?? null),
            'direccion'   => ($entregaDireccion !== '' ? $entregaDireccion : ($dom['direccion'] ?? null)),
            'colonia'     => $pedido->entrega_colonia     ?? ($dom['colonia']     ?? null),
            'ciudad'      => $pedido->entrega_municipio   ?? ($dom['ciudad']      ?? null),
            'referencias' => $pedido->entrega_referencias ?? ($dom['referencias'] ?? null),
        ];

        return Inertia::render('Pedidos/Show', [
            'role' => $role,
            'pedido' => [
                'id'            => (int) $pedido->id,
                'folio'         => $pedido->folio,
                'estado'        => $pedido->estado,
                'tipo'          => $pedido->tipo_entrega,
                'total'         => (float) $pedido->total,
                'observaciones' => $pedido->observaciones,
                'created_at'    => $pedido->created_at?->format('Y-m-d H:i'),
                'asignado_a'    => $pedido->asignado_a,

                // planas
                'entrega_nombre'      => $envio['nombre'],
                'entrega_telefono'    => $envio['telefono'],
                'entrega_calle'       => $pedido->entrega_calle,
                'entrega_numero'      => $pedido->entrega_numero,
                'entrega_colonia'     => $envio['colonia'],
                'entrega_municipio'   => $envio['ciudad'],
                'entrega_referencias' => $envio['referencias'],
                'entrega_direccion'   => $envio['direccion'],
                'dom'                 => $dom,

                // agrupado
                'envio'               => $envio,

                'items' => $pedido->items->map(fn ($it) => [
                    'id'        => (int) $it->id,
                    'producto'  => $it->producto?->nombre ?? '—',
                    'cantidad'  => (int) $it->cantidad,
                    'precio'    => (float) $it->precio_unitario,
                    'subtotal'  => (float) $it->subtotal,
                ]),
                'logs' => $pedido->logs->map(fn ($log) => [
                    'id'     => (int) $log->id,
                    'accion' => $log->accion,
                    'de'     => $log->de,
                    'a'      => $log->a,
                    'motivo' => $log->motivo,
                    'by'     => $log->user?->name ?? 'Sistema',
                    'fecha'  => $log->created_at?->format('Y-m-d H:i'),
                ]),
            ],
            'repartidores' => $repartidores,
        ]);
    }

    /** Cambiar estado (con inventario por piezas + notificaciones) */
    public function setEstado(Request $request, Pedido $pedido)
    {
        $estados = ['pendiente', 'preparando', 'listo', 'en_camino', 'entregado'];

        $data = $request->validate([
            'estado' => ['required', 'in:' . implode(',', $estados)],
        ]);

        if ($pedido->estado === 'cancelado') {
            return back()->with('error', 'No se puede cambiar el estado de un pedido cancelado.');
        }

        $anterior = $pedido->estado;
        $nuevo    = $data['estado'];

        if (in_array($nuevo, ['listo', 'entregado'], true)) {
            $pedido->loadMissing('items.producto');
            foreach ($pedido->items as $it) {
                $inv = Inventario::firstOrCreate(
                    ['producto_id' => $it->producto_id],
                    ['stock_actual' => 0, 'stock_minimo' => 0]
                );

                if ($inv->stock_actual < (int) $it->cantidad) {
                    $nombre = $it->producto?->nombre ?? ('ID ' . $it->producto_id);
                    return back()->with('error', "Stock insuficiente para «{$nombre}». Requeridas: {$it->cantidad}, disponibles: {$inv->stock_actual}.");
                }
            }
        }

        DB::transaction(function () use ($pedido, $anterior, $nuevo) {
            $pedido->update(['estado' => $nuevo]);

            if (in_array($nuevo, ['listo', 'entregado'], true)) {
                $pedido->loadMissing('items.producto');

                foreach ($pedido->items as $it) {
                    $prodId = (int) $it->producto_id;
                    $pzs    = (int) $it->cantidad;

                    $yaAplicado = InventarioMovimiento::where('producto_id', $prodId)
                        ->where('tipo', 'salida')
                        ->where('motivo', 'pedido:' . $pedido->id)
                        ->exists();

                    if ($yaAplicado) continue;

                    $inv = Inventario::firstOrCreate(
                        ['producto_id' => $prodId],
                        ['stock_actual' => 0, 'stock_minimo' => 0]
                    );

                    $nuevoStock = (int) $inv->stock_actual - $pzs;
                    $inv->update(['stock_actual' => $nuevoStock]);

                    InventarioMovimiento::create([
                        'producto_id'      => $prodId,
                        'user_id'          => auth()->id(),
                        'tipo'             => 'salida',
                        'cantidad'         => $pzs,
                        'delta'            => -$pzs,
                        'motivo'           => 'pedido:' . $pedido->id,
                        'stock_resultante' => $nuevoStock,
                    ]);

                    if ($nuevoStock <= (int) ($inv->stock_minimo ?? 0)) {
                        $nombre = $it->producto?->nombre ?? ('ID ' . $prodId);
                        try {
                            Notify::lowStock($prodId, $nombre, $nuevoStock, (int) ($inv->stock_minimo ?? 0));
                        } catch (\Throwable $e) {
                            \Log::warning('Notify::lowStock error: ' . $e->getMessage());
                        }
                    }
                }
            }

            PedidoLog::create([
                'pedido_id' => $pedido->id,
                'user_id'   => auth()->id(),
                'accion'    => 'estado_cambiado',
                'de'        => $anterior,
                'a'         => $nuevo,
            ]);

            BitacoraService::add('pedidos', 'estado_cambiado', $pedido->id, [
                'de'     => $anterior,
                'a'      => $nuevo,
                'unidad' => 'piezas',
            ]);

            // 🔔 Notificación correcta usando Notify::pedidoEstado
            try {
                Notify::pedidoEstado(
                    $pedido->id,   // int $pedidoId
                    $nuevo,        // string $estado (nuevo estado)
                    [
                        'estado_anterior' => $anterior,
                        'via'             => 'admin',
                        'user_id'         => auth()->id(),
                    ]
                );
            } catch (\Throwable $e) {
                \Log::warning('Notify::pedidoEstado error: ' . $e->getMessage());
            }
        });

        return back()->with('success', 'Estado actualizado.');
    }

    public function updateEstado(Request $request, Pedido $pedido)
    {
        return $this->setEstado($request, $pedido);
    }

    /** Cancelar (con reposición por piezas + notificación) */
    public function cancelar(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        if ($pedido->estado === 'entregado') {
            return back()->with('error', 'No se puede cancelar un pedido entregado.');
        }

        $anterior = $pedido->estado;

        DB::transaction(function () use ($pedido, $data, $anterior) {
            $pedido->update([
                'estado'             => 'cancelado',
                'motivo_cancelacion' => $data['motivo'],
            ]);

            $pedido->loadMissing('items');
            foreach ($pedido->items as $it) {
                $prodId = (int) $it->producto_id;
                $pzs    = (int) $it->cantidad;

                $seDescontoAntes = InventarioMovimiento::where('producto_id', $prodId)
                    ->where('tipo', 'salida')
                    ->where('motivo', 'pedido:' . $pedido->id)
                    ->exists();

                $yaRepuesto = InventarioMovimiento::where('producto_id', $prodId)
                    ->where('tipo', 'entrada')
                    ->where('motivo', 'cancelacion:' . $pedido->id)
                    ->exists();

                if ($seDescontoAntes && !$yaRepuesto) {
                    $inv        = Inventario::firstOrCreate(['producto_id' => $prodId], ['stock_actual' => 0, 'stock_minimo' => 0]);
                    $nuevoStock = (int) $inv->stock_actual + $pzs;
                    $inv->update(['stock_actual' => $nuevoStock]);

                    InventarioMovimiento::create([
                        'producto_id'      => $prodId,
                        'user_id'          => auth()->id(),
                        'tipo'             => 'entrada',
                        'cantidad'         => $pzs,
                        'delta'            =>  $pzs,
                        'motivo'           => 'cancelacion:' . $pedido->id,
                        'stock_resultante' => $nuevoStock,
                    ]);
                }
            }

            PedidoLog::create([
                'pedido_id' => $pedido->id,
                'user_id'   => auth()->id(),
                'accion'    => 'cancelado',
                'de'        => $anterior,
                'a'         => 'cancelado',
                'motivo'    => $data['motivo'],
            ]);

            BitacoraService::add('pedidos', 'cancelado', $pedido->id, [
                'de'     => $anterior,
                'a'      => 'cancelado',
                'motivo' => $data['motivo'],
                'unidad' => 'piezas',
            ]);

            // 🔔 Notificación correcta usando Notify::pedidoCancelado
            try {
                Notify::pedidoCancelado(
                    $pedido->id,
                    [
                        'estado_anterior' => $anterior,
                        'motivo'          => $data['motivo'],
                        'via'             => 'admin',
                        'user_id'         => auth()->id(),
                    ]
                );
            } catch (\Throwable $e) {
                \Log::warning('Notify::pedidoCancelado error: ' . $e->getMessage());
            }
        });

        return back()->with('success', 'Pedido cancelado.');
    }

    /** Asignar / desasignar repartidor */
    public function asignar(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'repartidor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $antes   = $pedido->asignado_a ? User::find($pedido->asignado_a) : null;
        $despues = null;

        if (!empty($data['repartidor_id'])) {
            $isRepartidor = User::where('id', $data['repartidor_id'])
                ->where('role_id', 3)
                ->exists();

            if (!$isRepartidor) {
                return back()->with('error', 'El usuario seleccionado no es repartidor.');
            }

            $despues = User::find($data['repartidor_id']);
        }

        $anteriorId = (string) ($pedido->asignado_a ?? '');
        $nuevoId    = (string) ($data['repartidor_id'] ?? '');

        $pedido->update(['asignado_a' => $data['repartidor_id'] ?: null]);

        PedidoLog::create([
            'pedido_id' => $pedido->id,
            'user_id'   => auth()->id(),
            'accion'    => $nuevoId !== '' ? 'asignado' : 'desasignado',
            'de'        => $anteriorId,
            'a'         => $nuevoId,
        ]);

        BitacoraService::add('pedidos', $nuevoId !== '' ? 'asignado' : 'desasignado', $pedido->id, [
            'de' => $anteriorId,
            'a'  => $nuevoId,
        ]);

        // 🔔 Notificación correcta usando Notify::pedidoAsignacion
        try {
            $accionTexto = $nuevoId !== ''
                ? 'asignado al repartidor ' . ($despues?->name ?? ('ID ' . $nuevoId))
                : 'dejado sin asignar';

            Notify::pedidoAsignacion(
                $pedido->id,      // int $pedidoId
                $accionTexto,     // string $accion
                [
                    'anterior_id'      => $anteriorId,
                    'nuevo_id'         => $nuevoId,
                    'anterior_nombre'  => $antes?->name,
                    'nuevo_nombre'     => $despues?->name,
                    'via'              => 'admin',
                    'user_id'          => auth()->id(),
                ]
            );
        } catch (\Throwable $e) {
            \Log::warning('Notify::pedidoAsignacion error: ' . $e->getMessage());
        }

        return back()->with('success', 'Asignación actualizada.');
    }
}
