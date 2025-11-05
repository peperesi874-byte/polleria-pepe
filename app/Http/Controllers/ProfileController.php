<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'user' => [
                'id'    => $request->user()->id,
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            // contraseña opcional
            'password'              => ['nullable','string','min:8','confirmed'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Perfil actualizado.');
    }

    // Opcional: eliminar cuenta
    public function destroy(Request $request)
    {
        $user = $request->user();
        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalogo.index');
    }
}
