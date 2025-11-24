<?php

namespace App\Services;

use App\Models\Configuracion;

class ConfiguracionService
{
    /**
     * Devuelve la configuración global (fila única, id = 1)
     */
    public static function get(): ?Configuracion
    {
        return cache()->remember('config_global', 60, function () {
            return Configuracion::first(); // tu fila única
        });
    }

    /**
     * Indica si la tienda está abierta AHORA según horario_apertura / horario_cierre
     */
    public static function isOpenNow(): bool
    {
        $cfg = self::get();
        if (!$cfg) {
            // Si no hay configuración, asumimos abierto para no bloquear nada
            return true;
        }

        $now = now()->format('H:i');
        $ap  = substr($cfg->horario_apertura, 0, 5);
        $ci  = substr($cfg->horario_cierre, 0, 5);

        return $now >= $ap && $now <= $ci;
    }
}
