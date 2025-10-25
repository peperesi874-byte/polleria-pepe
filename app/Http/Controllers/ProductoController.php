<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
   public function index()
{
    $q = request('q', '');

    $productos = Producto::orderBy('id', 'desc')
        ->when($q !== '', fn($w) => $w->where('nombre', 'like', "%{$q}%"))
        ->paginate(12)
        ->through(fn ($p) => [
            'id'        => $p->id,
            'nombre'    => $p->nombre,
            'descripcion'=> $p->descripcion,
            'precio'    => (float) $p->precio,
            'stock'     => (int) $p->stock,
            'activo'    => (bool) $p->activo,
            'imagen'    => $p->imagen,                                   // ruta relativa
            'imagenUrl' => $p->imagen ? \Storage::url($p->imagen) : null, // URL pública
        ]);

    return \Inertia\Inertia::render('Productos/Index', [
        'productos' => $productos,
        'filters'   => ['q' => $q], // 👈 agregado
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

        if ($r->hasFile('imagen')) {
            $data['imagen'] = $r->file('imagen')->store('productos', 'public');
        }

        $data['activo'] = (bool) ($data['activo'] ?? false);

        Producto::create($data);

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

        unset($data['imagen'], $data['eliminar_imagen']);
        $data['activo'] = (bool) ($data['activo'] ?? false);

        $producto->fill($data)->save();

        return Redirect::route('productos.index')
            ->with('success', '✅ Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return Redirect::route('productos.index')
            ->with('success', '🗑️ Producto eliminado.');
    }
}
