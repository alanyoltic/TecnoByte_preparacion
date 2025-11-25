<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Servicios TecnoByte - Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- FONDO A PANTALLA COMPLETA --}}
<body
    class="min-h-screen flex items-center justify-center
           bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900"
>
    {{-- Si viene $slot (componente), lo muestra. Si no, muestra @yield --}}
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset
</body>

</html>
