<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')
            <x-topbar
                title="Catálogo de Piezas"
                chip="Preparación · Inventario"
                description="Administra el catálogo y el stock de piezas disponibles."
            >
                <x-slot name="right">
                    <div class="flex items-center gap-2">
                        <button wire:click="irADeshueso()"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-white/80 dark:bg-slate-900/60 border border-purple-300/70 dark:border-purple-600/50
                                   text-purple-700 dark:text-purple-300 text-xs font-semibold
                                   hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:-translate-y-0.5 transition-all duration-200">
                            🔩 Registrar deshueso
                        </button>
                        <button wire:click="irACompra()"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-white/80 dark:bg-slate-900/60 border border-emerald-300/70 dark:border-emerald-600/50
                                   text-emerald-700 dark:text-emerald-300 text-xs font-semibold
                                   hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:-translate-y-0.5 transition-all duration-200">
                            🛒 Registrar compra
                        </button>
                        <button wire:click="nuevaPieza"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                   text-white text-xs font-semibold shadow-md shadow-blue-800/40
                                   hover:shadow-blue-500/60 hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nueva pieza
                        </button>
                    </div>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'form')
            <x-topbar
                title="{{ $editandoId ? 'Editar pieza' : 'Nueva pieza' }}"
                chip="Catálogo de Piezas"
                description="{{ $editandoId ? 'Modifica los datos de la pieza.' : 'Agrega una nueva pieza al catálogo.' }}"
            >
                <x-slot name="right">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Volver
                    </button>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'inventario')
            <x-topbar
                title="{{ $this->piezaActual?->nombre ?? 'Inventario' }}"
                chip="{{ $this->piezaActual?->categoria ?? '' }}"
                description="{{ $this->piezaActual?->stock_disponible ?? 0 }} unidad(es) disponibles en almacén."
            >
                <x-slot name="right">
                    <div class="flex items-center gap-2">
                        <button wire:click="irADeshueso({{ $piezaInventarioId }})"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-white/80 dark:bg-slate-900/60 border border-purple-300/70 dark:border-purple-600/50
                                   text-purple-700 dark:text-purple-300 text-xs font-semibold
                                   hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:-translate-y-0.5 transition-all duration-200">
                            🔩 Registrar deshueso
                        </button>
                        <button wire:click="irACompra({{ $piezaInventarioId }})"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-gradient-to-r from-emerald-600 to-emerald-500
                                   text-white text-xs font-semibold shadow-md shadow-emerald-800/40
                                   hover:shadow-emerald-500/60 hover:-translate-y-0.5 transition-all duration-200">
                            🛒 Comprar más
                        </button>
                        <button wire:click="volverALista"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                                   bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                                   text-xs font-medium text-slate-700 dark:text-slate-200
                                   hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                            ← Catálogo
                        </button>
                    </div>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'compra')
            <x-topbar
                title="Registrar compra de piezas"
                chip="Inventario · Compras"
                description="Registra una compra a proveedor. Puedes incluir varias piezas en la misma orden."
            >
                <x-slot name="right">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Catálogo
                    </button>
                </x-slot>
            </x-topbar>

        @elseif($vista === 'deshueso')
            <x-topbar
                title="Registrar deshueso de equipo"
                chip="Inventario · Deshueso"
                description="Extrae piezas de un equipo para agregarlas al inventario disponible."
            >
                <x-slot name="right">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                               bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                               text-xs font-medium text-slate-700 dark:text-slate-200
                               hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        ← Catálogo
                    </button>
                </x-slot>
            </x-topbar>
        @endif


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 1: LISTA                                            --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($vista === 'lista')

            {{-- Métricas rápidas --}}
            @php
                $totalPiezas     = \App\Models\CatalogoPieza::count();
                $conStock        = \App\Models\CatalogoPieza::whereHas('inventarioDisponible')->count();
                $totalUnidades   = (int) \App\Models\InventarioPieza::sum('cantidad_disponible');
                $sinStock        = $totalPiezas - $conStock;
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-sky-500/20 dark:hover:shadow-sky-500/25">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Total piezas</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalPiezas }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-emerald-500/40">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">Con stock</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">{{ $conStock }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-amber-500/40">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">Sin stock</p>
                    <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">{{ $sinStock }}</p>
                </div>
                <div class="rounded-2xl bg-indigo-50/90 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-500/70
                            backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1
                            hover:shadow-lg hover:shadow-indigo-500/40">
                    <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">Unidades disponibles</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">{{ $totalUnidades }}</p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-5 py-4
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-indigo-500/25
                        hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Búsqueda --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input type="text" wire:model.live.debounce.400ms="busqueda"
                            placeholder="Buscar pieza..."
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-2xl
                                   bg-white/80 dark:bg-slate-900/60
                                   border border-white/60 dark:border-slate-700/70
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-blue-500/70 backdrop-blur-xl">
                    </div>

                    {{-- Categoría --}}
                    <select wire:model.live="filtroCategoria"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70 px-3 py-2.5">
                        <option value="">Todas las categorías</option>
                        @foreach(\App\Livewire\Preparacion\Inventario\CatalogoPiezas::CATEGORIAS as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>

                    {{-- Stock --}}
                    <select wire:model.live="filtroStock"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70 px-3 py-2.5">
                        <option value="">Todas</option>
                        <option value="con_stock">Con stock</option>
                        <option value="sin_stock">Sin stock</option>
                    </select>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md overflow-hidden
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-indigo-500/25
                        hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50">

                <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Piezas registradas</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $this->piezas->total() }} resultado(s)
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-slate-100 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800/80">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Pieza</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Categoría</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Requiere serie</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Stock disponible</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Estatus</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->piezas as $pieza)
                                <tr class="border-b border-slate-200 dark:border-slate-800/80
                                           hover:bg-white/60 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $pieza->nombre }}</p>
                                        @if($pieza->descripcion)
                                            <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($pieza->descripcion, 50) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300
                                                     border border-indigo-300/50">
                                            {{ $pieza->categoria ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($pieza->requiere_serie)
                                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">✓ Sí</span>
                                        @else
                                            <span class="text-xs text-slate-400">No</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-lg font-bold
                                                     {{ ($pieza->stock_disponible ?? 0) > 0
                                                         ? 'text-emerald-600 dark:text-emerald-400'
                                                         : 'text-rose-500 dark:text-rose-400' }}">
                                            {{ $pieza->stock_disponible ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <button wire:click="toggleActivo({{ $pieza->id }})"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border cursor-pointer transition
                                                   {{ $pieza->activo
                                                       ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-300/50'
                                                       : 'bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-slate-300/50' }}">
                                            {{ $pieza->activo ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center gap-2 justify-end">
                                            <button wire:click="verInventario({{ $pieza->id }})"
                                                class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5
                                                       bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                                                       text-xs font-medium text-slate-700 dark:text-slate-200
                                                       hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                                📦 Inventario
                                            </button>
                                            <button wire:click="editarPieza({{ $pieza->id }})"
                                                class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5
                                                       bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                                                       text-xs font-medium text-slate-700 dark:text-slate-200
                                                       hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                                ✏️ Editar
                                            </button>
                                            <button wire:click="abrirEliminarCatalogo({{ $pieza->id }})"
                                                class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5
                                                       bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60 dark:border-rose-600/40
                                                       text-xs font-medium text-rose-600 dark:text-rose-300
                                                       hover:bg-rose-100 dark:hover:bg-rose-900/40 transition">
                                                🗑
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                        No se encontraron piezas con los filtros actuales.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/40">
                    {{ $this->piezas->links() }}
                </div>
            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 2: FORM CATÁLOGO                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'form')

            <div class="max-w-2xl mx-auto">
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-6 py-6 space-y-5">

                    @if($error)
                        <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                                    px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                            {{ $error }}
                        </div>
                    @endif

                    {{-- Nombre --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Nombre <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model.live.debounce.400ms="nombre"
                            placeholder="Ej. RAM DDR4 16GB, SSD 512GB M.2..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                        @error('nombre') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Categoría --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Categoría <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.defer="categoria"
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                            <option value="">Selecciona una categoría...</option>
                            @foreach(\App\Livewire\Preparacion\Inventario\CatalogoPiezas::CATEGORIAS as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('categoria') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Alerta: piezas similares (anti-redundancia) --}}
                    @if($this->piezasSimilares->isNotEmpty())
                        <div class="rounded-xl border border-amber-300/60 bg-amber-50/80 dark:bg-amber-950/30 px-4 py-3">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-2">
                                ⚠ Ya existen piezas con nombre similar. ¿Es una de estas?
                            </p>
                            <ul class="space-y-1">
                                @foreach($this->piezasSimilares as $similar)
                                    <li class="flex items-center justify-between gap-2">
                                        <span class="text-xs text-amber-800 dark:text-amber-300">
                                            <span class="font-semibold">[{{ $similar->categoria }}]</span>
                                            {{ $similar->nombre }}
                                            @if($similar->especificacion)
                                                <span class="text-amber-600">— {{ $similar->especificacion }}</span>
                                            @endif
                                        </span>
                                        <button wire:click="editarPieza({{ $similar->id }})" type="button"
                                            class="text-xs px-2 py-0.5 rounded-lg bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 hover:bg-amber-300 transition shrink-0">
                                            Editar esa
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Especificación --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Especificación <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <input type="text" wire:model.defer="especificacion"
                            placeholder="Ej: Para Lenovo, DDR4 2666MHz, 15.6&quot; FHD..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                        <p class="text-xs text-slate-400">Tag corto para diferenciar piezas del mismo tipo</p>
                    </div>

                    {{-- Notas de compatibilidad --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Compatibilidad / notas <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <textarea wire:model.defer="notasCompatibilidad" rows="2"
                            placeholder="Ej: ThinkPad E14 Gen2, IdeaPad 5, compatible con modelos 20Y7-20Y8..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none resize-none">
                        </textarea>
                    </div>

                    {{-- Descripción --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Descripción interna <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <textarea wire:model.defer="descripcion" rows="2"
                            placeholder="Notas internas, especificaciones adicionales..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none resize-none">
                        </textarea>
                    </div>

                    {{-- Opciones --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                    bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3
                                    flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Requiere número de serie</p>
                                <p class="text-xs text-slate-400 mt-0.5">Para SSD, RAM con serie, etc.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="requiereSerie" class="sr-only peer">
                                <div class="w-11 h-6 rounded-full bg-slate-300/70 dark:bg-white/15
                                            peer-checked:bg-indigo-500/80 transition"></div>
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow
                                            transition peer-checked:translate-x-5"></div>
                            </label>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                    bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3
                                    flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Activo en catálogo</p>
                                <p class="text-xs text-slate-400 mt-0.5">Las inactivas no aparecen en solicitudes.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="activo" class="sr-only peer">
                                <div class="w-11 h-6 rounded-full bg-slate-300/70 dark:bg-white/15
                                            peer-checked:bg-indigo-500/80 transition"></div>
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow
                                            transition peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-between pt-2">
                        <button wire:click="volverALista"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium
                                   border border-slate-300/80 dark:border-slate-700
                                   bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                            Cancelar
                        </button>
                        <button wire:click="guardarPieza"
                            wire:loading.attr="disabled" wire:target="guardarPieza"
                            class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold
                                   bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                   text-white shadow-md shadow-blue-800/40
                                   hover:shadow-blue-500/60 hover:-translate-y-0.5
                                   disabled:opacity-60 transition-all duration-200">
                            <span wire:loading.remove wire:target="guardarPieza">
                                {{ $editandoId ? 'Guardar cambios' : 'Agregar al catálogo' }}
                            </span>
                            <span wire:loading wire:target="guardarPieza">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 3: INVENTARIO DE UNA PIEZA                          --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'inventario')
            @php $pieza = $this->piezaActual; @endphp

            {{-- Info de la pieza --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-5 py-4">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30
                                    flex items-center justify-center shrink-0">
                            <span class="text-xl">🔧</span>
                        </div>
                        <div>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-50">{{ $pieza?->nombre }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                             bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300
                                             border border-indigo-300/50">
                                    {{ $pieza?->categoria }}
                                </span>
                                @if($pieza?->requiere_serie)
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400">✓ Requiere serie</span>
                                @endif
                                @if($pieza?->descripcion)
                                    <span class="text-xs text-slate-400">{{ $pieza->descripcion }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Mini métricas --}}
                    <div class="flex items-center gap-4">
                        @php
                            $items       = $this->inventarioItems;
                            $disponibles = $items->sum('cantidad_disponible');
                            $reservadas  = $items->sum('cantidad_reservada');
                            $usadas      = $items->sum('cantidad_usada');
                        @endphp
                        <div class="text-center">
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $disponibles }}</p>
                            <p class="text-xs text-slate-400">Disponibles</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reservadas }}</p>
                            <p class="text-xs text-slate-400">Reservadas</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-slate-500 dark:text-slate-400">{{ $usadas }}</p>
                            <p class="text-xs text-slate-400">Usadas</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtro estatus --}}
            <div class="flex items-center gap-3 flex-wrap">
                @foreach(['' => 'Todas', 'DISPONIBLE' => 'Disponibles', 'AGOTADA' => 'Agotadas', 'DADA_DE_BAJA' => 'Dadas de baja'] as $val => $label)
                    <button wire:click="$set('filtroEstatus', '{{ $val }}')"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border transition
                               {{ $filtroEstatus === $val
                                   ? 'border-[#FF9521] bg-[#FF9521]/10 text-[#FF9521]'
                                   : 'border-slate-300/80 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300 hover:border-slate-400' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Tabla inventario --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md overflow-hidden">

                <div class="px-5 py-3 border-b border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Entradas de stock · {{ $this->inventarioItems->count() }}
                    </p>
                </div>

                @if($this->inventarioItems->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm text-slate-400 dark:text-slate-500">
                            No hay unidades registradas. Usa "Registrar entrada" para agregar.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-slate-100 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800/80">
                                <tr>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Origen / Proveedor</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Dispon.</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Reserv.</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Usadas</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Costo unit.</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Fecha</th>
                                    <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->inventarioItems as $item)
                                    <tr class="border-b border-slate-200 dark:border-slate-800/80
                                               hover:bg-white/60 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-5 py-3">
                                            <div class="flex items-start gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shrink-0
                                                             {{ $item->origen === 'COMPRA'
                                                                 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-300/50'
                                                                 : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-300/50' }}">
                                                    {{ $item->origen === 'COMPRA' ? '🛒' : '🔩' }}
                                                </span>
                                                <div>
                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $item->origen_descripcion }}</p>
                                                    @if($item->numero_serie)
                                                        <p class="text-xs font-mono text-slate-500">S/N: {{ $item->numero_serie }}</p>
                                                    @endif
                                                    @if($item->notas)
                                                        <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($item->notas, 40) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-lg font-bold {{ $item->cantidad_disponible > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                                {{ $item->cantidad_disponible }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-sm font-semibold {{ $item->cantidad_reservada > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}">
                                                {{ $item->cantidad_reservada }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-sm text-slate-500">{{ $item->cantidad_usada }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-slate-700 dark:text-slate-300 text-sm">
                                            {{ $item->costo ? '$'.number_format($item->costo, 2) : '—' }}
                                        </td>
                                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                            {{ $item->fecha_ingreso?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <div class="flex items-center gap-2 justify-end">
                                                @if($item->estatus === 'DISPONIBLE')
                                                    <button wire:click="cambiarEstatusInventario({{ $item->id }}, 'DADA_DE_BAJA')"
                                                        class="rounded-xl px-2.5 py-1 text-xs font-medium
                                                               bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60 dark:border-rose-600/40
                                                               text-rose-600 dark:text-rose-300 hover:bg-rose-100 transition">
                                                        Dar de baja
                                                    </button>
                                                @endif
                                                <button wire:click="abrirEliminarInventario({{ $item->id }})"
                                                    class="rounded-xl px-2.5 py-1 text-xs font-medium
                                                           bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60 dark:border-rose-600/40
                                                           text-rose-600 dark:text-rose-300 hover:bg-rose-100 transition">
                                                    🗑
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 4: REGISTRAR COMPRA                                 --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'compra')

            <div class="max-w-3xl mx-auto space-y-5">

                @if($error)
                    <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                                px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                        {{ $error }}
                    </div>
                @endif

                {{-- Datos de la compra --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                        Datos de la orden
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Proveedor --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Proveedor <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model.defer="compraProveedorId"
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                <option value="">Selecciona un proveedor...</option>
                                @foreach($this->proveedores as $prov)
                                    <option value="{{ $prov->id }}">
                                        {{ $prov->abreviacion ? "[{$prov->abreviacion}] " : '' }}{{ $prov->nombre_empresa }}
                                    </option>
                                @endforeach
                            </select>
                            @error('compraProveedorId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fecha --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Fecha de compra <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" wire:model.defer="compraFecha"
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            @error('compraFecha') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Folio --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Folio / Factura <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" wire:model.defer="compraFolio"
                                placeholder="Ej. FAC-2026-001, REMISION-123..."
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                        </div>

                        {{-- Lote --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Lote asociado <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <select wire:model.defer="compraLoteId"
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                <option value="">Sin lote</option>
                                @foreach($this->lotes as $lote)
                                    <option value="{{ $lote->id }}">#{{ $lote->id }} — {{ $lote->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Notas --}}
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Notas <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <textarea wire:model.defer="compraNotas" rows="2"
                                placeholder="Observaciones de la compra..."
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none resize-none"></textarea>
                        </div>

                    </div>
                </div>

                {{-- Items de la compra --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                            Piezas compradas
                        </h3>
                        <button wire:click="agregarItemCompra" type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                   bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-300/60 dark:border-emerald-600/40
                                   text-xs font-medium text-emerald-700 dark:text-emerald-300
                                   hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition">
                            + Agregar pieza
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach($compraItems as $i => $item)
                            <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                        bg-slate-50/60 dark:bg-slate-900/40 px-4 py-4">

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">

                                    {{-- Pieza --}}
                                    <div class="md:col-span-5 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pieza *</label>
                                        <select wire:model.defer="compraItems.{{ $i }}.catalogo_pieza_id"
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                            <option value="">Selecciona una pieza...</option>
                                            @foreach($this->catalogo as $cp)
                                                <option value="{{ $cp->id }}">
                                                    [{{ $cp->categoria }}] {{ $cp->nombre }}
                                                    {{ $cp->especificacion ? " — {$cp->especificacion}" : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("compraItems.{$i}.catalogo_pieza_id")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Cantidad --}}
                                    <div class="md:col-span-2 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cantidad *</label>
                                        <input type="number" min="1" wire:model.defer="compraItems.{{ $i }}.cantidad"
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                        @error("compraItems.{$i}.cantidad")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Precio unitario --}}
                                    <div class="md:col-span-3 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Precio unit.</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                                            <input type="number" step="0.01" min="0" wire:model.defer="compraItems.{{ $i }}.precio_unitario"
                                                placeholder="0.00"
                                                class="w-full pl-7 rounded-xl px-3 py-2 text-sm
                                                       bg-white/70 dark:bg-slate-900/60
                                                       border border-slate-300/80 dark:border-slate-700
                                                       text-slate-900 dark:text-slate-100
                                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                        </div>
                                    </div>

                                    {{-- Quitar --}}
                                    <div class="md:col-span-2 flex items-end justify-end">
                                        @if(count($compraItems) > 1)
                                            <button wire:click="removerItemCompra({{ $i }})" type="button"
                                                class="rounded-xl px-3 py-2 text-xs font-medium
                                                       bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60 dark:border-rose-600/40
                                                       text-rose-600 dark:text-rose-300 hover:bg-rose-100 transition">
                                                🗑 Quitar
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Notas del item --}}
                                    <div class="md:col-span-12 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notas del item</label>
                                        <input type="text" wire:model.defer="compraItems.{{ $i }}.notas"
                                            placeholder="Observaciones opcionales de esta pieza..."
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                                   focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total estimado --}}
                    <div class="flex items-center justify-end pt-2 border-t border-slate-200/60 dark:border-slate-800/80">
                        <div class="text-right">
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total estimado</p>
                            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                ${{ number_format($this->getTotalCompra(), 2) }}
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-between">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        Cancelar
                    </button>
                    <button wire:click="guardarCompra"
                        wire:loading.attr="disabled" wire:target="guardarCompra"
                        class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold
                               bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-500
                               text-white shadow-md shadow-emerald-800/40
                               hover:shadow-emerald-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="guardarCompra">Registrar compra</span>
                        <span wire:loading wire:target="guardarCompra">Guardando...</span>
                    </button>
                </div>

            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 5: REGISTRAR DESHUESO                               --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'deshueso')

            <div class="max-w-3xl mx-auto space-y-5">

                @if($error)
                    <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                                px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                        {{ $error }}
                    </div>
                @endif

                {{-- Equipo a deshuesar --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                        Equipo origen
                    </h3>

                    <div class="space-y-1.5" x-data>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Buscar equipo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model.live.debounce.300ms="deshuesoEquipoBusqueda"
                            placeholder="Buscar por número de serie o modelo..."
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:ring-2 focus:ring-purple-500/70 outline-none">

                        @if($this->equiposBusqueda->isNotEmpty())
                            <div class="mt-1 rounded-xl border border-slate-200 dark:border-slate-700
                                        bg-white dark:bg-slate-900 shadow-lg overflow-hidden">
                                @foreach($this->equiposBusqueda as $equipo)
                                    <button type="button" wire:click="seleccionarEquipoDeshueso({{ $equipo->id }})"
                                        class="w-full text-left px-4 py-2.5 text-sm
                                               hover:bg-slate-50 dark:hover:bg-slate-800 transition
                                               border-b border-slate-100 dark:border-slate-800 last:border-0">
                                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $equipo->numero_serie }}</span>
                                        <span class="text-slate-500 ml-2">{{ $equipo->marca }} {{ $equipo->modelo }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($deshuesoEquipoId)
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                                ✓ Equipo seleccionado — ID #{{ $deshuesoEquipoId }}
                            </p>
                        @endif

                        @error('deshuesoEquipoId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>

                {{-- Items del deshueso --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                            Piezas extraídas
                        </h3>
                        <button wire:click="agregarItemDeshueso" type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                   bg-purple-50 dark:bg-purple-900/20 border border-purple-300/60 dark:border-purple-600/40
                                   text-xs font-medium text-purple-700 dark:text-purple-300
                                   hover:bg-purple-100 dark:hover:bg-purple-900/40 transition">
                            + Agregar pieza
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach($deshuesoItems as $i => $item)
                            <div class="rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                        bg-slate-50/60 dark:bg-slate-900/40 px-4 py-4">

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">

                                    {{-- Pieza --}}
                                    <div class="md:col-span-5 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pieza *</label>
                                        <select wire:model.defer="deshuesoItems.{{ $i }}.catalogo_pieza_id"
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-purple-500/70 outline-none">
                                            <option value="">Selecciona una pieza...</option>
                                            @foreach($this->catalogo as $cp)
                                                <option value="{{ $cp->id }}">
                                                    [{{ $cp->categoria }}] {{ $cp->nombre }}
                                                    {{ $cp->especificacion ? " — {$cp->especificacion}" : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("deshuesoItems.{$i}.catalogo_pieza_id")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Cantidad --}}
                                    <div class="md:col-span-2 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cantidad *</label>
                                        <input type="number" min="1" wire:model.defer="deshuesoItems.{{ $i }}.cantidad"
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-purple-500/70 outline-none">
                                        @error("deshuesoItems.{$i}.cantidad")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Número de serie --}}
                                    <div class="md:col-span-3 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">No. Serie</label>
                                        <input type="text" wire:model.defer="deshuesoItems.{{ $i }}.numero_serie"
                                            placeholder="Opcional..."
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                                   focus:ring-2 focus:ring-purple-500/70 outline-none">
                                    </div>

                                    {{-- Quitar --}}
                                    <div class="md:col-span-2 flex items-end justify-end">
                                        @if(count($deshuesoItems) > 1)
                                            <button wire:click="removerItemDeshueso({{ $i }})" type="button"
                                                class="rounded-xl px-3 py-2 text-xs font-medium
                                                       bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60 dark:border-rose-600/40
                                                       text-rose-600 dark:text-rose-300 hover:bg-rose-100 transition">
                                                🗑 Quitar
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Notas del item --}}
                                    <div class="md:col-span-12 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notas</label>
                                        <input type="text" wire:model.defer="deshuesoItems.{{ $i }}.notas"
                                            placeholder="Observaciones opcionales..."
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                                   focus:ring-2 focus:ring-purple-500/70 outline-none">
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-between">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        Cancelar
                    </button>
                    <button wire:click="guardarDeshueso"
                        wire:loading.attr="disabled" wire:target="guardarDeshueso"
                        class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold
                               bg-gradient-to-r from-purple-700 via-purple-600 to-purple-500
                               text-white shadow-md shadow-purple-800/40
                               hover:shadow-purple-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="guardarDeshueso">Registrar deshueso</span>
                        <span wire:loading wire:target="guardarDeshueso">Guardando...</span>
                    </button>
                </div>

            </div>
        @endif


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- MODAL: CONFIRMAR ELIMINAR                                 --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($modalEliminar)
            <div class="fixed inset-0 z-50 flex items-center justify-center"
                 x-data @keydown.escape.window="$wire.cerrarModalEliminar()">
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                     wire:click="cerrarModalEliminar"></div>

                <div class="relative w-[92%] max-w-sm rounded-2xl border border-white/10
                            bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                            shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-4">

                    <h4 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                        ¿Confirmar eliminación?
                    </h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @if($tipoEliminar === 'catalogo')
                            Se eliminará la pieza del catálogo y todo su historial de inventario.
                        @else
                            Se eliminará esta entrada del inventario.
                        @endif
                        Esta acción no se puede deshacer.
                    </p>

                    <div class="flex items-center justify-between pt-1">
                        <button wire:click="cerrarModalEliminar"
                            class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                                   border border-slate-300/80 dark:border-slate-700
                                   bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        <button wire:click="confirmarEliminar"
                            class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-semibold
                                   bg-rose-600 hover:bg-rose-500 text-white
                                   shadow-md shadow-rose-800/40 hover:shadow-rose-500/60
                                   hover:-translate-y-0.5 transition-all duration-200">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-tb-background>
</div>
