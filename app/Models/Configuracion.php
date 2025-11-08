<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    // Usamos id=1 como fila única; no es obligatorio cambiar el autoincremento.
    // Si tu migración creó id autoincremental, no pongas $incrementing = false.

    protected $fillable = [
        'horario_apertura',
        'horario_cierre',
        'cobertura_ciudad',
        'stock_umbral',
    ];

    protected $casts = [
        'stock_umbral' => 'integer',
    ];
}
