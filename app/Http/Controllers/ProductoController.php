<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Services\BitacoraService;
use App\Services\Notify;

class ProductoController extends Controller
{
    /** Normaliza cualquier ruta guardada en BD o recién subida */
    private function normalizePath(?string $path): ?string
    {
        if (!$path) return null;

        // Quita cualquier repetición de "storage/" o "public/" al inicio
        $path = ltrim($path, '/\\');
        $path = preg_replace('#^(storage/)+#i', '', $path); // storage/, storage/storage/, etc.
        $path = preg_replace('#^(public/)+#i',   '', $path); // public/, public/public/, etc.

        // Evita barras iniciales residuales
        return ltrim($path, '/\\');
    }

    public function index()
    {
        $q = request('q', '');

        $productos = Producto::query()
            ->when($q !== '', fn ($w) => $w->where('nombre', 'like', "%{$q}%"))
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
                    'imagen'       => $this->normalizePath($p->imagen),
                    'imagenUrl'    => $p->imagen_url, // accesor del modelo
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
            'imagen'      => 'nullable|image|max:4096',
        ]);

        $rutaImagen = null;
        if ($r->hasFile('imagen')) {
            $rutaImagen = $this->normalizePath(
                $r->file('imagen')->store('productos', 'public') // guarda en disk public
            ); // BD: productos/archivo.jpg
        }

        $producto = new Producto();
        $producto->nombre       = $data['nombre'];
        $producto->descripcion  = $data['descripcion'] ?? null;
        $producto->precio       = $data['precio'];         // <- sin (float)
        $producto->stock        = (int) $data['stock'];
        $producto->activo       = (bool) ($data['activo'] ?? false);
        $producto->imagen       = $rutaImagen;
        $producto->save();

        // Inventario inicial
        Inventario::updateOrCreate(
            ['producto_id' => $producto->id],
            ['stock_actual' => (int) $producto->stock, 'stock_minimo' => 0]
        );

        BitacoraService::add('productos', 'crear', $producto->id, [
            'precio'       => (float) $producto->precio,
            'stock'        => (int) $producto->stock,
            'stock_minimo' => 0,
            'activo'       => (bool) $producto->activo,
        ]);

        // Notify::push acepta 5 args: tipo, titulo, mensaje, payload, nivel
        Notify::push(
            'producto_creado',
            'Producto creado',
            "Se creó el producto «{$producto->nombre}» con stock de {$producto->stock} pzs.",
            [
                'accion'      => 'crear',
                'producto_id' => (int) $producto->id,
                'stock'       => (int) $producto->stock,
            ],
            'success'
        );

        $this->notificarBajoMinimoSiAplica($producto->id);

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
                'imagen'      => $this->normalizePath($producto->imagen),
                'imagenUrl'   => $producto->imagen_url, // accesor
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

        // Eliminar imagen actual si se pide
        if (!empty($data['eliminar_imagen']) && $producto->imagen) {
            Storage::disk('public')->delete($this->normalizePath($producto->imagen));
            $producto->imagen = null;
        }

        // Nueva imagen
        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($this->normalizePath($producto->imagen));
            }
            $producto->imagen = $this->normalizePath(
                $request->file('imagen')->store('productos', 'public')
            );
        }

        $producto->nombre       = $data['nombre'];
        $producto->precio       = $data['precio'];         // <- sin (float) y sin doble asignación
        $producto->stock        = (int) $data['stock'];
        $producto->descripcion  = $data['descripcion'] ?? null;
        $producto->activo       = (bool) ($data['activo'] ?? false);
        $producto->save();

        // Sincronizar inventario básico
        $stockMinimo = optional($producto->inventario)->stock_minimo ?? 0;
        Inventario::updateOrCreate(
            ['producto_id' => $producto->id],
            ['stock_actual' => (int) $producto->stock, 'stock_minimo' => (int) $stockMinimo]
        );

        BitacoraService::add('productos', 'actualizar', $producto->id, [
            'precio'       => (float) $producto->precio,
            'stock'        => (int) $producto->stock,
            'stock_minimo' => (int) $stockMinimo,
            'activo'       => (bool) $producto->activo,
        ]);

        Notify::push(
            'producto_actualizado',
            'Producto actualizado',
            "Se actualizó «{$producto->nombre}».",
            [
                'accion'       => 'actualizar',
                'producto_id'  => (int) $producto->id,
                'stock'        => (int) $producto->stock,
                'stock_minimo' => (int) $stockMinimo,
            ],
            'info'
        );

        $this->notificarBajoMinimoSiAplica($producto->id);

        return Redirect::route('productos.index')
            ->with('success', '✅ Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        BitacoraService::add('productos', 'eliminar', $producto->id, [
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
        ]);

        if ($producto->imagen) {
            Storage::disk('public')->delete($this->normalizePath($producto->imagen));
        }

        $nombre = $producto->nombre;
        $idRef  = $producto->id;

        $producto->delete();

        Notify::push(
            'producto_eliminado',
            'Producto eliminado',
            "Se eliminó el producto «{$nombre}».",
            [
                'accion'      => 'eliminar',
                'producto_id' => (int) $idRef,
            ],
            'warning'
        );

        return Redirect::route('productos.index')
            ->with('success', '🗑 Producto eliminado.');
    }

    /** 🔔 Alerta de inventario bajo */
    private function notificarBajoMinimoSiAplica(int $productoId): void
    {
        $inv = Inventario::query()
            ->where('producto_id', $productoId)
            ->first();

        if (!$inv) return;

        if (($inv->stock_minimo ?? 0) > 0 && (int)$inv->stock_actual <= (int)$inv->stock_minimo) {
            $prodNombre = optional(Producto::find($productoId))->nombre ?? "ID {$productoId}";

            Notify::push(
                'inventario_bajo',
                'Inventario bajo',
                "«{$prodNombre}» está con stock bajo: {$inv->stock_actual} pzs (mínimo {$inv->stock_minimo}).",
                [
                    'accion'       => 'alerta_inventario',
                    'producto_id'  => (int) $productoId,
                    'stock'        => (int) $inv->stock_actual,
                    'stock_minimo' => (int) $inv->stock_minimo,
                ],
                'danger'
            );
        }
    }
}
