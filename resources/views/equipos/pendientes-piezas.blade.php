<x-app-layout>

    {{-- FONDO ESTILO LOGIN + PENDIENTES PIEZAS --}}
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-100 to-slate-200
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >

        {{-- Luces estilo login (posiciones aleatorias) --}}
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
                id="glow-1"
                class="absolute w-[1100px] h-[1100px]
                       bg-[#1E3A8A] rounded-full blur-[240px]
                       opacity-70 md:opacity-90 mix-blend-screen"
                style="top: {{ $glow1Top }}px; left: {{ $glow1Left }}px;"
            ></div>

            {{-- Glow azul grande inferior derecho --}}
            <div
                id="glow-2"
                class="absolute w-[1000px] h-[1000px]
                       bg-[#0F1A35] rounded-full blur-[240px]
                       opacity-70 md:opacity-95 mix-blend-screen"
                style="bottom: {{ $glow2Bottom }}px; right: {{ $glow2Right }}px;"
            ></div>

            {{-- Glow naranja suave central --}}
            <div
                id="glow-3"
                class="absolute w-[850px] h-[850px]
                       bg-[#FF9521]/40 md:bg-[#FF9521]/50
                       rounded-full blur-[260px]
                       opacity-80 md:opacity-90 mix-blend-screen"
                style="bottom: {{ $glow3Bottom }}px; left: {{ $glow3LeftPercent }}%;"
            ></div>
        </div>

        {{-- Capa glass suave --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- CONTENIDO: HEADER + TABLA LIVEWIRE --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

            {{-- HEADER GLASS PARA PENDIENTES PIEZAS --}}
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

                    {{-- IZQUIERDA: título y descripción --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Equipos pendientes por piezas
                            </h2>

                            {{-- Chip sección --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                       text-[0.7rem] font-semibold tracking-wide
                                       bg-[#FF9521]/10 text-[#FF9521]
                                       border border-[#FF9521]/40"
                            >
                                Preparación · Piezas
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                            Equipos que no pueden darse por terminados porque están esperando la compra o instalación de una pieza.
                        </p>
                    </div>

                    {{-- DERECHA: espacio para info/resumen si luego quieres --}}
                    <div class="flex flex-col items-end gap-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        {{-- Ej: aquí podrías mostrar un pequeño contador global o un botón extra en el futuro --}}
                    </div>
                </div>
            </div>

            {{-- CONTENIDO PRINCIPAL: LIVEWIRE --}}
            <div class="max-w-7xl mx-auto">
                <livewire:inventario.pendientes-piezas />
            </div>
        </div>
    </div>
</x-app-layout>
