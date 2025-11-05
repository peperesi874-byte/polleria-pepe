<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * Atributos ocultos para arrays/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión automática de tipos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Valor por defecto para nuevos usuarios.
     * 👉 Todo usuario registrado sin rol asignado será CLIENTE (role_id = 4)
     */
    protected $attributes = [
        'role_id' => 4,
    ];

    /**
     * Relación con el modelo Role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Role::class);
    }
}
