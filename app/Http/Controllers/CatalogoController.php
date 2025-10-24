<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $minInput = $request->input('min');
        $maxInput = $request->input('max');
        $sort     = $request->input('sort', 'nombre'); // nombre|precio|recientes
        $dir      = $request->input('dir', 'asc');     // asc|desc

        // 1) Construir query
        $query = Producto::query()
            ->where('activo', 1)
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('nombre', 'like', "%{$q}%")
                      ->orWhere('descripcion', 'like', "%{$q}%");
                });
            })
            ->when($minInput !== null && $minInput !== '', fn ($w) => $w->where('precio', '>=', (float) $minInput))
            ->when($maxInput !== null && $maxInput !== '', fn ($w) => $w->where('precio', '<=', (float) $maxInput));

        // 2) Orden
        if ($sort === 'precio') {
            $query->orderBy('precio', $dir === 'desc' ? 'desc' : 'asc');
        } elseif ($sort === 'recientes') {
            $query->orderBy('id', 'desc');
        } else {
            $query->orderBy('nombre', $dir === 'desc' ? 'desc' : 'asc');
        }

        // 3) Paginación
        $productos = $query->paginate(12)
    ->through(function ($p) {
        return [
            'id'          => $p->id,
            'nombre'      => $p->nombre,
            'descripcion' => $p->descripcion,
            'precio'      => (float) $p->precio,
            'stock'       => (int) $p->stock,
            // URL pública que el front puede mostrar:
            'imagenUrl'   => $p->imagen ? Storage::url($p->imagen) : null,
        ];
    })
    ->withQueryString();

        // 4) Respuesta Inertia
        return Inertia::render('Catalogo/Index', [
            'productos' => $productos,
            'filters'   => [
                'q'    => $q,
                'min'  => $request->filled('min') ? (float) $minInput : null,
                'max'  => $request->filled('max') ? (float) $maxInput : null,
                'sort' => $sort,
                'dir'  => $dir,
            ],
            // para enlaces del header (evita error si no existen)
            'canLogin'     => Route::has('login'),
            'canRegister'  => Route::has('register'),
            'loginUrl'     => Route::has('login') ? route('login') : null,
            'registerUrl'  => Route::has('register') ? route('register') : null,
        ]);
    }
}
