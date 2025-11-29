@php
  // Puedes poner tu logo en public/logo.png (opcional)
  $logoPath = public_path('logo.png');
  $hasLogo  = file_exists($logoPath);

  $rows     = $rows ?? [];

  // Métricas básicas
  $total    = count($rows);
  $bajoMin  = collect($rows)->filter(fn($r) => $r->stock_actual <= $r->stock_minimo)->count();

  // Métricas extra para que se vea más pro
  $stockTotal = collect($rows)->sum(fn($r) => (int) ($r->stock_actual ?? 0));
  $valorTotal = collect($rows)->sum(fn($r) => (float) ($r->stock_actual * $r->precio));

  $fechaReporte = $fecha ?? now()->format('d/m/Y H:i');
@endphp
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>{{ $titulo ?? 'Reporte de inventario' }}</title>
  <style>
    /* Márgenes del documento */
    @page { margin: 28px 28px 36px 28px; }

    /* Tipografía básica (compatible con DomPDF) */
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }

    /* Encabezado */
    .header {
      width: 100%;
      border-bottom: 2px solid #e5e7eb;
      padding-bottom: 10px;
      margin-bottom: 12px;
    }
    .brand {
      display: inline-block;
      vertical-align: middle;
    }
    .brand-title {
      font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 2px 0;
    }
    .brand-sub {
      font-size: 11px; color: #6b7280; margin: 0;
    }
    .brand-right {
      float: right; text-align: right; font-size: 11px; color: #374151;
    }
    .muted { color: #6b7280; }

    /* Bloques de resumen */
    .stats {
      margin: 8px 0 14px 0;
      width: 100%;
    }
    .stat {
      display: inline-block;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      padding: 8px 10px;
      border-radius: 6px;
      margin-right: 6px;
      margin-bottom: 6px;
      font-size: 11px;
      min-width: 130px;
    }
    .stat-label { font-size: 10px; text-transform: uppercase; color: #6b7280; }
    .stat-strong { font-weight: 700; color: #111827; display: block; margin-top: 2px; }

    /* Tabla */
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #e5e7eb; padding: 7px 8px; }
    thead th {
      background: #eef2ff;
      color: #1e40af;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: .3px;
    }
    tbody tr:nth-child(odd) { background: #fcfcff; }
    .right { text-align: right; }
    .center { text-align: center; }

    /* Chips / badges */
    .chip {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 999px;
      font-size: 10px;
      font-weight: 700;
    }
    .chip-low { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; }
    .chip-ok  { color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; }

    /* Footer con numeración de páginas */
    .footer {
      position: fixed;
      left: 0; right: 0; bottom: -10px;
      height: 20px;
      text-align: right;
      font-size: 10px;
      color: #6b7280;
    }
  </style>
</head>
<body>

  {{-- Header --}}
  <div class="header">
    <div class="brand">
      @if($hasLogo)
        <img src="{{ $logoPath }}" alt="Logo" height="36" style="vertical-align:middle;margin-right:8px;">
      @endif
      <div style="display:inline-block;vertical-align:middle;">
        <h1 class="brand-title">
          {{ $titulo ?? 'Reporte de Inventario' }}
        </h1>
        <p class="brand-sub">Sistema Web • Pollería Pepe</p>
      </div>
    </div>
    <div class="brand-right">
      <div><strong>Generado:</strong> {{ $fechaReporte }}</div>
      @isset($filtrosActivos)
        <div class="muted">{{ $filtrosActivos }}</div>
      @endisset
    </div>
    <div style="clear: both;"></div>
  </div>

  {{-- Resumen --}}
  <div class="stats">
    <div class="stat">
      <span class="stat-label">Productos</span>
      <span class="stat-strong">{{ $total }}</span>
    </div>
    <div class="stat">
      <span class="stat-label">Bajo mínimo</span>
      <span class="stat-strong">{{ $bajoMin }}</span>
    </div>
    <div class="stat">
      <span class="stat-label">Stock total</span>
      <span class="stat-strong">{{ $stockTotal }}</span>
    </div>
    <div class="stat">
      <span class="stat-label">Valor estimado</span>
      <span class="stat-strong">${{ number_format($valorTotal, 2) }}</span>
    </div>
  </div>

  {{-- Tabla --}}
  <table>
    <thead>
      <tr>
        <th class="center" style="width: 38px;">#</th>
        <th>Producto</th>
        <th class="right" style="width: 90px;">Precio</th>
        <th class="right" style="width: 90px;">Stock actual</th>
        <th class="right" style="width: 90px;">Stock mínimo</th>
        <th style="width: 110px;">Estado</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $i => $r)
        @php $low = $r->stock_actual <= $r->stock_minimo; @endphp
        <tr>
          <td class="center">{{ $i + 1 }}</td>
          <td>{{ $r->nombre }}</td>
          <td class="right">{{ number_format($r->precio, 2) }}</td>
          <td class="right">{{ $r->stock_actual }}</td>
          <td class="right">{{ $r->stock_minimo }}</td>
          <td>
            @if($low)
              <span class="chip chip-low">Bajo mínimo</span>
            @else
              <span class="chip chip-ok">Normal</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="center muted">Sin datos</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Footer (número de página) --}}
  <div class="footer">
    Página <span class="pagenum"></span>
  </div>

  {{-- Contador de páginas (truco dompdf) --}}
  <script type="text/php">
    if (isset($pdf)) {
      $x = 535; $y = 812; // coordenadas aprox. para carta vertical
      $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
      $font = $fontMetrics->get_font("DejaVu Sans", "normal");
      $size = 9; $color = [107,114,128];
      $pdf->page_text($x, $y, $text, $font, $size, $color);
    }
  </script>
</body>
</html>
