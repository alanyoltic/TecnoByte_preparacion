<div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">
    
    <x-topbar
        title="Estadísticas Detalladas de Inventario"
        chip="Dashboard · Concentrado por Modelos y Fases"
    />

    {{-- Filtros Avanzados --}}
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-4 shadow-sm flex flex-col xl:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-2 w-full xl:w-auto">
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Filtros:</span>
        </div>
        
        <div class="flex flex-col sm:flex-row w-full xl:w-auto gap-3 flex-wrap">
            {{-- Orden --}}
            <select wire:model.live="orden"
                    class="w-full sm:w-40 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:ring-2 focus:ring-[#FF9521] outline-none">
                <option value="total_desc">Mayor cantidad</option>
                <option value="marca_asc">Alfabético</option>
            </select>

            {{-- Tipo de Equipo --}}
            <select wire:model.live="filtroTipo"
                    class="w-full sm:w-40 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:ring-2 focus:ring-[#FF9521] outline-none">
                <option value="">Todos los Tipos</option>
                @foreach($listaTipos as $tipo)
                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                @endforeach
            </select>

            {{-- Marca --}}
            <select wire:model.live="filtroMarca"
                    class="w-full sm:w-48 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:ring-2 focus:ring-[#FF9521] outline-none">
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
                       class="w-full pl-10 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:ring-2 focus:ring-[#FF9521] outline-none">
            </div>
        </div>
    </div>

    {{-- Tarjetas de Resumen Global (Detallado) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        
        {{-- Fila 1: General y Operativos --}}
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800/60 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ number_format($totales['general']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-slate-500 mt-1">Total Inv.</span>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-slate-600 dark:text-slate-300">{{ number_format($totales['espera']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-slate-500 mt-1">En Espera</span>
        </div>

        <div class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totales['asignado']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-blue-500/80 mt-1">Asignados</span>
        </div>

        <div class="bg-[#FF9521]/10 border border-[#FF9521]/20 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-[#FF9521]">{{ number_format($totales['proceso']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-[#FF9521]/80 mt-1">En Proceso</span>
        </div>

        <div class="bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($totales['calidad']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-purple-500/80 mt-1">En Calidad</span>
        </div>

        {{-- Fila 2: Bloqueos y Salidas --}}
        <div class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($totales['pieza']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-amber-500/80 mt-1">Falta Pieza</span>
        </div>

        <div class="bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($totales['garantia']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-red-500/80 mt-1">Garantías</span>
        </div>

        <div class="bg-rose-50/50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($totales['desarme']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-rose-500/80 mt-1">Desarme</span>
        </div>

        <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($totales['finalizado']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-emerald-500/80 mt-1">Finalizados</span>
        </div>

        <div class="bg-teal-50/50 dark:bg-teal-900/10 border border-teal-100 dark:border-teal-800/50 rounded-2xl p-3 shadow-sm flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($totales['transferido']) }}</span>
            <span class="text-[0.60rem] font-bold uppercase tracking-wider text-teal-500/80 mt-1">Transferidos</span>
        </div>
    </div>

    {{-- Tabla Concentrada por Marca y Modelo --}}
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800/60 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-5 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-slate-500 w-[22%]">Modelo del Equipo</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-slate-500">Espera</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-blue-500">Asig.</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-[#FF9521]">Proceso</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-amber-500">Pieza</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-red-500">Gtía.</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-rose-500">Desarme</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-purple-500">Calidad</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-emerald-500">Fin.</th>
                        <th class="px-3 py-3 text-[0.65rem] font-bold uppercase tracking-wider text-center text-teal-500">Transf.</th>
                        <th class="px-5 py-3 text-[0.7rem] font-bold uppercase tracking-wider text-right text-slate-800 dark:text-slate-200 border-l border-slate-200 dark:border-slate-700">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($estadisticas as $marca => $datosMarca)
                        
                        {{-- Cabecera de la Marca --}}
                        <tr class="bg-slate-50/40 dark:bg-slate-800/40 border-t-2 border-t-slate-200 dark:border-t-slate-700">
                            <td colspan="10" class="px-5 py-2">
                                <span class="text-[0.8rem] font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">{{ $marca }}</span>
                            </td>
                            <td class="px-5 py-2 text-right border-l border-slate-200 dark:border-slate-700">
                                <span class="px-2 py-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-[0.7rem] font-black text-slate-700 dark:text-slate-300 shadow-sm">
                                    {{ number_format($datosMarca['total_marca']) }}
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
                                
                                {{-- Espera --}}
                                <td class="px-3 py-2.5 text-center">
                                    @if($m->c_espera > 0)
                                        <span class="text-xs font-medium text-slate-500">{{ $m->c_espera }}</span>
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