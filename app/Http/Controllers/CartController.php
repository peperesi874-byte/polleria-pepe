<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    // Normaliza cualquier valor de imagen a una URL pública
    protected function normalizeImageUrl(?string $raw): ?string
    {
        if (!$raw) return null;

        $r = ltrim($raw, '/');

        // Si ya es absoluta, la dejamos
        if (str_starts_with($r, 'http://') || str_starts_with($r, 'https://')) {
            return $r;
        }

        // public/... -> storage/...
        if (str_starts_with($r, 'public/')) {
            return '/storage/' . substr($r, 7);
        }

        // storage/... -> /storage/...
        if (str_starts_with($r, 'storage/')) {
            return '/'.$r;
        }

        // Cualquier otra ruta relativa -> Storage::url()
        return Storage::url($r);
    }

    public function index(Request $request)
    {
        // Tomamos el carrito y normalizamos URLs de imagen
        $items = collect($request->session()->get('cart', []))
            ->map(function ($i) {
                $i['image_url'] = $this->normalizeImageUrl($i['image'] ?? null);
                return $i;
            });

        $subtotal = $items->sum(fn($i) => (float)$i['price'] * (int)$i['qty']);
        $envio    = 0.0;
        $total    = $subtotal + $envio;

        return Inertia::render('Carrito/Index', [
            'items'    => $items->values()->all(),
            'subtotal' => (float) $subtotal,
            'envio'    => (float) $envio,
            'total'    => (float) $total,
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'qty'         => 'nullable|integer|min:1|max:999',
        ]);

        $qty = $data['qty'] ?? 1;

        $p = Producto::query()
            ->select('id','nombre as name','precio as price','imagen as image')
            ->findOrFail($data['producto_id']);

        $cart = collect($request->session()->get('cart', []));

        if ($cart->has($p->id)) {
            $item = $cart->get($p->id);
            $item['qty'] += $qty;
            $cart->put($p->id, $item);
        } else {
            $cart->put($p->id, [
                'id'        => $p->id,
                'name'      => $p->name,
                'price'     => (float) $p->price,
                'qty'       => (int) $qty,
                // guardamos el valor crudo y también la url normalizada
                'image'     => $p->image,
                'image_url' => $this->normalizeImageUrl($p->image),
            ]);
        }

        $request->session()->put('cart', $cart->toArray());

        return back()->with('success', 'Producto añadido al carrito');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id'  => 'required|integer',
            'items.*.qty' => 'required|integer|min:1|max:999',
        ]);

        $cart = collect($request->session()->get('cart', []));
        foreach ($data['items'] as $i) {
            if ($cart->has($i['id'])) {
                $item = $cart->get($i['id']);
                $item['qty'] = (int)$i['qty'];
                $cart->put($i['id'], $item);
            }
        }
        $request->session()->put('cart', $cart->toArray());

        return back()->with('success', 'Carrito actualizado');
    }

    public function remove(Request $request)
    {
        $id = (int) $request->validate(['id' => 'required|integer'])['id'];
        $cart = collect($request->session()->get('cart', []))->forget($id);
        $request->session()->put('cart', $cart->toArray());
        return back()->with('success', 'Producto eliminado');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('cart');
        return back()->with('success', 'Carrito vacío');
    }
}
