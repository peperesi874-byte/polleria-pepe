<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Ticket</title>
  <style>
    * { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1, h2, h3 { margin: 0 0 6px; padding: 0; }
    .center { text-align: center; }
    .right  { text-align: right; }
    .muted  { color: #666; }
    .line   { border-top: 1px dashed #999; margin: 8px 0; }
    table   { width: 100%; border-collapse: collapse; }
    td      { padding: 2px 0; vertical-align: top; }
  </style>
</head>
<body>
  <div class="center">
    <h2>Pollería Pepe</h2>
    <div class="muted">Tapachula, Chiapas</div>
  </div>

  <div class="line"></div>

  <table>
    <tr>
      <td>Folio</td>
      <td class="right">{{ $pedido->folio ?? $pedido->id }}</td>
    </tr>
    <tr>
      <td>Fecha</td>
      <td class="right">{{ optional($pedido->created_at)->format('Y-m-d H:i') }}</td>
    </tr>
    <tr>
      <td>Tipo</td>
      <td class="right">{{ ucfirst(str_replace('_',' ', $pedido->tipo_entrega)) }}</td>
    </tr>
    <tr>
      <td>Estado</td>
      <td class="right">{{ ucfirst(str_replace('_',' ', $pedido->estado)) }}</td>
    </tr>
    @if($pedido->relationLoaded('cliente') && $pedido->cliente)
      <tr>
        <td>Cliente</td>
        <td class="right">{{ $pedido->cliente->name }}</td>
      </tr>
    @endif
  </table>

  @if($pedido->tipo_entrega === 'domicilio')
    <div class="line"></div>
    <strong>Envío a domicilio</strong>
    <div>{{ $pedido->entrega_nombre }}</div>
    <div>{{ $pedido->entrega_telefono }}</div>
    <div>{{ $pedido->entrega_direccion }}</div>
    <div>{{ $pedido->entrega_colonia }}</div>
    <div>{{ $pedido->entrega_ciudad }}</div>
    @if($pedido->entrega_referencias)
      <div class="muted">{{ $pedido->entrega_referencias }}</div>
    @endif
  @endif

  <div class="line"></div>

  <table>
    @foreach($pedido->items as $it)
      <tr>
        <td>
          {{ $it->producto->nombre ?? 'Producto' }}
          <span class="muted">× {{ (int) $it->cantidad }}</span>
        </td>
        <td class="right">${{ number_format($it->subtotal, 2) }}</td>
      </tr>
      <tr>
        <td class="muted">P. unit:</td>
        <td class="right" class="muted">${{ number_format($it->precio_unitario, 2) }}</td>
      </tr>
    @endforeach
  </table>

  <div class="line"></div>

  <table>
    <tr>
      <td><strong>Total</strong></td>
      <td class="right"><strong>${{ number_format($pedido->total, 2) }}</strong></td>
    </tr>
  </table>

  <div class="line"></div>

  <div class="center">¡Gracias por su compra! 🐔</div>
</body>
</html>
