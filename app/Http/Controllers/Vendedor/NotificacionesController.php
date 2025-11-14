<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    /**
     * Listado de notificaciones del vendedor autenticado.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;
        $filtro = $request->get('estado', 'todas'); // todas | no_leidas | leidas

        $query = Notificacion::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if ($filtro === 'no_leidas') {
            $query->where('leida', false);
        } elseif ($filtro === 'leidas') {
            $query->where('leida', true);
        }

        $notificaciones = $query
            ->paginate(15)
            ->withQueryString()
            ->through(function (Notificacion $n) {
                return [
                    'id'         => $n->id,
                    'tipo'       => $n->tipo,
                    'nivel'      => $n->nivel,
                    'titulo'     => $n->titulo,
                    'mensaje'    => $n->mensaje,
                    'meta'       => $n->meta,
                    'leida'      => (bool) $n->leida,
                    'created_at' => optional($n->created_at)->toDateTimeString(),
                ];
            });

        $unreadCount = Notificacion::where('user_id', $userId)
            ->where('leida', false)
            ->count();

        return Inertia::render('Vendedor/Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'estado'         => $filtro,
            'unread_count'   => $unreadCount,
        ]);
    }

    /**
     * Marcar UNA notificación como leída.
     */
    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        abort_unless($notificacion->user_id === $request->user()->id, 403);

        if (! $notificacion->leida) {
            $notificacion->leida = true;
            $notificacion->save();
        }

        return back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Marcar TODAS las notificaciones de este vendedor como leídas.
     */
    public function marcarTodasLeidas(Request $request)
    {
        $userId = $request->user()->id;

        Notificacion::where('user_id', $userId)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }
}
