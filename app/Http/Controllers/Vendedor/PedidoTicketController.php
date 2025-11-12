<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoTicketController extends Controller
{
    public function show(Pedido $pedido)
    {
        // Cargar relaciones para el ticket
        $pedido->load([
            'items.producto:id,nombre',   // productos de las líneas
            'cliente:id,name,email',      // si tienes relación cliente() en Pedido
        ]);

        // Render de vista PDF (80mm ancho aprox)
        $pdf = Pdf::loadView('pdf.ticket', [
            'pedido' => $pedido,
        ])->setPaper([0, 0, 226.77, 841.89]); // 80mm x A4 alto aprox

        $name = 'ticket-' . ($pedido->folio ?: $pedido->id) . '.pdf';
        return $pdf->stream($name); // o ->download($name)
    }
}
