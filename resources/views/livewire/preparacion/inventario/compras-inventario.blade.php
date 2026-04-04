<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')
            <x-topbar
                title="Compras de Inventario"
                chip="Preparación · Inventario"
                description="Registro de compras de piezas con trazabilidad de proveedor."
            >
                <x-slot name="right">
                    <button wire:click="nuevaCompra"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                               bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                               text-white text-xs font-semibold shadow-md shadow-blue-800/40
                               hover:shadow-blue-500/60 hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Registrar compra
                    </button>
                </x-slot>
            </x-topbar>
        @else
            <x-topbar
                title="Nueva Compra"
                chip="Compras de Inventario"
                description="Registra una compra de piezas con su proveedor y detalle."
            >
                <x-slot name="right">
                    <button wire:click="volver"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Volver
                    </button>
                </x-slot>
            </x-topbar>
        @endif


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA LISTA                                               --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')

            {{-- Filtros --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-5 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input type="text" wire:model.live.debounce.400ms="busqueda"
                            placeholder="Buscar por proveedor o folio..."
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-2xl
                                   bg-white/80 dark:bg-slate-900/60
                                   border border-white/60 dark:border-slate-700/70
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-blue-500/70 backdrop-blur-xl">
                    </div>
                    <select wire:model.live="filtroProveedor"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70 px-3 py-2.5">
                        <option value="">Todos los proveedores</option>
                        @foreach($this->proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tabla de compras --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md overflow-hidden">

                <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Historial de compras</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $this->compras->total() }} registro(s)</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-slate-100 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800/80">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Fecha</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Proveedor</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Folio</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Lote</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Tipos de pieza</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->compras as $compra)
                                <tr class="border-b border-slate-200 dark:border-slate-800/80
                                           hover:bg-white/60 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $compra->fecha_compra->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $compra->proveedor->nombre_empresa }}</p>
                                        @if($compra->proveedor->abreviacion)
                                            <p class="text-xs text-slate-400">{{ $compra->proveedor->abreviacion }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">
                                        {{ $compra->folio ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $compra->lote?->nombre ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300
                                                     border border-indigo-300/50">
                                            {{ $compra->items_count }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $compra->registradoPor?->nombre ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                                        No hay compras registradas.
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

        @else
        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA NUEVA COMPRA                                        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

            @if($error)
                <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                    {{ $error }}
                </div>
            @endif

            {{-- Cabecera de la compra --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-6 py-6 space-y-5">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Datos de la compra</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Proveedor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Proveedor <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="proveedorId"
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-[#3B82F6] outline-none">
                            <option value="">Selecciona un proveedor...</option>
                            @foreach($this->proveedores as $prov)
                                <option value="{{ $prov->id }}">
                                    {{ $prov->nombre_empresa }}
                                    @if($prov->abreviacion) ({{ $prov->abreviacion }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('proveedorId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Fecha --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Fecha de compra <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model="fechaCompra"
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-[#3B82F6] outline-none">
                        @error('fechaCompra') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Folio --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Folio / Factura <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <input type="text" wire:model="folio"
                            placeholder="Ej: F-00123, R-456..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#3B82F6] outline-none">
                    </div>

                    {{-- Lote --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Lote de equipos <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <select wire:model="loteId"
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-[#3B82F6] outline-none">
                            <option value="">Sin lote — compra general</option>
                            @foreach($this->lotes as $lote)
                                <option value="{{ $lote->id }}">{{ $lote->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Notas --}}
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Notas generales <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <textarea wire:model="notasCompra" rows="2"
                            placeholder="Condiciones de compra, observaciones..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#3B82F6] outline-none resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Ítems de la compra --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-6 py-6 space-y-4">

                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Piezas compradas
                    </h3>
                    <button wire:click="agregarItem" type="button"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                               bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-300/60 dark:border-emerald-600/40
                               text-xs font-semibold text-emerald-700 dark:text-emerald-300
                               hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition">
                        + Agregar pieza
                    </button>
                </div>

                @error('items') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror

                @if(empty($items))
                    <div class="rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 py-10 text-center">
                        <p class="text-sm text-slate-400">Usa "+ Agregar pieza" para añadir los artículos de esta compra.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($items as $i => $item)
                            <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                        bg-slate-50/80 dark:bg-slate-900/40 px-4 py-4">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">

                                    {{-- Tipo de pieza (col 5) --}}
                                    <div class="sm:col-span-5 space-y-1">
                                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tipo de pieza *</label>
                                        <select wire:model="items.{{ $i }}.catalogo_pieza_id"
                                            class="w-full rounded-lg px-3 py-2 text-sm
                                                   bg-white dark:bg-slate-800
                                                   border border-slate-300 dark:border-slate-600
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-blue-500 outline-none">
                                            <option value="">Selecciona pieza...</option>
                                            @foreach($this->catalogo as $pieza)
                                                <option value="{{ $pieza->id }}">
                                                    [{{ $pieza->categoria }}] {{ $pieza->nombre }}
                                                    @if($pieza->especificacion) — {{ $pieza->especificacion }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.{$i}.catalogo_pieza_id")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Cantidad (col 2) --}}
                                    <div class="sm:col-span-2 space-y-1">
                                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Cantidad *</label>
                                        <input type="number" min="1" wire:model="items.{{ $i }}.cantidad"
                                            class="w-full rounded-lg px-3 py-2 text-sm
                                                   bg-white dark:bg-slate-800
                                                   border border-slate-300 dark:border-slate-600
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-blue-500 outline-none">
                                        @error("items.{$i}.cantidad")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Precio unitario (col 2) --}}
                                    <div class="sm:col-span-2 space-y-1">
                                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Precio unit.</label>
                                        <input type="number" step="0.01" min="0" wire:model="items.{{ $i }}.precio_unitario"
                                            placeholder="0.00"
                                            class="w-full rounded-lg px-3 py-2 text-sm
                                                   bg-white dark:bg-slate-800
                                                   border border-slate-300 dark:border-slate-600
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>

                                    {{-- Almacén (col 2) --}}
                                    <div class="sm:col-span-2 space-y-1">
                                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Almacén *</label>
                                        <select wire:model="items.{{ $i }}.almacen_id"
                                            class="w-full rounded-lg px-3 py-2 text-sm
                                                   bg-white dark:bg-slate-800
                                                   border border-slate-300 dark:border-slate-600
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-blue-500 outline-none">
                                            @foreach($this->almacenes as $almacen)
                                                <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Quitar (col 1) --}}
                                    <div class="sm:col-span-1 flex items-end justify-end pb-0.5">
                                        <button type="button" wire:click="removerItem({{ $i }})"
                                            class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60 dark:border-rose-600/40
                                                   text-rose-500 dark:text-rose-400 hover:bg-rose-100 transition flex items-center justify-center text-sm">
                                            ✕
                                        </button>
                                    </div>

                                    {{-- Subtotal --}}
                                    @if(!empty($item['precio_unitario']) && is_numeric($item['precio_unitario']))
                                        <div class="sm:col-span-12">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Subtotal: <span class="font-semibold text-slate-700 dark:text-slate-300">
                                                    ${{ number_format((float)$item['precio_unitario'] * (int)($item['cantidad'] ?? 0), 2) }}
                                                </span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total estimado --}}
                    @php $total = $this->getTotalEstimado(); @endphp
                    @if($total > 0)
                        <div class="flex justify-end">
                            <div class="rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800
                                        px-5 py-3 text-right">
                                <p class="text-xs text-indigo-600 dark:text-indigo-400 uppercase tracking-wide font-semibold">Total estimado</p>
                                <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">${{ number_format($total, 2) }}</p>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                    <button wire:click="volver"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        Cancelar
                    </button>
                    <button wire:click="guardarCompra"
                        wire:loading.attr="disabled" wire:target="guardarCompra"
                        class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold
                               bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                               text-white shadow-md shadow-blue-800/40
                               hover:shadow-blue-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="guardarCompra">Registrar compra</span>
                        <span wire:loading wire:target="guardarCompra">Guardando...</span>
                    </button>
                </div>
            </div>

        @endif

    </div>
</x-tb-background>
</div>
