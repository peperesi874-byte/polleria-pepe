<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    protected $fillable = [
        'producto_id','user_id','tipo','cantidad','delta','motivo','stock_resultante'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'delta' => 'integer',
        'stock_resultante' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
