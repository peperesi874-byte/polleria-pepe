<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'folio',
        'id_cliente',
        'id_direccion',
        'asignado_a',
        'creado_por',
        'tipo_entrega',     // mostrador | domicilio
        'total',
        'estado',           // pendiente | preparando | listo | en_camino | entregado | cancelado
        'motivo_cancelacion',
        'observaciones',
    ];

    protected $casts = [
        'total' => 'float',
    ];

    /** Items del pedido */
    public function items(): HasMany
    {
        return $this->hasMany(\App\Models\PedidoItem::class, 'pedido_id');
    }

    /** Usuario repartidor asignado (users.id = asignado_a) */
    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'asignado_a');
    }

    /** Bitácora del pedido */
    public function logs(): HasMany
    {
        return $this->hasMany(\App\Models\PedidoLog::class, 'pedido_id')->latest('id');
    }
}
