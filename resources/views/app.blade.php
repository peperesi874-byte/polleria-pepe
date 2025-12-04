<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- 🔥 Favicon PRO (compatible con todos los dispositivos) -->
    <link rel="icon" type="image/png" href="{{ asset('logo.jpg') }}">
    <link rel="icon" sizes="32x32" href="{{ asset('logo.jpg') }}">
    <link rel="icon" sizes="192x192" href="{{ asset('logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.jpg') }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @routes

    @if (app()->environment('local'))
        {{-- En tu PC: usa Vite normalmente --}}
        @vite('resources/js/app.js')
    @else
        {{-- En producción (Hostinger): usa los archivos ya generados en /build --}}
        <link rel="stylesheet" href="{{ asset('build/assets/app-BZw7K7PJ.css') }}">
        <script type="module" src="{{ asset('build/assets/app-CPmFZllL.js') }}"></script>
    @endif

    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
