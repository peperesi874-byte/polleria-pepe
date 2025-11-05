<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoLog extends Model
{
    protected $table = 'pedido_logs';

    protected $fillable = [
        'pedido_id',
        'accion',   // estado_cambiado | asignado | desasignado | cancelado
        'de',
        'a',
        'motivo',   // para cancelaciones
        'user_id',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pedido::class, 'pedido_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
