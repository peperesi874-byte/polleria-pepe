<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>
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
            margin: 14px 0 4px 0;
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
            background: #fef3c7;
            color: #92400e;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .summary-cell {
            width: 25%;
            padding: 6px 8px;
        }

        .summary-box {
            border-radius: 10px;
            padding: 6px 8px;
        }

        .bg-amber {
            background: #fef3c7;
        }

        .bg-indigo {
            background: #e0e7ff;
        }

        .bg-emerald {
            background: #d1fae5;
        }

        .bg-sky {
            background: #e0f2fe;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #6b7280;
        }

        .summary-value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }

        .summary-extra {
            font-size: 9.5px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table {
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 4px 6px;
        }

        .table th {
            background: #f9fafb;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #6b7280;
        }

        .table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-sm {
            font-size: 9.5px;
        }

        .footer {
            margin-top: 12px;
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
            <h1>Reporte de Ingresos</h1>
            <div class="muted">
                Rango de fechas:
                <strong>{{ $desde }}</strong>
                al
                <strong>{{ $hasta }}</strong>
            </div>
        </div>

        <div class="header-right">
            <div class="tag">Sólo pedidos entregados</div>
            <div class="muted" style="margin-top:6px;">
                Generado:<br>
                {{ $generadoEn->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- RESUMEN GENERAL --}}
    <h2>Resumen ejecutivo</h2>
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="summary-box bg-amber">
                    <div class="summary-label">Ingresos totales</div>
                    <div class="summary-value">
                        ${{ number_format($resumen['ingresos_total'], 2) }}
                    </div>
                    <div class="summary-extra">Importe global del periodo.</div>
                </div>
            </td>
            <td class="summary-cell">
                <div class="summary-box bg-indigo">
                    <div class="summary-label">Pedidos entregados</div>
                    <div class="summary-value">
                        {{ $resumen['pedidos'] }}
                    </div>
                    <div class="summary-extra">Número total de tickets cerrados.</div>
                </div>
            </td>
            <td class="summary-cell">
                <div class="summary-box bg-emerald">
                    <div class="summary-label">Promedio por ticket</div>
                    <div class="summary-value">
                        ${{ number_format($resumen['promedio_ticket'], 2) }}
                    </div>
                    <div class="summary-extra">Ingreso promedio por pedido.</div>
                </div>
            </td>
            <td class="summary-cell">
                <div class="summary-box bg-sky">
                    <div class="summary-label">Canales de venta</div>
                    <div class="summary-extra">
                        Mostrador:
                        <strong>
                            ${{ number_format($resumen['mostrador']['ingresos'] ?? 0, 2) }}
                        </strong>
                        ({{ $resumen['mostrador']['pedidos'] ?? 0 }} pzs)<br>
                        Domicilio:
                        <strong>
                            ${{ number_format($resumen['domicilio']['ingresos'] ?? 0, 2) }}
                        </strong>
                        ({{ $resumen['domicilio']['pedidos'] ?? 0 }} pzs)
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DETALLE POR DÍA --}}
    <h2>Detalle diario de ingresos</h2>

    <table class="table">
        <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:16%;">Fecha</th>
            <th style="width:15%;" class="text-right">Pedidos</th>
            <th style="width:20%;" class="text-right">Ingresos del día</th>
            <th class="text-right">Ticket promedio</th>
        </tr>
        </thead>
        <tbody>
        @php $i = 1; @endphp
        @forelse($series as $item)
            @php
                $diaPedidos = (int) $item->pedidos;
                $diaIng     = (float) $item->ingresos;
                $diaProm    = $diaPedidos > 0 ? $diaIng / $diaPedidos : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $i++ }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}</td>
                <td class="text-right">{{ $diaPedidos }}</td>
                <td class="text-right">${{ number_format($diaIng, 2) }}</td>
                <td class="text-right">${{ number_format($diaProm, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-sm">
                    No se encontraron pedidos entregados en el rango seleccionado.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- PIE --}}
    <div class="footer">
        Reporte generado automáticamente por el sistema Pollería Pepe.
        Uso interno del área administrativa.
    </div>
</body>
</html>
