<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Services\BitacoraService;

class ProductoController extends Controller
{
    public function index()
    {
        $q = request('q', '');

        $productos = Producto::query()
            ->when($q !== '', fn($w) => $w->where('nombre', 'like', "%{$q}%"))
            ->with(['inventario:id,producto_id,stock_actual,stock_minimo'])
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($p) {
                $inv = $p->inventario;

                return [
                    'id'           => $p->id,
                    'nombre'       => $p->nombre,
                    'descripcion'  => $p->descripcion,
                    'precio'       => (float) $p->precio,
                    'stock'        => (int) ($inv->stock_actual ?? $p->stock ?? 0),
                    'stock_minimo' => (int) ($inv->stock_minimo ?? 0),
                    'activo'       => (bool) $p->activo,
                    'imagen'       => $p->imagen,
                    'imagenUrl'    => $p->imagen ? Storage::url($p->imagen) : null,
                ];
            });

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'filters'   => ['q' => $q],
        ]);
    }

    public function create()
    {
        return Inertia::render('Productos/Create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre'      => 'required|string|max:255|unique:productos,nombre',
            'descripcion' => 'nullable|string|max:1000',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'activo'      => 'sometimes|boolean',
            'imagen'      => 'nullable|image|max:2048',
        ]);

        $rutaImagen = null;
        if ($r->hasFile('imagen')) {
            $rutaImagen = $r->file('imagen')->store('productos', 'public');
        }

        $producto = new Producto();
        $producto->nombre       = $data['nombre'];
        $producto->descripcion  = $data['descripcion'] ?? null;
        $producto->precio       = (float) $data['precio'];
        $producto->stock        = (int)   $data['stock'];
        $producto->activo       = (bool)  ($data['activo'] ?? false);
        $producto->imagen       = $rutaImagen;
        $producto->save();

        // Sync inventario inicial
        Inventario::updateOrCreate(
            ['producto_id' => $producto->id],
            ['stock_actual' => (int) $producto->stock, 'stock_minimo' => 0]
        );

        // Bitácora
        BitacoraService::add('productos', 'crear', $producto->id, [
            'precio'       => (float) $producto->precio,
            'stock'        => (int) $producto->stock,
            'stock_minimo' => 0,
            'activo'       => (bool) $producto->activo,
        ]);

        return redirect()->route('productos.index')
            ->with('success', '✅ Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        return Inertia::render('Productos/Edit', [
            'producto' => [
                'id'          => $producto->id,
                'nombre'      => $producto->nombre,
                'precio'      => (float) $producto->precio,
                'stock'       => (int) $producto->stock,
                'descripcion' => $producto->descripcion,
                'activo'      => (bool) $producto->activo,
                'imagen'      => $producto->imagen,
                'imagenUrl'   => $producto->imagen ? Storage::url($producto->imagen) : null,
            ],
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:255|unique:productos,nombre,' . $producto->id,
            'precio'          => 'required|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'descripcion'     => 'nullable|string|max:1000',
            'activo'          => 'sometimes|boolean',
            'imagen'          => 'nullable|image|max:4096',
            'eliminar_imagen' => 'nullable|boolean',
        ]);

        if (!empty($data['eliminar_imagen']) && $producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
            $producto->imagen = null;
        }

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $producto->imagen = $request->file('imagen')->store('productos', 'public');
        }

        $producto->nombre       = $data['nombre'];
        $producto->precio       = (float) $data['precio'];
        $producto->stock        = (int)   $data['stock'];
        $producto->descripcion  = $data['descripcion'] ?? null;
        $producto->activo       = (bool)  ($data['activo'] ?? false);
        $producto->save();

        // Sync inventario (respetando mínimo existente)
        $stockMinimo = optional($producto->inventario)->stock_minimo ?? 0;
        Inventario::updateOrCreate(
            ['producto_id' => $producto->id],
            ['stock_actual' => (int) $producto->stock, 'stock_minimo' => $stockMinimo]
        );

        // Bitácora
        BitacoraService::add('productos', 'actualizar', $producto->id, [
            'precio'       => (float) $producto->precio,
            'stock'        => (int) $producto->stock,
            'stock_minimo' => (int) $stockMinimo,
            'activo'       => (bool) $producto->activo,
        ]);

        return Redirect::route('productos.index')
            ->with('success', '✅ Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        // Bitácora antes de borrar
        BitacoraService::add('productos', 'eliminar', $producto->id, [
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
        ]);

        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return Redirect::route('productos.index')
            ->with('success', '🗑 Producto eliminado.');
    }
}
