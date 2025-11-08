<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'titulo', 'mensaje', 'tipo', 'nivel', 'meta', 'leida'
    ];

    protected $casts = [
        'meta'  => 'array',
        'leida' => 'boolean',
    ];
}
