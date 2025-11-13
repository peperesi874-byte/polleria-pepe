<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use App\Models\Pedido;
use App\Models\PedidoItem; // si no existe, puedes borrar esta línea
use App\Models\Notificacion; // 👈 agregado
use App\Models\User;         // 👈 agregado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    /**
     * Mostrar el formulario de checkout (confirmación del carrito)
     */
    public function create(Request $request)
    {
        $user = $request->user();

        // 🛒 Leer el carrito intentando varias claves comunes
        $cartFromSession = session('cart.items')
            ?? session('cart')
            ?? session('carrito')
            ?? session('cart_items')
            ?? [];

        // Si viene como { items: [...] } tomar items; si ya es array, usarlo tal cual
        if (is_array($cartFromSession) && array_key_exists('items', $cartFromSession)) {
            $cartItems = $cartFromSession['items'];
        } else {
            $cartItems = $cartFromSession;
        }

        // Asegurar arreglo indexado
        $cartItems = is_array($cartItems) ? array_values($cartItems) : [];

        // Si está vacío, regresa al carrito con aviso
        if (empty($cartItems)) {
            return redirect()
                ->route('cliente.carrito.index')
                ->with('warning', 'Tu carrito está vacío. Agrega productos antes de continuar al checkout.');
        }

        // Direcciones del cliente
        $direcciones = Direccion::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        // Mandar al frontend como { items: [...] }
        return Inertia::render('Cliente/Checkout', [
            'cart'            => ['items' => $cartItems],
            'direcciones'     => $direcciones,
            'direccionPerfil' => (string) ($user->direccion ?? ''), // 👈 línea agregada
        ]);
    }

    /**
     * Validar dirección y CREAR el pedido (+ items si aplica)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // 1) Validación
        $data = $request->validate([
            'tipo_entrega'            => ['required', Rule::in(['mostrador', 'domicilio', 'recoger'])],
            'notas'                   => ['nullable', 'string', 'max:500'],
            'usar_nueva'              => ['nullable', 'boolean'],
            'direccion_id'            => ['nullable', 'integer', 'exists:direcciones,id'],
            'direccion_nueva.calle'   => ['nullable', 'string', 'max:120'],
            'direccion_nueva.numero'  => ['nullable', 'string', 'max:20'],
            'direccion_nueva.colonia' => ['nullable', 'string', 'max:120'],
            'direccion_nueva.cp'      => ['nullable', 'string', 'max:10'],
            'ciudad'                  => ['nullable', 'string', 'max:100'],
        ]);

        // Normalizar tipo (el front usa 'recoger')
        $tipoEntrega = $data['tipo_entrega'] === 'recoger' ? 'mostrador' : $data['tipo_entrega'];

        // 2) Carrito real desde sesión
        $cartFromSession = session('cart.items')
            ?? session('cart')
            ?? session('carrito')
            ?? session('cart_items')
            ?? [];

        $raw = (is_array($cartFromSession) && array_key_exists('items', $cartFromSession))
            ? $cartFromSession['items']
            : $cartFromSession;

        $items = is_array($raw) ? array_values($raw) : [];

        if (empty($items)) {
            return redirect()->route('cliente.carrito.index')
                ->with('warning', 'Tu carrito está vacío.');
        }

        // 3) Si es DOMICILIO, resolver/crear dirección
        $direccionId = null;

        if ($tipoEntrega === 'domicilio') {
            $usarNueva = (bool) ($data['usar_nueva'] ?? false);

            if (!$usarNueva && !empty($data['direccion_id'])) {
                // Debe pertenecer al usuario
                Direccion::where('id', $data['direccion_id'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $direccionId = (int) $data['direccion_id'];
            } else {
                $n = $data['direccion_nueva'] ?? [];
                if (
                    empty(trim($n['calle'] ?? '')) ||
                    empty(trim($n['colonia'] ?? '')) ||
                    empty(trim($n['cp'] ?? ''))
                ) {
                    return back()->withErrors([
                        'direccion_nueva.calle' => 'Calle, colonia y CP son obligatorios al capturar una dirección nueva.',
                    ])->withInput();
                }

                $dir = Direccion::create([
                    'user_id' => $user->id,
                    'calle'   => trim($n['calle'] ?? ''),
                    'numero'  => trim($n['numero'] ?? ''),
                    'colonia' => trim($n['colonia'] ?? ''),
                    'cp'      => trim($n['cp'] ?? ''),
                    'ciudad'  => 'Tapachula',
                ]);

                $direccionId = $dir->id;
            }
        }

        // 4) Calcular total
        $total = 0.0;
        foreach ($items as $it) {
            $precio = (float) ($it['precio'] ?? $it['price'] ?? 0);
            $qty    = (int)   ($it['cantidad'] ?? $it['qty'] ?? 0);
            $total += $precio * $qty;
        }

        // 5) Crear pedido (+ items) en transacción
        $pedido = DB::transaction(function () use ($user, $direccionId, $tipoEntrega, $total, $data, $items) {
            $pedido = Pedido::create([
                'folio'        => $this->makeFolio(),
                'id_cliente'   => $user->id,
                'direccion_id' => $direccionId,     // usa la nueva columna
                'tipo_entrega' => $tipoEntrega,     // 'mostrador' | 'domicilio'
                'total'        => $total,
                'estado'       => 'pendiente',
                'observaciones'=> $data['notas'] ?? null,
                'creado_por'   => $user->id,
            ]);

            // Crear items si el modelo existe y la relación está definida
            if (class_exists(PedidoItem::class) && method_exists($pedido, 'items')) {
                foreach ($items as $it) {
                    $precio = (float) ($it['precio'] ?? $it['price'] ?? 0);
                    $qty    = (int)   ($it['cantidad'] ?? $it['qty'] ?? 0);
                    $nombre = (string)($it['nombre'] ?? $it['name'] ?? '');
                    $prodId = $it['id'] ?? $it['producto_id'] ?? null;

                    if ($qty <= 0 || $precio < 0) continue;

                    try {
                        $pedido->items()->create([
                            'producto_id' => $prodId,
                            'nombre'      => $nombre,
                            'precio'      => $precio,
                            'cantidad'    => $qty,
                            'subtotal'    => $precio * $qty,
                        ]);
                    } catch (\Throwable $e) {
                        // Si la estructura de pedido_items difiere, no rompemos el flujo
                    }
                }
            }

            return $pedido;
        });

        // 🔔 Notificar a Admin y Vendedor (role_id 1 y 2)
        try {
            $destinatarios = User::query()
                ->whereIn('role_id', [1, 2])
                ->pluck('id');

            $titulo  = 'Nuevo pedido registrado';
            $mensaje = 'Pedido ' . ($pedido->folio ?? ('#' . $pedido->id)) . ' creado por ' . ($user->name ?? 'cliente');
            $meta    = [
                'pedido_id' => $pedido->id,
                'folio'     => $pedido->folio,
                'url'       => route('admin.pedidos.show', $pedido->id),
            ];

            foreach ($destinatarios as $uid) {
                Notificacion::create([
                    'user_id' => $uid,
                    'tipo'    => 'pedido_nuevo',
                    'titulo'  => $titulo,
                    'mensaje' => $mensaje,
                    'meta'    => $meta,
                    'leida'   => 0,
                    'nivel'   => 'info',
                    'estado'  => 'activo',
                ]);
            }
        } catch (\Throwable $e) {
            // Silenciar para no romper el flujo del cliente si falla la notificación
        }

        // 6) Limpiar carrito
        session()->forget(['cart.items', 'cart', 'carrito', 'cart_items']);

        // 7) Redirigir a confirmación (solo cambio necesario)
        return redirect()
            ->route('cliente.checkout.confirmacion', $pedido->id)
            ->with('success', '¡Pedido creado correctamente!');
    }

    /** Vista de confirmación del pedido */
    public function confirmacion(int $pedido, Request $request)
    {
        $user = $request->user();

        $p = Pedido::query()
            ->with(['items', 'direccion'])
            ->where('id', $pedido)
            ->where('id_cliente', $user->id)
            ->firstOrFail();

        $payload = [
            'id'           => $p->id,
            'folio'        => $p->folio,
            'estado'       => $p->estado,
            'tipo_entrega' => $p->tipo_entrega,
            'total'        => (float) $p->total,
            'notas'        => $p->observaciones,
            'created_at'   => optional($p->created_at)->toDateTimeString(),
            'direccion'    => $p->direccion ? [
                'calle'   => $p->direccion->calle,
                'numero'  => $p->direccion->numero,
                'colonia' => $p->direccion->colonia,
                'cp'      => $p->direccion->cp,
                'ciudad'  => $p->direccion->ciudad,
            ] : null,
            'items' => $p->relationLoaded('items')
                ? $p->items->map(fn($it) => [
                    'nombre'   => $it->nombre ?? '',
                    'precio'   => (float)($it->precio ?? 0),
                    'cantidad' => (int)($it->cantidad ?? 0),
                    'subtotal' => (float)($it->subtotal ?? (($it->precio ?? 0) * ($it->cantidad ?? 0))),
                ])
                : [],
        ];

        return Inertia::render('Cliente/Checkout/Confirmacion', [
            'pedido' => $payload,
        ]);
    }

    private function makeFolio(): string
    {
        $seq = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'PP-' . now()->format('Ymd') . '-' . $seq;
    }
}
