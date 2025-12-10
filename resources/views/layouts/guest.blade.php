<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script>
        // Si el usuario ya tenía activado darkMode, aplica la clase dark INMEDIATAMENTE
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

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
