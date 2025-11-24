<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'horario_apertura',
        'horario_cierre',
        'zona_cobertura',
        'umbral_stock_bajo',
    ];

    protected $casts = [
        'umbral_stock_bajo' => 'integer',
    ];

    public $timestamps = false; // si tu tabla no tiene created_at / updated_at
}
