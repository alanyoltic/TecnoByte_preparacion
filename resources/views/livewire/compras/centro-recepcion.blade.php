<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-24 space-y-6 max-w-7xl mx-auto" x-data>
        
        {{-- Encabezado Superior con Topbar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <x-topbar
                title="Recepción de Mercancía: {{ $compra->folio ?: 'OC-' . $compra->id }}"
                chip="Operación > Ingreso a Inventario"
                description="Verificación física, escaneo de series y alta definitiva en el almacén de destino."
            />
            <div>
                <a href="{{ route($compra->area === 'VENTAS' ? 'ventas.compras' : 'preparacion.compras') }}"
                   class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver a Órdenes
                </a>
            </div>
        </div>

        {{-- Tarjetas de Información Ejecutiva (4 Columnas) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            
            {{-- Proveedor --}}
            <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-xl">
                <div class="flex items-center gap-3 mb-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Proveedor</h3>
                        <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate max-w-[150px]" title="{{ optional($compra->proveedor)->nombre_empresa }}">
                            {{ optional($compra->proveedor)->nombre_empresa ?? 'Sin proveedor' }}
                        </p>
                    </div>
                </div>
                <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <p><span class="text-slate-400 font-medium">Contacto:</span> {{ optional($compra->proveedor)->contacto_principal ?: 'No especificado' }}</p>
                    <p class="truncate" title="{{ optional($compra->proveedor)->email_contacto }}"><span class="text-slate-400 font-medium">Email:</span> {{ optional($compra->proveedor)->email_contacto ?: 'No registrado' }}</p>
                </div>
            </div>

            {{-- Solicitante y Área --}}
            <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-xl">
                <div class="flex items-center gap-3 mb-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="p-2.5 rounded-xl bg-amber-500/10 text-[#FF9521]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Solicitante</h3>
                        <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate max-w-[150px]" title="{{ optional($compra->registradoPor)->name }}">
                            {{ optional($compra->registradoPor)->name ?? 'No especificado' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="text-slate-400 font-medium">Área Destino:</span>
                    <span class="px-2.5 py-0.5 rounded-md font-black tracking-wider text-[11px] {{ $compra->area === 'VENTAS' ? 'bg-amber-500/15 text-[#FF9521] border border-[#FF9521]/40' : 'bg-blue-500/15 text-blue-400 border border-blue-500/40' }}">
                        {{ $compra->area }}
                    </span>
                </div>
            </div>

            {{-- Almacén Destino --}}
            <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-xl">
                <div class="flex items-center gap-3 mb-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Destino Físico</h3>
                        @php
                            $almacenes = $compra->items->map(fn($i) => optional($i->almacen)->nombre)->filter()->unique();
                        @endphp
                        <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate max-w-[150px]" title="{{ $almacenes->join(', ') }}">
                            {{ $almacenes->first() ?? 'Almacén General' }}
                        </p>
                    </div>
                </div>
                <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400">
                    <p><span class="text-slate-400 font-medium">Ingreso:</span> Inmediato al confirmar</p>
                    <p><span class="text-slate-400 font-medium">Total Items:</span> {{ $compra->items->count() }} renglones</p>
                </div>
            </div>

            {{-- Carga y Contenido --}}
            <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xl backdrop-blur-xl">
                <div class="flex items-center gap-3 mb-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unidades</h3>
                        <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                            {{ $compra->items->sum('cantidad') }} totales
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5 text-[11px] font-bold">
                    @if($compra->items->whereNotNull('catalogo_equipo_id')->sum('cantidad') > 0)
                        <span class="px-2 py-0.5 rounded-md bg-sky-500/15 text-sky-400 border border-sky-500/30">
                            {{ $compra->items->whereNotNull('catalogo_equipo_id')->sum('cantidad') }} Equipos
                        </span>
                    @endif
                    @if($compra->items->whereNotNull('catalogo_pieza_id')->sum('cantidad') > 0)
                        <span class="px-2 py-0.5 rounded-md bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">
                            {{ $compra->items->whereNotNull('catalogo_pieza_id')->sum('cantidad') }} Piezas
                        </span>
                    @endif
                    @if($compra->items->whereNotNull('producto_id')->sum('cantidad') > 0)
                        <span class="px-2 py-0.5 rounded-md bg-teal-500/15 text-teal-400 border border-teal-500/30">
                            {{ $compra->items->whereNotNull('producto_id')->sum('cantidad') }} Accesorios
                        </span>
                    @endif
                    @if($compra->items->whereNotNull('consumible_id')->sum('cantidad') > 0)
                        <span class="px-2 py-0.5 rounded-md bg-pink-500/15 text-pink-400 border border-pink-500/30">
                            {{ $compra->items->whereNotNull('consumible_id')->sum('cantidad') }} Consumibles
                        </span>
                    @endif
                    @if($compra->items->filter(fn($i) => $i->esCargador())->sum('cantidad') > 0)
                        <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-[#FF9521] border border-[#FF9521]/30">
                            {{ $compra->items->filter(fn($i) => $i->esCargador())->sum('cantidad') }} Cargadores
                        </span>
                    @endif
                </div>
            </div>

        </div>

        {{-- Barra de Pestañas (Tabs) --}}
        <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl backdrop-blur-xl p-1.5 flex flex-wrap gap-2">
            
            {{-- Pestaña Resumen --}}
            <button wire:click="setTab('resumen')"
                    class="px-5 py-2.5 rounded-xl font-bold text-xs tracking-wider uppercase transition flex items-center gap-2 {{ $tab === 'resumen' ? 'bg-slate-800 text-[#FF9521] border border-[#FF9521]/40 shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Resumen General
            </button>

            {{-- Pestaña Equipos (Laptops) --}}
            @if($compra->items->whereNotNull('catalogo_equipo_id')->isNotEmpty())
                <button wire:click="setTab('equipos')"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs tracking-wider uppercase transition flex items-center gap-2 {{ $tab === 'equipos' ? 'bg-slate-800 text-[#FF9521] border border-[#FF9521]/40 shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Mesa de Equipos
                    @if($seriesPendientesCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-700 text-slate-300 border border-slate-600">
                            {{ $seriesPendientesCount }} sin serie
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ✓ Todas con serie
                        </span>
                    @endif
                </button>
            @endif

            {{-- Pestaña Cargadores --}}
            @if($compra->items->filter(fn($i) => $i->esCargador())->isNotEmpty())
                <button wire:click="setTab('cargadores')"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs tracking-wider uppercase transition flex items-center gap-2 {{ $tab === 'cargadores' ? 'bg-slate-800 text-[#FF9521] border border-[#FF9521]/40 shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Mesa de Cargadores
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-700 text-slate-300">
                        {{ $compra->items->filter(fn($i) => $i->esCargador())->sum('cantidad') }} uds
                    </span>
                </button>
            @endif

            {{-- Pestaña Componentes --}}
            @if($compra->items->whereNotNull('catalogo_pieza_id')->isNotEmpty())
                <button wire:click="setTab('componentes')"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs tracking-wider uppercase transition flex items-center gap-2 {{ $tab === 'componentes' ? 'bg-slate-800 text-[#FF9521] border border-[#FF9521]/40 shadow-md' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Mesa de Componentes
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-700 text-slate-300">
                        {{ $compra->items->whereNotNull('catalogo_pieza_id')->sum('cantidad') }} uds
                    </span>
                </button>
            @endif

        </div>

        {{-- CONTENIDO DE PESTAÑAS --}}

        {{-- 1. TAB: RESUMEN GENERAL --}}
        @if($tab === 'resumen')
            <div class="space-y-6">
                <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl backdrop-blur-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Artículos de la Orden</h3>
                            <p class="text-xs text-slate-500">Revisión de cantidades pactadas y almacén de llegada</p>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                            {{ $compra->items->count() }} conceptos
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3 px-6">Tipo</th>
                                    <th class="py-3 px-6">Descripción</th>
                                    <th class="py-3 px-6">Almacén Destino</th>
                                    <th class="py-3 px-6 text-center">Cantidad</th>
                                    <th class="py-3 px-6 text-right">Costo Unitario</th>
                                    <th class="py-3 px-6 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @foreach($compra->items as $item)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-4 px-6">
                                            @if($item->catalogo_equipo_id)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-sky-500/15 text-sky-400 border border-sky-500/30">EQUIPO</span>
                                            @elseif($item->esCargador())
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/15 text-[#FF9521] border border-[#FF9521]/30">CARGADOR</span>
                                            @elseif($item->catalogo_pieza_id)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">PIEZA</span>
                                            @elseif($item->producto_id)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-teal-500/15 text-teal-400 border border-teal-500/30">PRODUCTO</span>
                                            @elseif($item->consumible_id)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-pink-500/15 text-pink-400 border border-pink-500/30">CONSUMIBLE</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-500/15 text-slate-400 border border-slate-500/30">OTRO</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6">
                                            @if($item->catalogo_equipo_id)
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ optional($item->catalogoEquipo)->marca }} {{ optional($item->catalogoEquipo)->modelo }}</span>
                                            @elseif($item->esCargador())
                                                @php $cd = $item->datosCargador(); @endphp
                                                <span class="font-bold text-slate-800 dark:text-slate-100">
                                                    Cargador {{ $cd['marca'] ?: 'Genérico' }}
                                                    {{ $cd['voltaje'] ? $cd['voltaje'].'V' : '' }}
                                                    {{ $cd['amperaje'] ? $cd['amperaje'].'A' : '' }}
                                                    @if(!empty($cd['punta'])) (Punta: {{ $cd['punta'] }}) @endif
                                                </span>
                                            @elseif($item->catalogo_pieza_id)
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ optional($item->catalogoPieza)->nombre }}</span>
                                            @elseif($item->producto_id)
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ optional($item->producto)->nombre }}</span>
                                            @elseif($item->consumible_id)
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ optional($item->consumible)->nombre }}</span>
                                            @else
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $item->notas ?: 'Articulo genérico' }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-slate-600 dark:text-slate-300">
                                            <span class="inline-flex items-center gap-1.5 font-medium">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                {{ optional($item->almacen)->nombre ?? 'Almacén por asignar' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center font-bold text-slate-800 dark:text-slate-200">
                                            {{ $item->cantidad }}
                                        </td>
                                        <td class="py-4 px-6 text-right font-mono text-slate-700 dark:text-slate-300">
                                            ${{ number_format($item->precio_unitario, 2) }}
                                        </td>
                                        <td class="py-4 px-6 text-right font-mono font-bold text-slate-900 dark:text-slate-100">
                                            ${{ number_format($item->subtotal(), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50/80 dark:bg-slate-950/80 font-bold border-t border-slate-200 dark:border-slate-800">
                                <tr>
                                    <td colspan="5" class="py-4 px-6 text-right uppercase text-xs tracking-wider text-slate-500">Total de la Orden:</td>
                                    <td class="py-4 px-6 text-right text-base font-mono text-[#FF9521]">
                                        ${{ number_format($compra->items->sum(fn($i) => $i->subtotal()), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Recordatorio de Escaneo si hay Equipos --}}
                @if($compra->items->whereNotNull('catalogo_equipo_id')->isNotEmpty())
                    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-700/80 flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-xl bg-sky-500/15 text-sky-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-100">Captura de Números de Serie (Opcional)</h4>
                                <p class="text-xs text-slate-300 mt-0.5">
                                    Puedes registrar las series de las laptops ahora en la <strong class="text-[#FF9521]">"Mesa de Equipos"</strong>. Si las dejas vacías, el sistema creará un <strong class="text-sky-300">Lote Ciego</strong> y los técnicos escanearán cada máquina físicamente en su mesa de trabajo.
                                </p>
                            </div>
                        </div>
                        <button wire:click="setTab('equipos')"
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-[#FF9521] border border-[#FF9521]/40 font-bold text-xs uppercase tracking-wider transition whitespace-nowrap shadow-md">
                            Ver Mesa de Equipos &rarr;
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- 2. TAB: MESA DE EQUIPOS --}}
        @if($tab === 'equipos')
            <div class="space-y-6">
                
                {{-- Banner Informativo de Escáner --}}
                <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-lg flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-sky-500/15 text-sky-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">Compatibilidad con Lector de Código de Barras</h4>
                            <p class="text-xs text-slate-400">Haz clic en la primera casilla y escanea consecutivamente la etiqueta de cada equipo físico.</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 block font-medium">Estado de Captura:</span>
                        @if($this->seriesPendientesCount > 0)
                            <span class="text-xs font-black text-slate-300">{{ $this->seriesPendientesCount }} pendiente(s) (Lote ciego)</span>
                        @else
                            <span class="text-xs font-black text-emerald-400">✓ Todas las series completas</span>
                        @endif
                    </div>
                </div>

                {{-- Tarjetas por cada Modelo de Equipo --}}
                @foreach($compra->items->whereNotNull('catalogo_equipo_id') as $item)
                    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl backdrop-blur-xl overflow-hidden">
                        
                        {{-- Cabecera del Modelo --}}
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-[#FF9521] font-black">
                                    {{ $item->cantidad }}
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100">
                                        {{ optional($item->catalogoEquipo)->marca }} {{ optional($item->catalogoEquipo)->modelo }}
                                    </h3>
                                    <p class="text-xs text-slate-500">
                                        Destino: <strong class="text-slate-300">{{ optional($item->almacen)->nombre ?? 'Almacén por defecto' }}</strong>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-lg text-xs font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                    Costo: ${{ number_format($item->precio_unitario, 2) }} c/u
                                </span>
                            </div>
                        </div>

                        {{-- Grid de Entradas de Serie --}}
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @for($i = 0; $i < $item->cantidad; $i++)
                                    @php
                                        $valSerie = $seriesEquipos[$item->id][$i] ?? '';
                                        $tieneValor = trim($valSerie) !== '';
                                    @endphp
                                    <div class="relative">
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                                Unidad #{{ $i + 1 }}
                                            </label>
                                            @if($tieneValor)
                                                <span class="text-[10px] font-black text-emerald-400 flex items-center gap-1">
                                                    ✓ Capturada
                                                </span>
                                            @else
                                                <span class="text-[10px] font-medium text-slate-400">
                                                    Sin serie (Lote Ciego)
                                                </span>
                                            @endif
                                        </div>
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.defer="seriesEquipos.{{ $item->id }}.{{ $i }}"
                                                   placeholder="SERIE (ej. 5CD2348ABC)"
                                                   class="w-full px-4 py-2.5 bg-slate-950/80 border {{ $tieneValor ? 'border-emerald-500/50 focus:border-emerald-400' : 'border-slate-700/80 focus:border-[#FF9521]' }} rounded-xl text-white font-mono uppercase text-sm tracking-wider focus:ring-2 focus:ring-[#FF9521]/50 placeholder-slate-600 transition shadow-inner">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                    </div>
                @endforeach

            </div>
        @endif

        {{-- 3. TAB: MESA DE COMPONENTES --}}
        @if($tab === 'componentes')
            <div class="space-y-6">
                @foreach($compra->items->whereNotNull('catalogo_pieza_id') as $item)
                    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl backdrop-blur-xl overflow-hidden">
                        
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-base font-black text-slate-800 dark:text-slate-100">
                                    {{ optional($item->catalogoPieza)->nombre }}
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Compradas: <strong class="text-slate-200">{{ $item->cantidad }} uds</strong> &bull; Almacén: <strong class="text-slate-300">{{ optional($item->almacen)->nombre ?? 'Por defecto' }}</strong>
                                </p>
                            </div>
                            <button wire:click="agregarClasificacion({{ $item->id }})"
                                    type="button"
                                    class="inline-flex items-center px-3.5 py-1.5 rounded-xl font-bold text-xs text-[#FF9521] bg-[#FF9521]/10 hover:bg-[#FF9521]/20 border border-[#FF9521]/30 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Dividir en Sublote
                            </button>
                        </div>

                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="text-xs uppercase bg-slate-950/50 text-slate-400 border-b border-slate-800">
                                        <tr>
                                            <th class="py-2.5 px-4 w-36">Cantidad</th>
                                            <th class="py-2.5 px-4">Especificaciones / Frecuencia / Capacidad / Notas</th>
                                            <th class="py-2.5 px-4 w-16 text-right"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/60">
                                        @foreach($clasificacionesComponentes[$item->id] ?? [] as $index => $clasif)
                                            <tr>
                                                <td class="py-3 px-4">
                                                    <input type="number"
                                                           min="1"
                                                           max="{{ $item->cantidad }}"
                                                           wire:model.defer="clasificacionesComponentes.{{ $item->id }}.{{ $index }}.cantidad"
                                                           class="w-full px-3 py-2 bg-slate-950/80 border border-slate-700 rounded-xl text-white font-mono text-sm focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] shadow-inner">
                                                </td>
                                                <td class="py-3 px-4">
                                                    <input type="text"
                                                           wire:model.defer="clasificacionesComponentes.{{ $item->id }}.{{ $index }}.especificacion"
                                                           placeholder="Ej. DDR4 8GB 3200MHz / Kingston / NVMe 512GB"
                                                           class="w-full px-4 py-2 bg-slate-950/80 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] placeholder-slate-600 shadow-inner">
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    @if(count($clasificacionesComponentes[$item->id] ?? []) > 1)
                                                        <button wire:click="removerClasificacion({{ $item->id }}, {{ $index }})"
                                                                type="button"
                                                                class="p-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10 transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @php
                                $sumaClasif = 0;
                                foreach($clasificacionesComponentes[$item->id] ?? [] as $c) {
                                    $sumaClasif += (int) ($c['cantidad'] ?? 0);
                                }
                                $cuadra = $sumaClasif === (int) $item->cantidad;
                            @endphp

                            <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                                <span class="text-slate-400">Total asignado a este lote:</span>
                                <span class="font-bold font-mono px-3 py-1 rounded-lg {{ $cuadra ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30' }}">
                                    {{ $sumaClasif }} / {{ $item->cantidad }} unidades {{ $cuadra ? '✓ Cuadra' : '✗ No cuadra' }}
                                </span>
                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        @endif

        {{-- 4. TAB: MESA DE CARGADORES --}}
        @if($tab === 'cargadores')
            <div class="space-y-6">
                {{-- Banner Informativo de Reglas de Serie --}}
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 sm:p-5 flex items-start gap-3 backdrop-blur-xl shadow-xl">
                    <div class="p-2.5 rounded-xl bg-amber-500/20 text-[#FF9521] shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-[#FF9521] uppercase tracking-wider">Regla de Negocio: Trazabilidad de Cargadores</h4>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            La <strong class="text-white">serie interna de TecnoByte es obligatoria y se autogenera automáticamente</strong> para cada unidad al confirmar la recepción (formato: <code class="text-amber-400 font-mono">CARG-{{ strtoupper(optional($compra->proveedor)->abreviacion ?: 'TB') }}...</code>). La <strong class="text-white">serie del fabricante es opcional</strong>: si el cargador físico cuenta con una serie legible en su etiqueta, captúrala aquí para respaldar futuras reclamaciones de garantía.
                        </p>
                    </div>
                </div>

                @foreach($compra->items->filter(fn($i) => $i->esCargador()) as $item)
                    @php $cargData = $item->datosCargador(); @endphp
                    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl backdrop-blur-xl p-6 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-black bg-amber-500/15 text-[#FF9521] border border-[#FF9521]/30">CARGADOR</span>
                                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                        {{ $cargData['marca'] ?: 'Genérico' }}
                                    </h3>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                                    @if(!empty($cargData['voltaje']))
                                        <span class="bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md font-mono text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ $cargData['voltaje'] }}V</span>
                                    @endif
                                    @if(!empty($cargData['amperaje']))
                                        <span class="bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md font-mono text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ $cargData['amperaje'] }}A</span>
                                    @endif
                                    @if(!empty($cargData['punta']))
                                        <span class="bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Punta: {{ $cargData['punta'] }}</span>
                                    @endif
                                    <span class="text-slate-400 dark:text-slate-500">· Destino: <strong class="text-slate-700 dark:text-slate-300">{{ optional($item->almacen)->nombre ?? 'Almacén de Preparación' }}</strong></span>
                                </div>
                            </div>
                            <span class="px-3.5 py-1.5 rounded-xl font-mono font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 self-start sm:self-auto">
                                {{ $item->cantidad }} {{ $item->cantidad == 1 ? 'unidad' : 'unidades' }}
                            </span>
                        </div>

                        {{-- Grilla de Captura Ergonómica por Unidad --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @for($i = 0; $i < $item->cantidad; $i++)
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-3 shadow-inner">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Unidad #{{ $i + 1 }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500/10 text-[#FF9521] border border-amber-500/20">
                                            Serie Int: [Auto]
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                            Serie del Fabricante (Opcional):
                                        </label>
                                        <input type="text"
                                               wire:model="seriesFabricanteCargadores.{{ $item->id }}.{{ $i }}"
                                               placeholder="Escanear o digitar serie..."
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs font-mono focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none transition placeholder-slate-400">
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- BARRA FLOTANTE INFERIOR DE ACCIÓN (STICKY ACTION BAR) --}}
        <div class="sticky bottom-4 z-30 bg-slate-900/95 border border-slate-700/80 backdrop-blur-2xl shadow-2xl p-4 sm:p-5 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
            <div class="flex items-center gap-3">
                @if($seriesPendientesCount > 0)
                    <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-100">Listo para ingreso (con Lote Ciego)</h4>
                        <p class="text-xs text-slate-400 font-medium">Hay {{ $seriesPendientesCount }} equipo(s) sin serie que ingresarán para escaneo del técnico en taller.</p>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-100">Todo listo para ingreso</h4>
                        <p class="text-xs text-slate-400">Todas las series fueron capturadas y validadas.</p>
                    </div>
                @endif
            </div>

            <div class="w-full sm:w-auto flex items-center gap-3">
                <button wire:click="procesarRecepcion"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-7 py-3.5 rounded-xl font-black text-sm tracking-wide text-white bg-gradient-to-r from-[#FF9521] to-amber-600 hover:from-[#ff8800] hover:to-amber-500 shadow-lg shadow-orange-950/40 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading.remove wire:target="procesarRecepcion" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="procesarRecepcion" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span wire:loading.remove wire:target="procesarRecepcion">Confirmar Recepción e Ingreso</span>
                    <span wire:loading wire:target="procesarRecepcion">Procesando Ingreso...</span>
                </button>
            </div>
        </div>

    </div>
</x-tb-background>
