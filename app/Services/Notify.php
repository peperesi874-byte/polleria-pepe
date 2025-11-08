<?php

namespace App\Services;

use App\Models\Notificacion;

class Notify
{
    /** Creador genérico */
    public static function push(string $titulo, string $mensaje, string $tipo = 'sistema', array $meta = [], string $nivel = 'info'): Notificacion
    {
        return Notificacion::create([
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'tipo'    => $tipo,
            'nivel'   => $nivel,   // guardamos aunque no lo filtremos
            'meta'    => $meta,
            'leida'   => false,
        ]);
    }

    /** Inventario bajo */
    public static function lowStock(int $productoId, string $nombre, int $stockActual, int $stockMinimo): Notificacion
    {
        return self::push(
            'Inventario bajo',
            "El producto «{$nombre}» está por debajo del mínimo. Stock actual: {$stockActual} / Mínimo: {$stockMinimo}.",
            'inventario.bajo',
            ['producto_id' => $productoId, 'stock_actual' => $stockActual, 'stock_minimo' => $stockMinimo],
            'alerta'
        );
    }

    /** Pedido: cambio de estado */
    public static function pedidoEstado(int $pedidoId, string $estado, array $extra = []): Notificacion
    {
        return self::push(
            'Pedido actualizado',
            "El pedido #{$pedidoId} cambió a «{$estado}».",
            'pedido.estado',
            ['pedido_id' => $pedidoId] + $extra,
            'info'
        );
    }

    /** Pedido cancelado */
    public static function pedidoCancelado(int $pedidoId, array $extra = []): Notificacion
    {
        return self::push(
            'Pedido cancelado',
            "El pedido #{$pedidoId} fue cancelado.",
            'pedido.cancelado',
            ['pedido_id' => $pedidoId] + $extra,
            'alerta'
        );
    }

    /** Pedido asignado/desasignado */
    public static function pedidoAsignacion(int $pedidoId, string $accion, array $extra = []): Notificacion
    {
        return self::push(
            'Asignación de pedido',
            "El pedido #{$pedidoId} fue {$accion}.",
            'pedido.asignacion',
            ['pedido_id' => $pedidoId] + $extra,
            'info'
        );
    }
}
