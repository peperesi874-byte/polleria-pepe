<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['id' => 1, 'nombre' => 'Admin',      'descripcion' => 'Administrador del sistema', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nombre' => 'Vendedor',   'descripcion' => 'Gestiona catálogo/pedidos', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nombre' => 'Repartidor', 'descripcion' => 'Entregas',                  'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nombre' => 'Cliente',    'descripcion' => 'Cliente',                   'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($rows as $r) {
            DB::table('roles')->updateOrInsert(['id' => $r['id']], $r);
        }
    }
}
