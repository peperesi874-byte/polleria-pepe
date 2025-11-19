<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $items = Notificacion::query()
            ->whereIn('tipo', ['pedido.asignacion', 'pedido.estado', 'pedido.cancelado'])
            ->where(function ($q) use ($user) {
                $q->where('meta->repartidor_id', $user->id)
                  ->orWhere('meta->repartidor_nuevo', $user->id);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'id',
                'titulo',
                'mensaje',
                'tipo',
                'nivel',
                'meta',
                'leida',
                'created_at',
            ]);

        return Inertia::render('Repartidor/Notificaciones/Index', [
            'items' => $items,
        ]);
    }
}
