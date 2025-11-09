<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar la vista de registro.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Registrar un nuevo usuario y redirigir (con full reload) según rol.
     */
    public function store(Request $request): SymfonyResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            // ✅ Por documentos oficiales: al registrarse es CLIENTE por defecto
            // 1=Admin, 2=Vendedor, 3=Repartidor, 4=Cliente
            'role_id'  => 4,
        ]);

        event(new Registered($user));

        // Inicia sesión automáticamente
        Auth::login($user);

        // ✅ Full reload hacia la ruta que decide por rol
        // (evita tener que recargar manualmente después del registro)
        return Inertia::location(route('redirect.by.role'));
    }
}
