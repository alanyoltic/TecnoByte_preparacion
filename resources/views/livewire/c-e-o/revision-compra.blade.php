<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16 space-y-6 max-w-7xl mx-auto">
        
        {{-- Encabezado Superior --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <x-topbar
                title="Revisión de Orden: {{ $compra->folio ?: 'OC-' . $compra->id }}"
                chip="CEO > Autorización Financiera"
                description="Inspección detallada de artículos, costos pactados y justificación antes de autorizar el pago."
            />
            <div>
                <a href="{{ route('preparacion.compras') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver a Órdenes
                </a>
            </div>
        </div>

        {{-- Tarjetas de Información Ejecutiva --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Proveedor --}}
            <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-6 shadow-md backdrop-blur-xl">
                <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide">Proveedor</h3>
                        <p class="text-xs text-slate-500">Datos comerciales</p>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 uppercase font-semibold">Empresa:</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200 text-base">{{ optional($compra->proveedor)->nombre_empresa ?? 'Sin proveedor asignado' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Teléfono:</span>
                            <p class="font-medium text-slate-700 dark:text-slate-300">{{ optional($compra->proveedor)->telefono_contacto ?: 'No registrado' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Email:</span>
                            <p class="font-medium text-slate-700 dark:text-slate-300 truncate" title="{{ optional($compra->proveedor)->email_contacto }}">{{ optional($compra->proveedor)->email_contacto ?: 'No registrado' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Solicitante y Área --}}
            <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-6 shadow-md backdrop-blur-xl">
                <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-[#FF9521]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide">Solicitante</h3>
                        <p class="text-xs text-slate-500">Origen de la petición</p>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 uppercase font-semibold">Usuario:</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200 text-base">{{ optional($compra->registradoPor)->name ?? 'Usuario no especificado' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Área / Destino:</span>
                            <div class="mt-0.5">
                                <span class="text-xs font-black px-2.5 py-1 rounded-md {{ $compra->area === 'VENTAS' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-300 dark:border-amber-700/50' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-300 dark:border-blue-700/50' }}">
                                    {{ $compra->area }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Fecha:</span>
                            <p class="font-medium text-slate-700 dark:text-slate-300">{{ $compra->fecha_compra ? $compra->fecha_compra->format('d/m/Y') : 'S/F' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resumen Financiero --}}
            <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-6 shadow-md backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide">Importe a Autorizar</h3>
                            <p class="text-xs text-slate-500">Condición: {{ $compra->tipo_iva }}</p>
                        </div>
                    </div>
                    <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">${{ number_format($compra->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>I.V.A.:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">${{ number_format($compra->iva, 2) }}</span>
                        </div>
                        @if($compra->moneda === 'USD')
                            <div class="flex justify-between text-amber-600 font-bold">
                                <span>T. Cambio USD:</span>
                                <span>${{ number_format($compra->tipo_cambio, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-3 flex items-baseline justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total:</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white">
                        ${{ number_format($compra->total, 2) }} <span class="text-sm font-bold text-slate-500">{{ $compra->moneda ?? 'MXN' }}</span>
                    </span>
                </div>
            </div>

        </div>

        {{-- Justificación / Notas del Solicitante --}}
        <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-5 shadow-md backdrop-blur-xl">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                Motivo / Justificación de la Compra
            </h4>
            <p class="text-sm text-slate-700 dark:text-slate-300 font-medium italic bg-slate-50 dark:bg-slate-900/60 p-3.5 rounded-xl border border-slate-200/60 dark:border-slate-800">
                "{{ $compra->notas ?: 'No se especificaron comentarios u observaciones adicionales.' }}"
            </p>
        </div>

        {{-- Tabla de Artículos Solicitados --}}
        <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl shadow-xl overflow-hidden backdrop-blur-xl">
            <div class="px-6 py-4 bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-200/80 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Artículos y Costos Solicitados</h3>
                    <p class="text-xs text-slate-500">Desglose de cada producto, refacción o equipo incluido en esta orden</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                    {{ $compra->items->count() }} {{ $compra->items->count() === 1 ? 'partida' : 'partidas' }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-100/60 dark:bg-slate-900/40 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-white/10">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-center w-12">#</th>
                            <th class="px-5 py-3 font-semibold w-28">Tipo</th>
                            <th class="px-5 py-3 font-semibold min-w-[260px]">Artículo / Modelo</th>
                            <th class="px-5 py-3 font-semibold min-w-[180px]">Almacén Destino</th>
                            <th class="px-5 py-3 font-semibold text-center w-24">Cantidad</th>
                            <th class="px-5 py-3 font-semibold text-right w-32">Costo Unit.</th>
                            <th class="px-5 py-3 font-semibold text-right w-36">Importe</th>
                            <th class="px-5 py-3 font-semibold w-48">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-white/5">
                        @foreach($compra->items as $idx => $item)
                            @php
                                $tipoBadge = 'GENERAL';
                                $nombreItem = 'Artículo';
                                $subinfo = '';

                                if ($item->catalogo_equipo_id && $item->catalogoEquipo) {
                                    $tipoBadge = $compra->area === 'VENTAS' ? '💻 EQUIPO NUEVO' : '💻 EQUIPO';
                                    $nombreItem = $item->catalogoEquipo->marca . ' ' . $item->catalogoEquipo->modelo;
                                    $subinfo = $item->catalogoEquipo->tipo_equipo;
                                } elseif ($item->catalogo_pieza_id && $item->catalogoPieza) {
                                    $tipoBadge = '🔧 REFACCIÓN';
                                    $nombreItem = $item->catalogoPieza->nombre;
                                    $subinfo = $item->catalogoPieza->especificacion;
                                } elseif ($item->producto_id && $item->producto) {
                                    $tipoBadge = '📦 PRODUCTO';
                                    $nombreItem = $item->producto->nombre;
                                    $subinfo = 'SKU: ' . ($item->producto->sku ?: 'S/N');
                                } elseif ($item->consumible_id && $item->consumible) {
                                    $tipoBadge = '💧 CONSUMIBLE';
                                    $nombreItem = $item->consumible->nombre;
                                    $subinfo = 'Categoría: ' . ($item->consumible->categoria ?: 'General');
                                } else {
                                    $cargadorData = json_decode($item->notas, true);
                                    if (is_array($cargadorData) && ($cargadorData['tipo'] ?? '') === 'cargador') {
                                        $tipoBadge = '🔌 CARGADOR';
                                        $nombreItem = 'Cargador ' . ($cargadorData['marca'] ?: 'Genérico');
                                        $subinfo = ($cargadorData['voltaje'] ? $cargadorData['voltaje'].'V ' : '') . ($cargadorData['amperaje'] ? $cargadorData['amperaje'].'A ' : '') . ($cargadorData['punta'] ? 'Punta: '.$cargadorData['punta'] : '');
                                    } else {
                                        $tipoBadge = 'OTRO';
                                        $nombreItem = 'Partida ' . ($idx + 1);
                                    }
                                }

                                $importePartida = (float)$item->cantidad * (float)$item->precio_unitario;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40 transition">
                                <td class="px-5 py-4 text-center text-xs font-bold text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-block text-[10px] font-black px-2 py-0.5 rounded-md uppercase
                                        @if(str_contains($tipoBadge, 'EQUIPO')) bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300
                                        @elseif(str_contains($tipoBadge, 'REFACCIÓN')) bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300
                                        @elseif(str_contains($tipoBadge, 'PRODUCTO')) bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300
                                        @elseif(str_contains($tipoBadge, 'CONSUMIBLE')) bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300
                                        @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                                        {{ $tipoBadge }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $nombreItem }}</div>
                                    @if($subinfo)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $subinfo }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-medium text-xs text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/80 px-2 py-1 rounded-lg">
                                        📍 {{ optional($item->almacen)->nombre ?? 'Por asignar en recepción' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center font-bold text-slate-900 dark:text-white">
                                    {{ $item->cantidad }}
                                </td>
                                <td class="px-5 py-4 text-right font-medium text-slate-800 dark:text-slate-200">
                                    ${{ number_format($item->precio_unitario, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">
                                    ${{ number_format($importePartida, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500 italic">
                                    {{ $item->notas ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Barra de Acciones del CEO --}}
        <div class="bg-white/90 dark:bg-slate-950/90 border border-slate-200/80 dark:border-white/10 rounded-2xl p-5 shadow-2xl backdrop-blur-xl flex flex-col sm:flex-row items-center justify-between gap-4 sticky bottom-4 z-20">
            <div class="text-sm">
                <span class="text-slate-500">Estado actual:</span>
                @if($compra->estatus === 'PENDIENTE_CEO')
                    <span class="font-black px-2.5 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 ml-1">
                        PENDIENTE DE TU AUTORIZACIÓN
                    </span>
                @elseif($compra->estatus === 'APROBADA')
                    <span class="font-black px-2.5 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 ml-1">
                        AUTORIZADA FINANCIERAMENTE
                    </span>
                @else
                    <span class="font-black px-2.5 py-1 rounded-full text-xs bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300 ml-1">
                        {{ $compra->estatus }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('preparacion.compras') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition">
                    Volver a Órdenes
                </a>

                @if($compra->estatus === 'PENDIENTE_CEO')
                    <button wire:click="rechazar" 
                            wire:confirm="¿Estás seguro de que deseas RECHAZAR esta orden de compra?" 
                            class="px-5 py-2.5 text-sm font-bold text-red-700 dark:text-red-300 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 rounded-xl transition shadow-sm">
                        Rechazar Orden
                    </button>

                    <button wire:click="aprobar" 
                            wire:confirm="¿Confirmas autorizar financieramente esta orden por un total de ${{ number_format($compra->total, 2) }} {{ $compra->moneda }}?" 
                            class="inline-flex items-center px-6 py-2.5 text-sm font-black text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-lg shadow-emerald-600/30 transition hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Autorizar Compra (${{ number_format($compra->total, 2) }})
                    </button>
                @endif
            </div>
        </div>

    </div>
</x-tb-background>
