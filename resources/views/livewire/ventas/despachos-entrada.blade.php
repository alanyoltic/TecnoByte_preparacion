<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        {{-- TOPBAR MAESTRO --}}
        <x-topbar
            title="Despachos Entrantes"
            chip="Ventas · Recepción de mercancía"
            description="Recepción e incorporación al inventario comercial de equipos y productos enviados desde Preparación."
        />

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm backdrop-blur-md shadow-lg shadow-emerald-950/20">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        {{-- TARJETAS / FILTROS DE ESTADO --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $tarjetas = [
                    'ENVIADO'   => ['label' => 'Pendientes', 'icon' => 'truck', 'color' => 'border-amber-500/40 text-[#FF9521]', 'badge' => 'bg-amber-500/20 text-amber-300'],
                    'APROBADO'  => ['label' => 'Aprobados',  'icon' => 'check', 'color' => 'border-emerald-500/40 text-emerald-400', 'badge' => 'bg-emerald-500/20 text-emerald-300'],
                    'RECHAZADO' => ['label' => 'Rechazados', 'icon' => 'x',     'color' => 'border-rose-500/40 text-rose-400',       'badge' => 'bg-rose-500/20 text-rose-300'],
                    ''          => ['label' => 'Total Historial', 'icon' => 'list', 'color' => 'border-slate-500/40 text-slate-300',     'badge' => 'bg-slate-500/20 text-slate-300'],
                ];
            @endphp

            @foreach($tarjetas as $estatus => $meta)
            @php
                $activo = $filtroEstatus === $estatus;
                $cuenta = $estatus === '' ? $contadores->sum() : ($contadores[$estatus] ?? 0);
            @endphp
            <button wire:click="$set('filtroEstatus', '{{ $estatus }}')"
                    class="rounded-3xl p-5 text-left transition-all duration-300 backdrop-blur-xl border
                           {{ $activo
                               ? 'bg-slate-900/90 border-[#FF9521] shadow-xl shadow-orange-500/10 ring-2 ring-[#FF9521]/40 -translate-y-1'
                               : 'bg-white/80 dark:bg-slate-950/60 border-slate-200/80 dark:border-white/10 hover:border-slate-400 dark:hover:border-slate-600 hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ $meta['label'] }}
                    </span>
                    <span class="w-2.5 h-2.5 rounded-full {{ $activo ? 'bg-[#FF9521] shadow-md shadow-orange-500/50' : 'bg-slate-600' }}"></span>
                </div>
                <p class="text-3xl font-black mt-2 text-slate-900 dark:text-white">
                    {{ $cuenta }}
                </p>
            </button>
            @endforeach
        </div>

        {{-- TABLA GLASSMORPHISM --}}
        <div class="rounded-3xl overflow-hidden border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-950/60 backdrop-blur-2xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-slate-800/80
                                   text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-900/40">
                            <th class="px-6 py-4">Folio</th>
                            <th class="px-6 py-4">Creado por</th>
                            <th class="px-6 py-4">Almacén Destino</th>
                            <th class="px-6 py-4 text-center">Artículos</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4">Fecha Envío</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/50">
                        @forelse($despachos as $despacho)
                        <tr class="hover:bg-slate-100/50 dark:hover:bg-slate-900/50 transition-colors duration-150">
                            <td class="px-6 py-4 font-mono font-bold text-[#FF9521]">
                                {{ $despacho->folio }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-200">
                                {{ $despacho->creadoPor?->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-xs">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-mono">
                                    {{ $despacho->almacenDestino?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl
                                                 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-xs" title="Equipos serializados">
                                        <span>💻</span> {{ $despacho->equipos_count }}
                                    </span>
                                    @if($despacho->productos_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl
                                                 bg-purple-500/10 border border-purple-500/20 text-purple-400 font-bold text-xs" title="Accesorios / Productos">
                                        <span>📦</span> {{ $despacho->productos_count }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $colores = [
                                        'ENVIADO'   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                        'APROBADO'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                        'RECHAZADO' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                    ];
                                    $color = $colores[$despacho->estatus] ?? 'bg-slate-500/10 text-slate-400 border-slate-700';
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                    {{ $despacho->labelEstatus }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $despacho->enviado_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($despacho->estaEnviado())
                                <div class="flex items-center justify-end gap-2">
                                    @can('ventas.despachos.aprobar')
                                    <button wire:click="iniciarAprobacion({{ $despacho->id }})"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold
                                                   bg-[#FF9521] hover:bg-[#e6841b] text-white
                                                   shadow-md shadow-orange-500/20 transition-all flex items-center gap-1">
                                        <span>✓</span> Recibir
                                    </button>
                                    <button wire:click="iniciarRechazo({{ $despacho->id }})"
                                            class="px-3 py-1.5 rounded-xl text-xs font-semibold
                                                   bg-rose-500/10 text-rose-400 border border-rose-500/30
                                                   hover:bg-rose-500/20 transition-all">
                                        Rechazar
                                    </button>
                                    @endcan
                                </div>
                                @elseif($despacho->estaRechazado() && $despacho->notas_rechazo)
                                <span class="text-xs text-rose-400 italic max-w-xs block text-right truncate"
                                      title="{{ $despacho->notas_rechazo }}">
                                    {{ Str::limit($despacho->notas_rechazo, 35) }}
                                </span>
                                @else
                                <span class="text-xs text-slate-500 font-mono">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium">No hay despachos para mostrar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($despachos->hasPages())
            <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-900/30">
                {{ $despachos->links() }}
            </div>
            @endif
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- MODAL APROBACIÓN / RECEPCIÓN CON VERIFICACIÓN FÍSICA              --}}
    {{-- ================================================================ --}}
    @if($modalAprobacion && $despachoSeleccionado)
    @php
        $desp = $despachoSeleccionado;
        $totalEquipos = $desp->equipos->count();
        $equiposEscaneados = count(array_filter($equiposVerificados));
        $pctEquipos = $totalEquipos > 0 ? round(($equiposEscaneados / $totalEquipos) * 100) : 100;
        $esCompleto = $verificacionCompleta;
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
         x-data x-init="$el.focus()" @keydown.escape.window="$wire.cerrarModales()">
        <div class="w-full max-w-3xl rounded-3xl bg-slate-900 border border-slate-700/80 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
            
            {{-- HEADER MODAL --}}
            <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/50">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-[#FF9521]/15 border border-[#FF9521]/30 flex items-center justify-center text-[#FF9521] shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-white">
                                Verificación Física de Despacho
                            </h3>
                            <span class="font-mono text-sm font-bold text-[#FF9521] bg-[#FF9521]/10 px-2 py-0.5 rounded-lg border border-[#FF9521]/20">
                                {{ $desp->folio }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Almacén Destino: <span class="text-slate-200 font-semibold">{{ $desp->almacenDestino?->nombre }}</span> · Enviado por: <span class="text-slate-300">{{ $desp->creadoPor?->nombre }}</span>
                        </p>
                    </div>
                </div>
                <button wire:click="cerrarModales" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition">✕</button>
            </div>

            {{-- BARRA DE PROGRESO GLOBAL --}}
            <div class="px-6 py-3 bg-slate-950/70 border-b border-slate-800/80 flex items-center justify-between text-xs">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $equiposEscaneados === $totalEquipos ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                        <span class="text-slate-300 font-medium">Equipos:</span>
                        <span class="font-mono font-bold {{ $equiposEscaneados === $totalEquipos ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $equiposEscaneados }} / {{ $totalEquipos }} ({{ $pctEquipos }}%)
                        </span>
                    </div>
                    @if($desp->productos->isNotEmpty())
                    <div class="flex items-center gap-1.5 border-l border-slate-800 pl-4">
                        <span class="w-2.5 h-2.5 rounded-full {{ $productosDiscrepanciasCount === 0 ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                        <span class="text-slate-300 font-medium">Accesorios:</span>
                        <span class="font-mono font-bold {{ $productosDiscrepanciasCount === 0 ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $productosDiscrepanciasCount === 0 ? 'Conforme' : 'Con diferencias' }}
                        </span>
                    </div>
                    @endif
                </div>

                @if($esCompleto)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        ✓ 100% Verificado físicamente
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                        ⏳ Inspección en proceso
                    </span>
                @endif
            </div>

            {{-- CONTENIDO SCROLLABLE --}}
            <div class="p-6 space-y-6 overflow-y-auto flex-1">

                {{-- ======================================================== --}}
                {{-- MECÁNICA 1: PISTOLA ESCÁNER PARA EQUIPOS SERIALIZADOS    --}}
                {{-- ======================================================== --}}
                @if($totalEquipos > 0)
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                            <span>💻</span> Escaneo de Equipos con Pistola
                        </label>
                        <span class="text-[11px] text-slate-400">
                            Pasa el escáner sobre la serie o escribe y presiona Enter
                        </span>
                    </div>

                    {{-- Input de escaneo --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5 text-[#FF9521]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <input type="text"
                               wire:model="inputScanSerie"
                               wire:keydown.enter="escanearSerie"
                               placeholder="Escanea el código de barras del equipo..."
                               class="w-full pl-11 pr-24 py-3 rounded-2xl text-sm font-mono
                                      bg-slate-950/80 border border-slate-700
                                      text-white placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all shadow-inner">
                        <button type="button"
                                wire:click="escanearSerie"
                                class="absolute right-1.5 top-1.5 bottom-1.5 px-4 rounded-xl text-xs font-bold
                                       bg-[#FF9521] hover:bg-[#e0831a] text-slate-950 transition">
                            Validar
                        </button>
                    </div>

                    {{-- Retroalimentación del escáner --}}
                    @if($mensajeScan)
                    <div class="px-4 py-2.5 rounded-xl text-xs font-medium flex items-center gap-2
                                {{ $mensajeScanTipo === 'success' ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : '' }}
                                {{ $mensajeScanTipo === 'error' ? 'bg-rose-500/15 border border-rose-500/30 text-rose-300' : '' }}
                                {{ $mensajeScanTipo === 'warning' ? 'bg-amber-500/15 border border-amber-500/30 text-amber-300' : '' }}
                                {{ $mensajeScanTipo === 'info' ? 'bg-blue-500/15 border border-blue-500/30 text-blue-300' : '' }}">
                        <span>{{ $mensajeScan }}</span>
                    </div>
                    @endif

                    {{-- Lista de Equipos en el Manifiesto --}}
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/50 overflow-hidden divide-y divide-slate-800/60 max-h-56 overflow-y-auto">
                        @foreach($desp->equipos as $item)
                        @php
                            $equipoId = $item->equipo_id;
                            $verificado = !empty($equiposVerificados[$equipoId]);
                        @endphp
                        <div class="p-3 text-xs flex items-center justify-between transition-colors duration-150 {{ $verificado ? 'bg-emerald-500/5' : 'hover:bg-slate-900/60' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold {{ $verificado ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $verificado ? '✓' : ($loop->iteration) }}
                                </span>
                                <div>
                                    <div class="font-bold text-slate-200 flex items-center gap-2">
                                        <span>{{ $item->equipo?->marca }} {{ $item->equipo?->modelo }}</span>
                                        @if($verificado)
                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                VERIFICADO
                                            </span>
                                        @else
                                            <span class="text-[10px] font-medium px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 border border-slate-700">
                                                PENDIENTE
                                            </span>
                                        @endif
                                    </div>
                                    <div class="font-mono text-slate-400 text-[11px] mt-0.5">
                                        Serie: <span class="{{ $verificado ? 'text-emerald-400 font-bold' : 'text-slate-300' }}">{{ $item->equipo?->numero_serie }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        wire:click="alternarVerificacionEquipo({{ $equipoId }})"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition
                                               {{ $verificado
                                                   ? 'text-slate-400 hover:text-rose-400 hover:bg-rose-500/10'
                                                   : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700' }}"
                                        title="{{ $verificado ? 'Desmarcar' : 'Verificar manualmente si no lee el código' }}">
                                    {{ $verificado ? 'Desmarcar' : 'Verificar manual' }}
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ======================================================== --}}
                {{-- MECÁNICA 2: CONTEO DE ACCESORIOS Y CARGADORES            --}}
                {{-- ======================================================== --}}
                @if($desp->productos->isNotEmpty())
                <div class="space-y-3 border-t border-slate-800 pt-5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                            <span>📦</span> Conteo de Accesorios y Cargadores ({{ $desp->productos->count() }})
                        </label>
                        <button type="button"
                                wire:click="marcarTodoConformeProductos"
                                class="text-xs font-semibold text-[#FF9521] hover:underline flex items-center gap-1">
                            <span>✓</span> Marcar todo conforme
                        </button>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/50 overflow-hidden divide-y divide-slate-800/60">
                        @foreach($desp->productos as $item)
                        @php
                            $recibido = $productosVerificados[$item->producto_id] ?? 0;
                            $esperado = $item->cantidad;
                            $conforme = $recibido === $esperado;
                        @endphp
                        <div class="p-3.5 text-xs flex items-center justify-between gap-4 {{ $conforme ? 'bg-emerald-500/5' : '' }}">
                            <div>
                                <span class="font-bold text-slate-200 text-sm block">{{ $item->producto?->nombre }}</span>
                                <span class="text-slate-400 text-[11px]">
                                    Esperado en manifiesto: <strong class="text-white font-mono">{{ $esperado }}</strong> unidades
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                {{-- Controles de cantidad recibida --}}
                                <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-700 rounded-xl p-1">
                                    <button type="button"
                                            wire:click="decrementarProducto({{ $item->producto_id }})"
                                            class="w-7 h-7 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white flex items-center justify-center font-bold">
                                        -
                                    </button>
                                    <span class="w-10 text-center font-mono font-bold text-sm {{ $conforme ? 'text-emerald-400' : 'text-[#FF9521]' }}">
                                        {{ $recibido }}
                                    </span>
                                    <button type="button"
                                            wire:click="incrementarProducto({{ $item->producto_id }})"
                                            class="w-7 h-7 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white flex items-center justify-center font-bold">
                                        +
                                    </button>
                                </div>

                                {{-- Badges de estado --}}
                                @if($conforme)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        ✓ Completo
                                    </span>
                                @elseif($recibido < $esperado)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                        Faltan {{ $esperado - $recibido }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-500/15 text-purple-400 border border-purple-500/30">
                                        +{{ $recibido - $esperado }} sobrantes
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- NOTAS / OBSERVACIONES DE RECEPCIÓN --}}
                <div class="space-y-1.5 border-t border-slate-800 pt-4">
                    <label class="text-xs font-semibold text-slate-300 flex items-center justify-between">
                        <span>Notas u Observaciones de Recepción</span>
                        @if(!$esCompleto)
                            <span class="text-amber-400 font-bold text-[11px]">* Obligatorio al haber discrepancias</span>
                        @else
                            <span class="text-slate-500 text-[11px]">(Opcional)</span>
                        @endif
                    </label>
                    <textarea wire:model="notasRecepcion"
                              rows="2"
                              placeholder="{{ $esCompleto ? 'Anotaciones opcionales sobre el estado físico del paquete...' : 'Detalla el motivo de faltantes, piezas no recibidas o daños físicos antes de confirmar...' }}"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/70 border border-slate-700 text-xs text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#FF9521] focus:border-transparent outline-none transition"></textarea>
                </div>

                @if($mensajeError)
                <div class="px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $mensajeError }}</span>
                </div>
                @endif
            </div>

            {{-- FOOTER MODAL --}}
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between gap-3">
                <button wire:click="cerrarModales"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-400 border border-slate-700 hover:text-white transition">
                    Cancelar
                </button>

                <div class="flex items-center gap-3">
                    <button wire:click="confirmarAprobacion"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-lg flex items-center gap-2
                                   {{ $esCompleto
                                       ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-900/30'
                                       : 'bg-[#FF9521] hover:bg-[#e6841b] text-slate-950 shadow-orange-500/20' }}">
                        <span wire:loading.remove wire:target="confirmarAprobacion">
                            @if($esCompleto)
                                ✓ Confirmar Recepción Completa
                            @else
                                ⚠ Confirmar con Discrepancia
                            @endif
                        </span>
                        <span wire:loading wire:target="confirmarAprobacion">Procesando entrada...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL RECHAZO                                                     --}}
    {{-- ================================================================ --}}
    @if($modalRechazo)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         x-data @keydown.escape.window="$wire.cerrarModales()">
        <div class="w-full max-w-md rounded-3xl bg-slate-900 border border-slate-700/70 shadow-2xl p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="text-rose-400">✗</span> Rechazar Despacho
            </h3>
            <p class="text-slate-400 text-xs leading-relaxed">
                Indica el motivo del rechazo. La mercancía no se ingresará y Preparación recibirá la notificación para corregirla.
            </p>

            <textarea wire:model="motivoRechazo" rows="4"
                      placeholder="Ej: El empaque llegó dañado o faltan artículos del folio..."
                      class="w-full px-4 py-3 rounded-2xl text-xs resize-none
                             bg-slate-950/70 border border-slate-700 text-slate-200
                             placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#FF9521]"></textarea>

            @if($mensajeError)
            <div class="px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
                {{ $mensajeError }}
            </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="cerrarModales"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 border border-slate-700 hover:text-white transition">
                    Cancelar
                </button>
                <button wire:click="confirmarRechazo"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl text-xs font-bold
                               bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 transition-all">
                    Confirmar Rechazo
                </button>
            </div>
        </div>
    </div>
    @endif

</x-tb-background>
</div>
