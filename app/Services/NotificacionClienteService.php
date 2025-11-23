<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Pedido;

class NotificacionClienteService
{
    /**
     * Crea una notificación para el CLIENTE cuando cambia el estado de su pedido.
     *
     * @param  \App\Models\Pedido  $pedido
     * @param  string              $anterior  Estado anterior (ej. pendiente)
     * @param  string              $nuevo     Estado nuevo (ej. preparando)
     * @return \App\Models\Notificacion|null
     */
    public static function pedidoCambioEstado(Pedido $pedido, string $anterior, string $nuevo): ?Notificacion
    {
        // 👇 Cliente dueño del pedido (columna id_cliente en la tabla pedidos)
        $clienteId = $pedido->id_cliente;

        // Si por alguna razón el pedido no tiene cliente, no hacemos nada
        if (!$clienteId) {
            return null;
        }

        // Folio amigable
        $folio = $pedido->folio ?: ('#' . $pedido->id);

        // Creamos la notificación SOLO para ese cliente
        return Notificacion::create([
            'user_id' => $clienteId, // 👈 CLAVE: se amarra al cliente
            'titulo'  => 'Actualización de tu pedido',
            'mensaje' => "Tu pedido {$folio} cambió de «{$anterior}» a «{$nuevo}».",
            'tipo'    => 'cliente.pedido.estado',
            'nivel'   => 'info',
            'meta'    => [
                'pedido_id'        => $pedido->id,
                'folio'            => $folio,
                'estado_anterior'  => $anterior,
                'estado_nuevo'     => $nuevo,
            ],
            'leida'   => false,
        ]);
    }
}
