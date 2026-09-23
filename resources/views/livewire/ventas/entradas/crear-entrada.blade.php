<div class="space-y-6">
    <x-topbar title="Registro de Entradas / Restock" 
              chip="Ventas" 
              description="Recibe nueva mercancía, actualiza los costos y el inventario de accesorios en tiempo real." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Información y Búsqueda -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Detalles de la Factura/Entrada -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6 space-y-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Datos de la Entrada</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Almacén Destino <span class="text-red-500">*</span></label>
                        <select wire:model.defer="almacen_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                            @endforeach
                        </select>
                        @error('almacen_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Proveedor</label>
                        <select wire:model.defer="proveedor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">-- Sin Proveedor / Compra Local --</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.defer="fecha" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('fecha') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Folio (Opcional)</label>
                            <input type="text" wire:model.defer="folio_factura" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notas Adicionales</label>
                        <textarea wire:model.defer="notas" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
            </div>

            <!-- Buscador de Productos -->
            <div class="bg-white shadow sm:rounded-lg relative">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Agregar Productos</h3>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Escanear Código, Nombre o SKU..." class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-3 text-lg font-mono">
                    </div>
                    
                    @if(count($searchResults) > 0)
                        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm border border-gray-200">
                            @foreach($searchResults as $producto)
                                <div wire:click="agregarAlCarrito({{ $producto->id }})" class="cursor-pointer hover:bg-indigo-50 px-4 py-2 flex justify-between items-center group">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $producto->nombre }}</div>
                                        <div class="text-xs text-gray-500">{{ $producto->sku }}</div>
                                    </div>
                                    <div class="text-indigo-600 opacity-0 group-hover:opacity-100">
                                        Agregar
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Carrito -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow sm:rounded-lg flex flex-col h-full">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Lista de Recepción</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ count($cart) }} Items
                    </span>
                </div>
                
                <div class="flex-1 p-0 overflow-y-auto min-h-[300px]">
                    @if(count($cart) === 0)
                        <div class="h-full flex flex-col items-center justify-center py-12 text-gray-500">
                            <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                            </svg>
                            <p class="text-sm">No hay productos en la lista.</p>
                            <p class="text-xs">Utiliza el buscador para agregarlos.</p>
                        </div>
                    @else
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($cart as $index => $item)
                                <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="w-1/2">
                                            <p class="text-sm font-medium text-indigo-600 truncate">{{ $item['nombre'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $item['sku'] }}</p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div>
                                                <label class="sr-only">Costo Unit.</label>
                                                <div class="relative rounded-md shadow-sm w-24">
                                                    <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">$</span>
                                                    </div>
                                                    <input type="number" step="0.01" wire:model.lazy="cart.{{ $index }}.precio_unitario" wire:change="actualizarPrecio({{ $index }}, $event.target.value)" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-6 sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-gray-500 mx-2">x</span>
                                                <input type="number" min="1" wire:model.lazy="cart.{{ $index }}.cantidad" wire:change="actualizarCantidad({{ $index }}, $event.target.value)" class="focus:ring-indigo-500 focus:border-indigo-500 block w-20 sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                            <div class="w-24 text-right font-semibold text-gray-900">
                                                ${{ number_format($item['subtotal'], 2) }}
                                            </div>
                                            <button wire:click="removerDelCarrito({{ $index }})" class="text-red-500 hover:text-red-700">
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                
                <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-gray-500 text-sm">
                            Costo Total Estimado:
                        </div>
                        <div class="text-2xl font-bold text-gray-900">
                            ${{ number_format($this->total, 2) }}
                        </div>
                    </div>
                    @error('cart') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                    
                    <div class="mt-6 flex justify-end">
                        <button wire:click="guardar" @if(empty($cart)) disabled @endif class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Procesar Entrada y Actualizar Inventario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
