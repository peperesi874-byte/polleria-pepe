<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar la vista de inicio de sesión.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    /**
     * Procesar la autenticación y redirigir según rol.
     * 
     * 🔁 Importante:
     * Usamos Inertia::location() para forzar una recarga completa del navegador.
     * Así se actualizan correctamente los props de autenticación (sin tener que recargar manualmente).
     */
    public function store(LoginRequest $request): SymfonyResponse
    {
        // Verificar credenciales
        $request->authenticate();

        // Regenerar sesión
        $request->session()->regenerate();

        // 🚀 Forzar recarga completa a la ruta que decide por rol
        // (redirige automáticamente según role_id: admin, vendedor, cliente, etc.)
        return Inertia::location(route('redirect.by.role'));
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 👇 Aquí el cambio: manda al catálogo
        return redirect()->route('catalogo.index'); // o: return redirect('/catalogo');
    }
}
