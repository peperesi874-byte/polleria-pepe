<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ClienteRapidoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => ['required','string','max:120'],
            'telefono' => ['nullable','string','max:30'],
            'email'    => ['nullable','email','max:120','unique:users,email'],
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name'     => $data['nombre'],
            'email'    => $data['email'] ?? (Str::slug($data['nombre']).'@temporal.local'),
            'password' => Hash::make($password),
            'role_id'  => 4, // cliente
            // Si agregas columna 'telefono' en users, también guárdala aquí
        ]);

        return response()->json([
            'ok'      => true,
            'cliente' => [
                'id'       => (int) $user->id,
                'nombre'   => $user->name,
                'telefono' => $data['telefono'] ?? null,
                'email'    => $user->email,
            ],
        ]);
    }
}
