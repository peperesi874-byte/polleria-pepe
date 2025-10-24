<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['nombre' => 'Pierna grande', 'descripcion' => 'Pierna de pollo grande', 'precio' => 100, 'stock' => 20, 'activo' => 1, 'imagen' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Muslo',         'descripcion' => 'Muslo fresco',            'precio' =>  65, 'stock' => 35, 'activo' => 1, 'imagen' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Pechuga',       'descripcion' => 'Pechuga sin piel',        'precio' => 120, 'stock' => 15, 'activo' => 1, 'imagen' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($rows as $r) {
            DB::table('productos')->updateOrInsert(
                ['nombre' => $r['nombre']],
                $r
            );
        }
    }
}
