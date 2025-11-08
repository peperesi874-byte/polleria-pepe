<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        $notifs = Notificacion::query()
            ->when($q !== '', fn($w) => $w->where(function($qq) use ($q) {
                $qq->where('titulo', 'like', "%{$q}%")
                   ->orWhere('mensaje', 'like', "%{$q}%");
            }))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($n) {
                return [
                    'id'      => $n->id,
                    'fecha'   => $n->created_at?->format('d/m/Y H:i'),
                    'titulo'  => $n->titulo,
                    'mensaje' => $n->mensaje,
                    // 'leida'   => (bool) $n->leida, // opcional: ya no se usa
                ];
            });

        return Inertia::render('Admin/Notificaciones/Index', [
            'notificaciones' => $notifs,
            'filters'        => ['q' => $q],
        ]);
    }
}
