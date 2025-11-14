<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\BitacoraService;
use App\Services\Notify;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PedidosController extends Controller
{
    /**
     * Ver pedidos asignados al repartidor autenticado (CU21)
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        $pedidos = Pedido::query()
            ->where('asignado_a', $user->id)
            ->orderByDesc('created_at')
            ->get([
                'id',
                'folio',
                'estado',
                'total',
                'entrega_nombre',
                'entrega_telefono',
                'entrega_calle',
                'entrega_numero',
                'entrega_colonia',
                'entrega_municipio',
                'entrega_referencias',
                'created_at',
            ]);

        return Inertia::render('Repartidor/Pedidos/Index', [
            'pedidos' => $pedidos,
        ]);
    }

    /**
     * Cambiar estado: en_camino / entregado (CU22, CU23)
     * Nota: en_camino = "en reparto" según documentación.
     */
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $user = $request->user();

        if ((int) $pedido->asignado_a !== (int) $user->id) {
            abort(403, 'No tienes permiso para modificar este pedido.');
        }

        // Usamos los valores REALES de la BD
        $data = $request->validate([
            'estado' => 'required|in:en_camino,entregado',
        ]);

        $nuevoEstado    = $data['estado'];
        $estadoAnterior = $pedido->estado;

        if ($nuevoEstado === 'en_camino') {
            // No tocar pedidos ya entregados/cancelados
            if (in_array($pedido->estado, ['entregado', 'cancelado'], true)) {
                return back()->with('error', 'No se puede poner en reparto.');
            }

            $pedido->estado = 'en_camino';

        } elseif ($nuevoEstado === 'entregado') {
            // Solo permitir si está en camino
            if (!in_array($pedido->estado, ['en_camino'], true)) {
                return back()->with('error', 'Solo se puede marcar entregado si está en reparto.');
            }

            $pedido->estado = 'entregado';
        }

        $pedido->save();

        // 📝 Bitácora
        BitacoraService::add(
            'pedidos',
            'cambio_estado',
            $pedido->id,
            [
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $pedido->estado,
                'repartidor_id'   => $user->id,
            ]
        );

        // 🔔 Notificación general de cambio de estado
        Notify::pedidoEstado(
            $pedido->id,
            $pedido->estado,
            [
                'via'             => 'repartidor',
                'repartidor_id'   => $user->id,
                'estado_anterior' => $estadoAnterior,
            ]
        );

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
