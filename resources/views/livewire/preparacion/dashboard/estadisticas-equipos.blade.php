<div>
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

    {{-- Tabla Concentrada por Marca y Modelo --}}
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
</div>
</x-tb-background>
</div>