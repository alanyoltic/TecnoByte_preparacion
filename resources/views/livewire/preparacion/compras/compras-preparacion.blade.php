<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                                         --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')
            <x-topbar
                title="Compras — Preparación"
                chip="Preparación · Inventario"
                description="Órdenes de compra de piezas y cargadores para el área de preparación."
            >
                <x-slot name="right">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('compras.catalogo') }}" wire:navigate
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-white/80 dark:bg-slate-900/60 border border-purple-300/70 dark:border-purple-600/50
                                   text-purple-700 dark:text-purple-300 text-xs font-semibold
                                   hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:-translate-y-0.5 transition-all duration-200">
                            🔩 Catálogo / Despiece
                        </a>
                        @can('prep.compras.gestionar')
                        <button wire:click="nuevaCompra"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-gradient-to-r from-emerald-600 to-emerald-500
                                   text-white text-xs font-semibold shadow-md shadow-emerald-800/40
                                   hover:shadow-emerald-500/60 hover:-translate-y-0.5 transition-all duration-200">
                            🛒 Nueva compra
                        </button>
                        @endcan
                    </div>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'nueva')
            <x-topbar
                title="Nueva Orden de Compra"
                chip="Preparación · Compras"
                description="Registra una orden de compra a proveedor. Elige entre piezas y/o cargadores."
            >
                <x-slot name="right">
                    <button wire:click="volver"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Historial
                    </button>
                </x-slot>
            </x-topbar>
        @endif


        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- VISTA: HISTORIAL DE COMPRAS                             --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')

            {{-- Métricas --}}
            @php
                $totalCompras    = \App\Models\CompraInventario::deArea('PREPARACION')->count();
                $totalPiezas     = \App\Models\CompraInventarioItem::whereHas('compra', fn($q) => $q->deArea('PREPARACION'))->sum('cantidad');
                $totalCargadores = \App\Models\Cargador::where('area', 'PREPARACION')->count();
                $totalInvertido  = \App\Models\CompraInventario::deArea('PREPARACION')->whereNotNull('total_estimado')->sum('total_estimado');
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-sky-500/20">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Órdenes</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalCompras }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-emerald-500/40">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">Piezas</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">{{ (int)$totalPiezas }}</p>
                </div>
                <div class="rounded-2xl bg-orange-50/90 dark:bg-orange-950/40 border border-orange-200/80 dark:border-orange-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-orange-500/40">
                    <p class="text-xs font-semibold text-orange-700 dark:text-orange-200 uppercase tracking-wide">Cargadores</p>
                    <p class="mt-2 text-2xl font-bold text-orange-800 dark:text-orange-100">{{ $totalCargadores }}</p>
                </div>
                <div class="rounded-2xl bg-indigo-50/90 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-indigo-500/40">
                    <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">Invertido est.</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">${{ number_format($totalInvertido, 0) }}</p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-5 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative md:col-span-2">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input type="text" wire:model.live.debounce.400ms="busqueda"
                            placeholder="Buscar por proveedor, folio o lote…"
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-2xl
                                   bg-white/80 dark:bg-slate-900/60 border border-white/60 dark:border-slate-700/70
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 backdrop-blur-xl">
                    </div>
                    <select wire:model.live="filtroProveedor"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70 border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                        <option value="">— Todos los proveedores —</option>
                        @foreach($this->proveedores as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre_empresa }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <input type="date" wire:model.live="filtroFechaDesde"
                            class="flex-1 rounded-2xl bg-white/90 dark:bg-slate-900/70 border border-white/60 dark:border-slate-600/70
                                   text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                        <input type="date" wire:model.live="filtroFechaHasta"
                            class="flex-1 rounded-2xl bg-white/90 dark:bg-slate-900/70 border border-white/60 dark:border-slate-600/70
                                   text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Órdenes de compra</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $this->compras->total() }} resultado(s)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-slate-100 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800/80">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Proveedor</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Folio / Lote</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Fecha</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Piezas</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Cargadores</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Total est.</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->compras as $compra)
                                <tr class="border-b border-slate-200 dark:border-slate-800/80
                                           hover:bg-white/60 dark:hover:bg-slate-800/40 transition-colors group">
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $compra->proveedor?->nombre_empresa ?? '—' }}
                                        </p>
                                        @if($compra->proveedor?->abreviacion)
                                            <p class="text-xs text-slate-400">{{ $compra->proveedor->abreviacion }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $compra->folio ?? '—' }}</p>
                                        @if($compra->lote_compra)
                                            <p class="text-xs text-slate-400 mt-0.5">📦 {{ $compra->lote_compra }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        {{ $compra->fecha_compra?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if($compra->items_count > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                         bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-300/50">
                                                🔩 {{ $compra->items_count }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if($compra->cargadores_count > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                         bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border border-orange-300/50">
                                                🔌 {{ $compra->cargadores_count }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        @if($compra->total_estimado)
                                            ${{ number_format($compra->total_estimado, 2) }}
                                        @else
                                            <span class="text-slate-400 font-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <button wire:click="verDetalle({{ $compra->id }})"
                                            class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5
                                                   bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                                                   text-xs font-medium text-slate-700 dark:text-slate-200
                                                   hover:bg-slate-100 dark:hover:bg-slate-800/80 transition
                                                   opacity-0 group-hover:opacity-100">
                                            Ver →
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                        No hay órdenes de compra registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/40">
                    {{ $this->compras->links() }}
                </div>
            </div>

            {{-- Modal detalle --}}
            @if($verCompraId && $this->compraDetalle)
                @php $d = $this->compraDetalle; @endphp
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl
                                bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700
                                shadow-2xl p-6 space-y-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-base">
                                    {{ $d->proveedor?->nombre_empresa ?? '—' }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Folio: {{ $d->folio ?? 'Sin folio' }}
                                    · {{ $d->fecha_compra?->format('d/m/Y') }}
                                    @if($d->lote_compra) · 📦 Lote: {{ $d->lote_compra }} @endif
                                    @if($d->almacenDestino) · 🏪 {{ $d->almacenDestino->nombre }} @endif
                                </p>
                            </div>
                            <button wire:click="cerrarDetalle"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition text-xl leading-none">✕</button>
                        </div>

                        @if($d->items->count() > 0)
                            <div>
                                <p class="text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 mb-2">🔩 Piezas</p>
                                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden">
                                    @foreach($d->items as $item)
                                        <div class="flex justify-between items-center px-4 py-2.5 text-sm
                                                    border-b border-slate-100 dark:border-slate-800/80 last:border-0
                                                    hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                            <div>
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $item->catalogoPieza?->nombre ?? '—' }}</span>
                                                <span class="text-xs text-slate-400 ml-1.5">[{{ $item->catalogoPieza?->categoria }}]</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-slate-500 dark:text-slate-400">{{ $item->cantidad }} u.</span>
                                                @if($item->precio_unitario)
                                                    <span class="ml-2 font-semibold text-emerald-600 dark:text-emerald-400">
                                                        ${{ number_format($item->precio_unitario, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($d->items->whereNotNull('consumible_id')->count() > 0)
                            <div>
                                <p class="text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 mb-2">🧽 Consumibles</p>
                                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden">
                                    @foreach($d->items->whereNotNull('consumible_id') as $item)
                                        <div class="flex justify-between items-center px-4 py-2.5 text-sm
                                                    border-b border-slate-100 dark:border-slate-800/80 last:border-0
                                                    hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                            <div>
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $item->consumible?->nombre ?? '—' }}</span>
                                                <span class="text-xs text-slate-400 ml-1.5">[{{ $item->consumible?->categoria }}]</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-slate-500 dark:text-slate-400">{{ $item->cantidad }} u.</span>
                                                @if($item->precio_unitario)
                                                    <span class="ml-2 font-semibold text-emerald-600 dark:text-emerald-400">
                                                        ${{ number_format($item->precio_unitario, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($d->cargadores->count() > 0)
                            <div>
                                <p class="text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 mb-2">🔌 Cargadores</p>
                                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden">
                                    @foreach($d->cargadores as $carg)
                                        <div class="flex justify-between items-center px-4 py-2.5 text-sm
                                                    border-b border-slate-100 dark:border-slate-800/80 last:border-0">
                                            <div>
                                                <span class="font-mono text-xs text-slate-500">{{ $carg->serie }}</span>
                                                @if($carg->marca)
                                                    <span class="ml-2 text-slate-700 dark:text-slate-200">{{ $carg->marca }}</span>
                                                @endif
                                                @if($carg->voltaje || $carg->amperaje)
                                                    <span class="ml-1.5 text-xs text-slate-400">{{ $carg->voltaje }}V {{ $carg->amperaje }}A</span>
                                                @endif
                                            </div>
                                            @if($carg->costo)
                                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                                    ${{ number_format($carg->costo, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($d->total_estimado)
                            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Total estimado: <span class="text-emerald-600 dark:text-emerald-400">${{ number_format($d->total_estimado, 2) }}</span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif


        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- VISTA: NUEVA ORDEN DE COMPRA                            --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        @if($vista === 'nueva')

            @if($error)
                <div class="flex items-center gap-3 rounded-2xl border border-red-300/60 dark:border-red-500/40
                            bg-red-50/90 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    ⚠️ {{ $error }}
                </div>
            @endif

            <div class="space-y-6">

                {{-- ── PASO 1: Selector de Tipo de Compra ─────────── --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-5 py-4">
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 mb-3">
                        ¿Qué vas a comprar?
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['piezas' => '🔩 Piezas', 'consumibles' => '🧽 Consumibles', 'cargadores' => '🔌 Cargadores', 'todos' => '📦 Todos'] as $val => $label)
                            <button wire:click="$set('tipoCompra', '{{ $val }}')"
                                class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 border
                                       {{ $tipoCompra === $val
                                            ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white border-transparent shadow-md shadow-emerald-800/30'
                                            : 'bg-white/80 dark:bg-slate-900/60 border-slate-300/70 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-emerald-400 hover:-translate-y-0.5' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- ── PASO 2: Cabecera de la Orden ───────────────── --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-5 py-5">
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 mb-4">
                        Datos de la orden
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        {{-- Proveedor --}}
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                Proveedor *
                            </label>
                            <div class="flex gap-2">
                                <select wire:model="proveedorId"
                                    class="flex-1 rounded-xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                                    <option value="">— Selecciona —</option>
                                    @foreach($this->proveedores as $p)
                                        <option value="{{ $p->id }}">{{ $p->abreviacion }} · {{ $p->nombre_empresa }}</option>
                                    @endforeach
                                </select>
                                <button wire:click="abrirModalProveedor" title="Nuevo proveedor"
                                    class="rounded-xl px-3 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                                           text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-sm">
                                    +
                                </button>
                            </div>
                            @error('proveedorId')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Fecha *</label>
                            <input type="date" wire:model="fechaCompra"
                                class="w-full rounded-xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70
                                       text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                        </div>

                        {{-- Almacén Destino Global --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                Almacén destino *
                                <span class="text-slate-400 font-normal normal-case">(global para esta orden)</span>
                            </label>
                            @if($this->almacenes->isEmpty())
                                <p class="text-xs text-amber-600 dark:text-amber-400">
                                    ⚠️ No tienes almacenes asignados. Contacta al administrador.
                                </p>
                            @else
                                <select wire:model="almacenDestinoId"
                                    class="w-full rounded-xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                                    @foreach($this->almacenes as $alm)
                                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        {{-- Folio --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Folio / Factura</label>
                            <input type="text" wire:model="folio" placeholder="Ej: FAC-2025-001"
                                class="w-full rounded-xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70
                                       text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                        </div>

                        {{-- Lote de Compra --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                Lote de compra
                            </label>
                            <div class="flex gap-2">
                                <select wire:model="loteCompraId"
                                    class="flex-1 rounded-xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                                    <option value="">— Sin lote —</option>
                                    @foreach($this->lotesCompras as $lc)
                                        <option value="{{ $lc->id }}">{{ $lc->nombre }}</option>
                                    @endforeach
                                </select>
                                <button wire:click="abrirModalLoteCompra" title="Nuevo Lote de Compra"
                                    class="rounded-xl px-3 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                                           text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-sm">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Notas --}}
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Notas</label>
                            <input type="text" wire:model="notasCompra" placeholder="Observaciones opcionales…"
                                class="w-full rounded-xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70
                                       text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                        </div>
                    </div>
                </div>

                {{-- ── PASO 3: Tabla de PIEZAS ─────────────────────── --}}
                @if(in_array($tipoCompra, ['piezas', 'todos']))
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔩</span>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Piezas del catálogo</h3>
                            <span class="text-xs text-slate-400 dark:text-slate-500">(restock)</span>
                        </div>
                        <button wire:click="agregarItemPieza"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold
                                   bg-blue-50 dark:bg-blue-900/20 border border-blue-300/60 dark:border-blue-600/40
                                   text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                            + Agregar pieza
                        </button>
                    </div>

                    <div class="px-4 py-3 space-y-2">
                        @foreach($itemsPiezas as $idx => $item)
                            <div class="flex flex-wrap gap-2 items-start bg-slate-50/80 dark:bg-slate-900/40
                                        rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-3">

                                {{-- Selector de pieza --}}
                                <div class="flex-1 min-w-[200px]">
                                    <select wire:model="itemsPiezas.{{ $idx }}.catalogo_pieza_id"
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-3 py-2">
                                        <option value="">— Selecciona pieza —</option>
                                        @foreach($this->catalogoPiezas as $pieza)
                                            <option value="{{ $pieza->id }}">
                                                [{{ $pieza->categoria }}] {{ $pieza->nombre }}
                                                {{ $pieza->especificacion ? '· '.$pieza->especificacion : '' }}
                                                · Stock: {{ (int)$pieza->stock_disponible }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Cantidad --}}
                                <div class="w-20">
                                    <input type="number" min="1" wire:model="itemsPiezas.{{ $idx }}.cantidad"
                                        placeholder="Cant."
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-center text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-2">
                                </div>

                                {{-- Precio unitario --}}
                                <div class="w-28">
                                    <input type="number" step="0.01" min="0" wire:model="itemsPiezas.{{ $idx }}.precio_unitario"
                                        placeholder="$ Precio"
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-3 py-2">
                                </div>

                                {{-- Notas --}}
                                <div class="flex-1 min-w-[100px]">
                                    <input type="text" wire:model="itemsPiezas.{{ $idx }}.notas"
                                        placeholder="Nota opcional…"
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-3 py-2">
                                </div>

                                {{-- Acciones --}}
                                <div class="flex items-center gap-1.5 pt-0.5">
                                    <button wire:click="abrirModalNuevaPieza({{ $idx }})" title="Crear nueva pieza en el catálogo"
                                        class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-300/50 dark:border-emerald-600/40
                                               text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition text-xs">
                                        ✨
                                    </button>
                                    @if(count($itemsPiezas) > 1)
                                        <button wire:click="removerItemPieza({{ $idx }})"
                                            class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-300/50 dark:border-red-600/40
                                                   text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition text-xs">
                                            🗑
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ── PASO 3.5: Tabla de CONSUMIBLES ─────────────────────── --}}
                @if(in_array($tipoCompra, ['consumibles', 'todos']))
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧽</span>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Consumibles</h3>
                        </div>
                        <button wire:click="agregarItemConsumible"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold
                                   bg-purple-50 dark:bg-purple-900/20 border border-purple-300/60 dark:border-purple-600/40
                                   text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition">
                            + Agregar consumible
                        </button>
                    </div>

                    <div class="px-4 py-3 space-y-2">
                        @foreach($itemsConsumibles as $idx => $item)
                            <div class="flex flex-wrap gap-2 items-start bg-slate-50/80 dark:bg-slate-900/40
                                        rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-3">

                                {{-- Selector de consumible --}}
                                <div class="flex-1 min-w-[200px]">
                                    <select wire:model="itemsConsumibles.{{ $idx }}.consumible_id"
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 px-3 py-2">
                                        <option value="">— Selecciona consumible —</option>
                                        @foreach($this->catalogoConsumibles as $cons)
                                            <option value="{{ $cons->id }}">
                                                [{{ $cons->categoria }}] {{ $cons->nombre }}
                                                · Stock: {{ (int)$cons->stock_disponible }} {{ $cons->unidad_medida }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Cantidad --}}
                                <div class="w-20">
                                    <input type="number" min="1" wire:model="itemsConsumibles.{{ $idx }}.cantidad"
                                        placeholder="Cant."
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-center text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 px-2 py-2">
                                </div>

                                {{-- Precio unitario --}}
                                <div class="w-28">
                                    <input type="number" step="0.01" min="0" wire:model="itemsConsumibles.{{ $idx }}.precio_unitario"
                                        placeholder="$ Precio"
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 px-3 py-2">
                                </div>

                                {{-- Notas --}}
                                <div class="flex-1 min-w-[100px]">
                                    <input type="text" wire:model="itemsConsumibles.{{ $idx }}.notas"
                                        placeholder="Nota opcional…"
                                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 px-3 py-2">
                                </div>

                                {{-- Acciones --}}
                                <div class="flex items-center gap-1.5 pt-0.5">
                                    @if(count($itemsConsumibles) > 1)
                                        <button wire:click="removerItemConsumible({{ $idx }})"
                                            class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-300/50 dark:border-red-600/40
                                                   text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition text-xs">
                                            🗑
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ── PASO 4: Tabla de CARGADORES ─────────────────── --}}
                @if(in_array($tipoCompra, ['cargadores', 'todos']))
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔌</span>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Cargadores</h3>
                            <span class="text-xs text-slate-400 dark:text-slate-500">(series automáticas o manuales)</span>
                        </div>
                        <button wire:click="agregarItemCargador"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold
                                   bg-orange-50 dark:bg-orange-900/20 border border-orange-300/60 dark:border-orange-600/40
                                   text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition">
                            + Agregar cargador
                        </button>
                    </div>

                    <div class="px-4 py-3 space-y-2">
                        @foreach($itemsCargadores as $idx => $carg)
                            <div class="flex flex-wrap gap-2 items-start bg-slate-50/80 dark:bg-slate-900/40
                                        rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-3">

                                <input type="text" wire:model="itemsCargadores.{{ $idx }}.marca"
                                    placeholder="Marca"
                                    class="w-28 rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-3 py-2">

                                <input type="text" wire:model="itemsCargadores.{{ $idx }}.voltaje"
                                    placeholder="Voltaje"
                                    class="w-24 rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-3 py-2">

                                <input type="text" wire:model="itemsCargadores.{{ $idx }}.amperaje"
                                    placeholder="Amperaje"
                                    class="w-24 rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-3 py-2">

                                <input type="text" wire:model="itemsCargadores.{{ $idx }}.punta"
                                    placeholder="Punta"
                                    class="w-24 rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-3 py-2">

                                <input type="number" min="1" wire:model="itemsCargadores.{{ $idx }}.cantidad"
                                    placeholder="Cant."
                                    class="w-20 rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                           text-sm text-center text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-2 py-2">

                                <input type="number" step="0.01" min="0" wire:model="itemsCargadores.{{ $idx }}.costo"
                                    placeholder="$ Costo"
                                    class="w-28 rounded-xl bg-white dark:bg-slate-900 border border-slate-300/70 dark:border-slate-700
                                           text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-3 py-2">

                                {{-- Acciones --}}
                                <div class="flex items-center gap-1.5 pt-0.5">
                                    <button wire:click="abrirModalSeriesCargador({{ $idx }})" title="Asignar series manualmente"
                                        class="px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-300/50 dark:border-blue-600/40
                                               text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition text-xs font-semibold">
                                        # Series
                                        @if(!empty($carg['numeros_serie']))
                                            <span class="ml-1 text-emerald-500">✓</span>
                                        @endif
                                    </button>
                                    @if(count($itemsCargadores) > 1)
                                        <button wire:click="removerItemCargador({{ $idx }})"
                                            class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-300/50 dark:border-red-600/40
                                                   text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition text-xs">
                                            🗑
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-4 pb-3">
                        <p class="text-xs text-slate-400 dark:text-slate-500">
                            💡 Si no asignas números de serie, el sistema los generará automáticamente con el formato <span class="font-mono">PROV{fecha}-{correlativo}</span>.
                        </p>
                    </div>
                </div>
                @endif

                {{-- ── Totales y Guardar ───────────────────────────── --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4
                            rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-5 py-4">
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Total estimado:
                        <span class="ml-1 text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($this->getTotalEstimado(), 2) }}
                        </span>
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="volver"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold
                                   bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                                   text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                            Cancelar
                        </button>
                        <button wire:click="guardarCompra" wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-r from-emerald-600 to-emerald-500 shadow-md shadow-emerald-800/40
                                   hover:shadow-emerald-500/60 hover:-translate-y-0.5 transition-all duration-200
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="guardarCompra">💾 Registrar Compra</span>
                            <span wire:loading wire:target="guardarCompra">Guardando…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-tb-background>


{{-- ════════════════════════════════════════════════════════ --}}
{{-- MODAL: SERIES DE CARGADORES                             --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($modalSeriesCargador && isset($itemsCargadores[$serialesCargadorIndex]))
    @php $carg = $itemsCargadores[$serialesCargadorIndex]; @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">Números de serie</h3>
                <button wire:click="cerrarModalSeriesCargador"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">✕</button>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Cargador {{ $carg['marca'] ?? '' }} {{ $carg['voltaje'] ?? '' }}V · {{ $carg['cantidad'] ?? 1 }} unidad(es).<br>
                Deja en blanco para que el sistema los genere automáticamente.
            </p>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($carg['numeros_serie'] ?? [] as $si => $serie)
                    <div class="flex gap-2">
                        <input type="text"
                            wire:model="itemsCargadores.{{ $serialesCargadorIndex }}.numeros_serie.{{ $si }}"
                            placeholder="Serie #{{ $si + 1 }} (opcional)"
                            class="flex-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                                   text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 px-3 py-2">
                        <button wire:click="quitarSerieModalCargador({{ $si }})"
                            class="p-2 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200/60 dark:border-red-800/40
                                   text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition text-xs">
                            🗑
                        </button>
                    </div>
                @endforeach
            </div>
            @if(count($carg['numeros_serie'] ?? []) < ($carg['cantidad'] ?? 1))
                <button wire:click="agregarSerieModalCargador"
                    class="w-full py-2 rounded-xl border border-dashed border-slate-300 dark:border-slate-700
                           text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    + Agregar serie
                </button>
            @endif
            <div class="flex justify-end pt-2">
                <button wire:click="cerrarModalSeriesCargador"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-r from-orange-500 to-orange-400 hover:opacity-90 transition">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- MODAL: NUEVO PROVEEDOR                                  --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($modalProveedor)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">Nuevo proveedor</h3>
                <button wire:click="cerrarModalProveedor"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">✕</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Empresa *</label>
                    <input type="text" wire:model="proveedorNombre" placeholder="Nombre de la empresa"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                    @error('proveedorNombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Abreviación *</label>
                    <input type="text" wire:model="proveedorAbreviacion" placeholder="Ej: PROV01"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                    @error('proveedorAbreviacion') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Email</label>
                    <input type="email" wire:model="proveedorEmail" placeholder="correo@proveedor.com"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Teléfono</label>
                    <input type="text" wire:model="proveedorTelefono" placeholder="55 1234 5678"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button wire:click="cerrarModalProveedor"
                    class="px-4 py-2 rounded-xl text-sm border border-slate-300/70 dark:border-slate-700
                           text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    Cancelar
                </button>
                <button wire:click="guardarProveedor"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 transition">
                    Guardar
                </button>
            </div>
        </div>
    </div>
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- MODAL: LOTE COMPRA                                      --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($modalLoteCompra)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">Nuevo Lote de Compra</h3>
                <button wire:click="cerrarModalLoteCompra"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">✕</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Nombre del Lote *</label>
                    <input type="text" wire:model="loteCompraNombre" placeholder="Ej: LOTE-AGO-001"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5">
                    @error('loteCompraNombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Descripción</label>
                    <textarea wire:model="loteCompraDescripcion" placeholder="Importación de China #3..."
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 px-3 py-2.5"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button wire:click="cerrarModalLoteCompra"
                    class="px-4 py-2 rounded-xl text-sm border border-slate-300/70 dark:border-slate-700
                           text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    Cancelar
                </button>
                <button wire:click="guardarLoteCompra"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 transition">
                    Guardar
                </button>
            </div>
        </div>
    </div>
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- MODAL: NUEVA PIEZA EN CATÁLOGO                          --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($modalNuevaPieza)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">✨ Nueva pieza en catálogo</h3>
                <button wire:click="cerrarModalNuevaPieza"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">✕</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Nombre *</label>
                    <input type="text" wire:model="nuevaPiezaNombre" placeholder="Ej: RAM DDR5 16GB"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-3 py-2.5">
                    @error('nuevaPiezaNombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Categoría *</label>
                    <select wire:model="nuevaPiezaCategoria"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-3 py-2.5">
                        <option value="">— Selecciona —</option>
                        @foreach($this->getCategoriasPiezas() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('nuevaPiezaCategoria') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Especificación</label>
                    <input type="text" wire:model="nuevaPiezaEspecificacion" placeholder="Ej: 3200MHz, M.2 NVMe…"
                        class="mt-1 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-300/70 dark:border-slate-700
                               text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 px-3 py-2.5">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" wire:model="nuevaPiezaRequiereSerie" id="cb-serie"
                        class="rounded text-blue-600 focus:ring-blue-500">
                    <label for="cb-serie" class="text-sm text-slate-600 dark:text-slate-300">
                        Requiere número de serie
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button wire:click="cerrarModalNuevaPieza"
                    class="px-4 py-2 rounded-xl text-sm border border-slate-300/70 dark:border-slate-700
                           text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    Cancelar
                </button>
                <button wire:click="guardarNuevaPieza"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-r from-blue-600 to-blue-500 hover:opacity-90 transition">
                    Crear y seleccionar
                </button>
            </div>
        </div>
    </div>
@endif

</div>

