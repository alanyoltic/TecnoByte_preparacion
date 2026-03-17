<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ═══════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                     --}}
        {{-- ═══════════════════════════════════ --}}
        @if($vista === 'panel')
            <x-topbar
                title="Asignaciones"
                chip="Preparación · Gestión"
                description="Administra y monitorea los equipos asignados a cada técnico."
            >
                <x-slot name="right">
                    <button wire:click="irANuevaAsignacion"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                               bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                               text-white text-xs font-semibold
                               shadow-md shadow-blue-800/40
                               hover:shadow-blue-500/60 hover:-translate-y-0.5
                               transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva asignación
                    </button>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'nueva')
            <x-topbar
                title="Nueva asignación"
                chip="Preparación · Asignaciones"
                description="Selecciona el técnico y los equipos a asignar."
            >
                <x-slot name="right">
                    <button wire:click="volverDesdeNueva"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60
                               border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Volver
                    </button>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'detalle')
            <x-topbar
                title="{{ $this->tecnicoDetalle?->nombre }} {{ $this->tecnicoDetalle?->apellido_paterno }}"
                chip="Técnico · Asignaciones activas"
                description="{{ $this->asignacionesTecnico->count() }} asignación(es) en proceso."
            >
                <x-slot name="right">
                    <button wire:click="volverAPanel"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60
                               border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Panel
                    </button>
                </x-slot>
            </x-topbar>
        @endif


        {{-- ═══════════════════════════════════ --}}
        {{-- VISTA 1: PANEL GENERAL              --}}
        {{-- ═══════════════════════════════════ --}}
        @if($vista === 'panel')

            {{-- MÉTRICAS --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

                {{-- Técnicos activos --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-sky-500/20 dark:hover:shadow-sky-500/25
                            hover:border-sky-400/70 dark:hover:border-sky-300/50">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                        Técnicos activos
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                        {{ $this->metricas['tecnicos_activos'] }}
                    </p>
                </div>

                {{-- Equipos asignados --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-indigo-500/25
                            hover:border-indigo-400/70 dark:hover:border-indigo-300/50">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                        Equipos asignados
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                        {{ $this->metricas['equipos_asignados'] }}
                    </p>
                </div>

                {{-- En proceso --}}
                <div class="rounded-2xl bg-blue-50/90 dark:bg-blue-950/40
                            border border-blue-200/80 dark:border-blue-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-blue-900/10 dark:shadow-blue-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-blue-500/40 dark:hover:shadow-blue-400/50
                            hover:border-blue-400/70">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-200 uppercase tracking-wide">
                        En proceso
                    </p>
                    <p class="mt-2 text-2xl font-bold text-blue-800 dark:text-blue-100">
                        {{ $this->metricas['en_proceso'] }}
                    </p>
                </div>

                {{-- Completados hoy --}}
                <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40
                            border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-emerald-900/10 dark:shadow-emerald-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-emerald-500/40 dark:hover:shadow-emerald-400/50
                            hover:border-emerald-400/70">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                        Completados hoy
                    </p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                        {{ $this->metricas['completados_hoy'] }}
                    </p>
                </div>

                {{-- Piezas pendientes --}}
                <div class="rounded-2xl bg-amber-50/90 dark:bg-amber-950/40
                            border border-amber-200/80 dark:border-amber-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-amber-900/10 dark:shadow-amber-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-amber-500/40 dark:hover:shadow-amber-400/50
                            hover:border-amber-400/70">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                        Piezas pendientes
                    </p>
                    <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                        {{ $this->metricas['piezas_pendientes'] }}
                    </p>
                </div>

                {{-- Garantías --}}
                <div class="rounded-2xl bg-rose-50/90 dark:bg-rose-950/40
                            border border-rose-200/80 dark:border-rose-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-rose-900/10 dark:shadow-rose-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-rose-500/40 dark:hover:shadow-rose-400/50
                            hover:border-rose-400/70">
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-200 uppercase tracking-wide">
                        Garantías
                    </p>
                    <p class="mt-2 text-2xl font-bold text-rose-800 dark:text-rose-100">
                        {{ $this->metricas['garantias'] }}
                    </p>
                </div>

            </div>

            {{-- TABLA DE TÉCNICOS --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                        overflow-hidden
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-indigo-500/25
                        hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50">

                <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80
                            flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        Técnicos con asignaciones activas
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $this->asignacionesActivas->count() }} técnico(s)
                    </p>
                </div>

                @if($this->asignacionesActivas->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            No hay asignaciones activas. Crea una nueva asignación.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-slate-100 dark:bg-slate-950/90
                                          border-b border-slate-200 dark:border-slate-800/80">
                                <tr>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Técnico</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Asignaciones</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Total equipos</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">En proceso</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Completados</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Problemas</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Avance</th>
                                    <th class="px-5 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->asignacionesActivas as $tecnicoId => $asignaciones)
                                    @php
                                        $tecnico     = $asignaciones->first()->tecnico;
                                        $totalEq     = $asignaciones->sum('cantidad');
                                        $completados = $asignaciones->sum(fn($a) => $a->equipos->where('camino','COMPLETADO')->count());
                                        $enProceso   = $asignaciones->sum(fn($a) => $a->equipos->where('camino','EN_PROCESO')->count());
                                        $problemas   = $asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['PIEZA_PENDIENTE','GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count());
                                        $pct         = $totalEq > 0 ? round(($completados / $totalEq) * 100) : 0;
                                    @endphp
                                    <tr class="border-b border-slate-200 dark:border-slate-800/80
                                               hover:bg-white/60 dark:hover:bg-slate-800/40 transition-colors">

                                        {{-- Técnico --}}
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl
                                                            bg-gradient-to-br from-[#1E3A8A] to-[#3B82F6]
                                                            flex items-center justify-center shrink-0">
                                                    <span class="text-xs font-bold text-white">
                                                        {{ strtoupper(substr($tecnico->nombre, 0, 1)) }}{{ strtoupper(substr($tecnico->apellido_paterno ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-50">
                                                        {{ $tecnico->nombre }} {{ $tecnico->apellido_paterno }}
                                                    </p>
                                                    <p class="text-xs text-slate-400">{{ $tecnico->email }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Asignaciones --}}
                                        <td class="px-5 py-3 text-slate-700 dark:text-slate-200 font-semibold">
                                            {{ $asignaciones->count() }}
                                        </td>

                                        {{-- Total equipos --}}
                                        <td class="px-5 py-3 text-slate-700 dark:text-slate-200">
                                            {{ $totalEq }}
                                        </td>

                                        {{-- En proceso --}}
                                        <td class="px-5 py-3">
                                            @if($enProceso > 0)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                                             text-xs font-semibold
                                                             bg-blue-100 dark:bg-blue-900/30
                                                             text-blue-700 dark:text-blue-300
                                                             border border-blue-300/50 dark:border-blue-600/40">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                                    {{ $enProceso }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>

                                        {{-- Completados --}}
                                        <td class="px-5 py-3">
                                            @if($completados > 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                             text-xs font-semibold
                                                             bg-emerald-100 dark:bg-emerald-900/30
                                                             text-emerald-700 dark:text-emerald-300
                                                             border border-emerald-300/50">
                                                    {{ $completados }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>

                                        {{-- Problemas --}}
                                        <td class="px-5 py-3">
                                            @if($problemas > 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                             text-xs font-semibold
                                                             bg-amber-100 dark:bg-amber-900/30
                                                             text-amber-700 dark:text-amber-300
                                                             border border-amber-300/50">
                                                    ⚠ {{ $problemas }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>

                                        {{-- Avance --}}
                                        <td class="px-5 py-3 min-w-[120px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 h-1.5 rounded-full bg-slate-200 dark:bg-slate-800">
                                                    <div class="h-full rounded-full bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6] transition-all duration-500"
                                                         style="width: {{ $pct }}%"></div>
                                                </div>
                                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                                    {{ $pct }}%
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="px-5 py-3 text-right">
                                            <button
                                                wire:click="verDetalle({{ $tecnicoId }})"
                                                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                                       bg-white/80 dark:bg-slate-900/60
                                                       border border-slate-300/70 dark:border-slate-700
                                                       text-xs font-medium text-slate-700 dark:text-slate-200
                                                       hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>


        {{-- ═══════════════════════════════════ --}}
        {{-- VISTA 2: NUEVA ASIGNACIÓN           --}}
        {{-- ═══════════════════════════════════ --}}
        @elseif($vista === 'nueva')

            {{-- Error --}}
            @if($error)
                <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                            px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                    {{ $error }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- COLUMNA IZQUIERDA: Seleccionar técnico --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            px-5 py-5 space-y-4">

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            1. Selecciona el técnico
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    {{-- Buscador --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busquedaTecnico"
                            placeholder="Buscar técnico..."
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                    </div>

                    {{-- Lista de técnicos --}}
                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        @forelse($this->tecnicos as $tecnico)
                            @php
                                $asigActivas = \App\Models\Asignacion::where('tecnico_id', $tecnico->id)
                                    ->whereIn('estatus', ['PENDIENTE','EN_PROCESO'])
                                    ->count();
                            @endphp
                            <button
                                type="button"
                                wire:click="seleccionarTecnico({{ $tecnico->id }})"
                                class="w-full flex items-center justify-between gap-3 rounded-xl px-4 py-3
                                       border transition-all duration-200 text-left
                                       {{ $tecnicoId === $tecnico->id
                                           ? 'border-[#FF9521] bg-[#FF9521]/10'
                                           : 'border-slate-300/80 dark:border-slate-700
                                              bg-white/60 dark:bg-slate-900/40
                                              hover:border-slate-400 dark:hover:border-slate-600' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl shrink-0
                                                {{ $tecnicoId === $tecnico->id
                                                    ? 'bg-[#FF9521]'
                                                    : 'bg-gradient-to-br from-[#1E3A8A] to-[#3B82F6]' }}
                                                flex items-center justify-center">
                                        <span class="text-xs font-bold text-white">
                                            {{ strtoupper(substr($tecnico->nombre, 0, 1)) }}{{ strtoupper(substr($tecnico->apellido_paterno ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $tecnico->nombre }} {{ $tecnico->apellido_paterno }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            {{ $asigActivas }} asignación(es) activa(s)
                                        </p>
                                    </div>
                                </div>
                                @if($tecnicoId === $tecnico->id)
                                    <span class="text-[#FF9521] text-lg">✓</span>
                                @endif
                            </button>
                        @empty
                            <p class="text-sm text-slate-400 text-center py-4">No se encontraron técnicos.</p>
                        @endforelse
                    </div>
                </div>

                {{-- COLUMNA DERECHA: Seleccionar equipos --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                            px-5 py-5 space-y-4">

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            2. Selecciona los equipos
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                        @forelse($this->lotesDisponibles as $lote)
                            {{-- Acordeón por lote --}}
                            <div x-data="{ open: true }"
                                 class="rounded-xl border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">

                                {{-- Header lote --}}
                                <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between px-4 py-3
                                           bg-slate-50/80 dark:bg-slate-900/60
                                           hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                             :class="open ? 'rotate-180' : ''"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                            Lote {{ $lote->nombre_lote }}
                                        </span>
                                        @if($lote->fecha_llegada)
                                            <span class="text-xs text-slate-400">
                                                · {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-400">
                                        {{ $lote->modelosRecibidos->count() }} modelo(s)
                                    </span>
                                </button>

                                {{-- Modelos del lote --}}
                                <div x-show="open" x-transition>
                                    @foreach($lote->modelosRecibidos as $modelo)
                                        <div class="flex items-center justify-between gap-4 px-4 py-2.5
                                                    border-t border-slate-200/60 dark:border-slate-700/40
                                                    {{ isset($seleccion[$modelo->id]) && $seleccion[$modelo->id] > 0
                                                        ? 'bg-[#FF9521]/5 dark:bg-[#FF9521]/5'
                                                        : 'bg-white/60 dark:bg-slate-950/20' }}">
                                            <div>
                                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                    {{ $modelo->marca }} {{ $modelo->modelo }}
                                                </p>
                                                <p class="text-xs text-slate-400">
                                                    {{ $modelo->equipos_libres ?? 0 }} disponibles
                                                    de {{ $modelo->total_equipos ?? 0 }}
                                                </p>
                                            </div>
                                            <input
                                                type="number"
                                                min="0"
                                                max="{{ $modelo->equipos_libres ?? 0 }}"
                                                value="{{ $seleccion[$modelo->id] ?? 0 }}"
                                                wire:change="actualizarCantidad({{ $modelo->id }}, $event.target.value)"
                                                class="w-20 rounded-xl px-3 py-1.5 text-sm text-center
                                                       bg-white/70 dark:bg-slate-900/40
                                                       border border-slate-300/80 dark:border-slate-700
                                                       text-slate-900 dark:text-slate-100
                                                       focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 text-center py-4">
                                No hay equipos disponibles para asignar.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Resumen selección --}}
            @if(!empty($seleccion))
                <div class="rounded-xl border border-[#FF9521]/30 bg-[#FF9521]/5
                            px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[#FF9521] font-semibold text-sm">
                            {{ array_sum($seleccion) }} equipo(s) seleccionado(s)
                        </span>
                        <span class="text-xs text-slate-400">en {{ count($seleccion) }} modelo(s)</span>
                    </div>
                </div>
            @endif

            {{-- Notas --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                        px-5 py-5 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Notas para el técnico
                    </span>
                    <span class="text-[0.65rem] text-slate-400">(opcional)</span>
                    <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                </div>
                <textarea
                    wire:model.defer="notas"
                    rows="2"
                    placeholder="Instrucciones especiales, prioridades, observaciones..."
                    class="w-full rounded-xl px-4 py-2.5 text-sm
                           bg-white/70 dark:bg-slate-900/40
                           border border-slate-300/80 dark:border-slate-700
                           text-slate-900 dark:text-slate-100
                           placeholder:text-slate-400
                           focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none resize-none">
                </textarea>
            </div>

            {{-- Botón confirmar --}}
            <div class="flex items-center justify-between">
                <button type="button" wire:click="volverDesdeNueva"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium
                           border border-slate-300/80 dark:border-slate-700
                           bg-white/60 dark:bg-slate-900/40
                           text-slate-600 dark:text-slate-300
                           hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-all duration-200">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarAsignacion"
                    wire:loading.attr="disabled"
                    wire:target="guardarAsignacion"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl px-6 py-2.5 text-sm font-semibold
                           bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                           text-white shadow-md shadow-blue-800/50
                           hover:shadow-blue-500/70 hover:-translate-y-0.5
                           disabled:opacity-60 transition-all duration-200">
                    <span wire:loading.remove wire:target="guardarAsignacion">Confirmar asignación</span>
                    <span wire:loading wire:target="guardarAsignacion">Guardando...</span>
                </button>
            </div>


        {{-- ═══════════════════════════════════ --}}
        {{-- VISTA 3: DETALLE DE TÉCNICO         --}}
        {{-- ═══════════════════════════════════ --}}
        @elseif($vista === 'detalle')

            @if($this->asignacionesTecnico->isEmpty())
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-6 py-12 text-center">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Este técnico no tiene asignaciones activas.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($this->asignacionesTecnico as $asignacion)
                        @php
                            $completados = $asignacion->equipos->where('camino','COMPLETADO')->count();
                            $enProceso   = $asignacion->equipos->where('camino','EN_PROCESO')->count();
                            $piezas      = $asignacion->equipos->where('camino','PIEZA_PENDIENTE')->count();
                            $garantia    = $asignacion->equipos->whereIn('camino',['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count();
                            $sinIniciar  = max($asignacion->cantidad - $asignacion->equipos->count(), 0);
                            $pct         = $asignacion->cantidad > 0 ? round(($completados / $asignacion->cantidad) * 100) : 0;
                        @endphp

                        <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                                    border border-slate-200/80 dark:border-white/10
                                    backdrop-blur-xl dark:backdrop-blur-2xl
                                    shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                    px-5 py-5 space-y-4">

                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $asignacion->loteModelo->marca ?? '' }}
                                        {{ $asignacion->loteModelo->modelo ?? '—' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Lote: <span class="font-medium text-slate-700 dark:text-slate-200">
                                            {{ $asignacion->loteModelo->lote->nombre_lote ?? '—' }}
                                        </span>
                                        · Asignado: {{ $asignacion->fecha_asignacion->format('d/m/Y') }}
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
                            <div class="grid grid-cols-4 gap-2">
                                <div class="rounded-xl bg-slate-100/80 dark:bg-slate-800/50
                                            border border-slate-200/60 dark:border-slate-700/50
                                            px-3 py-2 text-center">
                                    <p class="text-[0.6rem] font-semibold text-slate-500 uppercase tracking-wide">Sin iniciar</p>
                                    <p class="text-base font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $sinIniciar }}</p>
                                </div>
                                <div class="rounded-xl {{ $enProceso > 0 ? 'bg-blue-50/80 dark:bg-blue-900/20 border border-blue-300/50 dark:border-blue-600/40' : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }} px-3 py-2 text-center">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide {{ $enProceso > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-slate-500' }}">En proceso</p>
                                    <p class="text-base font-bold mt-0.5 {{ $enProceso > 0 ? 'text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">{{ $enProceso }}</p>
                                </div>
                                <div class="rounded-xl {{ $completados > 0 ? 'bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-300/50 dark:border-emerald-600/40' : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }} px-3 py-2 text-center">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide {{ $completados > 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-500' }}">Completados</p>
                                    <p class="text-base font-bold mt-0.5 {{ $completados > 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-200' }}">{{ $completados }}</p>
                                </div>
                                <div class="rounded-xl {{ ($piezas + $garantia) > 0 ? 'bg-amber-50/80 dark:bg-amber-900/20 border border-amber-300/50 dark:border-amber-600/40' : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }} px-3 py-2 text-center">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide {{ ($piezas + $garantia) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-slate-500' }}">Problemas</p>
                                    <p class="text-base font-bold mt-0.5 {{ ($piezas + $garantia) > 0 ? 'text-amber-700 dark:text-amber-200' : 'text-slate-700 dark:text-slate-200' }}">{{ $piezas + $garantia }}</p>
                                </div>
                            </div>

                            {{-- Barra progreso --}}
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $completados }} de {{ $asignacion->cantidad }}</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pct }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6] transition-all duration-500"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            {{-- Lista de equipos si hay --}}
                            @if($asignacion->equipos->count() > 0)
                                <div class="rounded-xl border border-slate-200/60 dark:border-slate-700/40 overflow-hidden">
                                    @foreach($asignacion->equipos as $ae)
                                        <div class="flex items-center gap-3 px-4 py-2
                                                    border-b border-slate-200/40 dark:border-slate-800/40 last:border-0
                                                    hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                                            <div class="w-2 h-2 rounded-full shrink-0
                                                {{ match($ae->camino) {
                                                    'COMPLETADO'       => 'bg-emerald-400',
                                                    'EN_PROCESO'       => 'bg-blue-400 animate-pulse',
                                                    'PIEZA_PENDIENTE'  => 'bg-amber-400',
                                                    'GARANTIA_INTERNA',
                                                    'GARANTIA_EXTERNA' => 'bg-rose-400',
                                                    default            => 'bg-slate-400'
                                                } }}"></div>
                                            <span class="text-xs font-mono text-slate-700 dark:text-slate-300">
                                                {{ $ae->equipo?->numero_serie ?? '—' }}
                                            </span>
                                            <span class="text-xs text-slate-400">
                                                {{ \App\Models\AsignacionEquipo::labelsCamino()[$ae->camino] ?? $ae->camino }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

    </div>
</x-tb-background>
</div>
