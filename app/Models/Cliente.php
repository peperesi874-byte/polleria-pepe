<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    // Mantiene compatibilidad con tu código anterior
    protected $table = 'clientes';

    // Permite asignación masiva de los campos de perfil
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'direccion',
    ];

    // Opcional: para cuando relaciones con User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
