<x-app-layout>

    {{-- FONDO ESTILO LOGIN + REGISTRO / EDICIÓN DE EQUIPOS --}}
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-100 to-slate-200
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >

        {{-- Luces estilo login (con posiciones aleatorias) --}}
        @php
            // Azul superior izq
            $glow1Top  = rand(-420, -260);
            $glow1Left = rand(-340, -120);

            // Azul inferior der
            $glow2Bottom = rand(-420, -260);
            $glow2Right  = rand(-340, -120);

            // Naranja central
            $glow3Bottom      = rand(-360, -220);
            $glow3LeftPercent = rand(25, 75);
        @endphp

        <div class="pointer-events-none absolute inset-0">
            {{-- Glow azul grande superior izquierdo --}}
            <div
                class="absolute w-[1100px] h-[1100px]
                       bg-[#1E3A8A] rounded-full blur-[240px]
                       opacity-70 md:opacity-90 mix-blend-screen"
                style="top: {{ $glow1Top }}px; left: {{ $glow1Left }}px;"
            ></div>

            {{-- Glow azul grande inferior derecho --}}
            <div
                class="absolute w-[1000px] h-[1000px]
                       bg-[#0F1A35] rounded-full blur-[240px]
                       opacity-70 md:opacity-95 mix-blend-screen"
                style="bottom: {{ $glow2Bottom }}px; right: {{ $glow2Right }}px;"
            ></div>

            {{-- Glow naranja suave central --}}
            <div
                class="absolute w-[850px] h-[850px]
                       bg-[#FF9521]/40 md:bg-[#FF9521]/50
                       rounded-full blur-[260px]
                       opacity-80 md:opacity-90 mix-blend-screen"
                style="bottom: {{ $glow3Bottom }}px; left: {{ $glow3LeftPercent }}%;"
            ></div>
        </div>

        {{-- Capa glass suave --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- CONTENIDO: HEADER + LIVEWIRE --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

            {{-- HEADER GLASS PARA GESTIÓN DE INVENTARIO --}}
            <div
                class="relative overflow-hidden mb-6
                       rounded-3xl
                       bg-white/80 dark:bg-slate-950/70
                       border border-slate-200/80 dark:border-white/10
                       shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       px-6 sm:px-8 lg:px-10 py-4 sm:py-5"
            >
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">

                    {{-- IZQUIERDA: título --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Gestión avanzada de inventario
                            </h2>

                            {{-- Chip sección --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                       text-[0.7rem] font-semibold tracking-wide
                                       bg-[#FF9521]/10 text-[#FF9521]
                                       border border-[#FF9521]/40"
                            >
                                Inventario · Gestión
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-300">
                            Filtra, revisa y prepara lotes completos para reportes, ajustes masivos y descargas.
                        </p>
                    </div>

                    {{-- DERECHA: espacio para info extra (opcional) --}}
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        {{-- Aquí en el futuro podemos poner: "Total equipos: X" o "Última actualización: ..." --}}
                    </div>
                </div>

                {{-- NOTIFICACIÓN FLOTANTE DE ÉXITO (opcional para esta vista) --}}
                @if (session('success'))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 4000)"
                        x-show="show"
                        x-transition
                        class="fixed top-4 right-4 z-50 max-w-sm
                               rounded-2xl border border-emerald-300/70
                               bg-emerald-50/95 dark:bg-emerald-900/90
                               shadow-lg shadow-emerald-500/30
                               backdrop-blur-md
                               px-4 py-3 flex items-start gap-3"
                    >
                        {{-- Icono --}}
                        <div class="mt-0.5">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        {{-- Texto --}}
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-100">
                                {{ session('success') }}
                            </p>
                            <p class="text-xs text-emerald-700/90 dark:text-emerald-200/90 mt-0.5">
                                La operación sobre el inventario se completó correctamente.
                            </p>
                        </div>

                        {{-- Botón cerrar --}}
                        <button
                            type="button"
                            class="ml-1 text-emerald-700/70 hover:text-emerald-900 dark:text-emerald-200/80 dark:hover:text-emerald-50"
                            @click="show = false"
                        >
                            ✕
                        </button>
                    </div>
                @endif
            </div>

            {{-- CONTENEDOR PRINCIPAL: AQUÍ VA EL COMPONENTE LIVEWIRE DE GESTIÓN --}}
            <div class="max-w-7xl mx-auto">
                @livewire('inventario.gestion-inventario')
                {{-- o si prefieres tag:
                <livewire:inventario.gestion-inventario />
                --}}
            </div>
        </div>
    </div>
</x-app-layout>
