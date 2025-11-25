<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Notificación' }} | Pollería Pepe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* RESET BÁSICO */
        body {
            margin: 0;
            padding: 0;
            background: #f5f5fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #111827;
        }
        a { color: #ea580c; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .wrapper {
            width: 100%;
            padding: 24px 0;
        }

        .email-container {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow:
                0 20px 35px rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(148, 163, 184, 0.08);
        }

        /* ENCABEZADO */
        .header {
            padding: 20px 28px;
            background: radial-gradient(circle at 0 0, #fbbf24 0, #ea580c 40%, #7c3aed 100%);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .logo img {
            max-width: 40px;
            max-height: 40px;
            display: block;
        }
        .brand-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 11px;
            opacity: .9;
        }

        /* CONTENIDO */
        .content {
            padding: 24px 28px 10px 28px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .paragraph {
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 10px 0;
            color: #4b5563;
        }

        /* BOTÓN PRINCIPAL */
        .action-wrapper {
            text-align: center;
            margin: 22px 0 20px 0;
        }
        .action-button {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff !important;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            box-shadow: 0 12px 25px rgba(234, 88, 12, 0.35);
        }

        /* SALUDO FINAL */
        .salutation {
            margin-top: 18px;
            font-size: 14px;
            color: #374151;
        }

        /* SUBCOPY (link en texto plano) */
        .subcopy {
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
            padding: 16px 0 0 0;
        }
        .subcopy p {
            font-size: 12px;
            line-height: 1.6;
            color: #6b7280;
            margin: 0 0 4px 0;
        }
        .subcopy a {
            word-break: break-all;
        }

        /* PIE */
        .footer {
            text-align: center;
            padding: 14px 10px 4px 10px;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="email-container">

        {{-- HEADER CON LOGO --}}
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logo.jpg') }}" alt="Pollería Pepe">
            </div>
            <div>
                <div class="brand-title">POLLARÍA PEPE</div>
                <div class="brand-sub">Sistema de gestión de pedidos</div>
            </div>
        </div>

        {{-- CONTENIDO PRINCIPAL --}}
        <div class="content">
            {{-- Saludo --}}
            <p class="greeting">
                {{ $greeting ?? '¡Hola!' }}
            </p>

            {{-- Líneas de introducción --}}
            @foreach(($introLines ?? []) as $line)
                <p class="paragraph">{{ $line }}</p>
            @endforeach

            {{-- Botón de acción --}}
            @if (!empty($actionText) && !empty($actionUrl))
                <div class="action-wrapper">
                    <a href="{{ $actionUrl }}" class="action-button">
                        {{ $actionText }}
                    </a>
                </div>
            @endif

            {{-- Líneas de cierre --}}
            @foreach(($outroLines ?? []) as $line)
                <p class="paragraph">{{ $line }}</p>
            @endforeach

            {{-- Despedida --}}
            <p class="salutation">
                {{ $salutation ?? 'Saludos cordiales,<br>Pollería Pepe 🐔' }}
            </p>

            {{-- Subcopy con URL en texto plano --}}
            @if (!empty($actionText) && !empty($actionUrl))
                <div class="subcopy">
                    <p>
                        Si tienes problemas para hacer clic en el botón
                        <strong>"{{ $actionText }}"</strong>, copia y pega la siguiente
                        dirección en tu navegador:
                    </p>
                    <p>
                        <a href="{{ $actionUrl }}">{{ $displayableActionUrl ?? $actionUrl }}</a>
                    </p>
                </div>
            @endif
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            © {{ date('Y') }} Pollería Pepe. Todos los derechos reservados.
        </div>
    </div>
</div>
</body>
</html>
