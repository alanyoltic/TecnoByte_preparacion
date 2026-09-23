<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
        <div class="w-full space-y-6">
            <x-topbar title="Punto de Venta" chip="Ventas" description="Crea ventas, cotizaciones y notas de remisión de forma rápida." />
            
            <div class="w-full space-y-6">


        {{-- ================================================================
             CONFIGURACIÓN DEL DOCUMENTO (Row 1)
        ================================================================ --}}
        <div class="bg-white/80 dark:bg-slate-950/60 backdrop-blur-xl rounded-3xl p-5 shadow-xl shadow-slate-200/40 dark:shadow-black/20 border border-slate-200/70 dark:border-white/10">
            <div class="grid grid-cols-1 md:grid-cols-3 {{ $tipoComprobante === 'FACTURA' ? 'lg:grid-cols-6' : 'lg:grid-cols-5' }} gap-5">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Comprobante:</label>
                    <select wire:model.live="tipoComprobante" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm font-semibold text-blue-700 dark:text-blue-400">
                        <option value="NOTA">Nota de Venta</option>
                        <option value="FACTURA">Factura</option>
                    </select>
                </div>

                @if($tipoComprobante === 'FACTURA')
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Uso CFDI:</label>
                    <select wire:model="usoCfdi" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        <option value="">Seleccione...</option>
                        @foreach($opcionesUsoCfdi as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Sucursal:</label>
                    <select wire:model="sucursalId" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        <option value="">Seleccione Sucursal...</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Almacén:</label>
                    <select wire:model="almacenId" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        <option value="">Seleccione Almacén...</option>
                        @foreach($almacenes as $alm)
                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Cliente:</label>
                        <select wire:model="clienteId" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        @if($clienteSeleccionado)
                            <option value="{{ $clienteSeleccionado->id }}">{{ $clienteSeleccionado->nombre_completo }}</option>
                            <option value="">--- Quitar cliente (Público en general) ---</option>
                        @else
                            <option value="">Público en general</option>
                        @endif
                    </select>
                </div>
                <button wire:click="$set('modalCliente', true)" class="px-3 py-2 text-sm border border-blue-500 text-blue-600 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/30 transition shadow-sm font-medium whitespace-nowrap">
                    Buscar cliente
                </button>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Método de Pago:</label>
                    <select wire:model="metodoPago" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        @foreach($metodosPago as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                
            </div>
        </div>

        {{-- ================================================================
             TABLA DE PRODUCTOS (Row 2)
        ================================================================ --}}
        <div class="bg-white/80 dark:bg-slate-950/60 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/40 dark:shadow-black/20 border border-slate-200/70 dark:border-white/10 overflow-hidden">
            
            {{-- Encabezados Tabla --}}
            <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50/50 dark:bg-slate-900/30 border-b border-slate-200/70 dark:border-white/10 text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">
                <div class="col-span-2">Código</div>
                <div class="col-span-5">Concepto</div>
                <div class="col-span-2 text-center">Cant.</div>
                <div class="col-span-2 text-right">Precio</div>
                <div class="col-span-1 text-right">Importe</div>
            </div>

            {{-- Buscador "Agregar producto o servicio" --}}
            <div class="p-4 border-b border-slate-200/70 dark:border-white/10 bg-slate-50/30 dark:bg-slate-900/20 relative">
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input wire:model.live.debounce.300ms="busquedaProducto" type="text" 
                               placeholder="Agregar producto o servicio (Buscar por nombre o SKU)..." 
                               class="w-full pl-10 pr-4 py-3 bg-white/60 dark:bg-slate-900/40 backdrop-blur-md border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-0 transition text-center hover:border-blue-400">
                    </div>
                    <div class="w-full md:w-64 relative">
                        <form wire:submit.prevent="buscarEquipo" class="flex">
                            <input wire:model="busquedaSerial" type="text" 
                                   placeholder="Serie equipo..." 
                                   class="w-full px-4 py-3 bg-white/60 dark:bg-slate-900/40 backdrop-blur-md border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-l-xl border-r-0 text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-0 transition">
                            <button type="submit" class="px-4 py-3 bg-slate-100/60 dark:bg-slate-800/40 backdrop-blur-md border-2 border-dashed border-l-0 border-slate-300 dark:border-slate-600 rounded-r-xl hover:bg-slate-200/60 dark:hover:bg-slate-700/60 transition">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Resultados de Búsqueda --}}
                @if(count($resultadosProducto) > 0)
                <div class="absolute z-10 w-full mt-2 left-0 right-0 px-4">
                    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-64 overflow-y-auto">
                        @foreach($resultadosProducto as $res)
                        <button wire:click="agregarProducto({{ $res['id'] }})" class="w-full flex justify-between items-center px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-slate-100 dark:border-slate-700/50 transition text-left">
                            <div>
                                <p class="font-medium text-slate-800 dark:text-slate-100">{{ $res['nombre'] }}</p>
                                <p class="text-xs text-slate-500">{{ $res['sku'] ?? $res['marca'] }}</p>
                            </div>
                            <span class="font-bold text-blue-600 dark:text-blue-400">${{ number_format($res['precio_venta'], 2) }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($errorBusqueda)
                    <p class="text-rose-500 text-sm mt-2 text-center font-medium">{{ $errorBusqueda }}</p>
                @endif
            </div>

            {{-- Filas del Carrito --}}
            <div class="divide-y divide-slate-100 dark:divide-slate-800/50 max-h-80 overflow-y-auto">
                @forelse($carrito as $index => $item)
                <div class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition group">
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-2 text-sm text-slate-500 dark:text-slate-400 font-mono">
                            {{ $item['detalle'] ?: 'N/A' }}
                        </div>
                        <div class="col-span-5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                            {{ $item['nombre'] }}
                        </div>
                        <div class="col-span-2 flex justify-center items-center">
                            @if($item['tipo'] === 'producto')
                            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1 border border-slate-200 dark:border-slate-700">
                                <button wire:click="cambiarCantidad({{ $index }}, -1)" class="w-6 h-6 flex items-center justify-center rounded hover:bg-white dark:hover:bg-slate-700 shadow-sm text-slate-600 transition">−</button>
                                <span class="w-8 text-center text-sm font-bold">{{ $item['cantidad'] }}</span>
                                <button wire:click="cambiarCantidad({{ $index }}, 1)" class="w-6 h-6 flex items-center justify-center rounded hover:bg-white dark:hover:bg-slate-700 shadow-sm text-slate-600 transition">+</button>
                            </div>
                            @else
                            <span class="text-sm font-bold bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg">1</span>
                            @endif
                        </div>
                        <div class="col-span-2 flex justify-end">
                            <input type="number" step="0.01" wire:model.lazy="carrito.{{ $index }}.precio" wire:change="actualizarPrecio({{ $index }}, $event.target.value)" 
                                   class="w-24 text-right px-2 py-1 text-sm bg-transparent text-slate-900 dark:text-white border-0 border-b border-dashed border-slate-300 dark:border-slate-600 focus:ring-0 focus:border-blue-500">
                        </div>
                        <div class="col-span-1 text-right text-sm font-bold text-slate-800 dark:text-slate-200 flex justify-end items-center gap-2">
                            ${{ number_format($item['precio'] * $item['cantidad'], 2) }}
                            <button wire:click="quitarItem({{ $index }})" class="text-slate-300 hover:text-rose-500 transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if($item['tipo'] === 'equipo')
                    <div class="mt-2 ml-36 flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Cargador:</label>
                        <select wire:model="carrito.{{ $index }}.cargador_id" 
                            class="w-80 rounded-xl px-3 py-1.5 text-sm bg-white dark:bg-slate-900/60 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 outline-none text-slate-700 dark:text-slate-300">
                            <option value="">-- No incluir cargador --</option>
                            @foreach($this->cargadoresDisponibles as $cargador)
                                <option value="{{ $cargador->id }}">
                                    {{ $cargador->marca }} {{ $cargador->serie }} ({{ $cargador->voltaje }}V {{ $cargador->amperaje }}A) - Punta: {{ $cargador->punta }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                @empty
                <div class="py-12 text-center text-slate-400">
                    No hay productos en la lista.
                </div>
                @endforelse
            </div>
        </div>

        {{-- ================================================================
             PAGOS Y TOTALES (Row 3)
        ================================================================ --}}
        <div class="bg-white/80 dark:bg-slate-950/60 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/40 dark:shadow-black/20 border border-slate-200/70 dark:border-white/10 overflow-hidden">
            
            {{-- Header Pagos --}}
            <div class="bg-blue-600 px-6 py-3">
                <h3 class="text-white font-medium text-lg tracking-wide">Pagos</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6">
                
                {{-- Columna Izquierda --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Dirección Fiscal:</label>
                        <select wire:model="direccionFiscal" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm border-dashed">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Comentarios</label>
                        <textarea wire:model="notas" rows="3" class="w-full bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm border-dashed resize-none"></textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="enviarNotificacion" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">Notificar a:</span>
                        </label>
                        <div class="flex-1 relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input wire:model="emailNotificacion" type="email" placeholder="Email ....." class="w-full pl-9 py-2 bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha (Totales) --}}
                <div class="flex flex-col justify-between">
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                            <span class="text-sm font-medium">Subtotal:</span>
                            <span class="font-mono">${{ number_format($this->subtotal, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Descuento $:</span>
                                <select class="text-xs bg-transparent text-slate-900 dark:text-white border-slate-200 dark:border-slate-700 rounded-md">
                                    <option>$</option>
                                    <option>%</option>
                                </select>
                            </div>
                            <input type="number" wire:model.live="descuento" class="w-24 text-right py-1 text-sm bg-white/60 dark:bg-slate-900/40 backdrop-blur-md text-slate-900 dark:text-white border-slate-200/70 dark:border-white/10 rounded-lg shadow-sm border-dashed">
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">I.V.A.:</span>
                                <select class="text-xs bg-transparent text-slate-900 dark:text-white border-slate-200 dark:border-slate-700 rounded-md">
                                    <option>16%</option>
                                </select>
                            </div>
                            <span class="font-mono text-slate-600 dark:text-slate-400">${{ number_format($this->iva, 2) }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-slate-200 dark:border-slate-800">
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-lg">Total (MXN):</span>
                            <span class="font-black text-2xl tracking-tight text-blue-600 dark:text-blue-400">${{ number_format($this->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button wire:click="procesarVenta" 
                                class="px-8 py-3 rounded-full text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all active:scale-95 disabled:opacity-50"
                                {{ empty($carrito) ? 'disabled' : '' }}>
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    {{-- ================================================================
         MODAL ÉXITO (Reutilizado del original)
    ================================================================ --}}
    @if($modalExito)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-2xl p-8 text-center space-y-5">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">¡Venta completada!</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Folio: <span class="font-mono font-bold text-blue-600">{{ $folioVenta }}</span></p>
            </div>
            <button wire:click="nuevaVenta" class="w-full py-3 rounded-2xl font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                + Nueva Venta
            </button>
        </div>
    </div>
    @endif
        </div>
    </div>
    {{-- ================================================================
         MODAL CLIENTES
    ================================================================ --}}
    @if($modalCliente)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('modalCliente', false)"></div>
        <div class="relative w-full max-w-5xl rounded-3xl bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-slate-200/50 dark:border-slate-700/50 shadow-[0_0_40px_rgba(0,0,0,0.3)] flex flex-col max-h-[90vh]">
            
            {{-- Header --}}
            <div class="flex justify-between items-center p-5 border-b border-slate-200 dark:border-slate-700">
                <span class="text-lg font-bold text-slate-800 dark:text-white">
                    {{ $modoCrearCliente ? 'Registrar Nuevo Cliente' : 'Buscar Cliente' }}
                </span>
                <button wire:click="$set('modalCliente', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-5 overflow-y-auto">
                @if(!$modoCrearCliente)
                    {{-- Búsqueda --}}
                    <div class="space-y-4">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input wire:model.live.debounce.500ms="busquedaClienteModal" type="text" placeholder="Buscar por nombre, apellidos, o teléfono..." class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        @if(strlen($busquedaClienteModal) >= 2)
                            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-inner max-h-64 overflow-y-auto bg-white dark:bg-slate-800">
                                @forelse($resultadosClienteModal as $c)
                                    <button wire:click="seleccionarCliente({{ $c['id'] }})" class="w-full flex flex-col text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/30 border-b border-slate-100 dark:border-slate-700/50 transition last:border-0">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')) ?: ($c['razon_social'] ?? 'Sin nombre') }}</span>
                                        @if($c['telefono'])
                                            <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tel: {{ $c['telefono'] }}</span>
                                        @endif
                                    </button>
                                @empty
                                    <div class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                        No se encontraron clientes con "{{ $busquedaClienteModal }}"
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <div class="text-center py-8 text-sm text-slate-400 dark:text-slate-500">
                                Escribe al menos 2 caracteres para buscar...
                            </div>
                        @endif

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700 text-center">
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">¿El cliente no existe?</p>
                            <button wire:click="$set('modoCrearCliente', true)" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm text-sm font-medium transition">
                                Registrar Nuevo Cliente
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Formulario Nuevo Cliente --}}
                    <div class="space-y-6">
                        
                        {{-- Tipo de Persona --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tipo de Persona <span class="text-red-500">*</span></label>
                            <select wire:model.live="nuevoClienteTipo" class="w-full sm:w-1/3 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                                <option value="FISICA">Persona Física</option>
                                <option value="MORAL">Persona Moral</option>
                            </select>
                        </div>

                        {{-- Datos Principales --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            @if($nuevoClienteTipo === 'FISICA')
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombres y Apellidos <span class="text-red-500">*</span></label>
                                    <div class="flex gap-2">
                                        <input wire:model="nuevoClienteNombres" type="text" placeholder="Nombres" class="w-1/2 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                                        <input wire:model="nuevoClienteApellidos" type="text" placeholder="Apellidos" class="w-1/2 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                                    </div>
                                    @error('nuevoClienteNombres') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @else
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Razón Social <span class="text-red-500">*</span></label>
                                    <input wire:model="nuevoClienteRazonSocial" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                                    @error('nuevoClienteRazonSocial') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RFC</label>
                                <input wire:model="nuevoClienteRfc" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors" placeholder="XAXX010101000">
                            </div>
                            
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Régimen Fiscal</label>
                                <select wire:model="nuevoClienteRegimen" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                                    <option value="">Seleccionar...</option>
                                    @foreach($opcionesRegimen as $key => $val)
                                        <option value="{{ $key }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Correo Electrónico</label>
                                <input wire:model="nuevoClienteCorreo" type="email" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
                                <input wire:model="nuevoClienteTelefono" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">¿Cómo se enteró de nosotros?</label>
                                <select wire:model="nuevoClienteComoSeEntero" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                                    <option value="">Seleccionar...</option>
                                    @foreach($opcionesComoSeEntero as $key => $val)
                                        <option value="{{ $key }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <hr class="border-slate-200 dark:border-slate-700/50">

                        {{-- Dirección --}}
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white mt-4 mb-3">Dirección del Cliente</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">País</label>
                                <input wire:model="nuevoClientePais" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
                                <input wire:model="nuevoClienteEstado" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Municipio</label>
                                <input wire:model="nuevoClienteMunicipio" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Localidad</label>
                                <input wire:model="nuevoClienteLocalidad" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Calle</label>
                                <input wire:model="nuevoClienteCalle" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Colonia</label>
                                <input wire:model="nuevoClienteColonia" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Código Postal</label>
                                <input wire:model="nuevoClienteCp" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. Exterior</label>
                                <input wire:model="nuevoClienteNoExt" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>

                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. Interior</label>
                                <input wire:model="nuevoClienteNoInt" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cód. Colonia (Opcional)</label>
                                <input wire:model="nuevoClienteCodigoColonia" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cód. Localidad (Opcional)</label>
                                <input wire:model="nuevoClienteCodigoLocalidad" type="text" class="w-full bg-slate-50/50 dark:bg-slate-950/50 border border-slate-300/50 dark:border-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 backdrop-blur-sm transition-colors">
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="p-5 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 rounded-b-3xl bg-slate-50 dark:bg-slate-900/50">
                @if($modoCrearCliente)
                    <button wire:click="$set('modoCrearCliente', false)" class="px-5 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Volver a Buscar
                    </button>
                    <button wire:click="guardarNuevoCliente" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium shadow-sm transition">
                        Guardar y Seleccionar
                    </button>
                @else
                    <button wire:click="$set('modalCliente', false)" class="px-5 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cerrar
                    </button>
                @endif
            </div>

        </div>
    </div>
    @endif

</x-tb-background>
