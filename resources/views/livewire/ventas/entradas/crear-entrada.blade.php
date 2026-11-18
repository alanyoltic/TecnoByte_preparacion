<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-toast />

        {{-- TOPBAR GLASS --}}
        <x-topbar
            title="Registro de Entradas / Restock"
            chip="Ventas · Recepción de Mercancía"
            description="Recibe nueva mercancía, actualiza los costos y el stock de accesorios en tiempo real."
        >
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('ventas.entradas.historial') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                           bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-800
                           text-slate-700 dark:text-slate-200 border border-slate-300/60 dark:border-white/10
                           shadow-sm hover:shadow transition-all duration-150 active:scale-95"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Historial de Entradas</span>
                </a>
            </div>
        </x-topbar>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Izquierda: Información y Búsqueda -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Detalles de la Factura/Entrada -->
                <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                            bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 space-y-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                        Datos de la Entrada
                    </h3>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Almacén Destino <span class="text-[#FF9521]">*</span>
                        </label>
                        <select wire:model.defer="almacen_id"
                                class="w-full px-4 py-2.5 rounded-2xl text-sm
                                       bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                       text-slate-800 dark:text-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                            @endforeach
                        </select>
                        @error('almacen_id') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Proveedor</label>
                        <select wire:model.defer="proveedor_id"
                                class="w-full px-4 py-2.5 rounded-2xl text-sm
                                       bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                       text-slate-800 dark:text-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                            <option value="">-- Sin Proveedor / Compra Local --</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                                Fecha <span class="text-[#FF9521]">*</span>
                            </label>
                            <input type="date" wire:model.defer="fecha"
                                   class="w-full px-4 py-2.5 rounded-2xl text-sm
                                          bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                          text-slate-800 dark:text-slate-200
                                          focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                            @error('fecha') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Folio Factura</label>
                            <input type="text" wire:model.defer="folio_factura" placeholder="Ej: F-1234"
                                   class="w-full px-4 py-2.5 rounded-2xl text-sm
                                          bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                          text-slate-800 dark:text-slate-200
                                          focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Notas Adicionales</label>
                        <textarea wire:model.defer="notas" rows="2" placeholder="Observaciones sobre la recepción..."
                                  class="w-full px-4 py-2.5 rounded-2xl text-sm resize-none
                                         bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                         text-slate-800 dark:text-slate-200
                                         focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all"></textarea>
                    </div>
                </div>

                <!-- Buscador de Productos -->
                <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                            bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 relative">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                        Agregar Productos
                    </h3>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Código, Nombre o SKU..."
                               class="w-full pl-10 pr-4 py-3 rounded-2xl text-sm font-mono
                                      bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                      text-slate-800 dark:text-slate-200
                                      focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                    </div>

                    @if(count($searchResults) > 0)
                        <div class="absolute z-20 mt-2 left-6 right-6 rounded-2xl
                                    bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-700
                                    shadow-2xl max-h-60 overflow-auto divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($searchResults as $producto)
                                <div wire:click="agregarAlCarrito({{ $producto->id }})"
                                     class="cursor-pointer hover:bg-[#FF9521]/10 px-4 py-3 flex justify-between items-center group transition-colors">
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $producto->nombre }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $producto->sku }}</div>
                                    </div>
                                    <div class="text-[#FF9521] font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                        + Agregar
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Columna Derecha: Carrito -->
            <div class="lg:col-span-2">
                <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                            bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl flex flex-col h-full overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-white/10 flex justify-between items-center bg-slate-100/60 dark:bg-slate-950/40">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                            Lista de Recepción
                        </h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                            {{ count($cart) }} Items
                        </span>
                    </div>

                    <div class="flex-1 p-0 overflow-y-auto min-h-[300px]">
                        @if(count($cart) === 0)
                            <div class="h-full flex flex-col items-center justify-center py-16 text-slate-400">
                                <svg class="h-12 w-12 text-slate-500/40 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                                <p class="text-sm font-semibold">No hay productos en la lista.</p>
                                <p class="text-xs text-slate-500 mt-1">Utiliza el buscador lateral para agregarlos al lote de entrada.</p>
                            </div>
                        @else
                            <ul role="list" class="divide-y divide-slate-200/60 dark:divide-white/5">
                                @foreach($cart as $index => $item)
                                    <li class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="w-1/2">
                                                <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">{{ $item['nombre'] }}</p>
                                                <p class="text-xs text-slate-400 font-mono">{{ $item['sku'] }}</p>
                                            </div>
                                            <div class="flex items-center space-x-3 sm:space-x-4">
                                                <div>
                                                    <label class="sr-only">Costo Unit.</label>
                                                    <div class="relative w-24">
                                                        <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 text-xs">$</span>
                                                        <input type="number" step="0.01"
                                                               wire:model.lazy="cart.{{ $index }}.precio_unitario"
                                                               wire:change="actualizarPrecio({{ $index }}, $event.target.value)"
                                                               class="w-full pl-6 pr-2 py-1.5 rounded-xl text-xs sm:text-sm text-right
                                                                      bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                                                      text-slate-800 dark:text-slate-200
                                                                      focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-slate-400 text-xs">×</span>
                                                    <input type="number" min="1"
                                                           wire:model.lazy="cart.{{ $index }}.cantidad"
                                                           wire:change="actualizarCantidad({{ $index }}, $event.target.value)"
                                                           class="w-16 px-2 py-1.5 rounded-xl text-xs sm:text-sm text-center font-bold
                                                                  bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                                                  text-slate-800 dark:text-slate-200
                                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                                </div>
                                                <div class="w-24 text-right font-black text-slate-900 dark:text-slate-100 text-sm">
                                                    ${{ number_format($item['subtotal'], 2) }}
                                                </div>
                                                <button wire:click="removerDelCarrito({{ $index }})"
                                                        class="p-1.5 rounded-xl text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors">
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="px-6 py-5 border-t border-slate-200/80 dark:border-white/10 bg-slate-100/60 dark:bg-slate-950/40">
                        <div class="flex justify-between items-center">
                            <div class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider">
                                Costo Total Estimado:
                            </div>
                            <div class="text-3xl font-black text-emerald-500 dark:text-emerald-400">
                                ${{ number_format($this->total, 2) }}
                            </div>
                        </div>
                        @error('cart') <span class="text-xs text-rose-500 mt-2 block font-medium">{{ $message }}</span> @enderror

                        <div class="mt-6 flex justify-end">
                            <button wire:click="guardar"
                                    @if(empty($cart)) disabled @endif
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl
                                           text-sm font-bold text-slate-950 bg-[#FF9521] hover:bg-[#e0821b]
                                           shadow-xl shadow-[#FF9521]/20 transition-all duration-150 active:scale-95
                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Procesar Entrada y Actualizar Inventario</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-tb-background>
