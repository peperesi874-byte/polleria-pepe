<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar la vista de inicio de sesión.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    /**
     * Procesar la autenticación y redirigir según rol.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $roleId = (int) (auth()->user()->role_id ?? 0);

        // Redirecciones duras por rol (sin intended / sin middleware)
        if ($roleId === 1) { // Administrador
            return redirect('/admin/dashboard');
        }
        if ($roleId === 2) { // Vendedor
            return redirect('/vendedor');
        }
        if ($roleId === 3) { // Repartidor
            return redirect('/repartidor');
        }

        // Cliente u otro → Catálogo
        return redirect('/catalogo');
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
