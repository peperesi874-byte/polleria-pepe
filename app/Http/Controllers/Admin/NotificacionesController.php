<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    /**
     * Listado de notificaciones del sistema (admin).
     */
    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        $notifs = Notificacion::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($qq) use ($q) {
                    $qq->where('titulo', 'like', "%{$q}%")
                       ->orWhere('mensaje', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($n) {
                return [
                    'id'      => (int) $n->id,
                    'fecha'   => $n->created_at?->format('d/m/Y H:i'),
                    'titulo'  => $n->titulo,
                    'mensaje' => $n->mensaje,
                    'tipo'    => $n->tipo ?? null,
                    // 👇 usamos solo la columna booleana "leida"
                    'leida'   => (bool) ($n->leida ?? false),
                ];
            });

        return Inertia::render('Admin/Notificaciones/Index', [
            'notificaciones' => $notifs,
            'filters'        => ['q' => $q],
        ]);
    }

    /**
     * PATCH /admin/notificaciones/{n}/leer
     * Marcar una notificación como leída.
     */
    public function markRead(Notificacion $n)
    {
        $n->leida = true;
        $n->save();

        return back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * PATCH /admin/notificaciones/leer-todas
     * Marcar todas como leídas.
     */
    public function markAllRead()
    {
        Notificacion::where('leida', '!=', 1)->update(['leida' => 1]);

        return back()->with('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }

    /**
     * DELETE /admin/notificaciones/leidas
     * Eliminar todas las notificaciones que ya están leídas.
     */
    public function deleteRead()
    {
        $count = Notificacion::where('leida', 1)->count();

        if ($count === 0) {
            return back()->with('info', 'No hay notificaciones leídas para eliminar.');
        }

        Notificacion::where('leida', 1)->delete();

        return back()->with('success', "Se eliminaron {$count} notificaciones leídas.");
    }

    /**
     * DELETE /admin/notificaciones
     * Eliminar TODAS las notificaciones (leídas y no leídas).
     */
    public function deleteAll()
    {
        $count = Notificacion::count();

        if ($count === 0) {
            return back()->with('info', 'No hay notificaciones para eliminar.');
        }

        Notificacion::query()->delete();

        return back()->with('success', "Se eliminaron todas las notificaciones ({$count}).");
    }
}
