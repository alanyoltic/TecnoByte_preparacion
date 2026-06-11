<div x-data x-init="
    let _scrollY = 0;
    Livewire.hook('commit', ({ succeed }) => {
        _scrollY = window.scrollY;
        succeed(({ snapshot, effect }) => {
            requestAnimationFrame(() => window.scrollTo({ top: _scrollY, behavior: 'instant' }));
        });
    });
">
<x-tb-background>
<div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">
    <x-toast />
    
    <x-topbar
        title="Estadísticas Detalladas de Inventario"
        chip="Dashboard · Concentrado por Modelos y Fases"
    />

    {{-- Filtros Avanzados --}}
    <div class="bg-white/80 dark:bg-slate-950/60
                backdrop-blur-xl dark:backdrop-blur-2xl
                border border-slate-200/80 dark:border-white/10
                rounded-2xl p-5
                shadow-md shadow-slate-900/10
                dark:shadow-lg dark:shadow-slate-900/30
                flex flex-col xl:flex-row gap-4 items-center justify-between
                transition-all duration-300
                hover:-translate-y-1
                hover:shadow-lg hover:shadow-indigo-500/20
                dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
                hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50">
        <div class="flex items-center gap-2 w-full xl:w-auto">
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Filtros:</span>
        </div>

        <div class="flex flex-col sm:flex-row w-full xl:w-auto gap-3 flex-wrap items-center">
            {{-- Orden --}}
            <select wire:model.live="orden"
                    class="w-full sm:w-36 rounded-2xl
                           bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm text-slate-900 dark:text-slate-100
                           px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                <option value="total_desc">Mayor cantidad</option>
                <option value="marca_asc">Alfabético</option>
            </select>

            {{-- Tipo de Equipo --}}
            <select wire:model.live="filtroTipo"
                    class="w-full sm:w-36 rounded-2xl
                           bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm text-slate-900 dark:text-slate-100
                           px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                <option value="">Todos los Tipos</option>
                @foreach($listaTipos as $tipo)
                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                @endforeach
            </select>

            {{-- Marca --}}
            <select wire:model.live="filtroMarca"
                    class="w-full sm:w-40 rounded-2xl
                           bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm text-slate-900 dark:text-slate-100
                           px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                <option value="">Todas las Marcas</option>
                @foreach($listaMarcas as $marca)
                    <option value="{{ $marca }}">{{ $marca }}</option>
                @endforeach
            </select>

            {{-- Modelo (reactivo a marca) --}}
            <select wire:model.live="filtroModelo"
                    class="w-full sm:w-48 rounded-2xl
                           bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm text-slate-900 dark:text-slate-100
                           px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                <option value="">Todos los Modelos</option>
                @foreach($listaModelos as $modelo)
                    <option value="{{ $modelo }}">{{ $modelo }}</option>
                @endforeach
            </select>

            {{-- Búsqueda Texto --}}
            <div class="relative w-full sm:w-56">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar..."
                       class="w-full pl-10 pr-3 py-2 rounded-2xl
                              bg-white/80 dark:bg-slate-900/60
                              border border-white/60 dark:border-slate-700/70
                              text-sm text-slate-900 dark:text-slate-100
                              placeholder:text-slate-400 dark:placeholder:text-slate-500
                              shadow-md shadow-slate-900/10 dark:shadow-xl dark:shadow-slate-950/60
                              focus:outline-none focus:ring-2 focus:ring-blue-500/70 focus:border-blue-500/70
                              backdrop-blur-xl">
            </div>

            {{-- Botón limpiar filtros --}}
            @if($filtroMarca || $filtroTipo || $filtroModelo || $filtroEstatus || $search)
                <button wire:click="limpiarFiltros"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-2xl
                               bg-red-50 dark:bg-red-950/40
                               border border-red-200 dark:border-red-500/50
                               text-xs font-semibold text-red-600 dark:text-red-300
                               hover:bg-red-100 dark:hover:bg-red-900/50
                               transition-all duration-200 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Limpiar filtros
                </button>
            @endif
        </div>
    </div>

    {{-- Tarjetas de Resumen — Clickables para filtrar la tabla por estatus --}}
    @php $fe = $filtroEstatus; @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-4">

        {{-- Total Preparación — Muestra todo (limpia filtro de estatus) --}}
        <button wire:click="filtrarPorEstatus('')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === '' 
                       ? 'bg-sky-50 dark:bg-sky-950/50 border-2 border-sky-400 dark:border-sky-400 shadow-lg shadow-sky-500/25 -translate-y-0.5' 
                       : 'bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 hover:-translate-y-1 hover:border-sky-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === '' ? 'text-sky-600 dark:text-sky-300' : 'text-slate-500 dark:text-slate-400' }}">Total Prep.</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === '' ? 'text-sky-700 dark:text-sky-200' : 'text-slate-900 dark:text-slate-50' }}">{{ number_format($totales['general']) }}</p>
            @if($fe === '')<span class="mt-1 inline-block text-[0.6rem] font-bold text-sky-500 dark:text-sky-400 uppercase tracking-wider">✔ Todos</span>@endif
        </button>

        {{-- Disponibles --}}
        <button wire:click="filtrarPorEstatus('disponibles')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'disponibles'
                       ? 'bg-slate-100/90 dark:bg-slate-800/60 border-2 border-slate-500 dark:border-slate-400 shadow-lg shadow-slate-500/20 -translate-y-0.5'
                       : 'bg-slate-50/90 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-500/70 shadow-md shadow-slate-900/10 hover:-translate-y-1 hover:border-slate-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'disponibles' ? 'text-slate-700 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400' }}">Disponibles</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'disponibles' ? 'text-slate-800 dark:text-slate-100' : 'text-slate-600 dark:text-slate-300' }}">{{ number_format($totales['disponibles']) }}</p>
            @if($fe === 'disponibles')<span class="mt-1 inline-block text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- Asignados --}}
        <button wire:click="filtrarPorEstatus('asignado')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'asignado'
                       ? 'bg-blue-100/90 dark:bg-blue-900/50 border-2 border-blue-500 dark:border-blue-400 shadow-lg shadow-blue-500/25 -translate-y-0.5'
                       : 'bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-500/70 shadow-md shadow-blue-900/10 hover:-translate-y-1 hover:border-blue-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'asignado' ? 'text-blue-700 dark:text-blue-200' : 'text-blue-600 dark:text-blue-300' }}">Asignados</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'asignado' ? 'text-blue-800 dark:text-blue-100' : 'text-blue-700 dark:text-blue-200' }}">{{ number_format($totales['asignado']) }}</p>
            @if($fe === 'asignado')<span class="mt-1 inline-block text-[0.6rem] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- En Proceso --}}
        <button wire:click="filtrarPorEstatus('proceso')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'proceso'
                       ? 'bg-orange-100/90 dark:bg-orange-900/50 border-2 border-orange-500 dark:border-orange-400 shadow-lg shadow-orange-500/25 -translate-y-0.5'
                       : 'bg-orange-50/90 dark:bg-orange-950/40 border border-orange-200/80 dark:border-orange-500/70 shadow-md shadow-orange-900/10 hover:-translate-y-1 hover:border-orange-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'proceso' ? 'text-orange-700 dark:text-orange-200' : 'text-orange-600 dark:text-orange-300' }}">En Proceso</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'proceso' ? 'text-orange-800 dark:text-orange-100' : 'text-orange-700 dark:text-orange-200' }}">{{ number_format($totales['proceso']) }}</p>
            @if($fe === 'proceso')<span class="mt-1 inline-block text-[0.6rem] font-bold text-orange-500 dark:text-orange-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- En Calidad --}}
        <button wire:click="filtrarPorEstatus('calidad')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'calidad'
                       ? 'bg-purple-100/90 dark:bg-purple-900/50 border-2 border-purple-500 dark:border-purple-400 shadow-lg shadow-purple-500/25 -translate-y-0.5'
                       : 'bg-purple-50/90 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-500/70 shadow-md shadow-purple-900/10 hover:-translate-y-1 hover:border-purple-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'calidad' ? 'text-purple-700 dark:text-purple-200' : 'text-purple-600 dark:text-purple-300' }}">En Calidad</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'calidad' ? 'text-purple-800 dark:text-purple-100' : 'text-purple-700 dark:text-purple-200' }}">{{ number_format($totales['calidad']) }}</p>
            @if($fe === 'calidad')<span class="mt-1 inline-block text-[0.6rem] font-bold text-purple-500 dark:text-purple-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- Falta Pieza --}}
        <button wire:click="filtrarPorEstatus('pieza')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'pieza'
                       ? 'bg-amber-100/90 dark:bg-amber-900/50 border-2 border-amber-500 dark:border-amber-400 shadow-lg shadow-amber-500/25 -translate-y-0.5'
                       : 'bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-500/70 shadow-md shadow-amber-900/10 hover:-translate-y-1 hover:border-amber-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'pieza' ? 'text-amber-700 dark:text-amber-200' : 'text-amber-600 dark:text-amber-300' }}">Falta Pieza</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'pieza' ? 'text-amber-800 dark:text-amber-100' : 'text-amber-700 dark:text-amber-200' }}">{{ number_format($totales['pieza']) }}</p>
            @if($fe === 'pieza')<span class="mt-1 inline-block text-[0.6rem] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- Garantías --}}
        <button wire:click="filtrarPorEstatus('garantia')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'garantia'
                       ? 'bg-red-100/90 dark:bg-red-900/50 border-2 border-red-500 dark:border-red-400 shadow-lg shadow-red-500/25 -translate-y-0.5'
                       : 'bg-red-50/90 dark:bg-red-950/40 border border-red-200/80 dark:border-red-500/70 shadow-md shadow-red-900/10 hover:-translate-y-1 hover:border-red-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'garantia' ? 'text-red-700 dark:text-red-200' : 'text-red-600 dark:text-red-300' }}">Garantías</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'garantia' ? 'text-red-800 dark:text-red-100' : 'text-red-700 dark:text-red-200' }}">{{ number_format($totales['garantia']) }}</p>
            @if($fe === 'garantia')<span class="mt-1 inline-block text-[0.6rem] font-bold text-red-500 dark:text-red-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- Desarme --}}
        <button wire:click="filtrarPorEstatus('desarme')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'desarme'
                       ? 'bg-rose-100/90 dark:bg-rose-900/50 border-2 border-rose-500 dark:border-rose-400 shadow-lg shadow-rose-500/25 -translate-y-0.5'
                       : 'bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-500/70 shadow-md shadow-rose-900/10 hover:-translate-y-1 hover:border-rose-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'desarme' ? 'text-rose-700 dark:text-rose-200' : 'text-rose-600 dark:text-rose-300' }}">Desarme</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'desarme' ? 'text-rose-800 dark:text-rose-100' : 'text-rose-700 dark:text-rose-200' }}">{{ number_format($totales['desarme']) }}</p>
            @if($fe === 'desarme')<span class="mt-1 inline-block text-[0.6rem] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- Aprobados --}}
        <button wire:click="filtrarPorEstatus('finalizado')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'finalizado'
                       ? 'bg-emerald-100/90 dark:bg-emerald-900/50 border-2 border-emerald-500 dark:border-emerald-400 shadow-lg shadow-emerald-500/25 -translate-y-0.5'
                       : 'bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-500/70 shadow-md shadow-emerald-900/10 hover:-translate-y-1 hover:border-emerald-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'finalizado' ? 'text-emerald-700 dark:text-emerald-200' : 'text-emerald-600 dark:text-emerald-300' }}">Aprobados</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'finalizado' ? 'text-emerald-800 dark:text-emerald-100' : 'text-emerald-700 dark:text-emerald-200' }}">{{ number_format($totales['finalizado']) }}</p>
            @if($fe === 'finalizado')<span class="mt-1 inline-block text-[0.6rem] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

        {{-- Transferidos --}}
        <button wire:click="filtrarPorEstatus('transferido')" type="button"
            class="text-left rounded-2xl px-4 py-3 w-full
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   transition-all duration-200 active:scale-[0.97] cursor-pointer select-none
                   {{ $fe === 'transferido'
                       ? 'bg-teal-100/90 dark:bg-teal-900/50 border-2 border-teal-500 dark:border-teal-400 shadow-lg shadow-teal-500/25 -translate-y-0.5'
                       : 'bg-teal-50/90 dark:bg-teal-950/40 border border-teal-200/80 dark:border-teal-500/70 shadow-md shadow-teal-900/10 hover:-translate-y-1 hover:border-teal-400/70' }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wide {{ $fe === 'transferido' ? 'text-teal-700 dark:text-teal-200' : 'text-teal-600 dark:text-teal-300' }}">Transferidos</p>
            <p class="mt-2 text-2xl font-bold {{ $fe === 'transferido' ? 'text-teal-800 dark:text-teal-100' : 'text-teal-700 dark:text-teal-200' }}">{{ number_format($totales['transferido']) }}</p>
            @if($fe === 'transferido')<span class="mt-1 inline-block text-[0.6rem] font-bold text-teal-500 dark:text-teal-400 uppercase tracking-wider">✔ Filtro activo</span>@endif
        </button>

    </div>

    {{-- ═══ Selector de Vista ═══ --}}
    <div class="bg-white/80 dark:bg-slate-950/60
                backdrop-blur-xl dark:backdrop-blur-2xl
                border border-slate-200/80 dark:border-white/10
                rounded-2xl p-3
                shadow-md shadow-slate-900/10
                dark:shadow-lg dark:shadow-slate-900/30
                flex items-center justify-between">
        <div class="flex items-center gap-1.5">
            {{-- Tabla --}}
            <button wire:click="cambiarVista('tabla')" type="button"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       transition-all duration-200 active:scale-[0.96]
                       {{ $vistaActiva === 'tabla'
                           ? 'bg-[#FF9521]/10 dark:bg-[#FF9521]/20 border-2 border-[#FF9521] text-[#FF9521] shadow-md shadow-[#FF9521]/20'
                           : 'bg-transparent border border-transparent text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-700 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M3 6h18M3 18h18"/>
                </svg>
                Tabla
            </button>

            {{-- Donut --}}
            <button wire:click="cambiarVista('donut')" type="button"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       transition-all duration-200 active:scale-[0.96]
                       {{ $vistaActiva === 'donut'
                           ? 'bg-[#FF9521]/10 dark:bg-[#FF9521]/20 border-2 border-[#FF9521] text-[#FF9521] shadow-md shadow-[#FF9521]/20'
                           : 'bg-transparent border border-transparent text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-700 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                Donut
            </button>

            {{-- Barras --}}
            <button wire:click="cambiarVista('barras')" type="button"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       transition-all duration-200 active:scale-[0.96]
                       {{ $vistaActiva === 'barras'
                           ? 'bg-[#FF9521]/10 dark:bg-[#FF9521]/20 border-2 border-[#FF9521] text-[#FF9521] shadow-md shadow-[#FF9521]/20'
                           : 'bg-transparent border border-transparent text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-700 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Barras
            </button>

            {{-- Apiladas --}}
            <button wire:click="cambiarVista('apiladas')" type="button"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       transition-all duration-200 active:scale-[0.96]
                       {{ $vistaActiva === 'apiladas'
                           ? 'bg-[#FF9521]/10 dark:bg-[#FF9521]/20 border-2 border-[#FF9521] text-[#FF9521] shadow-md shadow-[#FF9521]/20'
                           : 'bg-transparent border border-transparent text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-700 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17h6M9 13h6M9 9h2"/>
                </svg>
                Por Marca
            </button>
        </div>

        <span class="text-xs text-slate-400 dark:text-slate-500 hidden sm:inline-block">
            {{ count($estadisticas) }} marca(s) · {{ collect($estadisticas)->sum(fn($m) => count($m['modelos'])) }} modelo(s)
        </span>
    </div>

    {{-- ═══ VISTA: Tabla ═══ --}}
    @if($vistaActiva === 'tabla')
    <div class="bg-white/80 dark:bg-slate-950/60
                backdrop-blur-xl dark:backdrop-blur-2xl
                border border-slate-200/80 dark:border-white/10
                rounded-2xl overflow-hidden
                shadow-md shadow-slate-900/10
                dark:shadow-lg dark:shadow-slate-900/30
                transition-all duration-300
                hover:-translate-y-1
                hover:shadow-lg hover:shadow-indigo-500/20
                dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
                hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1400px]">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800/80">
                        <th class="px-5 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-slate-500 w-[18%]">Modelo del Equipo</th>
                        <th class="px-3 py-3 text-[0.65rem] font-black uppercase tracking-wider text-center text-slate-400 bg-slate-100/50 dark:bg-slate-800" title="Equipos en bodega: sin serie escaneada + con serie pero sin asignar aún">Disponibles</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-blue-500">Asig.</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-[#FF9521]">Proceso</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-amber-500">Pieza</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-red-500">Gtía.</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-rose-500">Desarme</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-purple-500">Calidad</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-emerald-500">Aprob.</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-teal-500">Transf.</th>
                        <th class="px-5 py-3 text-[0.7rem] font-bold uppercase tracking-wider text-right text-slate-800 dark:text-slate-200 border-l border-slate-200 dark:border-slate-700">Total Físico</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($estadisticas as $marca => $datosMarca)

                        {{-- Cabecera de la Marca --}}
                        <tr class="bg-slate-50/40 dark:bg-slate-800/40 border-t-2 border-t-slate-200 dark:border-t-slate-700">
                            <td colspan="1" class="px-5 py-2">
                                <span class="text-[0.8rem] font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">{{ $marca }}</span>
                            </td>
                            <td class="px-3 py-2 text-center bg-slate-100/50 dark:bg-slate-800">
                                <span class="text-xs font-black text-slate-400">
                                    {{ number_format($datosMarca['total_marca_disponibles']) }}
                                </span>
                            </td>
                            <td colspan="8"></td>
                            <td class="px-5 py-2 text-right border-l border-slate-200 dark:border-slate-700">
                                <span class="px-2 py-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-[0.7rem] font-black text-slate-700 dark:text-slate-300 shadow-sm" title="Total de equipos con número de serie en el sistema">
                                    {{ number_format($datosMarca['total_marca_fisicos']) }}
                                </span>
                            </td>
                        </tr>

                        {{-- Desglose de Modelos --}}
                        @foreach($datosMarca['modelos'] as $m)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-5 py-2.5 pl-8 border-r border-slate-100 dark:border-slate-800/50">
                                    <div class="flex flex-col">
                                        <span class="text-[0.75rem] font-semibold text-slate-700 dark:text-slate-300 leading-tight">{{ $m->modelo }}</span>
                                        @if($m->tipo_equipo)
                                            <span class="text-[0.6rem] text-slate-400 mt-0.5">{{ $m->tipo_equipo }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Disponibles (sin serie + con serie sin asignar) --}}
                                <td class="px-3 py-2.5 text-center bg-slate-100/30 dark:bg-slate-800/50">
                                    @if($m->disponibles > 0)
                                        <span class="text-xs font-black text-slate-500 dark:text-slate-400">{{ $m->disponibles }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300/50 dark:text-slate-700/50">-</span>
                                    @endif
                                </td>

                                {{-- Asignados --}}
                                <td class="px-3 py-2.5 text-center bg-blue-50/20 dark:bg-blue-900/5">
                                    @if($m->c_asignado > 0)
                                        <span class="text-xs font-bold text-blue-500">{{ $m->c_asignado }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- En Proceso --}}
                                <td class="px-3 py-2.5 text-center bg-[#FF9521]/5">
                                    @if($m->c_proceso > 0)
                                        <span class="text-xs font-black text-[#FF9521]">{{ $m->c_proceso }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- P. Pieza --}}
                                <td class="px-3 py-2.5 text-center bg-amber-50/20 dark:bg-amber-900/5">
                                    @if($m->c_pieza > 0)
                                        <span class="text-xs font-bold text-amber-500">{{ $m->c_pieza }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- P. Garantía --}}
                                <td class="px-3 py-2.5 text-center bg-red-50/20 dark:bg-red-900/5">
                                    @if($m->c_garantia > 0)
                                        <span class="text-xs font-bold text-red-500">{{ $m->c_garantia }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- P. Desarme --}}
                                <td class="px-3 py-2.5 text-center bg-rose-50/20 dark:bg-rose-900/5">
                                    @if($m->c_desarme > 0)
                                        <span class="text-xs font-bold text-rose-500">{{ $m->c_desarme }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- Calidad --}}
                                <td class="px-3 py-2.5 text-center bg-purple-50/20 dark:bg-purple-900/5">
                                    @if($m->c_calidad > 0)
                                        <span class="text-xs font-bold text-purple-500">{{ $m->c_calidad }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- Finalizado --}}
                                <td class="px-3 py-2.5 text-center bg-emerald-50/20 dark:bg-emerald-900/5 border-l border-emerald-100 dark:border-emerald-900/30">
                                    @if($m->c_finalizado > 0)
                                        <span class="text-xs font-bold text-emerald-500">{{ $m->c_finalizado }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- Transferido --}}
                                <td class="px-3 py-2.5 text-center bg-teal-50/20 dark:bg-teal-900/5">
                                    @if($m->c_transferido > 0)
                                        <span class="text-xs font-bold text-teal-500">{{ $m->c_transferido }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>

                                {{-- Total Fila --}}
                                <td class="px-5 py-2.5 text-right border-l border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/20">
                                    <span class="text-[0.8rem] font-black text-slate-700 dark:text-slate-300 group-hover:text-[#FF9521] transition-colors">
                                        {{ number_format($m->total_equipos) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-500">No se encontraron modelos con los filtros actuales.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══ VISTA: Donut ═══ --}}
    @if($vistaActiva === 'donut')
    @php $graficas = $this->datosGraficas; $chartKey = md5(json_encode($graficas)); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Donut principal --}}
        <div class="bg-white/80 dark:bg-slate-950/60
                    backdrop-blur-xl dark:backdrop-blur-2xl
                    border border-slate-200/80 dark:border-white/10
                    rounded-2xl p-6
                    shadow-md shadow-slate-900/10
                    dark:shadow-lg dark:shadow-slate-900/30"
             wire:key="donut-{{ $chartKey }}"
             x-data="{
                chart: null,
                init() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const data = @js($graficas['donut']);
                    this.chart = new ApexCharts(this.$refs.donutChart, {
                        chart: {
                            type: 'donut',
                            height: 420,
                            background: 'transparent',
                            fontFamily: 'Figtree, sans-serif',
                            animations: { enabled: true, easing: 'easeinout', speed: 800 },
                        },
                        series: data.series,
                        labels: data.labels,
                        colors: data.colors,
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        name: { show: true, fontSize: '14px', fontWeight: 700, color: isDark ? '#e2e8f0' : '#334155' },
                                        value: { show: true, fontSize: '28px', fontWeight: 800, color: isDark ? '#f1f5f9' : '#0f172a',
                                                 formatter: (val) => parseInt(val).toLocaleString() },
                                        total: {
                                            show: true, label: 'Total',
                                            fontSize: '13px', fontWeight: 600,
                                            color: isDark ? '#94a3b8' : '#64748b',
                                            formatter: (w) => w.globals.seriesTotals.reduce((a,b) => a+b, 0).toLocaleString()
                                        }
                                    }
                                }
                            }
                        },
                        dataLabels: { enabled: false },
                        legend: {
                            position: 'bottom', fontSize: '12px', fontWeight: 600,
                            labels: { colors: isDark ? '#cbd5e1' : '#475569' },
                            markers: { size: 6, offsetX: -4 },
                            itemMargin: { horizontal: 10, vertical: 5 },
                        },
                        stroke: { width: 3, colors: [isDark ? '#020617' : '#ffffff'] },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                            y: { formatter: (val) => val.toLocaleString() + ' equipos' }
                        },
                    });
                    this.chart.render();
                },
                destroy() { if(this.chart) this.chart.destroy(); }
             }"

        >
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 uppercase tracking-wide">
                Distribución General por Estatus
            </h3>
            <div x-ref="donutChart"></div>
        </div>

        {{-- Resumen lateral con barras horizontales --}}
        <div class="bg-white/80 dark:bg-slate-950/60
                    backdrop-blur-xl dark:backdrop-blur-2xl
                    border border-slate-200/80 dark:border-white/10
                    rounded-2xl p-6
                    shadow-md shadow-slate-900/10
                    dark:shadow-lg dark:shadow-slate-900/30"
             wire:key="hbar-{{ $chartKey }}"
             x-data="{
                chart: null,
                init() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const data = @js($graficas['donut']);
                    this.chart = new ApexCharts(this.$refs.hbarChart, {
                        chart: {
                            type: 'bar',
                            height: 420,
                            background: 'transparent',
                            fontFamily: 'Figtree, sans-serif',
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 800 },
                        },
                        series: [{ name: 'Equipos', data: data.series }],
                        xaxis: {
                            categories: data.labels,
                            labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px', fontWeight: 600 } },
                        },
                        yaxis: {
                            labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 6,
                                barHeight: '60%',
                                distributed: true,
                                dataLabels: { position: 'top' }
                            }
                        },
                        colors: data.colors,
                        dataLabels: {
                            enabled: true,
                            textAnchor: 'start',
                            offsetX: 5,
                            style: { fontSize: '11px', fontWeight: 700, colors: [isDark ? '#e2e8f0' : '#334155'] },
                            formatter: (val) => val.toLocaleString(),
                        },
                        grid: {
                            borderColor: isDark ? '#1e293b' : '#e2e8f0',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: true } },
                            yaxis: { lines: { show: false } },
                        },
                        legend: { show: false },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                            y: { formatter: (val) => val.toLocaleString() + ' equipos' }
                        },
                    });
                    this.chart.render();
                },
                destroy() { if(this.chart) this.chart.destroy(); }
             }"

        >
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 uppercase tracking-wide">
                Comparación por Fase
            </h3>
            <div x-ref="hbarChart"></div>
        </div>
    </div>
    @endif

    {{-- ═══ VISTA: Barras (Top Modelos) ═══ --}}
    @if($vistaActiva === 'barras')
    @php $graficas = $this->datosGraficas; $chartKey = md5(json_encode($graficas)); @endphp
    <div class="bg-white/80 dark:bg-slate-950/60
                backdrop-blur-xl dark:backdrop-blur-2xl
                border border-slate-200/80 dark:border-white/10
                rounded-2xl p-6
                shadow-md shadow-slate-900/10
                dark:shadow-lg dark:shadow-slate-900/30"
         wire:key="barras-{{ $chartKey }}"
         x-data="{
            chart: null,
            init() {
                const isDark = document.documentElement.classList.contains('dark');
                const data = @js($graficas['barras']);
                const series = data.series.map(s => ({ name: s.name, data: s.data }));
                const colors = data.series.map(s => s.color);

                this.chart = new ApexCharts(this.$refs.barrasChart, {
                    chart: {
                        type: 'bar',
                        height: Math.max(450, data.categorias.length * 45),
                        background: 'transparent',
                        fontFamily: 'Figtree, sans-serif',
                        stacked: false,
                        toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                        animations: { enabled: true, easing: 'easeinout', speed: 1000, animateGradually: { enabled: true, delay: 80 } },
                    },
                    series: series,
                    xaxis: {
                        categories: data.categorias,
                        labels: {
                            style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '10px', fontWeight: 600 },
                            rotate: -45, rotateAlways: data.categorias.length > 8,
                            trim: true, maxHeight: 100,
                        },
                        axisBorder: { color: isDark ? '#334155' : '#cbd5e1' },
                    },
                    yaxis: {
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '75%',
                            dataLabels: { position: 'top' },
                        }
                    },
                    colors: colors,
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: isDark ? '#1e293b' : '#e2e8f0',
                        strokeDashArray: 4,
                    },
                    legend: {
                        position: 'top', fontSize: '11px', fontWeight: 600,
                        labels: { colors: isDark ? '#cbd5e1' : '#475569' },
                        markers: { size: 6, offsetX: -3 },
                        itemMargin: { horizontal: 12, vertical: 5 },
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        shared: true, intersect: false,
                        y: { formatter: (val) => (val ?? 0).toLocaleString() }
                    },
                });
                this.chart.render();
            },
            destroy() { if(this.chart) this.chart.destroy(); }
         }"

    >
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 uppercase tracking-wide">
            Top 15 Modelos — Distribución por Estatus
        </h3>
        <div x-ref="barrasChart"></div>
    </div>
    @endif

    {{-- ═══ VISTA: Barras Apiladas (Por Marca) ═══ --}}
    @if($vistaActiva === 'apiladas')
    @php $graficas = $this->datosGraficas; $chartKey = md5(json_encode($graficas)); @endphp
    <div class="bg-white/80 dark:bg-slate-950/60
                backdrop-blur-xl dark:backdrop-blur-2xl
                border border-slate-200/80 dark:border-white/10
                rounded-2xl p-6
                shadow-md shadow-slate-900/10
                dark:shadow-lg dark:shadow-slate-900/30"
         wire:key="apiladas-{{ $chartKey }}"
         x-data="{
            chart: null,
            init() {
                const isDark = document.documentElement.classList.contains('dark');
                const data = @js($graficas['apiladas']);
                const series = data.series.map(s => ({ name: s.name, data: s.data }));
                const colors = data.series.map(s => s.color);

                this.chart = new ApexCharts(this.$refs.apiladasChart, {
                    chart: {
                        type: 'bar',
                        height: Math.max(500, data.categorias.length * 50),
                        background: 'transparent',
                        fontFamily: 'Figtree, sans-serif',
                        stacked: true,
                        stackType: 'normal',
                        toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                        animations: { enabled: true, easing: 'easeinout', speed: 1000, animateGradually: { enabled: true, delay: 100 } },
                    },
                    series: series,
                    xaxis: {
                        categories: data.categorias,
                        labels: {
                            style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px', fontWeight: 700 },
                        },
                        axisBorder: { color: isDark ? '#334155' : '#cbd5e1' },
                    },
                    yaxis: {
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                        title: { text: 'Equipos', style: { color: isDark ? '#94a3b8' : '#64748b', fontSize: '12px', fontWeight: 600 } },
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4,
                            barHeight: '65%',
                            dataLabels: { total: { enabled: true, style: { fontSize: '11px', fontWeight: 800, color: isDark ? '#e2e8f0' : '#1e293b' } } },
                        }
                    },
                    colors: colors,
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: isDark ? '#1e293b' : '#e2e8f0',
                        strokeDashArray: 4,
                        xaxis: { lines: { show: true } },
                        yaxis: { lines: { show: false } },
                    },
                    legend: {
                        position: 'top', fontSize: '11px', fontWeight: 600,
                        labels: { colors: isDark ? '#cbd5e1' : '#475569' },
                        markers: { size: 6, offsetX: -3 },
                        itemMargin: { horizontal: 12, vertical: 5 },
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        shared: true, intersect: false,
                        y: { formatter: (val) => (val ?? 0).toLocaleString() + ' equipos' }
                    },
                });
                this.chart.render();
            },
            destroy() { if(this.chart) this.chart.destroy(); }
         }"

    >
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 uppercase tracking-wide">
            Composición por Marca — Barras Apiladas
        </h3>
        <div x-ref="apiladasChart"></div>
    </div>
    @endif

</div>
</x-tb-background>
</div>