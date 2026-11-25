<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6 max-w-7xl mx-auto">

        <x-toast />

        {{-- Encabezado Superior con Topbar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <x-topbar
                title="Supervisión de Lote: {{ $lote->nombre_lote ?? ('Lote #' . $lote->id) }}"
                chip="Preparación · Supervisión de Taller"
                description="Vista de solo lectura para consultar los equipos del lote, sus series registradas y su estado operativo en taller."
            />
            <div>
                <a href="{{ route('preparacion.lotes') }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver a Lotes
                </a>
            </div>
        </div>

        {{-- Ficha Superior del Lote (Glassmorphism) --}}
        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800 gap-4">
                <div>
                    <span class="text-xs font-bold text-[#FF9521] uppercase tracking-wider">Ficha de Supervisión Gerencial</span>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ $nombre_lote }}
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        ID: #{{ $lote->id }}
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30">
                        {{ $lote->estatus }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Orden de Compra --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Orden de Compra</span>
                    <p class="text-sm font-black font-mono text-slate-800 dark:text-slate-200">
                        @if($lote->compraInventario)
                            {{ $lote->compraInventario->folio ?: ('OC-' . str_pad($lote->compra_inventario_id, 4, '0', STR_PAD_LEFT)) }}
                        @else
                            Ingreso Directo
                        @endif
                    </p>
                </div>

                {{-- Proveedor --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Proveedor</span>
                    <p class="text-sm font-black text-slate-800 dark:text-slate-200 truncate" title="{{ $proveedor_nombre }}">
                        {{ $proveedor_nombre }}
                    </p>
                </div>

                {{-- Fecha de Llegada --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Fecha de Llegada</span>
                    <p class="text-sm font-black text-slate-800 dark:text-slate-200">
                        {{ $fecha_llegada ? date('d/m/Y', strtotime($fecha_llegada)) : 'Sin fecha' }}
                    </p>
                </div>

                {{-- Resumen de Equipos --}}
                @php
                    $totalLaptops = collect($modelos)->sum('cantidad');
                    $totalConSerie = collect($modelos)->sum('con_serie');
                    $totalUsadas = collect($modelos)->sum('usados');
                    $totalPendientes = collect($modelos)->sum('pendientes');
                @endphp
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Equipos Totales</span>
                    <p class="text-sm font-black text-[#FF9521]">
                        {{ $totalLaptops }} unidades
                    </p>
                </div>
            </div>

            {{-- Métricas Rápidas del Lote --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">
                        ✓
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Con Serie Registrada:</span>
                        <strong class="text-emerald-400 text-sm font-bold">{{ $totalConSerie }} equipos</strong>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-[#FF9521] flex items-center justify-center font-bold text-xs">
                        ⚠️
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Lote Ciego (Serie Pendiente):</span>
                        <strong class="text-[#FF9521] text-sm font-bold">{{ $totalPendientes }} equipos</strong>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-xs">
                        🔧
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Asignados / En Taller:</span>
                        <strong class="text-blue-400 text-sm font-bold">{{ $totalUsadas }} equipos</strong>
                    </div>
                </div>
            </div>

        </div>

        {{-- TABLA EXCLUSIVA DE LAPTOPS Y MODELOS (SOLO LECTURA) --}}
        @if(count($modelos) > 0)
            <div class="space-y-6">
                @foreach($modelos as $index => $m)
                    <div wire:key="modelo-{{ $index }}"
                         class="rounded-3xl border border-slate-200/80 dark:border-white/10
                                bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 space-y-5">
                        
                        {{-- Cabecera del Modelo --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800 gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-black bg-sky-500/15 text-sky-400 border border-sky-500/30">EQUIPOS</span>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">
                                        {{ $m['marca'] }} {{ $m['modelo'] }}
                                    </h3>
                                </div>
                                <div class="flex flex-wrap gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    <span>Cantidad: <strong class="text-slate-800 dark:text-slate-200">{{ $m['cantidad'] }}</strong></span>
                                    <span>· Costo Unitario: <strong class="text-slate-800 dark:text-slate-200 font-mono">${{ number_format($m['valor_unitario'], 2) }}</strong></span>
                                    <span>· Con Serie: <strong class="text-emerald-500">{{ $m['con_serie'] }}</strong></span>
                                    @if($m['pendientes'] > 0)
                                        <span>· Serie Pendiente: <strong class="text-amber-500">{{ $m['pendientes'] }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(!empty($m['clasificacion_label']))
                                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-[#FF9521]/10 text-[#FF9521] border border-[#FF9521]/30">
                                        {{ $m['clasificacion_label'] }}
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        Solo Lectura
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Desglose de Unidades Físicas --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200/80 dark:border-slate-800 text-left text-xs uppercase text-slate-500 dark:text-slate-400">
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">Número de Serie</th>
                                        <th class="py-3 px-4">Técnico Asignado</th>
                                        <th class="py-3 px-4">Estatus Taller / Ciclo</th>
                                        <th class="py-3 px-4 text-right">Condición</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    @forelse($m['registros_existentes'] as $rIdx => $reg)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-3 px-4 text-center font-mono text-xs text-slate-400">
                                                {{ $rIdx + 1 }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @if(!empty($reg['numero_serie']))
                                                    <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700">
                                                        {{ $reg['numero_serie'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-[#FF9521] border border-amber-500/20">
                                                        ⚠️ Serie Pendiente (Lote Ciego)
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                @if(!empty($reg['tecnico']) && $reg['tecnico'] !== 'Sin asignar')
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-[10px]">
                                                            {{ strtoupper(substr($reg['tecnico'], 0, 1)) }}
                                                        </div>
                                                        <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $reg['tecnico'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                                        Sin Asignar
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                @php
                                                    $estatusArea = $reg['estatus_area'] ?? 'SIN_ASIGNAR';
                                                    $color = match($estatusArea) {
                                                        'SIN_ASIGNAR' => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                                        'ASIGNADO', 'EN_PROCESO' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                                        'EN_CALIDAD' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                                        'FINALIZADO', 'DISPONIBLE_VENTA' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                                        default => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                                    };
                                                @endphp
                                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-[11px] font-bold border {{ $color }}">
                                                    {{ $reg['area_label'] ?? $estatusArea }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                @if($reg['en_proceso'])
                                                    <span class="text-[11px] font-bold text-blue-400 px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20">
                                                        🔧 En Taller / Proceso
                                                    </span>
                                                @else
                                                    <span class="text-[11px] font-bold text-emerald-500 px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20">
                                                        ✓ Disponible (Sin Asignar)
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                                No hay unidades físicas registradas aún para este modelo.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                        bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <h4 class="text-base font-bold text-slate-300">Este lote no contiene equipos ni laptops</h4>
                <p class="text-xs text-slate-500 mt-1">Esta vista está especializada exclusivamente en la supervisión de equipos.</p>
            </div>
        @endif

    </div>
</x-tb-background>
</div>
