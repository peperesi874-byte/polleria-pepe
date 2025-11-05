<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventarioSeedInicial extends Seeder
{
    public function run(): void
    {
        // Este seeder es "a prueba de balas": sólo inserta si existen las tablas.
        if (Schema::hasTable('productos')) {
            // Ejemplo: asegura al menos 1 producto de prueba
            $exists = DB::table('productos')->where('slug', 'pollo-entero')->exists();
            if (!$exists) {
                DB::table('productos')->insert([
                    'nombre'      => 'Pollo entero',
                    'slug'        => 'pollo-entero',
                    'descripcion' => 'Producto de prueba para inventario',
                    'precio'      => 150.00,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Si tienes tablas de inventario, puedes dejarlas preparadas así:
        // if (Schema::hasTable('inventarios')) { ... }
        // if (Schema::hasTable('inventario_movimientos')) { ... }
    }
}
