<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraLog extends Model
{
    protected $table = 'bitacora_logs';

    protected $fillable = [
        'user_id', 'modulo', 'accion', 'ref_id', 'meta', 'ip', 'ua',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
