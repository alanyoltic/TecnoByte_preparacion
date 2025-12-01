<!DOCTYPE html>
<html 
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="text-[18px]"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }"
>
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

<style>
    html, body {
        
        background-color: #020617; /* parecido a slate-950 */
    }

    [x-cloak] { 
        display: none !important; 
    }
</style>

    </head>

<body class="font-sans text-lg antialiased overflow-x-hidden bg-transparent">

        {{-- FONDO GLOBAL FIJO DETRÁS DE TODO (para evitar el bloque negro) --}}
        <div
            class="fixed inset-0 -z-50
                   bg-gradient-to-br
                   from-slate-100 via-slate-200 to-slate-300
                   dark:from-slate-900 dark:via-slate-950 dark:to-slate-900">
        </div>

        {{-- CONTENEDOR PRINCIPAL --}}
        <div 
            x-data="{ sidebarOpen: false }" 
            class="relative min-h-screen flex bg-transparent"
        >
            @include('layouts.sidebar')

            <div 
                class="flex-1 flex flex-col transition-all duration-300 ease-in-out"
                :class="{ 'ml-64': sidebarOpen, 'ml-20': !sidebarOpen }"
            >
                {{-- Header del slot, sin max-w ni fondos extra --}}
                @if (isset($header))
                    <header class="w-full">
                        {{ $header }}
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        <style>
            :root {
                /* Modo claro */
                --brand-primary: #FF9521;
            }
        </style>

        @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    </body>
</html>
