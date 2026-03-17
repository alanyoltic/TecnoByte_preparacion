<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                                            --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')
            <x-topbar
                title="Mi Trabajo"
                chip="Preparación · Mis Asignaciones"
                description="Equipos que tienes asignados para preparar."
            />
        @elseif($vista === 'equipos')
            <x-topbar
                title="{{ $this->asignacionActual?->loteModelo?->marca }} {{ $this->asignacionActual?->loteModelo?->modelo }}"
                chip="Lote {{ $this->asignacionActual?->loteModelo?->lote?->nombre_lote }}"
                description="{{ $this->asignacionActual?->equipos?->count() ?? 0 }} / {{ $this->asignacionActual?->cantidad }} equipos escaneados"
            >
                <x-slot name="right">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60
                               border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Mis asignaciones
                    </button>
                </x-slot>
            </x-topbar>
        @elseif($vista === 'trabajar')
            <x-topbar
                title="Terminar equipo"
                chip="{{ $this->equipoEnTrabajo?->equipo?->numero_serie }}"
                description="Registra el resultado de este equipo."
            >
                <x-slot name="right">
                    <button wire:click="volverAEquipos"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60
                               border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Volver
                    </button>
                </x-slot>
            </x-topbar>
        @endif


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 1: LISTA DE ASIGNACIONES                             --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')

            @php
                $totalAsig      = $this->asignaciones->count();
                $totalEquipos   = $this->asignaciones->sum('cantidad');
                $totalCompletados = $this->asignaciones->sum(fn($a) => $a->equipos->where('camino','COMPLETADO')->count());
                $totalEnProceso   = $this->asignaciones->sum(fn($a) => $a->equipos->where('camino','EN_PROCESO')->count());
                $totalPiezas      = $this->asignaciones->sum(fn($a) => $a->equipos->where('camino','PIEZA_PENDIENTE')->count());
                $totalGarantia    = $this->asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count());
            @endphp

            {{-- TARJETAS MÉTRICAS --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

                {{-- Total asignaciones --}}
                <div class="rounded-2xl
                            bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-sky-500/20
                            dark:hover:shadow-sky-500/25
                            hover:border-sky-400/70 dark:hover:border-sky-300/50">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                        Asignaciones
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                        {{ $totalAsig }}
                    </p>
                </div>

                {{-- Total equipos --}}
                <div class="rounded-2xl
                            bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-indigo-500/20
                            dark:hover:shadow-indigo-500/25
                            hover:border-indigo-400/70 dark:hover:border-indigo-300/50">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                        Total equipos
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                        {{ $totalEquipos }}
                    </p>
                </div>

                {{-- Sin iniciar --}}
                <div class="rounded-2xl
                            bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-slate-500/20">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                        Sin iniciar
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                        {{ $totalEquipos - $this->asignaciones->sum(fn($a) => $a->equipos->count()) }}
                    </p>
                </div>

                {{-- En proceso --}}
                <div class="rounded-2xl
                            bg-blue-50/90 dark:bg-blue-950/40
                            border border-blue-200/80 dark:border-blue-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-blue-900/10 dark:shadow-blue-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-blue-500/40
                            dark:hover:shadow-blue-400/50
                            hover:border-blue-400/70">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-200 uppercase tracking-wide">
                        En proceso
                    </p>
                    <p class="mt-2 text-2xl font-bold text-blue-800 dark:text-blue-100">
                        {{ $totalEnProceso }}
                    </p>
                </div>

                {{-- Completados --}}
                <div class="rounded-2xl
                            bg-emerald-50/90 dark:bg-emerald-950/40
                            border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-emerald-900/10 dark:shadow-emerald-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-emerald-500/40
                            dark:hover:shadow-emerald-400/50
                            hover:border-emerald-400/70">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                        Completados
                    </p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                        {{ $totalCompletados }}
                    </p>
                </div>

                {{-- Con problema --}}
                <div class="rounded-2xl
                            bg-amber-50/90 dark:bg-amber-950/40
                            border border-amber-200/80 dark:border-amber-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-amber-900/10 dark:shadow-amber-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-amber-500/40
                            dark:hover:shadow-amber-400/50
                            hover:border-amber-400/70">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                        Con problema
                    </p>
                    <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                        {{ $totalPiezas + $totalGarantia }}
                    </p>
                </div>

            </div>

            {{-- Sin asignaciones --}}
            @if($this->asignaciones->isEmpty())
                <div class="rounded-2xl
                            bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            px-6 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl
                                bg-slate-100 dark:bg-slate-800/60
                                flex items-center justify-center mx-auto mb-4
                                border border-slate-200/80 dark:border-white/10">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Sin asignaciones activas</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                        Tu líder o gerente aún no te ha asignado equipos.
                    </p>
                </div>

            @else

                {{-- TARJETAS DE ASIGNACIONES --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($this->asignaciones as $asignacion)
                        @php
                            $escaneados   = $asignacion->equipos->count();
                            $completados  = $asignacion->equipos->where('camino', 'COMPLETADO')->count();
                            $enProceso    = $asignacion->equipos->where('camino', 'EN_PROCESO')->count();
                            $piezas       = $asignacion->equipos->where('camino', 'PIEZA_PENDIENTE')->count();
                            $garantia     = $asignacion->equipos->whereIn('camino', ['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count();
                            $sinIniciar   = max($asignacion->cantidad - $escaneados, 0);
                            $porcentaje   = $asignacion->cantidad > 0
                                ? round(($completados / $asignacion->cantidad) * 100)
                                : 0;
                        @endphp

                        <div class="rounded-2xl
                                    bg-white/80 dark:bg-slate-950/60
                                    border border-slate-200/80 dark:border-white/10
                                    backdrop-blur-xl dark:backdrop-blur-2xl
                                    shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                    px-5 py-5 space-y-4
                                    transition-all duration-300
                                    hover:-translate-y-1
                                    hover:shadow-lg hover:shadow-indigo-500/20
                                    dark:hover:shadow-indigo-500/25
                                    hover:border-indigo-400/50 dark:hover:border-indigo-400/30">

                            {{-- Encabezado --}}
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $asignacion->loteModelo->marca ?? '' }}
                                        {{ $asignacion->loteModelo->modelo ?? 'Sin modelo' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Lote: <span class="font-medium text-slate-700 dark:text-slate-200">
                                            {{ $asignacion->loteModelo->lote->nombre_lote ?? '—' }}
                                        </span>
                                        · {{ $asignacion->fecha_asignacion->format('d/m/Y') }}
                                    </p>
                                </div>
                                <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full
                                             text-[0.65rem] font-semibold tracking-wide
                                             {{ $asignacion->estatus === 'PENDIENTE'
                                                 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-300 border border-amber-400/40'
                                                 : 'bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-400/40' }}">
                                    {{ $asignacion->label_estatus }}
                                </span>
                            </div>

                            {{-- Desglose de estados --}}
                            <div class="grid grid-cols-3 gap-2">
                                {{-- Sin iniciar --}}
                                <div class="rounded-xl bg-slate-100/80 dark:bg-slate-800/50
                                            border border-slate-200/60 dark:border-slate-700/50
                                            px-3 py-2 text-center">
                                    <p class="text-[0.65rem] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Sin iniciar
                                    </p>
                                    <p class="text-lg font-bold text-slate-700 dark:text-slate-200 mt-0.5">
                                        {{ $sinIniciar }}
                                    </p>
                                </div>

                                {{-- En proceso --}}
                                <div class="rounded-xl
                                            {{ $enProceso > 0 ? 'bg-blue-50/80 dark:bg-blue-900/20 border border-blue-300/50 dark:border-blue-600/40' : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }}
                                            px-3 py-2 text-center">
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-wide
                                              {{ $enProceso > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-slate-500 dark:text-slate-400' }}">
                                        En proceso
                                    </p>
                                    <div class="flex items-center justify-center gap-1 mt-0.5">
                                        @if($enProceso > 0)
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                        @endif
                                        <p class="text-lg font-bold
                                                  {{ $enProceso > 0 ? 'text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                                            {{ $enProceso }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Completados --}}
                                <div class="rounded-xl
                                            {{ $completados > 0 ? 'bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-300/50 dark:border-emerald-600/40' : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }}
                                            px-3 py-2 text-center">
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-wide
                                              {{ $completados > 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }}">
                                        Completados
                                    </p>
                                    <p class="text-lg font-bold mt-0.5
                                              {{ $completados > 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-200' }}">
                                        {{ $completados }}
                                    </p>
                                </div>
                            </div>

                            {{-- Problemas (solo si hay) --}}
                            @if($piezas > 0 || $garantia > 0)
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($piezas > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                                     text-[0.65rem] font-semibold
                                                     bg-amber-100 dark:bg-amber-900/30
                                                     text-amber-700 dark:text-amber-300
                                                     border border-amber-300/50 dark:border-amber-600/40">
                                            🔧 {{ $piezas }} pieza(s) pendiente
                                        </span>
                                    @endif
                                    @if($garantia > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                                     text-[0.65rem] font-semibold
                                                     bg-rose-100 dark:bg-rose-900/30
                                                     text-rose-700 dark:text-rose-300
                                                     border border-rose-300/50 dark:border-rose-600/40">
                                            ⚠️ {{ $garantia }} garantía
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Barra de progreso --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $completados }} de {{ $asignacion->cantidad }} completados
                                    </span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                        {{ $porcentaje }}%
                                    </span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full transition-all duration-500
                                                bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6]"
                                         style="width: {{ $porcentaje }}%">
                                    </div>
                                </div>
                            </div>

                            {{-- Botón --}}
                            <button
                                wire:click="verEquipos({{ $asignacion->id }})"
                                class="w-full inline-flex items-center justify-center gap-2
                                       rounded-xl px-4 py-2.5 text-xs font-semibold
                                       bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                       text-white
                                       shadow-md shadow-blue-800/40
                                       hover:shadow-lg hover:shadow-blue-500/60
                                       hover:-translate-y-0.5
                                       transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Ver equipos
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 2: EQUIPOS DE LA ASIGNACIÓN                          --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'equipos')
            @php $a = $this->asignacionActual; @endphp

            {{-- Error --}}
            @if($error)
                <div class="rounded-xl border border-rose-500/40
                            bg-rose-50/80 dark:bg-rose-950/30
                            px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                    {{ $error }}
                </div>
            @endif

            <div class="space-y-4">

                {{-- Escanear serie --}}
                @if(($a?->equipos?->count() ?? 0) < ($a?->cantidad ?? 0))
                    <div class="rounded-2xl
                                bg-white/80 dark:bg-slate-950/60
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                px-5 py-5 space-y-3">

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Iniciar equipo
                            </span>
                            <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                        </div>

                        <div class="flex gap-3">
                            <input
                                type="text"
                                wire:model.defer="numeroSerie"
                                wire:keydown.enter="escanearSerie"
                                placeholder="Escanea o escribe el número de serie..."
                                autofocus
                                class="flex-1 rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       placeholder:text-slate-400
                                       focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none transition"
                            >
                            <button
                                wire:click="escanearSerie"
                                wire:loading.attr="disabled"
                                wire:target="escanearSerie"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5
                                       text-sm font-semibold
                                       bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                       text-white shadow-md shadow-blue-800/40
                                       hover:shadow-blue-500/60 hover:-translate-y-0.5
                                       disabled:opacity-60 transition-all duration-200">
                                <span wire:loading.remove wire:target="escanearSerie">Iniciar</span>
                                <span wire:loading wire:target="escanearSerie">...</span>
                            </button>
                        </div>
                        <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">
                            Puedes usar una pistola lectora de código de barras directamente en el campo.
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-emerald-400/40
                                bg-emerald-50/80 dark:bg-emerald-950/20
                                px-4 py-3 text-xs font-medium
                                text-emerald-700 dark:text-emerald-300
                                flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Todos los equipos de esta asignación han sido escaneados.
                    </div>
                @endif

                {{-- Lista de equipos --}}
                @if($a?->equipos?->count() > 0)
                    <div class="rounded-2xl
                                bg-white/80 dark:bg-slate-950/80
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                overflow-hidden">

                        <div class="px-5 py-3 border-b border-slate-200/60 dark:border-slate-800/80">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Equipos iniciados · {{ $a->equipos->count() }} de {{ $a->cantidad }}
                            </p>
                        </div>

                        <div class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                            @foreach($a->equipos as $ae)
                                <div class="px-5 py-3 flex items-center justify-between gap-4
                                            {{ $ae->camino === 'EN_PROCESO' ? 'bg-blue-50/40 dark:bg-blue-900/5' : '' }}
                                            hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">

                                    <div class="flex items-center gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full shrink-0
                                            {{ match($ae->camino) {
                                                'COMPLETADO'       => 'bg-emerald-400',
                                                'EN_PROCESO'       => 'bg-blue-400 animate-pulse',
                                                'PIEZA_PENDIENTE'  => 'bg-amber-400',
                                                'GARANTIA_INTERNA',
                                                'GARANTIA_EXTERNA' => 'bg-rose-400',
                                                default            => 'bg-slate-400'
                                            } }}">
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold font-mono text-slate-900 dark:text-slate-50">
                                                {{ $ae->equipo?->numero_serie ?? '—' }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ \App\Models\AsignacionEquipo::labelsCamino()[$ae->camino] ?? $ae->camino }}
                                                @if($ae->notas)
                                                    · {{ Str::limit($ae->notas, 50) }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    @if($ae->camino === 'EN_PROCESO')
                                        <button
                                            wire:click="continuarEquipo({{ $ae->id }})"
                                            class="shrink-0 inline-flex items-center gap-1.5
                                                   rounded-xl px-3 py-1.5
                                                   bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6]
                                                   text-white text-xs font-semibold
                                                   shadow-md shadow-blue-800/30
                                                   hover:shadow-blue-500/50 hover:-translate-y-0.5
                                                   transition-all duration-200">
                                            Terminar
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 3: TERMINAR EQUIPO                                   --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'trabajar')
            @php $ae = $this->equipoEnTrabajo; @endphp

            {{-- Error --}}
            @if($error)
                <div class="rounded-xl border border-rose-500/40
                            bg-rose-50/80 dark:bg-rose-950/30
                            px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                    {{ $error }}
                </div>
            @endif

            <div class="rounded-2xl
                        bg-white/80 dark:bg-slate-950/60
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                        px-5 py-6 space-y-6">

                {{-- ¿Cómo quedó? --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            ¿Cómo quedó el equipo?
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach([
                            ['value' => 'COMPLETADO',       'label' => 'Completado',       'emoji' => '✓',  'color' => 'emerald'],
                            ['value' => 'PIEZA_PENDIENTE',  'label' => 'Pieza Faltante',   'emoji' => '🔧', 'color' => 'amber'],
                            ['value' => 'GARANTIA_INTERNA', 'label' => 'Garantía Interna', 'emoji' => '⚠️', 'color' => 'rose'],
                            ['value' => 'GARANTIA_EXTERNA', 'label' => 'Garantía Externa', 'emoji' => '📦', 'color' => 'rose'],
                        ] as $opcion)
                            <button
                                type="button"
                                wire:click="$set('camino', '{{ $opcion['value'] }}')"
                                class="rounded-xl border px-3 py-4 text-center text-xs font-semibold
                                       transition-all duration-200
                                       {{ $camino === $opcion['value']
                                           ? 'border-[#FF9521] bg-[#FF9521]/10 text-[#FF9521] shadow-md shadow-[#FF9521]/20'
                                           : 'border-slate-300/80 dark:border-slate-700
                                              bg-white/60 dark:bg-slate-900/40
                                              text-slate-600 dark:text-slate-300
                                              hover:border-slate-400 dark:hover:border-slate-600
                                              hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                            >
                                <span class="block text-xl mb-2">{{ $opcion['emoji'] }}</span>
                                {{ $opcion['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Pieza faltante --}}
                @if($camino === 'PIEZA_PENDIENTE')
                    <div class="rounded-xl border border-amber-400/30
                                bg-amber-50/60 dark:bg-amber-900/10
                                px-4 py-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">
                                Pieza faltante
                            </span>
                            <div class="h-px flex-1 bg-amber-300/40"></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-xs text-slate-600 dark:text-slate-300">Del catálogo</label>
                                <select
                                    wire:model="catalogoPiezaId"
                                    class="w-full rounded-xl px-4 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                                    <option value="">Selecciona pieza...</option>
                                    @foreach($catalogoPiezas as $pieza)
                                        <option value="{{ $pieza->id }}">
                                            [{{ $pieza->categoria }}] {{ $pieza->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs text-slate-600 dark:text-slate-300">O descríbela</label>
                                <input
                                    type="text"
                                    wire:model.defer="descripcionPiezaLibre"
                                    placeholder="Ej. Batería 45Wh, bisagra derecha..."
                                    class="w-full rounded-xl px-4 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           placeholder:text-slate-400
                                           focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Notas --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Notas
                        </span>
                        <span class="text-[0.65rem] text-slate-400">(opcional)</span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>
                    <textarea
                        wire:model.defer="notas"
                        rows="2"
                        placeholder="Observaciones del equipo..."
                        class="w-full rounded-xl px-4 py-2.5 text-sm
                               bg-white/70 dark:bg-slate-900/40
                               border border-slate-300/80 dark:border-slate-700
                               text-slate-900 dark:text-slate-100
                               placeholder:text-slate-400
                               focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none resize-none">
                    </textarea>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-1">
                    <button
                        type="button"
                        wire:click="volverAEquipos"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40
                               text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-all duration-200">
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="terminarEquipo"
                        wire:loading.attr="disabled"
                        wire:target="terminarEquipo"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl px-6 py-2.5 text-sm font-semibold
                               bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                               text-white shadow-md shadow-blue-800/50
                               hover:shadow-blue-500/70 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="terminarEquipo">Guardar y terminar</span>
                        <span wire:loading wire:target="terminarEquipo">Guardando...</span>
                    </button>
                </div>
            </div>
        @endif

    </div>
</x-tb-background>
</div>