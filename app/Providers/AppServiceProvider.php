<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

use App\Models\BitacoraLog;
use App\Models\Notificacion;
use App\Models\Configuracion;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prefetch de Vite
        Vite::prefetch(concurrency: 3);

        /**
         * 🗒️ Bitácora reciente (dropdown/panel del dashboard)
         * Clave: bitacoraTop
         */
        Inertia::share('bitacoraTop', function () {
            if (App::runningInConsole()) return [];
            if (!Schema::hasTable('bitacora_logs')) return [];

            return BitacoraLog::query()
                ->with('user:id,name')
                ->latest('id')
                ->limit(10)
                ->get(['id','user_id','accion','modulo','meta','created_at'])
                ->map(function ($l) {
                    return [
                        'id'      => (int) $l->id,
                        'fecha'   => optional($l->created_at)->format('d/m/Y H:i'),
                        'actor'   => $l->user?->name ?? 'Sistema',
                        'accion'  => (string) $l->accion,
                        'entidad' => (string) $l->modulo,
                        'detalle' => is_array($l->meta)
                            ? ($l->meta['detalle'] ?? null)
                            : (is_string($l->meta) ? $l->meta : null),
                    ];
                });
        });

        /**
         * 🔔 Notificaciones recientes (dropdown del dashboard)
         * Clave: notifTop
         */
        Inertia::share('notifTop', function () {
            if (App::runningInConsole()) return [];
            if (!Schema::hasTable('notificaciones')) return [];

            // Soporte para esquemas con 'leida' (bool) o 'leida_at' (timestamp)
            $hasLeida   = Schema::hasColumn('notificaciones', 'leida');
            $hasLeidaAt = Schema::hasColumn('notificaciones', 'leida_at');

            return Notificacion::query()
                ->latest('id')
                ->limit(10)
                ->get(['id','titulo','mensaje','tipo','leida','leida_at','created_at'])
                ->map(function ($n) use ($hasLeida, $hasLeidaAt) {
                    $leida = false;
                    if ($hasLeida)   $leida = (bool) $n->leida;
                    if ($hasLeidaAt) $leida = $leida || !is_null($n->leida_at);

                    return [
                        'id'      => (int) $n->id,
                        'fecha'   => optional($n->created_at)->format('d/m/Y H:i'),
                        'titulo'  => (string) ($n->titulo ?? ''),
                        'mensaje' => (string) ($n->mensaje ?? ''),
                        'tipo'    => (string) ($n->tipo ?? ''), // inventario | pedido | sistema | usuario
                        'leida'   => $leida,
                    ];
                });
        });

        /**
         * ⚙️ Configuración básica global
         * Clave: cfgBasic  (usada para umbral, horario y ciudad en el frontend)
         */
        Inertia::share('cfgBasic', function () {
            if (App::runningInConsole()) return null;
            if (!Schema::hasTable('configuraciones')) return null;

            $c = Configuracion::find(1);
            if (!$c) return null;

            return [
                'horario_apertura' => $c->horario_apertura,
                'horario_cierre'   => $c->horario_cierre,
                'cobertura_ciudad' => $c->cobertura_ciudad,
                'stock_umbral'     => (int) $c->stock_umbral,
            ];
        });
    }
}
