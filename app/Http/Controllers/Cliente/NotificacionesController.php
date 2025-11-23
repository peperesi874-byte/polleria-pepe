<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    /**
     * Listado de notificaciones del CLIENTE autenticado.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }

        $items = Notificacion::query()
            ->where('user_id', $user->id)   // 👈 solo las del cliente
            ->orderByDesc('id')
            ->get()
            ->map(function (Notificacion $n) {
                return [
                    'id'         => (int) $n->id,
                    'titulo'     => $n->titulo,
                    'mensaje'    => $n->mensaje,
                    'tipo'       => $n->tipo ?? null,
                    'leida'      => (bool) ($n->leida ?? false),
                    'created_at' => optional($n->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Cliente/Notificaciones/Index', [
            // 👇 nombre EXACTO que tu Vue usa: props.items
            'items' => $items,
        ]);
    }

    /**
     * Marcar UNA notificación como leída.
     */
  public function markAsRead(Request $request, $id)
{
    $userId = $request->user()->id;

    // Buscar la notificación por id y que sea del usuario autenticado
    $notificacion = Notificacion::query()
        ->where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$notificacion) {
        abort(404);
    }

    if (!$notificacion->leida) {
        $notificacion->leida = 1; // marcar como leída
        $notificacion->save();
    }

    return back();
}

    /**
     * Marcar TODAS las notificaciones del cliente como leídas.
     */
    public function markAllAsRead(Request $request)
    {
        $userId = $request->user()->id;

        Notificacion::query()
            ->where('user_id', $userId)
            ->where('leida', 0)
            ->update(['leida' => 1]);

        return back();
    }
}
