<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">

        <x-topbar
            title="Gerencia General"
            chip="CEO > Panel Estratégico"
            description="Visión ejecutiva del desempeño comercial y operativo."
        />

        {{-- CONTROLES DE FILTRADO --}}
        <div class="flex flex-col sm:flex-row justify-end items-end sm:items-center gap-4 bg-white/40 dark:bg-slate-950/40 p-3 rounded-2xl border border-slate-200/60 dark:border-white/5 backdrop-blur-md">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label for="filtroSucursal" class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider shrink-0">Sucursal</label>
                <select id="filtroSucursal" wire:model.live="filtroSucursal" class="w-full sm:w-auto text-sm font-medium bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-white/10 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-slate-700 dark:text-slate-200 shadow-sm transition-all cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                    <option value="todas">Todas las sucursales</option>
                    @foreach($sucursalesSelect as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="hidden sm:block w-px h-6 bg-slate-200 dark:bg-slate-800"></div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label for="filtroMes" class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider shrink-0">Mes</label>
                <select id="filtroMes" wire:model.live="filtroMes" class="w-full sm:w-auto text-sm font-medium bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-white/10 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-slate-700 dark:text-slate-200 shadow-sm transition-all cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                    @php
                        $mesesNombres = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        $mesActual = \Carbon\Carbon::now()->month;
                    @endphp
                    @foreach($mesesNombres as $num => $nombre)
                        @if($num <= $mesActual)
                            <option value="{{ $num }}">{{ $nombre }} {{ $num === $mesActual ? '(Actual)' : '' }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        {{-- CONTENEDOR REACTIVO (Se difumina mientras carga) --}}
        <div wire:loading.class="opacity-40 blur-[2px] scale-[0.99]" class="transition-all duration-300 ease-out space-y-6 origin-top">
            
            {{-- ROW 1: 6 KPI CARDS --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6">
            
            {{-- KPI 1: Venta Total --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-4 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2">
                    <div class="p-1.5 bg-emerald-500/10 text-emerald-500 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Venta Total</p>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate">${{ number_format($ventaTotal, 2) }}</h3>
                    <p class="text-[0.65rem] text-emerald-500 font-bold mt-1 truncate">↑ Acumulado Mensual</p>
                </div>
            </div>

            {{-- KPI 2: Equipos (Monetario) --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-4 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2">
                    <div class="p-1.5 bg-blue-500/10 text-blue-500 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Ingreso Equipos</p>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate">${{ number_format($ventaEquiposMonetario, 2) }}</h3>
                    <p class="text-[0.65rem] text-blue-500 font-bold mt-1 truncate">Monetario</p>
                </div>
            </div>

            {{-- KPI 3: Equipos (Unidades) --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-4 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2">
                    <div class="p-1.5 bg-indigo-500/10 text-indigo-500 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <p class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Volumen Equipos</p>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate">{{ number_format($unidadesEquipos) }}</h3>
                    <p class="text-[0.65rem] text-indigo-500 font-bold mt-1 truncate">Unidades Vendidas</p>
                </div>
            </div>

            {{-- KPI 4: Accesorios --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-4 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2">
                    <div class="p-1.5 bg-purple-500/10 text-purple-500 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Accesorios</p>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate">${{ number_format($ventaAccesorios, 2) }}</h3>
                    <p class="text-[0.65rem] text-purple-500 font-bold mt-1 truncate">Monetario</p>
                </div>
            </div>

            {{-- KPI 5: Garantias --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-4 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2">
                    <div class="p-1.5 bg-orange-500/10 text-orange-500 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <p class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Garantías Ext.</p>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate">{{ number_format($garantiasUnidades) }}</h3>
                    <p class="text-[0.65rem] text-orange-500 font-bold mt-1 truncate">Unidades</p>
                </div>
            </div>

            {{-- KPI 6: Ticket Promedio --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-4 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2">
                    <div class="p-1.5 bg-cyan-500/10 text-cyan-500 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                    <p class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Ticket Promedio</p>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate">${{ number_format($ticketPromedio, 2) }}</h3>
                    <p class="text-[0.65rem] text-cyan-500 font-bold mt-1 truncate">Global</p>
                </div>
            </div>
        </div>

        {{-- ROW 2: PROYECCIONES Y SUCURSALES --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Proyecciones --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-5 sm:p-6 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col">
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide mb-6">Proyecciones de Venta</h3>
                
                <div class="space-y-4 flex-1 flex flex-col justify-center">
                    <div class="flex flex-col border-b border-slate-200/50 dark:border-slate-800/50 pb-3">
                        <span class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Cierre Diario Estimado</span>
                        <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100">${{ number_format($proyeccionDiaria, 2) }}</span>
                    </div>
                    <div class="flex flex-col border-b border-slate-200/50 dark:border-slate-800/50 pb-3">
                        <span class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Cierre Semanal Estimado</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400">${{ number_format($proyeccionSemanal, 2) }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[0.65rem] sm:text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Proyección Fin de Mes</span>
                        <span class="text-3xl sm:text-4xl font-black text-emerald-600 dark:text-emerald-400">${{ number_format($proyeccionMensual, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Sucursales --}}
            <div class="lg:col-span-2 rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-5 sm:p-6 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">Rendimiento por Canal / Sucursal</h3>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ count($sucursales) }} sedes registradas</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 flex-1 items-center">
                    @php $coloresSucursales = ['text-blue-500', 'text-indigo-500', 'text-emerald-500', 'text-amber-500', 'text-purple-500', 'text-cyan-500', 'text-rose-500']; @endphp
                    @forelse($sucursales as $index => $suc)
                    <div class="flex flex-col items-center justify-center p-3 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200/50 dark:border-white/5">
                        <!-- SVG Circular Progress (Responsive Size) -->
                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 flex items-center justify-center mb-2">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-200 dark:stroke-slate-800" stroke-width="2.5"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-current {{ $coloresSucursales[$index % count($coloresSucursales)] }}" stroke-width="2.5" stroke-dasharray="100" stroke-dashoffset="{{ 100 - min(100, max(0, $suc['porcentaje'])) }}" stroke-linecap="round"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center flex-col">
                                <span class="text-base sm:text-lg font-black text-slate-800 dark:text-white">{{ $suc['porcentaje'] }}%</span>
                            </div>
                        </div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 text-center truncate w-full" title="{{ $suc['nombre'] }}">{{ $suc['nombre'] }}</h4>
                        <p class="text-[0.65rem] font-bold {{ str_starts_with($suc['tendencia'], '+') ? 'text-emerald-500' : 'text-slate-400' }} mt-0.5">{{ $suc['tendencia'] }}</p>
                        <p class="text-sm sm:text-base font-black text-slate-900 dark:text-white mt-1 truncate w-full text-center">${{ number_format($suc['ventas'], 0) }}</p>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-8 text-slate-400 text-xs">
                        No hay sucursales registradas o con actividad en este periodo.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ROW 3: DESGLOSE, SEGMENTOS Y FISCAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Líneas de Negocio (DONUT CHART) - Toma 5 columnas --}}
            <div class="lg:col-span-5 rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-5 sm:p-6 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col min-h-[22rem] xl:min-h-[24rem]">
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide mb-6">Ventas por Línea de Negocio</h3>
                
                <div class="flex flex-row flex-1 items-center justify-between" x-data="{ hovered: null, hoverPct: null }">
                    {{-- El Donut --}}
                    <div class="relative w-44 h-44 sm:w-56 sm:h-56 shrink-0 mx-auto lg:mx-0">
                        <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                            @php 
                                $bgColors = ['text-blue-500', 'text-indigo-500', 'text-purple-500', 'text-pink-500', 'text-orange-500', 'text-emerald-500'];
                                $bgSolid = ['bg-blue-500', 'bg-indigo-500', 'bg-purple-500', 'bg-pink-500', 'bg-orange-500', 'bg-emerald-500'];
                                $cumulativeOffset = 0;
                                $i = 0;
                            @endphp
                            
                            {{-- Círculo base gris por si no llena el 100% (aunque siempre debería) --}}
                            <circle cx="18" cy="18" r="15.915" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="4"></circle>
                            
                            @foreach($desgloseTipos as $nombre => $valor)
                                @php 
                                    $pct = $ventaTotal > 0 ? ($valor / $ventaTotal) * 100 : 0;
                                    $colorText = $bgColors[$i % count($bgColors)];
                                @endphp
                                @if($pct > 0)
                                    <circle cx="18" cy="18" r="15.915" fill="none" 
                                            class="stroke-current {{ $colorText }} transition-all duration-1000 ease-out cursor-pointer" 
                                            :stroke-width="hovered === '{{ $nombre }}' ? 6 : 4"
                                            :class="(hovered && hovered !== '{{ $nombre }}') ? 'opacity-25' : 'opacity-100'"
                                            x-data="{ dash: '0 100' }"
                                            x-init="setTimeout(() => dash = '{{ $pct }} {{ 100 - $pct }}', 300)"
                                            :stroke-dasharray="dash"
                                            stroke-dashoffset="{{ 100 - $cumulativeOffset }}"
                                            @mouseenter="hovered = '{{ $nombre }}'; hoverPct = '{{ number_format($pct, 0) }}%'"
                                            @mouseleave="hovered = null"
                                    ></circle>
                                    @php $cumulativeOffset += $pct; @endphp
                                @endif
                                @php $i++; @endphp
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none transition-all duration-300">
                            {{-- Default Center --}}
                            <div x-show="!hovered" x-transition.opacity class="flex flex-col items-center">
                                <span class="text-sm sm:text-base font-bold text-slate-500 dark:text-slate-400">Total</span>
                                <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white truncate max-w-[80%]">${{ number_format($ventaTotal, 0) }}</span>
                            </div>
                            
                            {{-- Hover Center --}}
                            <div x-show="hovered" style="display: none;" x-transition.opacity class="flex flex-col items-center">
                                <span class="text-[0.65rem] sm:text-xs font-bold text-slate-500 dark:text-slate-400 text-center px-2 uppercase tracking-wide" x-text="hovered"></span>
                                <span class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white truncate" x-text="hoverPct"></span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Leyenda --}}
                    <div class="flex-1 ml-6 sm:ml-10 lg:ml-12 flex flex-col justify-center space-y-2">
                        @php $i = 0; @endphp
                        @foreach($desgloseTipos as $nombre => $valor)
                            @php 
                                $pct = $ventaTotal > 0 ? ($valor / $ventaTotal) * 100 : 0;
                                $colorSolid = $bgSolid[$i % count($bgSolid)];
                            @endphp
                            <div class="flex items-center justify-between text-xs sm:text-sm transition-all duration-300 rounded-lg -mx-2 px-2 py-1.5 cursor-pointer"
                                 :class="hovered === '{{ $nombre }}' ? 'bg-slate-100/50 dark:bg-slate-800/50 scale-105' : ((hovered && hovered !== '{{ $nombre }}') ? 'opacity-30' : '')"
                                 @mouseenter="hovered = '{{ $nombre }}'; hoverPct = '{{ number_format($pct, 0) }}%'"
                                 @mouseleave="hovered = null">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full {{ $colorSolid }} shrink-0"></span>
                                    <span class="font-bold text-slate-600 dark:text-slate-300 truncate">{{ $nombre }}</span>
                                </div>
                                <div class="flex items-center gap-3 sm:gap-4 ml-2 shrink-0">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium tracking-wide">${{ number_format($valor, 0) }}</span>
                                    <span class="font-black text-slate-800 dark:text-white w-8 text-right">{{ number_format($pct, 0) }}%</span>
                                </div>
                            </div>
                            @php $i++; @endphp
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Segmentos Económicos - Toma 4 columnas --}}
            <div class="lg:col-span-4 rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-5 sm:p-6 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col min-h-[22rem] xl:min-h-[24rem]">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">Desempeño por Gamas</h3>
                    <span class="px-2.5 py-1.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded shrink-0">Líder: {{ $mejorSegmento }}</span>
                </div>
                
                <div class="flex-1 flex flex-col justify-center space-y-8">
                    @php
                        $maxSeg = max($segmentos) ?: 1;
                        $gamaColors = ['Económico' => 'bg-emerald-400', 'Medio' => 'bg-blue-400', 'Premium' => 'bg-purple-400'];
                    @endphp
                    @foreach($segmentos as $seg => $val)
                        @php $w = ($val / $maxSeg) * 100; @endphp
                        <div>
                            <div class="flex justify-between text-xs sm:text-sm font-bold mb-3">
                                <span class="text-slate-600 dark:text-slate-300">Gama {{ $seg }}</span>
                                <span class="text-slate-800 dark:text-white">${{ number_format($val, 0) }}</span>
                            </div>
                            <div class="w-full h-3.5 bg-slate-200/50 dark:bg-slate-800/80 rounded-full overflow-hidden">
                                <div class="h-3.5 rounded-full {{ $gamaColors[$seg] }} transition-all duration-1000 ease-out" 
                                     x-data="{ width: '0%' }" 
                                     x-init="setTimeout(() => width = '{{ $w }}%', 400)" 
                                     :style="`width: ${width}`"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Facturación vs Remisión (UNIDADES, Gauge reducido) - Toma 3 columnas --}}
            <div class="lg:col-span-3 rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl p-5 sm:p-6 shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 flex flex-col items-center justify-between min-h-[22rem] xl:min-h-[24rem]">
                <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide w-full text-center mb-4">Tickets por Tipo</h3>
                
                @php 
                    $totalTickets = $facturasUnidades + $remisionesUnidades;
                    $pctF = $totalTickets > 0 ? ($facturasUnidades / $totalTickets) * 100 : 0; 
                @endphp
                
                <div class="relative w-40 h-20 sm:w-48 sm:h-24 xl:w-52 xl:h-26 overflow-hidden my-auto shrink-0">
                    <svg viewBox="0 0 100 50" class="w-full h-full transform origin-bottom">
                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" class="stroke-slate-200 dark:stroke-slate-800" stroke-width="14" stroke-linecap="round"></path>
                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" class="stroke-cyan-500 transition-all duration-1000 ease-out" 
                              stroke-width="14" stroke-linecap="round" stroke-dasharray="125.6" 
                              x-data="{ offset: 125.6 }"
                              x-init="setTimeout(() => offset = {{ 125.6 - (125.6 * ($pctF / 100)) }}, 500)"
                              :stroke-dashoffset="offset"></path>
                    </svg>
                    <div class="absolute bottom-0 w-full text-center translate-y-2">
                        <span class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-white">{{ number_format($pctF, 0) }}%</span>
                    </div>
                </div>
                
                <div class="w-full flex justify-between mt-6 pt-5 border-t border-slate-200/80 dark:border-slate-800/80">
                    <div class="text-center w-1/2 border-r border-slate-200/50 dark:border-slate-800/50">
                        <p class="text-[0.65rem] sm:text-[0.7rem] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider mb-1">Facturas</p>
                        <p class="text-lg sm:text-xl font-black text-cyan-500">{{ $facturasUnidades }}<span class="text-xs font-normal text-slate-400 ml-1">u</span></p>
                    </div>
                    <div class="text-center w-1/2">
                        <p class="text-[0.65rem] sm:text-[0.7rem] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider mb-1">Remisiones</p>
                        <p class="text-lg sm:text-xl font-black text-slate-700 dark:text-slate-300">{{ $remisionesUnidades }}<span class="text-xs font-normal text-slate-400 ml-1">u</span></p>
                    </div>
                </div>
            </div>

        </div>

        </div> {{-- Fin contenedor reactivo --}}

    </div>
</x-tb-background>
</div>
