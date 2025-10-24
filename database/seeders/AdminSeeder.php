<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Evita duplicados si ya existe el admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@polleria.test'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('password'), // <-- cámbialo luego
                'role_id'  => 1,                      // Admin
                'email_verified_at' => now(),
            ]
        );

        // Por si el usuario ya existía sin rol
        if ($admin->role_id !== 1) {
            $admin->forceFill(['role_id' => 1])->save();
        }
    }
}
