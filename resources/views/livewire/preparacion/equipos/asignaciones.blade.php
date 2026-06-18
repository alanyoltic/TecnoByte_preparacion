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
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">

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
                        ✅ Completados hoy
                    </p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                        {{ $this->metricas['completados_hoy'] }}
                    </p>
                    <p class="text-[0.65rem] text-emerald-600 dark:text-emerald-400 mt-1">Pasaron a calidad</p>
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
                        🔧 Pieza faltante
                    </p>
                    <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                        {{ $this->metricas['piezas_pendientes'] }}
                    </p>
                    <p class="text-[0.65rem] text-amber-600 dark:text-amber-400 mt-1">Esperando pieza</p>
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
                        ⚠️ En garantía
                    </p>
                    <p class="mt-2 text-2xl font-bold text-rose-800 dark:text-rose-100">
                        {{ $this->metricas['garantias'] }}
                    </p>
                    <p class="text-[0.65rem] text-rose-600 dark:text-rose-400 mt-1">Interna o externa</p>
                </div>

                {{-- Sin asignar --}}
                <div class="rounded-2xl bg-violet-50/90 dark:bg-violet-950/40
                            border border-violet-200/80 dark:border-violet-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl px-4 py-3
                            shadow-md shadow-violet-900/10 dark:shadow-violet-900/30
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-violet-500/40 dark:hover:shadow-violet-400/50
                            hover:border-violet-400/70">
                    <p class="text-xs font-semibold text-violet-700 dark:text-violet-200 uppercase tracking-wide">
                        Sin asignar
                    </p>
                    <p class="mt-2 text-2xl font-bold text-violet-800 dark:text-violet-100">
                        {{ $this->metricas['sin_asignar'] }}
                    </p>
                    <p class="text-[0.65rem] text-violet-600 dark:text-violet-400 mt-1">Pendientes de asignar</p>
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
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">🔵 Trabajando</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">✅ Completados</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">🔧 Pieza / ⚠️ Garantía</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Avance</th>
                                    <th class="px-5 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->asignacionesActivas as $tecnicoId => $asignaciones)
                                    @php
                                        $tecnico     = $asignaciones->first()->tecnico;
                                        $totalEq     = $asignaciones->sum('cantidad');
                                    @endphp
                                    @continue(!$tecnico)
                                    @php
                                        $completados = $asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['COMPLETADO','EN_CALIDAD'])->count());
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

                                        {{-- Pieza / Garantía --}}
                                        <td class="px-5 py-3">
                                            @php
                                                $piezasT   = $asignaciones->sum(fn($a) => $a->equipos->where('camino','PIEZA_PENDIENTE')->count());
                                                $garantiasT = $asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count());
                                            @endphp
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @if($piezasT > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                 text-xs font-semibold
                                                                 bg-amber-100 dark:bg-amber-900/30
                                                                 text-amber-700 dark:text-amber-300
                                                                 border border-amber-300/50">
                                                        🔧 {{ $piezasT }}
                                                    </span>
                                                @endif
                                                @if($garantiasT > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                 text-xs font-semibold
                                                                 bg-rose-100 dark:bg-rose-900/30
                                                                 text-rose-700 dark:text-rose-300
                                                                 border border-rose-300/50">
                                                        ⚠️ {{ $garantiasT }}
                                                    </span>
                                                @endif
                                                @if($piezasT === 0 && $garantiasT === 0)
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </div>
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

                    {{-- Buscador de lotes --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busquedaLote"
                            placeholder="Buscar lote..."
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                    </div>

                    <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                        @forelse($this->lotesDisponibles as $lote)
                            @php $loteAgotado = $lote->modelosRecibidos->sum('equipos_libres') <= 0; @endphp
                            {{-- Acordeón por lote --}}
                            <div x-data="{ open: {{ $loteAgotado ? 'false' : 'false' }} }"
                                 class="rounded-xl border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">

                                {{-- Header lote --}}
                                <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between px-4 py-3
                                           {{ $loteAgotado ? 'bg-slate-100/60 dark:bg-slate-900/30' : 'bg-slate-50/80 dark:bg-slate-900/60' }}
                                           hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                             :class="open ? 'rotate-180' : ''"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-semibold {{ $loteAgotado ? 'text-slate-400 dark:text-slate-500' : 'text-slate-900 dark:text-slate-50' }}">
                                            Lote {{ $lote->nombre_lote }}
                                        </span>
                                        @if($lote->fecha_llegada)
                                            <span class="text-xs text-slate-400">
                                                · {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                                            </span>
                                        @endif
                                        @if($loteAgotado)
                                            <span class="text-xs text-slate-400 bg-slate-200/80 dark:bg-slate-700/60 px-2 py-0.5 rounded-full">
                                                sin disponibles
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs {{ $loteAgotado ? 'text-slate-400' : 'text-emerald-600 dark:text-emerald-400 font-semibold' }}">
                                        {{ $lote->modelosRecibidos->sum('equipos_libres') }} disp · {{ $lote->modelosRecibidos->count() }} modelo(s)
                                    </span>
                                </button>

                                {{-- Modelos del lote --}}
                                <div x-show="open" x-transition>
                                    @foreach($lote->modelosRecibidos as $modelo)
                                        @php
                                            $agotado       = ($modelo->equipos_libres ?? 0) <= 0;
                                            $sinAsignar    = $this->equiposSinAsignarPorModelo[$modelo->id] ?? collect();
                                            $cantSelec     = $seleccion[$modelo->id] ?? 0;
                                            $seriesActivas = $seriesAsignadas[$modelo->id] ?? [];
                                        @endphp
                                        <div class="border-t border-slate-200/60 dark:border-slate-700/40
                                                    {{ $agotado
                                                        ? 'opacity-50 bg-slate-100/60 dark:bg-slate-900/30'
                                                        : ($cantSelec > 0
                                                            ? 'bg-[#FF9521]/5 dark:bg-[#FF9521]/5'
                                                            : 'bg-white/60 dark:bg-slate-950/20') }}">

                                            {{-- Fila principal: nombre + cantidad --}}
                                            <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                                <div>
                                                    <p class="text-sm font-medium {{ $agotado ? 'text-slate-400 dark:text-slate-500' : 'text-slate-900 dark:text-slate-100' }}">
                                                        {{ $modelo->marca }} {{ $modelo->modelo }}
                                                        @if($agotado)
                                                            <span class="ml-1 text-xs font-normal text-slate-400">(agotado)</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-slate-400">
                                                        <span class="font-semibold {{ $agotado ? 'text-slate-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                            {{ $modelo->equipos_libres ?? 0 }} disponibles
                                                        </span>
                                                        de {{ $modelo->cantidad_recibida ?? 0 }} recibidos
                                                        @if($sinAsignar->isNotEmpty())
                                                            · <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $sinAsignar->count() }} con serie</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="{{ $modelo->equipos_libres ?? 0 }}"
                                                    value="{{ $agotado ? 0 : $cantSelec }}"
                                                    @disabled($agotado)
                                                    wire:change="actualizarCantidad({{ $modelo->id }}, $event.target.value)"
                                                    class="w-20 rounded-xl px-3 py-1.5 text-sm text-center
                                                           bg-white/70 dark:bg-slate-900/40
                                                           border border-slate-300/80 dark:border-slate-700
                                                           text-slate-900 dark:text-slate-100
                                                           focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none
                                                           disabled:opacity-40 disabled:cursor-not-allowed">
                                            </div>

                                            {{-- Series específicas (solo si hay SIN_ASIGNAR y cantidad seleccionada) --}}
                                            @if(!$agotado && $cantSelec > 0 && $sinAsignar->isNotEmpty())
                                                <div x-data="{ openSeries: false }" class="px-4 pb-3">
                                                    <button type="button" @click="openSeries = !openSeries"
                                                        class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400
                                                               hover:text-[#FF9521] transition flex items-center gap-1">
                                                        <svg class="w-3 h-3 transition-transform" :class="openSeries ? 'rotate-180' : ''"
                                                             fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Elegir series específicas (opcional)
                                                        @if(count($seriesActivas) > 0)
                                                            <span class="ml-1 text-[#FF9521] font-semibold">{{ count($seriesActivas) }} seleccionada(s)</span>
                                                        @endif
                                                    </button>

                                                    <div x-show="openSeries" x-transition class="mt-2 space-y-1 max-h-40 overflow-y-auto pr-1">
                                                        <p class="text-[0.6rem] text-slate-400 mb-1">
                                                            Máximo {{ $cantSelec }}. Los no seleccionados se asignan al azar.
                                                        </p>
                                                        @foreach($sinAsignar as $eq)
                                                            @php $checked = in_array($eq->numero_serie, $seriesActivas, true); @endphp
                                                            <label class="flex items-center gap-2 rounded-lg px-3 py-1.5 cursor-pointer
                                                                          text-xs transition
                                                                          {{ $checked
                                                                              ? 'bg-[#FF9521]/10 border border-[#FF9521]/40'
                                                                              : 'hover:bg-slate-100 dark:hover:bg-slate-800/50' }}
                                                                          {{ !$checked && count($seriesActivas) >= $cantSelec ? 'opacity-40 pointer-events-none' : '' }}">
                                                                <input type="checkbox"
                                                                    wire:click="toggleSerie({{ $modelo->id }}, '{{ $eq->numero_serie }}')"
                                                                    {{ $checked ? 'checked' : '' }}
                                                                    class="rounded text-[#FF9521] focus:ring-[#FF9521]">
                                                                <span class="font-mono text-slate-800 dark:text-slate-100">{{ $eq->numero_serie }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
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
                    wire:model="notas"
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


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 3: DETALLE DE TÉCNICO                                --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'detalle')

            @php
                $tecnico      = $this->tecnicoDetalle;
                $asignaciones = $this->asignacionesTecnico;
                $totalEq      = $asignaciones->sum('cantidad');
                $totalComp    = $asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['COMPLETADO','EN_CALIDAD'])->count());
                $totalProc    = $asignaciones->sum(fn($a) => $a->equipos->where('camino','EN_PROCESO')->count());
                $totalPend    = $asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['PENDIENTE','PRE_ASIGNADO'])->count());
                $totalPiezas  = $asignaciones->sum(fn($a) => $a->equipos->where('camino','PIEZA_PENDIENTE')->count());
                $totalGar     = $asignaciones->sum(fn($a) => $a->equipos->whereIn('camino',['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count());
                $totalSinIni  = max($totalEq - $asignaciones->sum(fn($a) => $a->equipos->count()), 0);
            @endphp

            {{-- TARJETAS MÉTRICAS DEL TÉCNICO --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-4 py-3 shadow-md
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-slate-500/20">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">⬜ Sin serie</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalSinIni }}</p>
                    <p class="text-[0.65rem] text-slate-400 mt-1">Pendientes de escanear</p>
                </div>

                <div class="rounded-2xl bg-blue-50/90 dark:bg-blue-950/40
                            border border-blue-200/80 dark:border-blue-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-blue-500/40">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-200 uppercase tracking-wide">🔵 Trabajando</p>
                    <p class="mt-2 text-2xl font-bold text-blue-800 dark:text-blue-100">{{ $totalProc }}</p>
                    <p class="text-[0.65rem] text-blue-500 dark:text-blue-400 mt-1">En preparación</p>
                </div>

                <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40
                            border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-emerald-500/40">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">✅ Completados</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">{{ $totalComp }}</p>
                    <p class="text-[0.65rem] text-emerald-600 dark:text-emerald-400 mt-1">Pasaron a calidad</p>
                </div>

                <div class="rounded-2xl bg-amber-50/90 dark:bg-amber-950/40
                            border border-amber-200/80 dark:border-amber-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-amber-500/40">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">🔧 Pieza faltante</p>
                    <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">{{ $totalPiezas }}</p>
                    <p class="text-[0.65rem] text-amber-600 dark:text-amber-400 mt-1">Esperando pieza</p>
                </div>

                <div class="rounded-2xl bg-rose-50/90 dark:bg-rose-950/40
                            border border-rose-200/80 dark:border-rose-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-rose-500/40">
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-200 uppercase tracking-wide">⚠️ En garantía</p>
                    <p class="mt-2 text-2xl font-bold text-rose-800 dark:text-rose-100">{{ $totalGar }}</p>
                    <p class="text-[0.65rem] text-rose-600 dark:text-rose-400 mt-1">Interna o externa</p>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-4 py-3 shadow-md
                            transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-indigo-500/20">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Total equipos</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalEq }}</p>
                    <p class="text-[0.65rem] text-slate-400 mt-1">{{ $asignaciones->count() }} asignación(es)</p>
                </div>

            </div>

            {{-- ASIGNACIONES --}}
            @if($asignaciones->isEmpty())
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-6 py-12 text-center">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Este técnico no tiene asignaciones activas.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($asignaciones as $asignacion)
                        @php
                            $completados = $asignacion->equipos->whereIn('camino',['COMPLETADO','EN_CALIDAD'])->count();
                            $enProceso   = $asignacion->equipos->where('camino','EN_PROCESO')->count();
                            $piezas      = $asignacion->equipos->where('camino','PIEZA_PENDIENTE')->count();
                            $garantia    = $asignacion->equipos->whereIn('camino',['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count();
                            $sinIniciar  = max($asignacion->cantidad - $asignacion->equipos->count(), 0);
                            $iniciados   = $asignacion->equipos->filter(
                                fn($ae) => !in_array($ae->camino, ['PENDIENTE', 'PRE_ASIGNADO'], true)
                            )->count();
                            $pct         = $asignacion->cantidad > 0 ? round(($completados / $asignacion->cantidad) * 100) : 0;
                        @endphp

                        <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                                    border border-slate-200/80 dark:border-white/10
                                    backdrop-blur-xl dark:backdrop-blur-2xl
                                    shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
                                    px-5 py-5 space-y-4
                                    transition-all duration-300 hover:-translate-y-1
                                    hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-indigo-500/25
                                    hover:border-indigo-400/50 dark:hover:border-indigo-400/30">

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

                            @if($editarNotasAsignacionId === $asignacion->id)
                                <div class="mt-2 rounded-xl border border-slate-200/70 dark:border-slate-700/60 bg-slate-50/80 dark:bg-slate-900/40 p-3">
                                    <textarea wire:model.defer="notasEditar" rows="3" class="w-full rounded-lg border border-slate-300/70 dark:border-slate-700 bg-white/80 dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 outline-none"></textarea>
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button wire:click="cancelarEditarNotas" type="button" class="text-sm px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
                                        <button wire:click="guardarNotasAsignacion" type="button" class="text-sm px-3 py-1.5 rounded-lg bg-blue-600 text-white">Guardar</button>
                                    </div>
                                </div>
                            @elseif(auth()->user() && auth()->user()->tienePermiso('prep.inventario.gestion'))
                                @if(!empty($asignacion->notas))
                                    <div class="mt-2 flex items-start justify-between gap-3 rounded-xl bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/70 dark:border-amber-600/30 px-3 py-2">
                                        <div class="flex items-start gap-2 min-w-0">
                                            <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed">
                                                {{ $asignacion->notas }}
                                            </p>
                                        </div>
                                        <button wire:click="abrirEditarNotas({{ $asignacion->id }})" type="button" class="shrink-0 text-[0.65rem] font-semibold text-amber-700 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-100">
                                            Editar
                                        </button>
                                    </div>
                                @else
                                    <div class="mt-2 flex justify-end">
                                        <button wire:click="abrirEditarNotas({{ $asignacion->id }})" type="button" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                                            Agregar nota
                                        </button>
                                    </div>
                                @endif
                            @endif

                            {{-- Desglose 3 columnas --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-xl bg-slate-100/80 dark:bg-slate-800/50
                                            border border-slate-200/60 dark:border-slate-700/50 px-3 py-2 text-center">
                                    <p class="text-[0.6rem] font-semibold text-slate-500 uppercase tracking-wide">⬜ Sin iniciar</p>
                                    <p class="text-lg font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $sinIniciar }}</p>
                                </div>
                                <div class="rounded-xl px-3 py-2 text-center
                                            {{ $enProceso > 0
                                                ? 'bg-blue-50/80 dark:bg-blue-900/20 border border-blue-300/50 dark:border-blue-600/40'
                                                : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }}">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide
                                              {{ $enProceso > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-slate-500' }}">
                                        🔵 Trabajando
                                    </p>
                                    <div class="flex items-center justify-center gap-1 mt-0.5">
                                        @if($enProceso > 0)
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                        @endif
                                        <p class="text-lg font-bold {{ $enProceso > 0 ? 'text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                                            {{ $enProceso }}
                                        </p>
                                    </div>
                                </div>
                                <div class="rounded-xl px-3 py-2 text-center
                                            {{ $completados > 0
                                                ? 'bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-300/50 dark:border-emerald-600/40'
                                                : 'bg-slate-100/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50' }}">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wide
                                              {{ $completados > 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-500' }}">
                                        ✅ Completados
                                    </p>
                                    <p class="text-lg font-bold mt-0.5
                                              {{ $completados > 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-200' }}">
                                        {{ $completados }}
                                    </p>
                                </div>
                            </div>

                            {{-- Badges pieza/garantía --}}
                            @if($piezas > 0 || $garantia > 0)
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($piezas > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                                     text-[0.65rem] font-semibold
                                                     bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                                     border border-amber-300/50">
                                            🔧 {{ $piezas }} pieza(s) faltante
                                        </span>
                                    @endif
                                    @if($garantia > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                                     text-[0.65rem] font-semibold
                                                     bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300
                                                     border border-rose-300/50">
                                            ⚠️ {{ $garantia }} en garantía
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Barra progreso --}}
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $completados }} de {{ $asignacion->cantidad }} completados</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pct }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6] transition-all duration-500"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            {{-- Footer: cancelar o mensaje --}}
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button wire:click="abrirModalEditar({{ $asignacion->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                           bg-blue-50 dark:bg-blue-900/20
                                           border border-blue-300/60 dark:border-blue-600/40
                                           text-xs font-medium text-blue-700 dark:text-blue-300
                                           hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                                    Editar cantidad
                                </button>
                                @if($iniciados === 0)
                                    <button wire:click="abrirModalCancelar({{ $asignacion->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                               bg-rose-50 dark:bg-rose-900/20
                                               border border-rose-300/60 dark:border-rose-600/40
                                               text-xs font-medium text-rose-600 dark:text-rose-300
                                               hover:bg-rose-100 dark:hover:bg-rose-900/40 transition">
                                        Cancelar asignación
                                    </button>
                                @endif
                            </div>
                            @if($iniciados > 0)
                                <div class="flex justify-end">
                                    <span class="text-[0.65rem] text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                        No se puede cancelar — tiene equipos en proceso
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

    </div>

    {{-- ═══════════════════════════════════ --}}
    {{-- MODAL CANCELAR ASIGNACIÓN          --}}
    {{-- ═══════════════════════════════════ --}}
    @if($modalCancelar)
        @php
            $asigCancelar = $cancelarAsignacionId
                ? \App\Models\Asignacion::with(['tecnico','loteModelo.lote'])->find($cancelarAsignacionId)
                : null;
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center"
             x-data
             @keydown.escape.window="$wire.cerrarModalCancelar()">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                 wire:click="cerrarModalCancelar"></div>

            {{-- Modal --}}
            <div class="relative w-[92%] max-w-md rounded-2xl border border-white/10
                        bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                        shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-5">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                            Cancelar asignación
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <button wire:click="cerrarModalCancelar"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20
                               border border-white/10 text-slate-500 dark:text-slate-300
                               flex items-center justify-center transition">✕</button>
                </div>

                {{-- Detalle de la asignación --}}
                @if($asigCancelar)
                    <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Técnico</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                {{ $asigCancelar->tecnico?->nombre }} {{ $asigCancelar->tecnico?->apellido_paterno }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Modelo</span>
                            <span class="text-sm text-slate-700 dark:text-slate-200">
                                {{ $asigCancelar->loteModelo?->marca }} {{ $asigCancelar->loteModelo?->modelo }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Lote</span>
                            <span class="text-sm text-slate-700 dark:text-slate-200">
                                {{ $asigCancelar->loteModelo?->lote?->nombre_lote ?? '—' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Cantidad asignada</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                {{ $asigCancelar->cantidad }} equipo(s)
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Motivo --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Motivo de cancelación <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        wire:model="motivoCancelacion"
                        rows="3"
                        placeholder="Describe el motivo de la cancelación..."
                        class="w-full rounded-xl px-4 py-2.5 text-sm
                               bg-white/70 dark:bg-slate-900/40
                               border border-slate-300/80 dark:border-slate-700
                               text-slate-900 dark:text-slate-100
                               placeholder:text-slate-400
                               focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50
                               outline-none resize-none">
                    </textarea>
                    @error('motivoCancelacion')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-1">
                    <button wire:click="cerrarModalCancelar"
                        class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40
                               text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        No, mantener
                    </button>
                    <button wire:click="confirmarCancelacion"
                        wire:loading.attr="disabled"
                        wire:target="confirmarCancelacion"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl px-5 py-2.5 text-sm font-semibold
                               bg-rose-600 hover:bg-rose-500
                               text-white shadow-md shadow-rose-800/40
                               hover:shadow-rose-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="confirmarCancelacion">Sí, cancelar asignación</span>
                        <span wire:loading wire:target="confirmarCancelacion">Cancelando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</x-tb-background>

    {{-- ═══════════════════════════════════ --}}
    {{-- MODAL EDITAR CANTIDAD ASIGNACIÓN    --}}
    {{-- ═══════════════════════════════════ --}}
    @if($modalEditar)
        @php
            $asigEditar = $editarAsignacionId
                ? \App\Models\Asignacion::with(['tecnico','loteModelo.lote', 'equipos'])->find($editarAsignacionId)
                : null;
            $iniciadosEditar = 0;
            $maxDisponibles = 0;

            if ($asigEditar) {
                $iniciadosEditar = $asigEditar->equipos->filter(
                    fn ($ae) => ! in_array($ae->camino, [\App\Models\AsignacionEquipo::PENDIENTE, \App\Models\AsignacionEquipo::PRE_ASIGNADO], true)
                )->count();

                $loteModeloId = $asigEditar->lote_modelo_id;
                $modelo = \App\Models\LoteModeloRecibido::find($loteModeloId);

                // Calcular total disponible del modelo para aumentar asignación
                // Físicos sin asignar
                $sinSerie = \App\Models\Equipo::where('lote_modelo_id', $loteModeloId)
                    ->where('estatus_area', \App\Models\Equipo::AREA_SIN_ASIGNAR)
                    ->whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->whereNull('numero_serie')->orWhereRaw("TRIM(numero_serie) = ''");
                    })->count();
                $conSerie = \App\Models\Equipo::where('lote_modelo_id', $loteModeloId)
                    ->where('estatus_area', \App\Models\Equipo::AREA_SIN_ASIGNAR)
                    ->whereNull('deleted_at')
                    ->whereNotNull('numero_serie')->whereRaw("TRIM(numero_serie) <> ''")
                    ->whereExists(function ($q) {
                        $q->select(DB::raw('1'))->from('equipo_movimientos as em')
                            ->whereColumn('em.equipo_id', 'equipos.id')->where('em.tipo', 'ALTA_LOTE');
                    })->count();
                
                // Cupo pendiente
                $registrados = \App\Models\Equipo::where('lote_modelo_id', $loteModeloId)->whereNull('deleted_at')->count();
                $reservados = \App\Models\Asignacion::where('lote_modelo_id', $loteModeloId)
                    ->whereIn('estatus', [\App\Models\Asignacion::PENDIENTE, \App\Models\Asignacion::EN_PROCESO])
                    ->get()->sum(fn ($a) => max($a->cantidad - $a->equipos()->count(), 0));
                
                $cupo = max((int) ($modelo->cantidad_recibida ?? 0) - $registrados - $reservados, 0);
                
                // Max disponible = cantidad actual + los extras que puede agarrar
                $maxDisponibles = $asigEditar->cantidad + ($sinSerie + $conSerie + $cupo);
            }
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center"
             x-data
             @keydown.escape.window="$wire.cerrarModalEditar()">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                 wire:click="cerrarModalEditar"></div>

            {{-- Modal --}}
            <div class="relative w-[92%] max-w-md rounded-2xl border border-white/10
                        bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                        shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-5">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                            Editar cantidad de asignación
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Puedes aumentar o reducir los equipos asignados a este técnico.
                        </p>
                    </div>
                    <button wire:click="cerrarModalEditar"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20
                               border border-white/10 text-slate-500 dark:text-slate-300
                               flex items-center justify-center transition">✕</button>
                </div>

                {{-- Detalle de la asignación --}}
                @if($asigEditar)
                    <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Técnico</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                {{ $asigEditar->tecnico?->nombre }} {{ $asigEditar->tecnico?->apellido_paterno }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Modelo</span>
                            <span class="text-sm text-slate-700 dark:text-slate-200">
                                {{ $asigEditar->loteModelo?->marca }} {{ $asigEditar->loteModelo?->modelo }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Equipos ya en proceso</span>
                            <span class="text-sm font-semibold {{ $iniciadosEditar > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500' }}">
                                {{ $iniciadosEditar }} equipo(s)
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Input cantidad --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Nueva Cantidad <span class="text-blue-500">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="editarCantidad"
                        min="{{ $iniciadosEditar }}"
                        max="{{ $maxDisponibles }}"
                        class="w-full rounded-xl px-4 py-2.5 text-sm
                               bg-white/70 dark:bg-slate-900/40
                               border border-slate-300/80 dark:border-slate-700
                               text-slate-900 dark:text-slate-100
                               placeholder:text-slate-400
                               focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50
                               outline-none">
                    <p class="text-[0.65rem] text-slate-400 mt-1">
                        El técnico ya tiene {{ $iniciadosEditar }} en proceso (mínimo). 
                        Puedes subirlo hasta {{ $maxDisponibles }} según inventario.
                    </p>
                    @error('editarCantidad')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-1">
                    <button wire:click="cerrarModalEditar"
                        class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40
                               text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button wire:click="guardarEdicion"
                        wire:loading.attr="disabled"
                        wire:target="guardarEdicion"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl px-5 py-2.5 text-sm font-semibold
                               bg-blue-600 hover:bg-blue-500
                               text-white shadow-md shadow-blue-800/40
                               hover:shadow-blue-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="guardarEdicion">Guardar cambios</span>
                        <span wire:loading wire:target="guardarEdicion">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
