<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')
            <x-topbar
                title="Mi Trabajo"
                chip="Preparación · Mis Asignaciones"
                description="Tus equipos asignados y estadísticas del mes."
            />
        @elseif($vista === 'equipos')
            <x-topbar
                title="{{ $this->asignacionActual?->loteModelo?->marca }} {{ $this->asignacionActual?->loteModelo?->modelo }}"
                chip="Lote {{ $this->asignacionActual?->loteModelo?->lote?->nombre_lote }}"
                description="{{ $this->asignacionActual?->equipos?->count() ?? 0 }} / {{ $this->asignacionActual?->cantidad }} equipos iniciados"
            >
                <x-slot name="right">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Mis asignaciones
                    </button>
                </x-slot>
            </x-topbar>
        @elseif($vista === 'caracteristicas')
            <x-topbar
                title="Características del equipo"
                chip="{{ $this->equipoActual?->equipo?->numero_serie }}"
                description="Llena los datos del equipo. Puedes guardar el avance en cualquier momento."
            >
                <x-slot name="right">
                    <button wire:click="volverAEquipos"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Volver
                    </button>
                </x-slot>
            </x-topbar>
        @elseif($vista === 'terminar')
            <x-topbar
                title="Terminar equipo"
                chip="{{ $this->equipoActual?->equipo?->numero_serie }}"
                description="Indica el resultado final del equipo."
            >
                <x-slot name="right">
                    <button wire:click="volverACaracteristicas"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Características
                    </button>
                </x-slot>
            </x-topbar>
        @elseif($vista === 'confirmar_pieza')
            <x-topbar
                title="Instalar pieza"
                chip="{{ $this->solicitudPiezaActual?->nombre_pieza }}"
                description="Confirma si la pieza funcionó o no."
            >
                <x-slot name="right">
                    <button wire:click="volverDesdePieza"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Mi trabajo
                    </button>
                </x-slot>
            </x-topbar>
        @endif


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 1: LISTA DE ASIGNACIONES                            --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')

            @php
                $stats       = $this->estadisticasMes;
                $totalAsig   = $this->asignaciones->count();
                $totalEq     = $this->asignaciones->sum('cantidad');
                $completados = $this->asignaciones->sum(fn($a) => $a->equipos->where('camino','COMPLETADO')->count());
                $enProceso   = $this->asignaciones->sum(fn($a) => $a->equipos->where('camino','EN_PROCESO')->count());
            @endphp

            {{-- MÉTRICAS DEL MES --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                        px-5 py-4">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Mis estadísticas — {{ now()->translatedFormat('F Y') }}
                    </span>
                    <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $stats['total'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Terminados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['calidad'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">→ Calidad</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['piezas'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">→ Pieza pend.</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $stats['termino_pieza'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">🔧 Piezas ok</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $stats['garantia'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">→ Garantía</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#FF9521]">{{ $stats['puntos'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Puntos</p>
                    </div>
                </div>
            </div>

            {{-- TARJETAS ASIGNACIONES --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-4 py-3 shadow-md hover:-translate-y-1 transition-all duration-300
                            hover:shadow-lg hover:shadow-sky-500/20 dark:hover:shadow-sky-500/25">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Asignaciones</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalAsig }}</p>
                </div>
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-4 py-3 shadow-md hover:-translate-y-1 transition-all duration-300
                            hover:shadow-lg hover:shadow-indigo-500/20">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Total equipos</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalEq }}</p>
                </div>
                <div class="rounded-2xl bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md hover:-translate-y-1 transition-all duration-300
                            hover:shadow-lg hover:shadow-blue-500/40">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-200 uppercase tracking-wide">En proceso</p>
                    <p class="mt-2 text-2xl font-bold text-blue-800 dark:text-blue-100">{{ $enProceso }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md hover:-translate-y-1 transition-all duration-300
                            hover:shadow-lg hover:shadow-emerald-500/40">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">Completados</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">{{ $completados }}</p>
                </div>
            </div>

            {{-- SIN ASIGNACIONES --}}
            @if($this->asignaciones->isEmpty())
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-6 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800/60
                                flex items-center justify-center mx-auto mb-4 border border-slate-200/80 dark:border-white/10">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Sin asignaciones activas</p>
                    <p class="text-xs text-slate-400 mt-1">Tu líder o gerente aún no te ha asignado equipos.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($this->asignaciones as $asignacion)
                        @php
                            $completadosA = $asignacion->equipos->where('camino','COMPLETADO')->count();
                            $enProcesoA   = $asignacion->equipos->where('camino','EN_PROCESO')->count();
                            $piezasA      = $asignacion->equipos->where('camino','PIEZA_PENDIENTE')->count();
                            $garantiaA    = $asignacion->equipos->whereIn('camino',['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count();
                            $sinIniciar   = max($asignacion->cantidad - $asignacion->equipos->count(), 0);
                            $pct          = $asignacion->cantidad > 0 ? round(($completadosA / $asignacion->cantidad) * 100) : 0;
                        @endphp

                        <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                                    border border-slate-200/80 dark:border-white/10
                                    backdrop-blur-xl dark:backdrop-blur-2xl
                                    shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                    px-5 py-5 space-y-4
                                    transition-all duration-300 hover:-translate-y-1
                                    hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-indigo-500/25
                                    hover:border-indigo-400/50 dark:hover:border-indigo-400/30">

                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $asignacion->loteModelo->marca ?? '' }} {{ $asignacion->loteModelo->modelo ?? '—' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Lote: <span class="font-medium text-slate-700 dark:text-slate-200">
                                            {{ $asignacion->loteModelo->lote->nombre_lote ?? '—' }}
                                        </span>
                                        · {{ $asignacion->fecha_asignacion->format('d/m/Y') }}
                                    </p>
                                </div>
                                <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full
                                             text-[0.65rem] font-semibold
                                             {{ $asignacion->estatus === 'PENDIENTE'
                                                 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-300 border border-amber-400/40'
                                                 : 'bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-400/40' }}">
                                    {{ $asignacion->label_estatus }}
                                </span>
                            </div>

                            {{-- Desglose --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-xl bg-slate-100/80 dark:bg-slate-800/50
                                            border border-slate-200/60 dark:border-slate-700/50 px-3 py-2 text-center">
                                    <p class="text-[0.6rem] font-semibold text-slate-500 uppercase tracking-wide">Sin iniciar</p>
                                    <p class="text-lg font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $sinIniciar }}</p>
                                </div>
                                <div class="rounded-xl px-3 py-2 text-center
                                            {{ $enProcesoA > 0
                                                ? 'bg-blue-50/80 dark:bg-blue-900/20 border border-blue-300/50 dark:border-blue-600/40'
                                                : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }}">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide
                                              {{ $enProcesoA > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-slate-500' }}">
                                        En proceso
                                    </p>
                                    <div class="flex items-center justify-center gap-1 mt-0.5">
                                        @if($enProcesoA > 0)
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                        @endif
                                        <p class="text-lg font-bold {{ $enProcesoA > 0 ? 'text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                                            {{ $enProcesoA }}
                                        </p>
                                    </div>
                                </div>
                                <div class="rounded-xl px-3 py-2 text-center
                                            {{ $completadosA > 0
                                                ? 'bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-300/50 dark:border-emerald-600/40'
                                                : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }}">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide
                                              {{ $completadosA > 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-500' }}">
                                        Terminados
                                    </p>
                                    <p class="text-lg font-bold mt-0.5
                                              {{ $completadosA > 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-200' }}">
                                        {{ $completadosA }}
                                    </p>
                                </div>
                            </div>

                            @if($piezasA > 0 || $garantiaA > 0)
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($piezasA > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[0.65rem] font-semibold
                                                     bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                                     border border-amber-300/50">
                                            🔧 {{ $piezasA }} pieza(s)
                                        </span>
                                    @endif
                                    @if($garantiaA > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[0.65rem] font-semibold
                                                     bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300
                                                     border border-rose-300/50">
                                            ⚠️ {{ $garantiaA }} garantía(s)
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $completadosA }} de {{ $asignacion->cantidad }} terminados</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pct }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6] transition-all duration-500"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            <button wire:click="verEquipos({{ $asignacion->id }})"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5
                                       text-xs font-semibold
                                       bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                       text-white shadow-md shadow-blue-800/40
                                       hover:shadow-blue-500/60 hover:-translate-y-0.5 transition-all duration-200">
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

            {{-- ── PIEZAS PENDIENTES DE INSTALAR ──────────────────────── --}}
            @if($this->solicitudesPieza->isNotEmpty())
                <div class="rounded-2xl border border-amber-400/60 dark:border-amber-500/40
                            bg-amber-50/80 dark:bg-amber-950/30 backdrop-blur-xl
                            shadow-md shadow-amber-900/10 px-5 py-5 space-y-3">

                    <div class="flex items-center gap-2">
                        <span class="text-amber-600 dark:text-amber-400 text-base">🔧</span>
                        <p class="text-sm font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">
                            Piezas pendientes de instalar
                        </p>
                        <span class="ml-auto px-2 py-0.5 rounded-full text-xs font-bold
                                     bg-amber-500 text-white shadow-sm shadow-amber-500/40">
                            {{ $this->solicitudesPieza->count() }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach($this->solicitudesPieza as $sp)
                            @php
                                $eq          = $sp->equipo;
                                $piezaNombre = $sp->catalogoPieza?->nombre ?? $sp->descripcion_libre ?? 'Sin descripción';
                                $enCurso     = $sp->fueIniciada();
                            @endphp
                            <div class="flex items-center justify-between gap-3 flex-wrap
                                        rounded-xl bg-white/70 dark:bg-slate-900/50
                                        border {{ $enCurso ? 'border-sky-300/60 dark:border-sky-600/40' : 'border-amber-200/60 dark:border-amber-700/30' }}
                                        px-4 py-3">
                                <div class="flex-1 min-w-0 space-y-0.5">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50 truncate">
                                            {{ $piezaNombre }}
                                        </p>
                                        @if($enCurso)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                                                         text-[0.6rem] font-bold
                                                         bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300
                                                         border border-sky-300/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                                                En curso
                                            </span>
                                        @endif
                                    </div>
                                    @if($eq)
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            Equipo: <span class="font-medium text-slate-700 dark:text-slate-200">
                                                {{ $eq->numero_serie }} — {{ $eq->marca }} {{ $eq->modelo }}
                                            </span>
                                        </p>
                                    @endif
                                    @if($sp->inventarioPieza)
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            Almacén #{{ $sp->inventarioPieza->id }}
                                            @if($sp->inventarioPieza->numero_serie)
                                                · S/N <span class="font-mono">{{ $sp->inventarioPieza->numero_serie }}</span>
                                            @endif
                                        </p>
                                    @endif
                                    @if($enCurso)
                                        <p class="text-xs text-sky-600 dark:text-sky-400 font-medium">
                                            Iniciada {{ $sp->iniciada_instalacion_en?->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                                <button wire:click="verConfirmarPieza({{ $sp->id }})"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg
                                               text-white text-xs font-semibold shadow-md transition-all
                                               {{ $enCurso
                                                   ? 'bg-sky-500 hover:bg-sky-600 shadow-sky-500/40'
                                                   : 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/40' }}">
                                    {{ $enCurso ? 'Confirmar resultado' : 'Iniciar instalación' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 2: EQUIPOS DE LA ASIGNACIÓN                         --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'equipos')
            @php $a = $this->asignacionActual; @endphp

            <div class="space-y-4">

                {{-- Iniciar equipos --}}
                @if(($a?->equipos?->count() ?? 0) < ($a?->cantidad ?? 0))
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                px-5 py-5 space-y-4">

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Iniciar equipos
                            </span>
                            <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                            <button wire:click="agregarSerie"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium
                                       bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                                       text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                + Agregar serie
                            </button>
                        </div>

                        @if($errorSerie)
                            <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                                        px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                                {{ $errorSerie }}
                            </div>
                        @endif

                        <div class="space-y-2">
                            @foreach($series as $index => $serie)
                                <div class="flex gap-2 items-center" wire:key="serie-{{ $index }}">
                                    <span class="text-xs text-slate-400 w-5 text-right shrink-0">{{ $index + 1 }}</span>
                                    <input
                                        type="text"
                                        wire:model="series.{{ $index }}"
                                        placeholder="Número de serie..."
                                        class="flex-1 rounded-xl px-4 py-2.5 text-sm
                                               bg-white/70 dark:bg-slate-900/40
                                               border border-slate-300/80 dark:border-slate-700
                                               text-slate-900 dark:text-slate-100
                                               placeholder:text-slate-400
                                               focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none transition"
                                    >
                                    @if(count($series) > 1)
                                        <button wire:click="quitarSerie({{ $index }})"
                                            class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-900/20
                                                   border border-rose-300/50 text-rose-500
                                                   flex items-center justify-center
                                                   hover:bg-rose-100 transition shrink-0">
                                            ✕
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-between items-center">
                            <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">
                                Puedes agregar múltiples series a la vez. Máximo {{ max($a?->cantidad - ($a?->equipos?->count() ?? 0), 0) }} más.
                            </p>
                            <button wire:click="iniciarEquipos"
                                wire:loading.attr="disabled"
                                wire:target="iniciarEquipos"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold
                                       bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                       text-white shadow-md shadow-blue-800/40
                                       hover:shadow-blue-500/60 hover:-translate-y-0.5
                                       disabled:opacity-60 transition-all duration-200">
                                <span wire:loading.remove wire:target="iniciarEquipos">Iniciar equipos</span>
                                <span wire:loading wire:target="iniciarEquipos">Iniciando...</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-emerald-400/40 bg-emerald-50/80 dark:bg-emerald-950/20
                                px-4 py-3 text-xs font-medium text-emerald-700 dark:text-emerald-300
                                flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Todos los equipos de esta asignación han sido iniciados.
                    </div>
                @endif

                {{-- Lista de equipos --}}
                @if($a?->equipos?->count() > 0)
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                shadow-md shadow-slate-900/10 dark:shadow-slate-900/30 overflow-hidden">

                        <div class="px-5 py-3 border-b border-slate-200/60 dark:border-slate-800/80">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Equipos iniciados · {{ $a->equipos->count() }} de {{ $a->cantidad }}
                            </p>
                        </div>

                        <div class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                            @foreach($a->equipos as $ae)
                                @php
                                    $terminado = $ae->camino !== \App\Models\AsignacionEquipo::EN_PROCESO;
                                    $destino   = match($ae->camino) {
                                        'COMPLETADO'       => ['label' => 'Calidad',      'color' => 'emerald'],
                                        'PIEZA_PENDIENTE'  => ['label' => 'Pieza pend.',  'color' => 'amber'],
                                        'GARANTIA_INTERNA' => ['label' => 'Gar. Interna', 'color' => 'rose'],
                                        'GARANTIA_EXTERNA' => ['label' => 'Gar. Externa', 'color' => 'rose'],
                                        default            => null,
                                    };
                                @endphp

                                <div class="px-5 py-3 flex items-center justify-between gap-4
                                            hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full shrink-0
                                            {{ match($ae->camino) {
                                                'COMPLETADO'       => 'bg-emerald-400',
                                                'EN_PROCESO'       => 'bg-blue-400 animate-pulse',
                                                'PIEZA_PENDIENTE'  => 'bg-amber-400',
                                                'GARANTIA_INTERNA',
                                                'GARANTIA_EXTERNA' => 'bg-rose-400',
                                                default            => 'bg-slate-400'
                                            } }}"></div>
                                        <div>
                                            <p class="text-sm font-semibold font-mono text-slate-900 dark:text-slate-50">
                                                {{ $ae->equipo?->numero_serie ?? '—' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($terminado && $destino)
                                                    <span class="text-xs font-semibold text-{{ $destino['color'] }}-600 dark:text-{{ $destino['color'] }}-400">
                                                        ✓ Terminado → {{ $destino['label'] }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-blue-500 dark:text-blue-400">En proceso</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Acciones --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        {{-- Características siempre disponible --}}
                                        <button wire:click="verCaracteristicas({{ $ae->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                                   bg-white/80 dark:bg-slate-900/60
                                                   border border-slate-300/70 dark:border-slate-700
                                                   text-xs font-medium text-slate-700 dark:text-slate-200
                                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            📋 Características
                                        </button>

                                        {{-- Terminar solo si está en proceso --}}
                                        @if(!$terminado)
                                            <button wire:click="verTerminar({{ $ae->id }})"
                                                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                                       bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6]
                                                       text-white text-xs font-semibold
                                                       shadow-md shadow-blue-800/30
                                                       hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all duration-200">
                                                Terminar
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 3: CARACTERÍSTICAS (BORRADOR / SOLO LECTURA)        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'caracteristicas')
            @php $ae = $this->equipoActual; @endphp

            {{-- Banner solo lectura --}}
            @if($equipoTerminado)
                <div class="rounded-xl border border-emerald-400/40
                            bg-emerald-50/80 dark:bg-emerald-950/20
                            px-4 py-3 flex items-center gap-3 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                    <div>
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                            Equipo terminado — solo lectura
                        </p>
                        <p class="text-xs text-emerald-600/70 dark:text-emerald-400/70 mt-0.5">
                            Puedes consultar los datos pero no editarlos.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Info del equipo --}}
            <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                        bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3 mb-4
                        flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                        {{ $ae?->equipo?->marca }} {{ $ae?->equipo?->modelo }}
                    </p>
                    <p class="text-xs text-slate-400 font-mono">{{ $ae?->equipo?->numero_serie }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                             {{ $equipoTerminado
                                 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-300/50'
                                 : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-300/50' }}">
                    {{ $equipoTerminado ? '✓ Terminado' : 'Borrador' }}
                </span>
            </div>

            {{-- ── Datos fijos (siempre solo lectura) ────────────────────────────── --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                        px-5 py-4 space-y-3">

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Datos del equipo
                    </span>
                    <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php $eq = $this->equipoActual?->equipo; $lm = $this->asignacionActual?->loteModelo; @endphp

                    <div class="space-y-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Número de serie</p>
                        <p class="text-sm font-semibold font-mono text-slate-900 dark:text-slate-50">
                            {{ $eq?->numero_serie ?? '—' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Marca</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                            {{ $eq?->marca ?? $lm?->marca ?? '—' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Modelo</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                            {{ $eq?->modelo ?? $lm?->modelo ?? '—' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Lote</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                            {{ $lm?->lote?->nombre_lote ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── Formulario de características (bloqueado si terminado) ───────────── --}}
            <div class="{{ $equipoTerminado ? 'pointer-events-none opacity-70 select-none' : '' }}">
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            px-5 py-6 space-y-8 text-slate-900 dark:text-slate-100">

                    @include('livewire.preparacion.equipos._form', [
                        'mode'                   => 'mi-trabajo',
                        'form'                   => $form,
                        'proveedores'            => [],
                        'lotes'                  => [],
                        'modelosLote'            => [],
                        'lotesTerminadosIds'     => [],
                        'monitorEntradasOptions' => $monitorEntradasOptions,
                    ])

                </div>
            </div>

            {{-- Botones (siempre clickeables) --}}
            <div class="flex items-center justify-between mt-4">
                <button wire:click="volverAEquipos"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium
                           border border-slate-300/80 dark:border-slate-700
                           bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                    ← Volver
                </button>
                @if(!$equipoTerminado)
                    <div class="flex items-center gap-3">
                        <button wire:click="guardarAvance"
                            wire:loading.attr="disabled" wire:target="guardarAvance"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold
                                   border border-slate-300/80 dark:border-slate-600
                                   bg-white/80 dark:bg-slate-900/60 text-slate-700 dark:text-slate-200
                                   hover:bg-slate-100 dark:hover:bg-slate-800
                                   disabled:opacity-60 transition-all duration-200">
                            <span wire:loading.remove wire:target="guardarAvance">💾 Guardar avance</span>
                            <span wire:loading wire:target="guardarAvance">Guardando...</span>
                        </button>
                        <button wire:click="verTerminar({{ $this->asignacionEquipoId }})"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold
                                   bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                   text-white shadow-md shadow-blue-800/40
                                   hover:shadow-blue-500/60 hover:-translate-y-0.5 transition-all duration-200">
                            Terminar equipo →
                        </button>
                    </div>
                @endif
            </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 4: TERMINAR EQUIPO                                  --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'terminar')

            @if($error)
                <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                            px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                    {{ $error }}
                </div>
            @endif

            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                        px-5 py-6 space-y-6">

                {{-- Info equipo --}}
                <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                            bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                        {{ $this->equipoActual?->equipo?->marca }}
                        {{ $this->equipoActual?->equipo?->modelo }}
                    </p>
                    <p class="text-xs font-mono text-slate-400">
                        {{ $this->equipoActual?->equipo?->numero_serie }}
                    </p>
                </div>

                {{-- ¿A dónde va el equipo? --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            ¿A dónde va el equipo?
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Independientemente de la opción, tu trabajo en este equipo quedará como <strong class="text-slate-600 dark:text-slate-300">terminado</strong>.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach([
                            ['value' => 'COMPLETADO',       'label' => 'Listo — Calidad',   'desc' => 'El equipo quedó completo y pasa a revisión de calidad.', 'emoji' => '✅', 'color' => 'emerald'],
                            ['value' => 'PIEZA_PENDIENTE',  'label' => 'Falta una pieza',   'desc' => 'El equipo necesita una pieza para completarse.', 'emoji' => '🔧', 'color' => 'amber'],
                            ['value' => 'GARANTIA_INTERNA', 'label' => 'Garantía',          'desc' => 'El equipo tiene una falla que requiere garantía.', 'emoji' => '⚠️', 'color' => 'rose'],
                        ] as $opcion)
                            <button type="button"
                                wire:click="$set('camino', '{{ $opcion['value'] }}')"
                                class="rounded-xl border px-4 py-4 text-left transition-all duration-200
                                       {{ $camino === $opcion['value']
                                           ? 'border-[#FF9521] bg-[#FF9521]/10 shadow-md shadow-[#FF9521]/20'
                                           : 'border-slate-300/80 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40
                                              hover:border-slate-400 dark:hover:border-slate-600' }}">
                                <span class="block text-2xl mb-2">{{ $opcion['emoji'] }}</span>
                                <p class="text-sm font-semibold {{ $camino === $opcion['value'] ? 'text-[#FF9521]' : 'text-slate-800 dark:text-slate-100' }}">
                                    {{ $opcion['label'] }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $opcion['desc'] }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Si garantía: interna o externa --}}
                @if($camino === 'GARANTIA_INTERNA')
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tipo de garantía</p>
                        <div class="flex gap-3">
                            <button type="button" wire:click="$set('camino', 'GARANTIA_INTERNA')"
                                class="flex-1 rounded-xl border px-4 py-3 text-sm font-semibold text-center transition
                                       {{ $camino === 'GARANTIA_INTERNA'
                                           ? 'border-[#FF9521] bg-[#FF9521]/10 text-[#FF9521]'
                                           : 'border-slate-300/80 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-300' }}">
                                Interna (nosotros)
                            </button>
                            <button type="button" wire:click="$set('camino', 'GARANTIA_EXTERNA')"
                                class="flex-1 rounded-xl border px-4 py-3 text-sm font-semibold text-center transition
                                       {{ $camino === 'GARANTIA_EXTERNA'
                                           ? 'border-[#FF9521] bg-[#FF9521]/10 text-[#FF9521]'
                                           : 'border-slate-300/80 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-300' }}">
                                Externa (proveedor)
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Pieza faltante --}}
                @if($camino === 'PIEZA_PENDIENTE')
                    <div class="rounded-xl border border-amber-400/30 bg-amber-50/60 dark:bg-amber-900/10
                                px-4 py-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">
                                ¿Qué pieza falta?
                            </span>
                            <div class="h-px flex-1 bg-amber-300/40"></div>
                        </div>

                        {{-- Filtros de búsqueda --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Categoría</label>
                                <select wire:model.live="filtroCategoriaPieza"
                                    class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-[#FF9521] outline-none">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categoriasDisponibles as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Buscar por nombre</label>
                                <input type="text" wire:model.live.debounce.300ms="busquedaPieza"
                                    placeholder="Ej. RAM, SSD, Batería..."
                                    class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-[#FF9521] outline-none">
                            </div>
                        </div>

                        {{-- Lista de piezas del catálogo --}}
                        @if($catalogoPiezas->count() > 0)
                            <div class="max-h-48 overflow-y-auto space-y-1 rounded-xl border border-slate-200/80 dark:border-slate-700/50 bg-white/50 dark:bg-slate-900/20 p-2">
                                @foreach($catalogoPiezas as $pieza)
                                    <button type="button"
                                        wire:click="$set('catalogoPiezaId', {{ $pieza->id }})"
                                        @class([
                                            'w-full text-left rounded-lg px-3 py-2 text-sm transition-all duration-150',
                                            'bg-amber-100 dark:bg-amber-900/30 border border-amber-400/60 text-amber-800 dark:text-amber-300' => $catalogoPiezaId == $pieza->id,
                                            'hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300' => $catalogoPiezaId != $pieza->id,
                                        ])>
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <span class="font-medium">{{ $pieza->nombre }}</span>
                                                @if($pieza->especificacion)
                                                    <span class="text-xs text-slate-500 dark:text-slate-400 ml-1">— {{ $pieza->especificacion }}</span>
                                                @endif
                                            </div>
                                            <span class="text-[0.65rem] px-1.5 py-0.5 rounded bg-slate-200/70 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 shrink-0">
                                                {{ $pieza->categoria }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            @if($catalogoPiezaId)
                                @php $piezaSel = \App\Models\CatalogoPieza::find($catalogoPiezaId); @endphp
                                <p class="text-xs text-amber-700 dark:text-amber-400">
                                    Pieza seleccionada: <strong>{{ $piezaSel?->nombre ?? 'ID ' . $catalogoPiezaId }}</strong>
                                    @if($piezaSel) <span class="text-amber-500">({{ $piezaSel->categoria }})</span> @endif
                                    <button type="button" wire:click="$set('catalogoPiezaId', null)"
                                        class="ml-2 text-slate-400 hover:text-rose-500 transition-colors">&times; quitar</button>
                                </p>
                            @endif
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400 italic py-2 text-center">
                                No hay piezas en el catálogo
                                @if($busquedaPieza || $filtroCategoriaPieza) que coincidan con el filtro @endif.
                                Usa las notas para describir la pieza.
                            </p>
                        @endif

                        @if($error && $camino === 'PIEZA_PENDIENTE')
                            <p class="text-xs text-rose-500">{{ $error }}</p>
                        @endif

                        {{-- Notas adicionales de la pieza --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                                Especificaciones adicionales <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" wire:model="descripcionPiezaLibre"
                                placeholder="Ej. 8GB DDR4, 256GB SATA, 45Wh, color negro..."
                                class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-[#FF9521] outline-none">
                            <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">
                                Capacidad, tipo, generación, voltaje u otros detalles relevantes.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Notas --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notas</span>
                        <span class="text-[0.65rem] text-slate-400">(opcional)</span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>
                    <textarea wire:model="notasTerminar" rows="2"
                        placeholder="Observaciones finales del equipo..."
                        class="w-full rounded-xl px-4 py-2.5 text-sm bg-white/70 dark:bg-slate-900/40
                               border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-[#FF9521] outline-none resize-none"></textarea>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-1">
                    <button wire:click="volverACaracteristicas"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        ← Volver
                    </button>
                    <button wire:click="terminarEquipo"
                        wire:loading.attr="disabled"
                        wire:target="terminarEquipo"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl px-6 py-2.5 text-sm font-semibold
                               bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                               text-white shadow-md shadow-blue-800/50
                               hover:shadow-blue-500/70 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="terminarEquipo">✓ Confirmar y terminar</span>
                        <span wire:loading wire:target="terminarEquipo">Guardando...</span>
                    </button>
                </div>
            </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 5: CONFIRMAR INSTALACIÓN DE PIEZA                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'confirmar_pieza')
            @php $sp = $this->solicitudPiezaActual; @endphp

            <div class="max-w-lg mx-auto space-y-5">

                {{-- Info equipo y pieza --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-amber-300/60 dark:border-amber-600/40
                            backdrop-blur-xl shadow-md px-5 py-5 space-y-3">

                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-amber-500 text-lg">🔧</span>
                        <span class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                            Instalación de pieza
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-2 text-sm">
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50
                                    border border-slate-200/60 dark:border-slate-700/40 px-4 py-3">
                            <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                Pieza a instalar
                            </p>
                            <p class="font-bold text-slate-900 dark:text-slate-50">
                                {{ $sp?->catalogoPieza?->nombre ?? $sp?->descripcion_libre ?? 'Sin descripción' }}
                            </p>
                            @if($sp?->inventarioPieza)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Del almacén #{{ $sp->inventarioPieza->id }}
                                    @if($sp->inventarioPieza->numero_serie)
                                        · S/N: <span class="font-mono">{{ $sp->inventarioPieza->numero_serie }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        @if($sp?->equipo)
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50
                                        border border-slate-200/60 dark:border-slate-700/40 px-4 py-3">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                    Equipo
                                </p>
                                <p class="font-bold text-slate-900 dark:text-slate-50">
                                    {{ $sp->equipo->marca }} {{ $sp->equipo->modelo }}
                                </p>
                                <p class="text-xs font-mono text-slate-400 mt-0.5">
                                    {{ $sp->equipo->numero_serie }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- FASE A: No iniciada → botón de inicio --}}
                @if(!$sp?->fueIniciada())
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-5 py-6 space-y-4 text-center">

                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Cuando tengas la pieza en mano y estés listo, inicia la instalación.
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">
                            Se registrará el tiempo desde que inicies hasta que confirmes el resultado.
                        </p>

                        <button wire:click="iniciarInstalacionPieza"
                                wire:loading.attr="disabled"
                                wire:target="iniciarInstalacionPieza"
                                class="w-full inline-flex items-center justify-center gap-2
                                       rounded-xl px-6 py-4 text-sm font-bold
                                       bg-gradient-to-r from-amber-500 to-amber-600
                                       text-white shadow-lg shadow-amber-500/40
                                       hover:shadow-amber-500/60 hover:-translate-y-0.5
                                       disabled:opacity-60 transition-all duration-200">
                            <span wire:loading.remove wire:target="iniciarInstalacionPieza">🔧 Iniciar instalación</span>
                            <span wire:loading wire:target="iniciarInstalacionPieza">Iniciando...</span>
                        </button>

                        <button wire:click="volverDesdePieza"
                                class="w-full text-center text-xs text-slate-400 hover:text-slate-600
                                       dark:hover:text-slate-200 transition-colors py-1">
                            ← Volver
                        </button>
                    </div>

                {{-- FASE B: Ya iniciada → mostrar tiempo transcurrido + botones --}}
                @else
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-5 py-6 space-y-4">

                        {{-- Tiempo en curso --}}
                        <div class="flex items-center justify-center gap-2 py-1">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                                Instalación en curso · iniciada {{ $sp->iniciada_instalacion_en?->diffForHumans() }}
                            </p>
                        </div>

                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 text-center">
                            ¿La pieza funcionó correctamente?
                        </p>

                        <div class="grid grid-cols-2 gap-4">
                            <button wire:click="confirmarInstalacionPieza(true)"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmarInstalacionPieza"
                                    class="flex flex-col items-center gap-2 rounded-2xl px-4 py-5
                                           bg-emerald-50 dark:bg-emerald-950/40
                                           border-2 border-emerald-400/60 dark:border-emerald-500/50
                                           text-emerald-700 dark:text-emerald-300
                                           hover:bg-emerald-100 dark:hover:bg-emerald-900/40
                                           shadow-md shadow-emerald-500/20
                                           disabled:opacity-60 transition-all">
                                <span class="text-3xl">✅</span>
                                <span class="text-sm font-bold">Sí funcionó</span>
                                <span class="text-xs text-emerald-600/70 dark:text-emerald-400/70">La pieza quedó instalada</span>
                            </button>

                            <button wire:click="confirmarInstalacionPieza(false)"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmarInstalacionPieza"
                                    class="flex flex-col items-center gap-2 rounded-2xl px-4 py-5
                                           bg-rose-50 dark:bg-rose-950/40
                                           border-2 border-rose-400/60 dark:border-rose-500/50
                                           text-rose-700 dark:text-rose-300
                                           hover:bg-rose-100 dark:hover:bg-rose-900/40
                                           shadow-md shadow-rose-500/20
                                           disabled:opacity-60 transition-all">
                                <span class="text-3xl">❌</span>
                                <span class="text-sm font-bold">No funcionó</span>
                                <span class="text-xs text-rose-600/70 dark:text-rose-400/70">Se solicitará otra pieza al encargado</span>
                            </button>
                        </div>

                        <button wire:click="volverDesdePieza"
                                class="w-full text-center text-xs text-slate-400 hover:text-slate-600
                                       dark:hover:text-slate-200 transition-colors py-1">
                            ← Volver
                        </button>
                    </div>
                @endif

            </div>

        @endif

    </div>
</x-tb-background>
</div>