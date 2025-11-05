<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'descripcion', 'precio', 'stock', 'activo', 'imagen',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
        'stock'  => 'integer',
    ];

    public function inventario()
{
    return $this->hasOne(\App\Models\Inventario::class, 'producto_id');
}

public function movimientos()
{
    return $this->hasMany(\App\Models\InventarioMovimiento::class, 'producto_id')->latest('id');
}
}
