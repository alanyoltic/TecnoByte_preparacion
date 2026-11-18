<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-toast />

        {{-- ===== TOPBAR GLASS ===== --}}
        <x-topbar
            title="Catálogo de Productos"
            chip="Ventas · Artículos Comerciales"
            description="Catálogo de accesorios, periféricos y productos de exhibición para punto de venta."
        >
            <div class="flex items-center gap-3">
                @can('ventas.productos.crear')
                    <button wire:click="abrirCrear" id="btn-nuevo-producto"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold
                                   bg-[#FF9521] hover:bg-[#e0821b] text-slate-950
                                   shadow-lg shadow-[#FF9521]/20 active:scale-95 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Nuevo Producto</span>
                    </button>
                @endcan
            </div>
        </x-topbar>

        {{-- ===== FILTROS GLASS ===== --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </span>
                <input wire:model.live.debounce.350ms="busqueda" id="filtro-busqueda-productos"
                       type="text" placeholder="Buscar por nombre, SKU, marca…"
                       class="w-full pl-11 pr-4 py-2.5 rounded-2xl text-sm
                              bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-white/10
                              text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500
                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent backdrop-blur-xl transition-all"/>
            </div>
            <select wire:model.live="filtroCategoria" id="filtro-categoria-producto"
                    class="px-4 py-2.5 rounded-2xl text-sm bg-white/80 dark:bg-slate-900/60
                           border border-slate-200/80 dark:border-white/10 text-slate-800 dark:text-slate-200
                           focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent backdrop-blur-xl transition-all">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $valor => $label)
                    <option value="{{ $valor }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- ===== TABLA GLASS ===== --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-white/10
                                   bg-slate-100/60 dark:bg-slate-950/40 text-left">
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Producto / Modelo</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden sm:table-cell">SKU / Barras</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden md:table-cell">Categoría</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden lg:table-cell">Precio Venta</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden xl:table-cell">Estatus</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                        @forelse($productos as $producto)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150 group">

                                {{-- Nombre + Marca --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0
                                                    bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                                {{ $producto->nombre }}
                                            </p>
                                            @if($producto->marca)
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $producto->marca }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- SKU --}}
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="space-y-0.5">
                                        @if($producto->sku)
                                            <p class="text-xs font-mono font-bold text-slate-800 dark:text-slate-200">{{ $producto->sku }}</p>
                                        @endif
                                        @if($producto->codigo_barras)
                                            <p class="text-xs font-mono text-slate-400">{{ $producto->codigo_barras }}</p>
                                        @endif
                                        @if(!$producto->sku && !$producto->codigo_barras)
                                            <span class="text-xs text-slate-500">—</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Categoría --}}
                                <td class="px-6 py-4 hidden md:table-cell">
                                    @if($producto->categoria)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold
                                                     bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            {{ $categorias[$producto->categoria] ?? $producto->categoria }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>

                                {{-- Precio --}}
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <span class="font-bold text-emerald-500 dark:text-emerald-400 text-sm">
                                        {{ $producto->precio_venta ? '$' . number_format($producto->precio_venta, 2) : '—' }}
                                    </span>
                                </td>

                                {{-- Activo --}}
                                <td class="px-6 py-4 hidden xl:table-cell">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border
                                                 {{ $producto->activo
                                                     ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                                                     : 'bg-slate-500/10 text-slate-400 border-slate-500/30' }}">
                                        {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('ventas.productos.editar')
                                            <button wire:click="abrirEditar({{ $producto->id }})" title="Editar"
                                                    class="p-2 rounded-xl text-slate-400 hover:text-[#FF9521] hover:bg-[#FF9521]/10 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endcan
                                        @can('ventas.productos.eliminar')
                                            <button wire:click="confirmarEliminar({{ $producto->id }})" title="Eliminar"
                                                    class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
                                        <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                        <p class="text-sm font-medium">No se encontraron productos en el catálogo con los filtros actuales.</p>
                                        @can('ventas.productos.crear')
                                            <button wire:click="abrirCrear" class="text-[#FF9521] hover:underline text-xs font-bold">
                                                + Registrar primer producto
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-2">{{ $productos->links() }}</div>

        {{-- ================================================================
             MODAL CREAR / EDITAR GLASS
        ================================================================ --}}
        @if($modalAbierto)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md" wire:click="cerrarModal"></div>

                <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl shadow-2xl
                            bg-white/95 dark:bg-slate-900/95 border border-slate-200/80 dark:border-white/10
                            backdrop-blur-2xl">

                    {{-- Header --}}
                    <div class="sticky top-0 z-10 flex items-center justify-between px-6 sm:px-8 py-5
                                border-b border-slate-200/80 dark:border-white/10
                                bg-white/95 dark:bg-slate-900/95 backdrop-blur-md">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                            {{ $productoId ? 'Editar Producto' : 'Nuevo Producto' }}
                        </h2>
                        <button wire:click="cerrarModal"
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="guardar" class="p-6 sm:p-8 space-y-5">

                        {{-- Nombre + Marca --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                    Nombre del Artículo <span class="text-[#FF9521]">*</span>
                                </label>
                                <input wire:model="nombre" id="campo-nombre-producto" type="text"
                                       class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                              bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                       placeholder="Ej. Audífonos Logitech H390"/>
                                @error('nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Marca</label>
                                <input wire:model="marca" id="campo-marca" type="text"
                                       class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                              bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                       placeholder="Logitech"/>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Categoría</label>
                                <select wire:model="categoria" id="campo-categoria"
                                        class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                               bg-slate-50 dark:bg-slate-950/70 text-slate-700 dark:text-slate-200
                                               focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition">
                                    <option value="">— Sin categoría —</option>
                                    @foreach($categorias as $valor => $label)
                                        <option value="{{ $valor }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- SKU / Código --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">SKU</label>
                                <input wire:model="sku" id="campo-sku" type="text"
                                       class="w-full px-4 py-2.5 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700
                                              bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                       placeholder="LOG-H390"/>
                                @error('sku')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Código de Barras</label>
                                <input wire:model="codigo_barras" id="campo-codigo-barras" type="text"
                                       class="w-full px-4 py-2.5 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700
                                              bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                       placeholder="7501055360973"/>
                                @error('codigo_barras')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Precios --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-3xl border border-slate-200/80 dark:border-white/10
                                    bg-slate-100/60 dark:bg-slate-950/40 p-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Precio de Compra</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 text-sm">$</span>
                                    <input wire:model="precio_compra" id="campo-precio-compra" type="number" step="0.01" min="0"
                                           class="w-full pl-8 pr-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="0.00"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Precio de Venta</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 text-sm">$</span>
                                    <input wire:model="precio_venta" id="campo-precio-venta" type="number" step="0.01" min="0"
                                           class="w-full pl-8 pr-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="0.00"/>
                                </div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                            <textarea wire:model="descripcion" id="campo-descripcion" rows="3"
                                      class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                             bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                             focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition resize-none"
                                      placeholder="Descripción y detalles del producto…"></textarea>
                        </div>

                        {{-- Activo --}}
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="activo" id="campo-activo"
                                   class="w-4 h-4 rounded text-[#FF9521] border-slate-300 focus:ring-[#FF9521]"/>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Producto activo (visible en el catálogo y Punto de Venta)</span>
                        </label>

                        {{-- Botones --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80 dark:border-white/10">
                            <button type="button" wire:click="cerrarModal"
                                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300
                                           bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                Cancelar
                            </button>
                            <button type="submit" id="btn-guardar-producto"
                                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-950
                                           bg-[#FF9521] hover:bg-[#e0821b]
                                           shadow-lg shadow-[#FF9521]/20 transition-all duration-150 active:scale-95"
                                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                                <span wire:loading.remove wire:target="guardar">
                                    {{ $productoId ? 'Guardar Cambios' : 'Registrar Producto' }}
                                </span>
                                <span wire:loading wire:target="guardar">Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ================================================================
             MODAL ELIMINAR GLASS
        ================================================================ --}}
        @if($modalEliminar)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md"
                     wire:click="$set('modalEliminar', false)"></div>
                <div class="relative w-full max-w-sm rounded-3xl bg-white/95 dark:bg-slate-900/95
                            border border-slate-200 dark:border-white/10 shadow-2xl p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">¿Eliminar producto?</h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Esta acción eliminará el artículo del catálogo pero preservará el historial previo de ventas.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button wire:click="$set('modalEliminar', false)"
                                class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold
                                       bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300
                                       hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            Cancelar
                        </button>
                        <button wire:click="eliminar" id="btn-confirmar-eliminar-producto"
                                class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white
                                       bg-rose-600 hover:bg-rose-500 transition active:scale-95 shadow-md shadow-rose-900/20">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-tb-background>
