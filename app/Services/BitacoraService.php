<?php

namespace App\Services;

use App\Models\BitacoraLog;

class BitacoraService
{
    public static function add(string $modulo, string $accion, ?int $refId = null, array $meta = []): void
    {
        try {
            $req = request();
            BitacoraLog::create([
                'user_id' => auth()->id(),
                'modulo'  => $modulo,
                'accion'  => $accion,
                'ref_id'  => $refId,
                'meta'    => $meta,
                'ip'      => $req?->ip(),
                'ua'      => $req?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // en caso de error no se rompe la ejecución principal
            \Log::error('Bitácora error: ' . $e->getMessage());
        }
    }
}
