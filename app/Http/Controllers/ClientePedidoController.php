<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientePedidoController extends Controller
{
    /**
     * Mostrar los pedidos del cliente autenticado.
     */
    public function index()
    {
        // Obtiene los pedidos del cliente logueado
        $pedidos = DB::table('pedidos')
            ->where('id_cliente', auth()->id()) // ✅ columna correcta
            ->orderByDesc('created_at')
            ->select('id', 'estado', 'total', 'created_at') // ✅ sintaxis corregida
            ->get();

        // Envía los pedidos a la vista de Inertia
        return Inertia::render('Cliente/Pedidos/Index', [
            'pedidos' => $pedidos,
        ]);
    }
}
