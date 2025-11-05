<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $fillable = [
        'producto_id',
        'stock_actual',
        'stock_minimo',
    ];

    protected static function booted()
    {
        // Cada vez que cambie el inventario, refleja en productos.stock
        static::saved(function (Inventario $inv) {
            if ($inv->producto_id) {
                \App\Models\Producto::where('id', $inv->producto_id)
                    ->update(['stock' => (int) $inv->stock_actual]);
            }
        });
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id');
    }

    public function movimientos()
    {
        return $this->hasMany(\App\Models\InventarioMovimiento::class, 'producto_id', 'producto_id');
    }
}
