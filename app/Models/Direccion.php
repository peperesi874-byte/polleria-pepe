<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    protected $table = 'direcciones';

    protected $fillable = [
        'user_id',
        'nombre_contacto',
        'telefono',
        'calle',
        'numero',
        'colonia',
        'ciudad',
        'estado',
        'cp',
        'referencias',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
