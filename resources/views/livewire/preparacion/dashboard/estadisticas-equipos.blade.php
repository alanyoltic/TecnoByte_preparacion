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

        <div class="flex flex-col sm:flex-row w-full xl:w-auto gap-3 flex-wrap">
            {{-- Orden --}}
            <select wire:model.live="orden"
                    class="w-full sm:w-40 rounded-2xl
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
                    class="w-full sm:w-40 rounded-2xl
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
                    class="w-full sm:w-48 rounded-2xl
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

            {{-- Búsqueda Texto --}}
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar modelo o marca..."
                       class="w-full pl-10 pr-3 py-2 rounded-2xl
                              bg-white/80 dark:bg-slate-900/60
                              border border-white/60 dark:border-slate-700/70
                              text-sm text-slate-900 dark:text-slate-100
                              placeholder:text-slate-400 dark:placeholder:text-slate-500
                              shadow-md shadow-slate-900/10 dark:shadow-xl dark:shadow-slate-950/60
                              focus:outline-none focus:ring-2 focus:ring-blue-500/70 focus:border-blue-500/70
                              backdrop-blur-xl">
            </div>
        </div>
    </div>

    {{-- Tarjetas de Resumen Global (Detallado) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-4">

        {{-- Total Recibido --}}
        <div
            class="rounded-2xl
                   bg-white/80 dark:bg-slate-950/60
                   border border-slate-200/80 dark:border-white/10
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-slate-900/10
                   dark:shadow-lg dark:shadow-slate-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-sky-500/20
                   dark:hover:shadow-2xl dark:hover:shadow-sky-500/25
                   hover:border-sky-400/70 dark:hover:border-sky-300/50"
        >
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                Total Recibido
            </p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                {{ number_format($totales['general']) }}
            </p>
        </div>

        {{-- Disponibles (en lote, sin serie aún) --}}
        <div
            class="rounded-2xl
                   bg-slate-50/90 dark:bg-slate-900/40
                   border border-slate-200/80 dark:border-slate-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-slate-900/10
                   dark:shadow-lg dark:shadow-slate-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-slate-400/30
                   dark:hover:shadow-2xl dark:hover:shadow-slate-400/25
                   hover:border-slate-400/70"
        >
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                Disponibles
            </p>
            <p class="mt-2 text-2xl font-bold text-slate-600 dark:text-slate-300">
                {{ number_format($totales['sin_registrar']) }}
            </p>
        </div>

        {{-- Sin Asignar --}}
        <div
            class="rounded-2xl
                   bg-violet-50/90 dark:bg-violet-950/40
                   border border-violet-200/80 dark:border-violet-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-violet-900/10
                   dark:shadow-lg dark:shadow-violet-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-violet-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-violet-400/50
                   hover:border-violet-400/70"
        >
            <p class="text-xs font-semibold text-violet-700 dark:text-violet-200 uppercase tracking-wide">
                Sin Asignar
            </p>
            <p class="mt-2 text-2xl font-bold text-violet-800 dark:text-violet-100">
                {{ number_format($totales['sin_asignar']) }}
            </p>
        </div>

        {{-- Asignados --}}
        <div
            class="rounded-2xl
                   bg-blue-50/90 dark:bg-blue-950/40
                   border border-blue-200/80 dark:border-blue-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-blue-900/10
                   dark:shadow-lg dark:shadow-blue-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-blue-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-blue-400/50
                   hover:border-blue-400/70"
        >
            <p class="text-xs font-semibold text-blue-700 dark:text-blue-200 uppercase tracking-wide">
                Asignados
            </p>
            <p class="mt-2 text-2xl font-bold text-blue-800 dark:text-blue-100">
                {{ number_format($totales['asignado']) }}
            </p>
        </div>

        {{-- En Proceso --}}
        <div
            class="rounded-2xl
                   bg-orange-50/90 dark:bg-orange-950/40
                   border border-orange-200/80 dark:border-orange-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-orange-900/10
                   dark:shadow-lg dark:shadow-orange-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-orange-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-orange-400/50
                   hover:border-orange-400/70"
        >
            <p class="text-xs font-semibold text-orange-700 dark:text-orange-200 uppercase tracking-wide">
                En Proceso
            </p>
            <p class="mt-2 text-2xl font-bold text-orange-800 dark:text-orange-100">
                {{ number_format($totales['proceso']) }}
            </p>
        </div>

        {{-- En Calidad --}}
        <div
            class="rounded-2xl
                   bg-purple-50/90 dark:bg-purple-950/40
                   border border-purple-200/80 dark:border-purple-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-purple-900/10
                   dark:shadow-lg dark:shadow-purple-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-purple-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-purple-400/50
                   hover:border-purple-400/70"
        >
            <p class="text-xs font-semibold text-purple-700 dark:text-purple-200 uppercase tracking-wide">
                En Calidad
            </p>
            <p class="mt-2 text-2xl font-bold text-purple-800 dark:text-purple-100">
                {{ number_format($totales['calidad']) }}
            </p>
        </div>

        {{-- Falta Pieza --}}
        <div
            class="rounded-2xl
                   bg-amber-50/90 dark:bg-amber-950/40
                   border border-amber-200/80 dark:border-amber-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-amber-900/10
                   dark:shadow-lg dark:shadow-amber-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-amber-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-amber-400/50
                   hover:border-amber-400/70"
        >
            <p class="text-xs font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                Falta Pieza
            </p>
            <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                {{ number_format($totales['pieza']) }}
            </p>
        </div>

        {{-- Garantías --}}
        <div
            class="rounded-2xl
                   bg-red-50/90 dark:bg-red-950/40
                   border border-red-200/80 dark:border-red-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-red-900/10
                   dark:shadow-lg dark:shadow-red-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-red-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-red-400/50
                   hover:border-red-400/70"
        >
            <p class="text-xs font-semibold text-red-700 dark:text-red-200 uppercase tracking-wide">
                Garantías
            </p>
            <p class="mt-2 text-2xl font-bold text-red-800 dark:text-red-100">
                {{ number_format($totales['garantia']) }}
            </p>
        </div>

        {{-- Desarme --}}
        <div
            class="rounded-2xl
                   bg-rose-50/90 dark:bg-rose-950/40
                   border border-rose-200/80 dark:border-rose-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-rose-900/10
                   dark:shadow-lg dark:shadow-rose-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-rose-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-rose-400/50
                   hover:border-rose-400/70"
        >
            <p class="text-xs font-semibold text-rose-700 dark:text-rose-200 uppercase tracking-wide">
                Desarme
            </p>
            <p class="mt-2 text-2xl font-bold text-rose-800 dark:text-rose-100">
                {{ number_format($totales['desarme']) }}
            </p>
        </div>

        {{-- Aprobados --}}
        <div
            class="rounded-2xl
                   bg-emerald-50/90 dark:bg-emerald-950/40
                   border border-emerald-200/80 dark:border-emerald-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-emerald-900/10
                   dark:shadow-lg dark:shadow-emerald-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-emerald-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-emerald-400/50
                   hover:border-emerald-400/70"
        >
            <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                Aprobados
            </p>
            <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                {{ number_format($totales['finalizado']) }}
            </p>
        </div>

        {{-- Transferidos --}}
        <div
            class="rounded-2xl
                   bg-teal-50/90 dark:bg-teal-950/40
                   border border-teal-200/80 dark:border-teal-500/70
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   px-4 py-3
                   shadow-md shadow-teal-900/10
                   dark:shadow-lg dark:shadow-teal-900/30
                   transition-all duration-300
                   hover:-translate-y-1
                   hover:shadow-lg hover:shadow-teal-500/40
                   dark:hover:shadow-2xl dark:hover:shadow-teal-400/50
                   hover:border-teal-400/70"
        >
            <p class="text-xs font-semibold text-teal-700 dark:text-teal-200 uppercase tracking-wide">
                Transferidos
            </p>
            <p class="mt-2 text-2xl font-bold text-teal-800 dark:text-teal-100">
                {{ number_format($totales['transferido']) }}
            </p>
        </div>

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
                        <th class="px-3 py-3 text-[0.65rem] font-black uppercase tracking-wider text-center text-slate-400 bg-slate-100/50 dark:bg-slate-800" title="Equipos recibidos en lote sin número de serie escaneado aún (físicos en bodega)">Disponibles</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-slate-500">Sin Asig.</th>
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
                                    {{ number_format($datosMarca['total_marca_sin_reg']) }}
                                </span>
                            </td>
                            <td colspan="9"></td>
                            <td class="px-5 py-2 text-right border-l border-slate-200 dark:border-slate-700">
                                <span class="px-2 py-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-[0.7rem] font-black text-slate-700 dark:text-slate-300 shadow-sm" title="Total de equipos con serie física (excluye aire)">
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

                                {{-- Sin Registrar (Aire) --}}
                                <td class="px-3 py-2.5 text-center bg-slate-100/30 dark:bg-slate-800/50">
                                    @if($m->sin_registrar > 0)
                                        <span class="text-xs font-black text-slate-500 dark:text-slate-400">{{ $m->sin_registrar }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300/50 dark:text-slate-700/50">-</span>
                                    @endif
                                </td>

                                {{-- Sin Asignar --}}
                                <td class="px-3 py-2.5 text-center">
                                    @if($m->c_sin_asignar > 0)
                                        <span class="text-xs font-medium text-slate-600">{{ $m->c_sin_asignar }}</span>
                                    @else
                                        <span class="text-[0.65rem] text-slate-300 dark:text-slate-700">-</span>
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
                            <td colspan="12" class="px-6 py-16 text-center">
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