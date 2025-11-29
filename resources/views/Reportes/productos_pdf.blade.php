<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de productos</title>
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            box-sizing: border-box;
        }

        body {
            margin: 18px;
            color: #1f2933;
        }

        .header {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 6px;
        }

        .header-left {
            float: left;
        }

        .header-right {
            float: right;
            text-align: right;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
            color: #111827;
        }

        h2 {
            font-size: 13px;
            margin: 12px 0 4px 0;
            color: #111827;
        }

        .muted {
            font-size: 9.5px;
            color: #6b7280;
        }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            background: #e0f2fe;
            color: #0369a1;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .stats {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .stats td {
            padding: 4px 8px 0 0;
            font-size: 9.5px;
            color: #4b5563;
        }

        .stats strong {
            font-size: 11px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 4px 6px;
        }

        th {
            background: #f9fafb;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #6b7280;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .footer {
            margin-top: 10px;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    {{-- ENCABEZADO --}}
    <div class="header clearfix">
        <div class="header-left">
            <div style="font-size:11px; text-transform:uppercase; color:#6366f1; font-weight:600;">
                Sistema Web Pollería Pepe
            </div>
            <h1>Catálogo de productos</h1>
            <div class="muted">
                Listado general de productos registrados en el sistema.
            </div>
        </div>

        <div class="header-right">
            <div class="tag">Uso interno</div>
            <div class="muted" style="margin-top:6px;">
                Generado:<br>
                {{ $generadoEn->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- MINI ESTADÍSTICAS --}}
    @php
        $total    = $productos->count();
        $activos  = $productos->where('activo', 1)->count();
        $inactivos = $total - $activos;
    @endphp

    <table class="stats">
        <tr>
            <td>Total de productos: <strong>{{ $total }}</strong></td>
            <td>Activos: <strong>{{ $activos }}</strong></td>
            <td>Inactivos: <strong>{{ $inactivos }}</strong></td>
        </tr>
    </table>

    {{-- TABLA PRINCIPAL --}}
    <h2>Detalle de productos</h2>

    <table>
        <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th>Producto</th>
            <th style="width:15%;" class="text-right">Precio</th>
            <th style="width:12%;" class="text-right">Stock</th>
            <th style="width:14%;">Estado</th>
        </tr>
        </thead>
        <tbody>
        @php $i = 1; @endphp
        @forelse($productos as $p)
            <tr>
                <td class="text-center">{{ $i++ }}</td>
                <td>{{ $p->nombre }}</td>
                <td class="text-right">
                    ${{ number_format((float) $p->precio, 2) }}
                </td>
                <td class="text-right">
                    {{ (int) ($p->stock ?? 0) }}
                </td>
                <td>
                    @if($p->activo)
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-danger">Inactivo</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center" style="font-size:9.5px;">
                    No se encontraron productos configurados.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">
        Catálogo generado automáticamente por el sistema Pollería Pepe.
        Verifique precios y existencias antes de su publicación externa.
    </div>
</body>
</html>
