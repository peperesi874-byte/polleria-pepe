<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Pedido;
use App\Models\PedidoLog;





class ClientePedidoController extends Controller
{
    /**
     * Mostrar los pedidos del cliente autenticado.
     */
    public function index()
{
    // Obtiene los pedidos del cliente logueado con paginación
    $pedidos = DB::table('pedidos')
    ->where('id_cliente', auth()->id())
    ->orderByDesc('created_at')
    ->select('id', 'folio', 'estado', 'total', 'created_at') // agregamos folio
    ->paginate(5)
    ->withQueryString();

    // Envía los pedidos a la vista de Inertia
    return Inertia::render('Cliente/Pedidos/Index', [
        'pedidos' => $pedidos,
    ]);
}

 public function cancelar(Request $request, Pedido $pedido)
{
    $userId = auth()->id();

    // 1) Validar que el pedido pertenece al cliente autenticado
    if ((int) $pedido->id_cliente !== (int) $userId) {
        abort(403, 'No puedes cancelar este pedido.');
    }

    // 2) Solo se puede cancelar si está pendiente o preparando
    $estadosCancelables = ['pendiente', 'preparando'];

    if (! in_array((string) $pedido->estado, $estadosCancelables, true)) {
        return back()->with('error', 'Este pedido ya no puede ser cancelado.');
    }

    // 3) Validar motivo enviado desde el formulario
    $data = $request->validate([
        'motivo' => ['required', 'string', 'max:255'],
    ]);

    // 🔹 Guardar el estado anterior
    $anterior = $pedido->estado;

    // 4) Cambiar a cancelado y guardar motivo EN ESA COLUMNA
    $pedido->estado = 'cancelado';
    $pedido->motivo_cancelacion = $data['motivo'];
    $pedido->save();

    // 🧾 Log interno del pedido (lo que ve la bitácora del show)
    PedidoLog::create([
        'pedido_id' => $pedido->id,
        'user_id'   => auth()->id(),
        'accion'    => 'cancelado',
        'de'        => $anterior,
        'a'         => 'cancelado',
        'motivo'    => $data['motivo'],
    ]);

    // 📝 Bitácora global
    \App\Services\BitacoraService::add('pedidos', 'cancelado', $pedido->id, [
        'motivo' => $data['motivo'],
        'by'     => auth()->user()->name,
    ]);

    return back()->with('success', 'Tu pedido ha sido cancelado correctamente.');
}


}
