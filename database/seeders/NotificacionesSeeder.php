<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificacionesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('notificaciones')->insert([
            [
                'user_id'    => 1,
                'titulo'     => 'Bienvenido',
                'mensaje'    => 'Tu cuenta ha sido creada correctamente.',
                'tipo'       => 'info',     // info | alerta
                'nivel'      => 'bajo',     // opcional: solo si tu tabla lo tiene
                'leida'      => false,      // asegura compatibilidad al marcar leídas
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id'    => 1,
                'titulo'     => 'Inventario bajo',
                'mensaje'    => '“Pechuga de pollo” está por debajo del mínimo (3 / 5 piezas).',
                'tipo'       => 'alerta',
                'nivel'      => 'alto',
                'leida'      => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
