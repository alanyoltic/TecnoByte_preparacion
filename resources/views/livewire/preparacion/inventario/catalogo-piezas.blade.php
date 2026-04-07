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
                        <button wire:click="irADeshueso"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-white/80 dark:bg-slate-900/60 border border-purple-300/70 dark:border-purple-600/50
                                   text-purple-700 dark:text-purple-300 text-xs font-semibold
                                   hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:-translate-y-0.5 transition-all duration-200">
                            🔩 Registrar despiece
                        </button>
                        <button wire:click="irACompra"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-gradient-to-r from-emerald-600 to-emerald-500
                                   text-white text-xs font-semibold shadow-md shadow-emerald-800/40
                                   hover:shadow-emerald-500/60 hover:-translate-y-0.5 transition-all duration-200">
                            🛒 Registrar compra
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
                        <button wire:click="irADeshueso"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-white/80 dark:bg-slate-900/60 border border-purple-300/70 dark:border-purple-600/50
                                   text-purple-700 dark:text-purple-300 text-xs font-semibold
                                   hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:-translate-y-0.5 transition-all duration-200">
                            🔩 Registrar despiece
                        </button>
                        <button wire:click="irACompra"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                                   bg-gradient-to-r from-emerald-600 to-emerald-500
                                   text-white text-xs font-semibold shadow-md shadow-emerald-800/40
                                   hover:shadow-emerald-500/60 hover:-translate-y-0.5 transition-all duration-200">
                            🛒 Registrar compra
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
                title="Registrar despiece de equipo"
                chip="Inventario · Despiece"
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

        @elseif($vista === 'restock')
            <x-topbar
                title="Agregar stock · {{ $this->restockPieza?->nombre }}"
                chip="{{ $this->restockPieza?->categoria ?? 'Inventario' }}"
                description="Agrega unidades a una pieza ya existente en el catálogo."
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
                                            <button wire:click="iniciarRestock({{ $pieza->id }})"
                                                class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5
                                                       bg-white/80 dark:bg-slate-900/60 border border-emerald-300/70 dark:border-emerald-700/60
                                                       text-xs font-medium text-emerald-700 dark:text-emerald-300
                                                       hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition">
                                                + Restock
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
                        <select wire:model="categoria"
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
                        <input type="text" wire:model="especificacion"
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
                        <textarea wire:model="notasCompatibilidad" rows="2"
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
                        <textarea wire:model="descripcion" rows="2"
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
                            <div class="flex items-center gap-2">
                                <select wire:model="compraProveedorId"
                                    class="flex-1 rounded-xl px-4 py-2.5 text-sm
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
                                <button type="button" wire:click="abrirModalProveedor"
                                    title="Agregar nuevo proveedor"
                                    class="shrink-0 w-10 h-10 rounded-xl
                                           bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-300/60 dark:border-emerald-600/40
                                           text-emerald-700 dark:text-emerald-300 text-lg font-bold
                                           hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition flex items-center justify-center">
                                    +
                                </button>
                            </div>
                            @error('compraProveedorId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fecha compra --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Fecha de compra <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" wire:model="compraFecha"
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            @error('compraFecha') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fecha recibido --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Fecha de recibido <span class="text-slate-400 font-normal">(si difiere)</span>
                            </label>
                            <input type="date" wire:model="compraFechaRecibido"
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            <p class="text-[0.65rem] text-slate-400">Si se deja vacío se usa la fecha de compra.</p>
                        </div>

                        {{-- Folio --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Folio / Factura <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" wire:model="compraFolio"
                                placeholder="Ej. FAC-2026-001, REMISION-123..."
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                        </div>

                        {{-- Almacén destino --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Almacén destino <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="compraAlmacenId"
                                class="w-full rounded-xl px-4 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                <option value="">Selecciona un almacén...</option>
                                @foreach($this->almacenes as $almacen)
                                    <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                @endforeach
                            </select>
                            @error('compraAlmacenId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Lote --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Lote de compra <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            @if($compraLoteId && $this->lotes->firstWhere('id', $compraLoteId))
                                @php $loteSeleccionado = $this->lotes->firstWhere('id', $compraLoteId); @endphp
                                <div class="flex items-center gap-2 rounded-xl px-3 py-2.5
                                            bg-emerald-50 dark:bg-emerald-900/20
                                            border border-emerald-300/60 dark:border-emerald-600/40">
                                    <span class="text-emerald-500 text-xs">✓</span>
                                    <span class="flex-1 text-sm font-medium text-emerald-800 dark:text-emerald-200">
                                        {{ $loteSeleccionado->nombre_lote }}
                                    </span>
                                    <button wire:click="$set('compraLoteId', null)" type="button"
                                        class="text-xs text-emerald-600 dark:text-emerald-400 underline hover:text-emerald-800">
                                        Quitar
                                    </button>
                                </div>
                            @else
                                <button wire:click="abrirModalLote" type="button"
                                    class="w-full flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-dashed border-slate-300/80 dark:border-slate-600
                                           text-slate-500 dark:text-slate-400
                                           hover:border-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-400
                                           transition">
                                    <span class="text-base leading-none">+</span>
                                    <span>Crear nuevo lote para esta compra</span>
                                </button>
                                @error('compraProveedorId')
                                    {{-- silencioso, el error ya sale arriba --}}
                                @enderror
                            @endif
                        </div>

                        {{-- Notas --}}
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Notas <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <textarea wire:model="compraNotas" rows="2"
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
                                        bg-slate-50/60 dark:bg-slate-900/40 px-4 py-4"
                                 x-data="{
                                    open: false,
                                    selected: {{ !empty($item['catalogo_pieza_id_sel']) ? 'true' : 'false' }},
                                    query: @entangle('compraItems.' . $i . '.nombre'),
                                    catalogo: @js($this->catalogo->values()),
                                    get sugerencias() {
                                        if (this.selected || this.query.length < 2) return [];
                                        const q = this.query.toLowerCase();
                                        return this.catalogo.filter(p => p.nombre.toLowerCase().includes(q)).slice(0, 6);
                                    },
                                    seleccionar(p) {
                                        this.query = p.nombre;
                                        $wire.set(@js('compraItems.' . $i . '.nombre'), p.nombre);
                                        $wire.set(@js('compraItems.' . $i . '.categoria'), p.categoria);
                                        $wire.set(@js('compraItems.' . $i . '.catalogo_pieza_id_sel'), p.id);
                                        this.selected = true;
                                        this.open = false;
                                    },
                                    cambiar() {
                                        this.selected = false;
                                        this.query = '';
                                        $wire.set(@js('compraItems.' . $i . '.nombre'), '');
                                        $wire.set(@js('compraItems.' . $i . '.catalogo_pieza_id_sel'), null);
                                    }
                                 }"
                                 @click.outside="open = false">

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">

                                    {{-- Nombre con autocomplete --}}
                                    <div class="md:col-span-5 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Nombre de la pieza *
                                        </label>
                                        {{-- Estado bloqueado --}}
                                        <div x-show="selected"
                                             class="flex items-center gap-2 rounded-xl px-3 py-2
                                                    bg-emerald-50 dark:bg-emerald-900/20
                                                    border border-emerald-300/60 dark:border-emerald-600/40">
                                            <span class="text-emerald-500 text-xs shrink-0">✓</span>
                                            <span class="flex-1 text-sm font-medium text-emerald-800 dark:text-emerald-200 truncate" x-text="query"></span>
                                            <button @click="cambiar()" type="button"
                                                class="text-xs text-emerald-600 dark:text-emerald-400 underline hover:text-emerald-800 shrink-0">
                                                Cambiar
                                            </button>
                                        </div>
                                        {{-- Estado de búsqueda --}}
                                        <div x-show="!selected" class="relative">
                                            <input type="text"
                                                x-model="query"
                                                @input="open = sugerencias.length > 0"
                                                @focus="open = sugerencias.length > 0"
                                                placeholder="Ej. Memoria RAM DDR4 8GB Kingston..."
                                                class="w-full rounded-xl px-3 py-2 text-sm
                                                       bg-white/70 dark:bg-slate-900/60
                                                       border border-slate-300/80 dark:border-slate-700
                                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                            <ul x-show="open" x-cloak
                                                class="absolute z-30 mt-1 w-full rounded-xl shadow-lg
                                                       bg-white dark:bg-slate-900
                                                       border border-slate-200 dark:border-slate-700
                                                       max-h-48 overflow-y-auto text-sm">
                                                <template x-for="p in sugerencias" :key="p.id">
                                                    <li @click="seleccionar(p)"
                                                        class="px-3 py-2 cursor-pointer
                                                               hover:bg-emerald-50 dark:hover:bg-emerald-900/20
                                                               border-b border-slate-100 dark:border-slate-800 last:border-0">
                                                        <span class="font-medium text-slate-800 dark:text-slate-200" x-text="p.nombre"></span>
                                                        <span class="ml-1 text-xs text-slate-400" x-text="'[' + p.categoria + ']'"></span>
                                                        <span class="ml-2 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">+ agregar stock</span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                        <p x-show="!selected && query.length >= 3 && sugerencias.length === 0"
                                           class="text-xs text-amber-600 dark:text-amber-400">
                                            ⚠ Se creará nueva pieza en el catálogo
                                        </p>
                                        @error("compraItems.{$i}.nombre")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Categoría --}}
                                    <div class="md:col-span-3 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría *</label>
                                        <select wire:model="compraItems.{{ $i }}.categoria"
                                            :disabled="selected"
                                            :class="selected ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white/70 dark:bg-slate-900/60'"
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                            <option value="">Categoría...</option>
                                            @foreach(\App\Livewire\Preparacion\Inventario\CatalogoPiezas::CATEGORIAS as $cat)
                                                <option value="{{ $cat }}">{{ $cat }}</option>
                                            @endforeach
                                        </select>
                                        @error("compraItems.{$i}.categoria")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Cantidad --}}
                                    <div class="md:col-span-2 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cantidad *</label>
                                        <input type="number" min="1" wire:model="compraItems.{{ $i }}.cantidad"
                                            class="w-full rounded-xl px-3 py-2 text-sm
                                                   bg-white/70 dark:bg-slate-900/60
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                        @error("compraItems.{$i}.cantidad")
                                            <p class="text-xs text-rose-500">{{ $message }}</p>
                                        @enderror
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

                                    {{-- Precio + Notas --}}
                                    <div class="md:col-span-6 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Precio unitario</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                                            <input type="number" step="0.01" min="0" wire:model="compraItems.{{ $i }}.precio_unitario"
                                                placeholder="0.00"
                                                class="w-full pl-7 rounded-xl px-3 py-2 text-sm
                                                       bg-white/70 dark:bg-slate-900/60
                                                       border border-slate-300/80 dark:border-slate-700
                                                       text-slate-900 dark:text-slate-100
                                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                        </div>
                                    </div>

                                    <div class="md:col-span-6 space-y-1">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notas</label>
                                        <input type="text" wire:model="compraItems.{{ $i }}.notas"
                                            placeholder="Condición, marca, detalles adicionales..."
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

                {{-- ── Paso 1: Seleccionar equipo ── --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                        1 · Equipo origen del deshueso
                    </h3>

                    <div class="space-y-1.5">
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
                                @foreach($this->equiposBusqueda as $eq)
                                    <button type="button" wire:click="seleccionarEquipoDeshueso({{ $eq->id }})"
                                        class="w-full text-left px-4 py-2.5 text-sm
                                               hover:bg-slate-50 dark:hover:bg-slate-800 transition
                                               border-b border-slate-100 dark:border-slate-800 last:border-0">
                                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $eq->numero_serie }}</span>
                                        <span class="text-slate-500 ml-2">{{ $eq->marca }} {{ $eq->modelo }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($deshuesoEquipoId)
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                ✓ {{ $deshuesoEquipoBusqueda }}
                            </p>
                        @endif
                    </div>

                    {{-- Almacén destino --}}
                    @if($deshuesoEquipoId)
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Almacén destino <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="deshuesoAlmacenId"
                            class="w-full rounded-xl px-4 py-2.5 text-sm
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-purple-500/70 outline-none">
                            @foreach($this->almacenes as $alm)
                                <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400">Almacén al que van las piezas extraídas.</p>
                    </div>
                    @endif
                </div>

                {{-- ── Paso 2A: Piezas ya registradas en este equipo ── --}}
                @if($deshuesoEquipoId)
                    @php $piezasRegistradas = $this->piezasEquipoOrigen; @endphp

                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-6 py-5 space-y-3">

                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                            2A · Piezas registradas en este equipo
                            <span class="ml-1 text-xs font-normal text-slate-400 normal-case">
                                (piezas que el sistema ya tiene aplicadas aquí)
                            </span>
                        </h3>

                        @if($piezasRegistradas->isEmpty())
                            <p class="text-sm text-slate-400 dark:text-slate-500 py-4 text-center">
                                No hay piezas registradas aplicadas a este equipo en el sistema.
                            </p>
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Selecciona las piezas que vas a extraer para devolverlas al inventario disponible.
                            </p>
                            <div class="space-y-2">
                                @foreach($piezasRegistradas as $pr)
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200/80 dark:border-slate-700/60
                                                  bg-slate-50/60 dark:bg-slate-900/40 px-4 py-3 cursor-pointer
                                                  hover:bg-purple-50/60 dark:hover:bg-purple-900/10 transition group">
                                        <input type="checkbox"
                                            wire:model="deshuesoRecuperar"
                                            value="{{ $pr->id }}"
                                            class="w-4 h-4 rounded text-purple-600 border-slate-400 focus:ring-purple-500">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                {{ $pr->catalogoPieza?->nombre ?? '—' }}
                                                @if($pr->catalogoPieza?->categoria)
                                                    <span class="text-xs font-normal text-indigo-600 dark:text-indigo-400 ml-1">
                                                        [{{ $pr->catalogoPieza->categoria }}]
                                                    </span>
                                                @endif
                                            </p>
                                            <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-400">
                                                @if($pr->numero_serie)
                                                    <span>S/N: {{ $pr->numero_serie }}</span>
                                                @endif
                                                <span>Almacén: {{ $pr->almacen?->nombre ?? '—' }}</span>
                                                <span class="font-mono">Entrada #{{ $pr->id }}</span>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                     bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300
                                                     border border-purple-300/50 shrink-0">
                                            {{ $pr->origen }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @if(!empty($deshuesoRecuperar))
                                <p class="text-xs text-purple-600 dark:text-purple-400 font-semibold">
                                    ✓ {{ count($deshuesoRecuperar) }} pieza(s) seleccionada(s) para recuperar.
                                </p>
                            @endif
                        @endif
                    </div>

                    {{-- ── Paso 2B: Piezas físicas no registradas ── --}}
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                                    2B · Piezas físicas no registradas
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Piezas que físicamente están en el equipo pero no están en el sistema. Se agregarán como nuevas entradas de inventario.
                                </p>
                            </div>
                            <button wire:click="agregarItemDeshueso" type="button"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5
                                       bg-purple-50 dark:bg-purple-900/20 border border-purple-300/60 dark:border-purple-600/40
                                       text-xs font-semibold text-purple-700 dark:text-purple-300
                                       hover:bg-purple-100 dark:hover:bg-purple-900/40 transition">
                                + Agregar pieza
                            </button>
                        </div>

                        @if(!empty($deshuesoItems))
                            <div class="space-y-3">
                                @foreach($deshuesoItems as $i => $item)
                                    <div class="rounded-xl border border-purple-200/60 dark:border-purple-700/40
                                                bg-purple-50/40 dark:bg-purple-900/10 px-4 py-4"
                                         x-data="{
                                            open: false,
                                            selected: {{ !empty($item['catalogo_pieza_id_sel']) ? 'true' : 'false' }},
                                            query: @entangle('deshuesoItems.' . $i . '.nombre'),
                                            catalogo: @js($this->catalogo->values()),
                                            get sugerencias() {
                                                if (this.selected || this.query.length < 2) return [];
                                                const q = this.query.toLowerCase();
                                                return this.catalogo.filter(p => p.nombre.toLowerCase().includes(q)).slice(0, 6);
                                            },
                                            seleccionar(p) {
                                                this.query = p.nombre;
                                                $wire.set(@js('deshuesoItems.' . $i . '.nombre'), p.nombre);
                                                $wire.set(@js('deshuesoItems.' . $i . '.categoria'), p.categoria);
                                                $wire.set(@js('deshuesoItems.' . $i . '.catalogo_pieza_id_sel'), p.id);
                                                this.selected = true;
                                                this.open = false;
                                            },
                                            cambiar() {
                                                this.selected = false;
                                                this.query = '';
                                                $wire.set(@js('deshuesoItems.' . $i . '.nombre'), '');
                                                $wire.set(@js('deshuesoItems.' . $i . '.catalogo_pieza_id_sel'), null);
                                            }
                                         }"
                                         @click.outside="open = false">

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">

                                            {{-- Nombre con autocomplete --}}
                                            <div class="md:col-span-5 space-y-1">
                                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Nombre de la pieza <span class="text-rose-500">*</span>
                                                </label>
                                                {{-- Estado bloqueado --}}
                                                <div x-show="selected"
                                                     class="flex items-center gap-2 rounded-xl px-3 py-2
                                                            bg-purple-50 dark:bg-purple-900/20
                                                            border border-purple-300/60 dark:border-purple-600/40">
                                                    <span class="text-purple-500 text-xs shrink-0">✓</span>
                                                    <span class="flex-1 text-sm font-medium text-purple-800 dark:text-purple-200 truncate" x-text="query"></span>
                                                    <button @click="cambiar()" type="button"
                                                        class="text-xs text-purple-600 dark:text-purple-400 underline hover:text-purple-800 shrink-0">
                                                        Cambiar
                                                    </button>
                                                </div>
                                                {{-- Estado de búsqueda --}}
                                                <div x-show="!selected" class="relative">
                                                    <input type="text"
                                                        x-model="query"
                                                        @input="open = sugerencias.length > 0"
                                                        @focus="open = sugerencias.length > 0"
                                                        placeholder="Ej. Pantalla 15.6 FHD, SSD 256GB SATA..."
                                                        class="w-full rounded-xl px-3 py-2 text-sm
                                                               bg-white/70 dark:bg-slate-900/60
                                                               border border-slate-300/80 dark:border-slate-700
                                                               text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                                               focus:ring-2 focus:ring-purple-500/70 outline-none">
                                                    <ul x-show="open" x-cloak
                                                        class="absolute z-30 mt-1 w-full rounded-xl shadow-lg
                                                               bg-white dark:bg-slate-900
                                                               border border-slate-200 dark:border-slate-700
                                                               max-h-48 overflow-y-auto text-sm">
                                                        <template x-for="p in sugerencias" :key="p.id">
                                                            <li @click="seleccionar(p)"
                                                                class="px-3 py-2 cursor-pointer
                                                                       hover:bg-purple-50 dark:hover:bg-purple-900/20
                                                                       border-b border-slate-100 dark:border-slate-800 last:border-0">
                                                                <span class="font-medium text-slate-800 dark:text-slate-200" x-text="p.nombre"></span>
                                                                <span class="ml-1 text-xs text-slate-400" x-text="'[' + p.categoria + ']'"></span>
                                                                <span class="ml-2 text-xs text-purple-600 dark:text-purple-400 font-semibold">+ agregar stock</span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                                <p x-show="!selected && query.length >= 3 && sugerencias.length === 0"
                                                   class="text-xs text-amber-600 dark:text-amber-400">
                                                    ⚠ Se creará nueva pieza en el catálogo
                                                </p>
                                            </div>

                                            {{-- Categoría --}}
                                            <div class="md:col-span-3 space-y-1">
                                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Categoría <span class="text-rose-500">*</span>
                                                </label>
                                                <select wire:model="deshuesoItems.{{ $i }}.categoria"
                                                    :disabled="selected"
                                                    :class="selected ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white/70 dark:bg-slate-900/60'"
                                                    class="w-full rounded-xl px-3 py-2 text-sm
                                                           border border-slate-300/80 dark:border-slate-700
                                                           text-slate-900 dark:text-slate-100
                                                           focus:ring-2 focus:ring-purple-500/70 outline-none">
                                                    <option value="">Categoría...</option>
                                                    @foreach(\App\Livewire\Preparacion\Inventario\CatalogoPiezas::CATEGORIAS as $cat)
                                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Cantidad --}}
                                            <div class="md:col-span-2 space-y-1">
                                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cantidad *</label>
                                                <input type="number" min="1" wire:model="deshuesoItems.{{ $i }}.cantidad"
                                                    class="w-full rounded-xl px-3 py-2 text-sm
                                                           bg-white/70 dark:bg-slate-900/60
                                                           border border-slate-300/80 dark:border-slate-700
                                                           text-slate-900 dark:text-slate-100
                                                           focus:ring-2 focus:ring-purple-500/70 outline-none">
                                            </div>

                                            {{-- Quitar --}}
                                            <div class="md:col-span-2 flex items-end justify-end">
                                                <button wire:click="removerItemDeshueso({{ $i }})" type="button"
                                                    class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-300/60
                                                           text-rose-500 hover:bg-rose-100 transition flex items-center justify-center text-sm">
                                                    ✕
                                                </button>
                                            </div>

                                            {{-- Notas --}}
                                            <div class="md:col-span-12 space-y-1">
                                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notas</label>
                                                <input type="text" wire:model="deshuesoItems.{{ $i }}.notas"
                                                    placeholder="Condición física, modelo exacto, observaciones..."
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
                        @else
                            <p class="text-xs text-slate-400 text-center py-3">
                                Usa "+ Agregar pieza" para registrar piezas físicas extraídas del equipo (despiece).
                            </p>
                        @endif
                    </div>

                @endif

                {{-- Acciones --}}
                <div class="flex items-center justify-between">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        Cancelar
                    </button>
                    @if($deshuesoEquipoId)
                    <button wire:click="guardarDeshueso"
                        wire:loading.attr="disabled" wire:target="guardarDeshueso"
                        class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold
                               bg-gradient-to-r from-purple-700 via-purple-600 to-purple-500
                               text-white shadow-md shadow-purple-800/40
                               hover:shadow-purple-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="guardarDeshueso">Registrar despiece</span>
                        <span wire:loading wire:target="guardarDeshueso">Guardando...</span>
                    </button>
                    @endif
                </div>

            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISTA 6: RESTOCK                                          --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @elseif($vista === 'restock')

            @php $rp = $this->restockPieza; @endphp

            <div class="max-w-2xl mx-auto space-y-5">

                @if($error)
                    <div class="rounded-xl border border-rose-500/40 bg-rose-50/80 dark:bg-rose-950/30
                                px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                        {{ $error }}
                    </div>
                @endif

                {{-- Info de la pieza (bloqueada) --}}
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-emerald-200/60 dark:border-emerald-700/40
                            backdrop-blur-xl shadow-md px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30
                                flex items-center justify-center shrink-0 text-lg">🔧</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $rp?->nombre }}</p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                         bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300
                                         border border-indigo-300/50">{{ $rp?->categoria }}</span>
                            @if($rp?->especificacion)
                                <span class="text-xs text-slate-400">{{ $rp->especificacion }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $rp?->stock_disponible ?? \App\Models\InventarioPieza::where('catalogo_pieza_id', $rp?->id)->sum('cantidad_disponible') }}
                        </p>
                        <p class="text-xs text-slate-400">en stock</p>
                    </div>
                </div>

                {{-- Paso 1: Seleccionar origen --}}
                @if($restockOrigen === '')
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-6 py-6 space-y-4">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                            ¿De dónde proviene el stock?
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button wire:click="seleccionarRestockOrigen('compra')"
                                class="flex flex-col items-center gap-3 rounded-2xl border-2 border-emerald-300/60 dark:border-emerald-600/40
                                       bg-emerald-50/80 dark:bg-emerald-950/30 px-6 py-6
                                       hover:border-emerald-500 hover:bg-emerald-100/80 dark:hover:bg-emerald-900/30
                                       transition-all duration-200 group">
                                <span class="text-3xl">🛒</span>
                                <div class="text-center">
                                    <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Compra a proveedor</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Registrar factura o remisión</p>
                                </div>
                            </button>
                            <button wire:click="seleccionarRestockOrigen('deshueso')"
                                class="flex flex-col items-center gap-3 rounded-2xl border-2 border-purple-300/60 dark:border-purple-600/40
                                       bg-purple-50/80 dark:bg-purple-950/30 px-6 py-6
                                       hover:border-purple-500 hover:bg-purple-100/80 dark:hover:bg-purple-900/30
                                       transition-all duration-200 group">
                                <span class="text-3xl">🔩</span>
                                <div class="text-center">
                                    <p class="text-sm font-semibold text-purple-800 dark:text-purple-200">Despiece de equipo</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pieza extraída de un equipo</p>
                                </div>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Paso 2A: Form compra --}}
                @if($restockOrigen === 'compra')
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                                🛒 Datos de la compra
                            </h3>
                            <button wire:click="seleccionarRestockOrigen('')" type="button"
                                class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 underline transition">
                                Cambiar origen
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Proveedor --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Proveedor <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="restockCompraProveedorId"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                    <option value="">Selecciona un proveedor...</option>
                                    @foreach($this->proveedores as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                                    @endforeach
                                </select>
                                @error('restockCompraProveedorId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Fecha --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Fecha de compra <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" wire:model="restockCompraFecha"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                @error('restockCompraFecha') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Folio --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Folio / Factura <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <input type="text" wire:model="restockCompraFolio"
                                    placeholder="FAC-2026-001, REMISION-123..."
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                           focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            </div>

                            {{-- Almacén --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Almacén destino <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="restockAlmacenId"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                    <option value="">Selecciona un almacén...</option>
                                    @foreach($this->almacenes as $alm)
                                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('restockAlmacenId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cantidad --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Cantidad <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" min="1" wire:model="restockCantidad"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                @error('restockCantidad') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Precio --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Precio unitario <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                                    <input type="number" step="0.01" min="0" wire:model="restockPrecio"
                                        placeholder="0.00"
                                        class="w-full pl-7 rounded-xl px-3 py-2.5 text-sm
                                               bg-white/70 dark:bg-slate-900/40
                                               border border-slate-300/80 dark:border-slate-700
                                               text-slate-900 dark:text-slate-100
                                               focus:ring-2 focus:ring-emerald-500/70 outline-none">
                                </div>
                                @error('restockPrecio') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Notas --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Notas <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <input type="text" wire:model="restockNotas"
                                    placeholder="Condición, proveedor alterno, observaciones..."
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                           focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Paso 2B: Form deshueso --}}
                @if($restockOrigen === 'deshueso')
                    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl shadow-md px-6 py-5 space-y-4">

                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                                🔩 Equipo origen del despiece
                            </h3>
                            <button wire:click="seleccionarRestockOrigen('')" type="button"
                                class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 underline transition">
                                Cambiar origen
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Buscar equipo --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Equipo origen <span class="text-rose-500">*</span>
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
                                        @foreach($this->equiposBusqueda as $eq)
                                            <button type="button" wire:click="seleccionarEquipoDeshueso({{ $eq->id }})"
                                                class="w-full text-left px-4 py-2.5 text-sm
                                                       hover:bg-slate-50 dark:hover:bg-slate-800 transition
                                                       border-b border-slate-100 dark:border-slate-800 last:border-0">
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $eq->numero_serie }}</span>
                                                <span class="text-slate-500 ml-2">{{ $eq->marca }} {{ $eq->modelo }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @if($deshuesoEquipoId)
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                                        ✓ {{ $deshuesoEquipoBusqueda }}
                                    </p>
                                @endif
                                @error('deshuesoEquipoId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Almacén --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Almacén destino <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="restockAlmacenId"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-purple-500/70 outline-none">
                                    <option value="">Selecciona un almacén...</option>
                                    @foreach($this->almacenes as $alm)
                                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('restockAlmacenId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cantidad --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Cantidad <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" min="1" wire:model="restockCantidad"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-purple-500/70 outline-none">
                                @error('restockCantidad') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Notas --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Notas <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <input type="text" wire:model="restockNotas"
                                    placeholder="Condición física, observaciones..."
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                           focus:ring-2 focus:ring-purple-500/70 outline-none">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Acciones --}}
                <div class="flex items-center justify-between">
                    <button wire:click="volverALista"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        Cancelar
                    </button>
                    @if($restockOrigen !== '')
                        <button wire:click="guardarRestock"
                            wire:loading.attr="disabled" wire:target="guardarRestock"
                            class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold
                                   {{ $restockOrigen === 'compra'
                                       ? 'bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-500 shadow-emerald-800/40 hover:shadow-emerald-500/60'
                                       : 'bg-gradient-to-r from-purple-700 via-purple-600 to-purple-500 shadow-purple-800/40 hover:shadow-purple-500/60' }}
                                   text-white shadow-md hover:-translate-y-0.5
                                   disabled:opacity-60 transition-all duration-200">
                            <span wire:loading.remove wire:target="guardarRestock">Guardar restock</span>
                            <span wire:loading wire:target="guardarRestock">Guardando...</span>
                        </button>
                    @endif
                </div>

            </div>

        @endif


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- MODAL: NUEVO PROVEEDOR                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($modalProveedor)
            <div class="fixed inset-0 z-50 flex items-center justify-center"
                 x-data @keydown.escape.window="$wire.cerrarModalProveedor()">
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                     wire:click="cerrarModalProveedor"></div>

                <div class="relative w-[94%] max-w-md rounded-2xl border border-white/10
                            bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                            shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-4">

                    <div class="flex items-center justify-between">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                            Nuevo proveedor
                        </h4>
                        <button wire:click="cerrarModalProveedor"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition text-xl leading-none">×</button>
                    </div>

                    <div class="space-y-3">
                        {{-- Nombre empresa --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Nombre de la empresa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model.live="proveedorNombre"
                                placeholder="Ej: Distribuidora Tecnológica del Norte..."
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            @error('proveedorNombre') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Abreviación --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Abreviación <span class="text-rose-500">*</span>
                                <span class="text-slate-400 font-normal normal-case">(máx. 20 car., única)</span>
                            </label>
                            <input type="text" wire:model="proveedorAbreviacion"
                                placeholder="Ej: DTN, Tech-MX..."
                                maxlength="20"
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            @error('proveedorAbreviacion') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Correo contacto <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="email" wire:model="proveedorEmail"
                                placeholder="contacto@empresa.com"
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            @error('proveedorEmail') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Teléfono <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" wire:model="proveedorTelefono"
                                placeholder="Ej: 81-1234-5678"
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button wire:click="cerrarModalProveedor"
                            class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                                   border border-slate-300/80 dark:border-slate-700
                                   bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        <button wire:click="guardarProveedor"
                            wire:loading.attr="disabled" wire:target="guardarProveedor"
                            class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-semibold
                                   bg-emerald-600 hover:bg-emerald-500 text-white
                                   shadow-md shadow-emerald-800/40 hover:shadow-emerald-500/60
                                   hover:-translate-y-0.5 transition-all duration-200
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardarProveedor">Guardar proveedor</span>
                            <span wire:loading wire:target="guardarProveedor">Guardando...</span>
                        </button>
                    </div>
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

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- MODAL: NUEVO LOTE                                         --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($modalLote)
            <div class="fixed inset-0 z-50 flex items-center justify-center"
                 x-data @keydown.escape.window="$wire.cerrarModalLote()">
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                     wire:click="cerrarModalLote"></div>

                <div class="relative w-[94%] max-w-sm rounded-2xl border border-white/10
                            bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                            shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-4">

                    <div class="flex items-center justify-between">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                            Nuevo lote de compra
                        </h4>
                        <button wire:click="cerrarModalLote"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition text-xl leading-none">×</button>
                    </div>

                    <div class="space-y-3">
                        {{-- Nombre del lote --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Nombre del lote <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="loteNombre"
                                placeholder="Ej: COMP-ABR-2026, RAM-BTB-01..."
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                            @error('loteNombre') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fecha de llegada --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Fecha de llegada <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="date" wire:model="loteFechaLlegada"
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-emerald-500/70 outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button wire:click="cerrarModalLote"
                            class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                                   border border-slate-300/80 dark:border-slate-700
                                   bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        <button wire:click="guardarLote"
                            wire:loading.attr="disabled" wire:target="guardarLote"
                            class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-semibold
                                   bg-emerald-600 hover:bg-emerald-500 text-white
                                   shadow-md shadow-emerald-800/40 hover:shadow-emerald-500/60
                                   hover:-translate-y-0.5 transition-all duration-200
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardarLote">Crear lote</span>
                            <span wire:loading wire:target="guardarLote">Creando...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-tb-background>
</div>
