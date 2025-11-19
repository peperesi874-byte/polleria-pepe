<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $pedido->folio ?? ('PED-'.$pedido->id) }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .ticket {
            width: 210px; /* aprox 80mm */
            margin: 0 auto;
            padding: 6px 4px;
        }
        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }
        .mt-2 { margin-top: 2px; }
        .mt-4 { margin-top: 4px; }
        .mt-6 { margin-top: 6px; }
        .mb-2 { margin-bottom: 2px; }
        .mb-4 { margin-bottom: 4px; }
        .mb-6 { margin-bottom: 6px; }
        .small  { font-size: 9px; }
        .tiny   { font-size: 8px; }
        .uppercase { text-transform: uppercase; }

        .divider {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            font-size: 9px;
            padding: 1px 0;
            vertical-align: top;
        }

        th {
            font-weight: bold;
        }

        .w-40 { width: 40%; }
        .w-20 { width: 20%; }
        .w-15 { width: 15%; }
        .w-25 { width: 25%; }

        .line-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>
<div class="ticket">

    {{-- ENCABEZADO --}}
    <div class="center">
        <div class="bold uppercase" style="font-size: 13px;">
            POLLERÍA PEPE
        </div>
        <div class="tiny mt-2">
            Calle Oriente No. 15, Col. Centro<br>
            C.P. 30700, Tapachula, Chiapas
        </div>
        <div class="tiny mt-2">
            Pedidos para llevar y a domicilio
        </div>
    </div>

    <div class="divider"></div>

    {{-- INFO PEDIDO / CLIENTE --}}
    @php
        $folio   = $pedido->folio ?? ('PED-'.$pedido->id);
        $fecha   = optional($pedido->created_at)->format('d/m/Y H:i');
        $tipo    = $pedido->tipo_entrega ?? $pedido->tipo ?? '';
        $estado  = $pedido->estado ?? '';
        $cliente = $pedido->cliente->name ?? null;
        $correo  = $pedido->cliente->email ?? null;
        $itemsCount = $pedido->items?->count() ?? 0;
    @endphp

    <table>
        <tr>
            <td class="small">Folio:</td>
            <td class="small right bold">{{ $folio }}</td>
        </tr>
        <tr>
            <td class="small">Fecha:</td>
            <td class="small right">{{ $fecha }}</td>
        </tr>
        <tr>
            <td class="small">Tipo:</td>
            <td class="small right">
                {{ ucfirst(str_replace('_', ' ', $tipo)) }}
            </td>
        </tr>
        <tr>
            <td class="small">Estado:</td>
            <td class="small right">
                {{ ucfirst(str_replace('_', ' ', $estado)) }}
            </td>
        </tr>

        @if($cliente)
            <tr>
                <td class="small">Cliente:</td>
                <td class="small right">{{ $cliente }}</td>
            </tr>
        @endif

        @if($correo)
            <tr>
                <td class="small">Correo:</td>
                <td class="small right tiny">{{ $correo }}</td>
            </tr>
        @endif
    </table>

    {{-- ENVÍO A DOMICILIO (SI APLICA) --}}
    @php
        $esDomicilio = $tipo === 'domicilio';

        $nombreEnv      = $pedido->entrega_nombre      ?? data_get($pedido, 'dom.nombre');
        $telEnv         = $pedido->entrega_telefono    ?? data_get($pedido, 'dom.telefono');
        $calleEnv       = $pedido->entrega_calle       ?? null;
        $numeroEnv      = $pedido->entrega_numero      ?? null;
        $coloniaEnv     = $pedido->entrega_colonia     ?? data_get($pedido, 'dom.colonia');
        $munEnv         = $pedido->entrega_municipio   ?? data_get($pedido, 'dom.ciudad');
        $refsEnv        = $pedido->entrega_referencias ?? data_get($pedido, 'dom.referencias');

        $lineaDir = trim(
            ($calleEnv ? $calleEnv : '') .
            ($numeroEnv ? ' #'.$numeroEnv : '')
        );

        $lineaZona = collect([$coloniaEnv, $munEnv])->filter()->implode(', ');
    @endphp

    @if($esDomicilio)
        <div class="divider"></div>
        <div class="small bold mb-2">Datos de envío</div>
        <table>
            @if($nombreEnv)
                <tr>
                    <td class="small">Nombre:</td>
                    <td class="small right">{{ $nombreEnv }}</td>
                </tr>
            @endif
            @if($telEnv)
                <tr>
                    <td class="small">Teléfono:</td>
                    <td class="small right">{{ $telEnv }}</td>
                </tr>
            @endif
            @if($lineaDir !== '')
                <tr>
                    <td class="small">Dirección:</td>
                    <td class="small right tiny">{{ $lineaDir }}</td>
                </tr>
            @endif
            @if($lineaZona !== '')
                <tr>
                    <td class="small">Zona:</td>
                    <td class="small right tiny">{{ $lineaZona }}</td>
                </tr>
            @endif
            @if($refsEnv)
                <tr>
                    <td class="small">Ref.:</td>
                    <td class="small right tiny">{{ $refsEnv }}</td>
                </tr>
            @endif
        </table>
    @endif

    <div class="divider"></div>

    {{-- DETALLE DE PRODUCTOS --}}
    <table>
        <thead>
        <tr>
            <th class="small w-40">Prod.</th>
            <th class="small center w-15">Cant</th>
            <th class="small right w-20">P.U.</th>
            <th class="small right w-25">Importe</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pedido->items ?? [] as $item)
            <tr>
                <td class="small line-clamp">
                    {{ $item->producto->nombre ?? 'Prod '.$item->producto_id }}
                </td>
                <td class="small center">
                    {{ (int) $item->cantidad }}
                </td>
                <td class="small right">
                    ${{ number_format($item->precio_unitario ?? 0, 2) }}
                </td>
                <td class="small right">
                    ${{ number_format($item->subtotal ?? 0, 2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- TOTALES / RESUMEN --}}
    <table>
        <tr>
            <td class="small">Artículos:</td>
            <td class="small right">{{ $itemsCount }}</td>
        </tr>
        <tr>
            <td class="small bold">TOTAL</td>
            <td class="small bold right">
                ${{ number_format($pedido->total ?? 0, 2) }}
            </td>
        </tr>
    </table>

    @if($pedido->observaciones)
        <div class="divider"></div>
        <div class="small bold mb-1">Notas:</div>
        <div class="tiny">
            {{ $pedido->observaciones }}
        </div>
    @endif

    <div class="divider"></div>

    {{-- MENSAJE FINAL --}}
    <div class="center mt-4 tiny">
        ¡Gracias por su compra!<br>
        Pollería Pepe · Tapachula, Chiapas
    </div>

</div>
</body>
</html>
