<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class ClientePerfilController extends Controller
{
    /**
     * Mostrar el formulario de perfil del cliente.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Cliente/Perfil/Edit', [
            'perfil' => [
                'name'      => $user->name,
                'apellido'  => $user->apellido,
                'email'     => $user->email,
                'telefono'  => $user->telefono,
                'direccion' => $user->direccion,
            ],
        ]);
    }

    /**
     * Actualizar la información del perfil del cliente.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'apellido'  => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($data);

        return redirect()
            ->back()
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
