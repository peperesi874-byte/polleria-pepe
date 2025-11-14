<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeRedirectController extends Controller
{
    public function __invoke()
    {
        $u = Auth::user();

        if (!$u) {
            return redirect()->route('login');
        }

        return match ($u->rol ?? $u->role ?? $u->role_id ?? 'cliente') {

            /*
            |--------------------------------------------------------------
            | ADMIN (role_id = 1)
            |--------------------------------------------------------------
            */
            'admin', 1 => redirect()->route('admin.dashboard'),

            /*
            |--------------------------------------------------------------
            | VENDEDOR (role_id = 2)
            |--------------------------------------------------------------
            */
            'vendedor', 2 => redirect()->route('vendedor.dashboard'),

            /*
            |--------------------------------------------------------------
            | REPARTIDOR (role_id = 3)
            |--------------------------------------------------------------
            */
             'repartidor', 3        => redirect()->route('repartidor.dashboard'),

            /*
            |--------------------------------------------------------------
            | CLIENTE (role_id = 4)
            |--------------------------------------------------------------
            */
            'cliente', 4 => redirect()->route('cliente.inicio'),

            /*
            |--------------------------------------------------------------
            | DEFAULT → catálogo público
            |--------------------------------------------------------------
            */
            default => redirect()->route('catalogo.index'),
        };
    }
}
