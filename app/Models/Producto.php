<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    // Exponer automáticamente la URL pública
    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) return null;

        // Asegura que no vengan prefijos indebidos desde BD
        $path = ltrim($this->imagen, '/\\');
        $path = preg_replace('#^(storage/)+#i', '', $path);
        $path = preg_replace('#^(public/)+#i',   '', $path);
        $path = ltrim($path, '/\\');

        return Storage::disk('public')->url($path); // => /storage/productos/archivo.jpg
    }

    public function inventario()
    {
        return $this->hasOne(\App\Models\Inventario::class, 'producto_id');
    }

    public function movimientos()
    {
        return $this->hasMany(\App\Models\InventarioMovimiento::class, 'producto_id')->latest('id');
    }
}
