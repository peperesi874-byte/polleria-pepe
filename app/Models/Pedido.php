<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Direccion;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'folio',
        'id_cliente',
        'direccion_id',      // 👈 IMPORTANTE: permitir guardar la dirección
        'tipo_entrega',      // mostrador | domicilio
        'estado',            // pendiente, preparando, listo, en_camino, entregado, cancelado
        'observaciones',
        'total',
        'asignado_a',        // id de repartidor (users.id)

        // Datos de envío a domicilio (coinciden con la BD real)
        'entrega_nombre',
        'entrega_telefono',
        'entrega_calle',
        'entrega_numero',
        'entrega_colonia',
        'entrega_municipio',
        'entrega_referencias',

        // JSON opcional con el bloque de domicilio
        'dom',

        // Otros
        'motivo_cancelacion',
    ];

    protected $casts = [
        'dom'   => 'array',
        'total' => 'decimal:2',
    ];

    /* Relaciones */
    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_cliente');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PedidoLog::class, 'pedido_id');
    }

    /**
     * Dirección asociada al pedido (usa la columna direccion_id).
     */
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }
}
