<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    // permite create() / update() con estos campos
    protected $fillable = [
        'nombre', 'descripcion', 'precio', 'stock', 'activo', 'imagen',
    ];

    // casteos útiles
    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
        'stock' => 'integer',
    ];
}
