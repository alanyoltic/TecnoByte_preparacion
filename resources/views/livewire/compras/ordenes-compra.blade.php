<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">
    @if($vista === 'lista')
        <div wire:key="vista-lista" class="space-y-6">
            <x-topbar 
            title="Órdenes de Compra" 
            chip="{{ $area === 'VENTAS' ? 'Ventas' : 'Preparación' }}" 
            description="Administra el abastecimiento de piezas, productos y consumibles." 
        >
            <x-slot name="right">
                <button wire:click="verNuevaOrden"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-sm font-semibold shadow-md shadow-emerald-800/40 hover:shadow-emerald-500/60 hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nueva Orden
                </button>
            </x-slot>
        </x-topbar>

        @php
            $totalCompras    = \App\Models\CompraInventario::deArea($area)->count();
            $totalItems      = \App\Models\CompraInventarioItem::whereHas('compra', fn($q) => $q->deArea($area))->sum('cantidad');
            $totalCargadores = \App\Models\Cargador::where('area', $area)->whereNotNull('compra_inventario_id')->count();
            $totalInvertido  = \App\Models\CompraInventario::deArea($area)->whereNotNull('total_estimado')->sum('total_estimado');
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-sky-500/20">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Órdenes</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $totalCompras }}</p>
            </div>
            <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-500/70 backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/40">
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">Ítems Globales</p>
                <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">{{ (int)$totalItems }}</p>
            </div>
            <div class="rounded-2xl bg-orange-50/90 dark:bg-orange-950/40 border border-orange-200/80 dark:border-orange-500/70 backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-500/40">
                <p class="text-xs font-semibold text-orange-700 dark:text-orange-200 uppercase tracking-wide">Cargadores</p>
                <p class="mt-2 text-2xl font-bold text-orange-800 dark:text-orange-100">{{ $totalCargadores }}</p>
            </div>
            <div class="rounded-2xl bg-indigo-50/90 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-500/70 backdrop-blur-xl px-4 py-3 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-500/40">
                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">Inversión Est.</p>
                <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">${{ number_format($totalInvertido, 0) }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl shadow-md px-5 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative md:col-span-2">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                    <input type="text" wire:model.live.debounce.400ms="busqueda" placeholder="Buscar por proveedor o folio..."
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-white/10 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Fecha / Folio</th>
                            <th class="px-5 py-3 font-semibold">Proveedor</th>
                            <th class="px-5 py-3 font-semibold text-center">Ítems</th>
                            <th class="px-5 py-3 font-semibold text-right">Total</th>
                            <th class="px-5 py-3 font-semibold text-center">Estatus</th>
                            <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-white/5">
                        @forelse($this->compras as $compra)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $compra->fecha_compra->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-500">Folio: {{ $compra->folio ?? 'S/F' }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $compra->proveedor->nombre_empresa ?? 'Sin proveedor' }}</div>
                                    <div class="text-xs text-slate-500">Por: {{ $compra->registradoPor->name ?? 'Usuario' }}</div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 font-medium">
                                        {{ $compra->items_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">${{ number_format($compra->total, 2) }}</div>
                                    <div class="text-xs text-slate-500">{{ $compra->moneda ?? 'MXN' }}</div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($compra->estatus === 'PENDIENTE_GERENTE')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">PENDIENTE GERENTE</span>
                                    @elseif($compra->estatus === 'PENDIENTE_CEO')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">PENDIENTE CEO</span>
                                    @elseif($compra->estatus === 'APROBADA')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">APROBADA</span>
                                    @elseif($compra->estatus === 'RECIBIDA')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-800">RECIBIDA</span>
                                    @elseif($compra->estatus === 'CANCELADA' || $compra->estatus === 'RECHAZADA')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">{{ $compra->estatus }}</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">{{ $compra->estatus }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        {{-- Botón Cancelar (Dueño o CEO) --}}
                                        @if(in_array($compra->estatus, ['PENDIENTE_GERENTE', 'PENDIENTE_CEO', 'APROBADA']) && (Auth::id() === $compra->registrado_por_id || Auth::user()->tienePermiso('prep.compras.aprobar_ceo')))
                                            <button wire:click="cancelarOrden({{ $compra->id }})" wire:confirm="¿Seguro que deseas cancelar esta orden?" class="text-red-500 hover:text-red-700 font-medium text-xs">
                                                Cancelar
                                            </button>
                                        @endif

                                        {{-- Rechazar (Si eres aprobador) --}}
                                        @if(($compra->estatus === 'PENDIENTE_GERENTE' && Auth::user()->tienePermiso('prep.compras.aprobar_gerente')) || ($compra->estatus === 'PENDIENTE_CEO' && Auth::user()->tienePermiso('prep.compras.aprobar_ceo')))
                                            <button wire:click="rechazarOrden({{ $compra->id }})" wire:confirm="¿Rechazar esta orden?" class="text-orange-500 hover:text-orange-700 font-medium text-xs">
                                                Rechazar
                                            </button>
                                        @endif

                                        {{-- Acciones Principales --}}
                                        @if($compra->estatus === 'PENDIENTE_GERENTE' && Auth::user()->tienePermiso('prep.compras.aprobar_gerente'))
                                            <button wire:click="autorizarGerente({{ $compra->id }})" wire:confirm="¿Autorizar como Gerente?" class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                                Autorizar
                                            </button>
                                        @elseif($compra->estatus === 'PENDIENTE_CEO' && Auth::user()->tienePermiso('prep.compras.aprobar_ceo'))
                                            <button wire:click="aprobarOrden({{ $compra->id }})" wire:confirm="¿Aprobar financieramente esta orden (CEO)?" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm">
                                                Aprobar CEO
                                            </button>
                                        @elseif($compra->estatus === 'APROBADA' && Auth::id() === $compra->registrado_por_id)
                                            <button wire:click="recibirOrden({{ $compra->id }})" wire:confirm="¿Seguro que deseas recibir esta mercancía?" class="text-emerald-600 hover:text-emerald-800 font-bold text-sm">
                                                Recibir Mercancía
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-500">No se encontraron órdenes de compra.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-900/50">
                {{ $this->compras->links() }}
            </div>
        </div>

        </div>
    @else
        <div wire:key="vista-nueva" class="space-y-6">
            <!-- VISTA DE NUEVA ORDEN (OMNI SEARCH) -->
        <x-topbar 
            title="Nueva Orden de Compra" 
            chip="{{ $area === 'VENTAS' ? 'Ventas' : 'Preparación' }}" 
            description="Crea una nueva orden de compra añadiendo productos a la tabla." 
        >
            <x-slot name="right">
                <button wire:click="verHistorial"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                    Ver Historial
                </button>
            </x-slot>
        </x-topbar>

        <div class="space-y-6">
            <!-- 1. CABECERA DE LA ORDEN -->
            <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl shadow-sm backdrop-blur-xl p-5">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Información General
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Proveedor *</label>
                        <select wire:model="proveedorId" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="">Selecciona un proveedor</option>
                            @foreach($this->proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                            @endforeach
                        </select>
                        @error('proveedorId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Fecha *</label>
                        <input type="date" wire:model="fechaCompra" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        @error('fechaCompra') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Folio / Referencia</label>
                        <input type="text" wire:model="folio" placeholder="Ej: FAC-1029" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>

                    <div class="{{ $moneda === 'USD' ? 'md:col-span-1' : 'md:col-span-2' }}">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Moneda</label>
                        <select wire:model.live="moneda" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="MXN">MXN</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>

                    @if($moneda === 'USD')
                    <div class="md:col-span-1">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">T. Cambio</label>
                        <input type="number" step="0.01" wire:model="tipoCambio" placeholder="19.50" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        @error('tipoCambio') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>
            </div>

            <!-- 2. TABLA DE PARTIDAS -->
            <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl shadow-sm backdrop-blur-xl flex flex-col overflow-hidden">
                
                <!-- BUSCADOR (AGREGAR PARTIDA) -->
                <div class="p-5 bg-slate-50/50 dark:bg-slate-900/30 border-b border-slate-200 dark:border-slate-800">
                    <div class="relative max-w-3xl mx-auto" x-data @click.outside="$wire.showSearchDropdown = false">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" wire:model.live.debounce.200ms="searchTerm" placeholder="Busca refacciones, productos o suministros para agregar a la orden..."
                            class="w-full pl-11 pr-4 py-3 rounded-xl border-indigo-200 dark:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm shadow-sm transition-all"
                            autocomplete="off"
                            @focus="$wire.showSearchDropdown = true"
                            @click="$wire.showSearchDropdown = true">
                            
                        <div wire:loading wire:target="searchTerm" class="absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>

                        <!-- Dropdown Results -->
                        @if($showSearchDropdown && !empty($searchTerm))
                            <div class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden max-h-80 overflow-y-auto">
                                @forelse($searchResults as $index => $res)
                                    <div wire:click="selectItem({{ $index }})" 
                                         class="p-3 border-b border-slate-100 dark:border-slate-800/50 transition 
                                         {{ !empty($res['agregado']) && $res['tipo'] !== 'cargador' ? 'opacity-40 bg-slate-50 dark:bg-slate-800/20 cursor-not-allowed' : 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $res['nombre'] }}</span>
                                                @if(!empty($res['agregado']) && $res['tipo'] !== 'cargador')
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">✓ YA AGREGADO</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md 
                                                @if($res['tipo']=='pieza') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400
                                                @elseif($res['tipo']=='producto') bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400
                                                @elseif($res['tipo']=='consumible') bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400
                                                @else bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400 @endif uppercase">
                                                {{ $res['badge'] }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $res['extra'] }}</div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">No se encontraron resultados para "{{ $searchTerm }}"</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                    @error('carrito') <p class="text-red-500 mt-3 text-sm text-center font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="overflow-x-auto min-h-[250px]">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-100 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-5 py-3 font-semibold w-20">Tipo</th>
                                <th class="px-5 py-3 font-semibold min-w-[250px]">Producto / Descripción</th>
                                <th class="px-5 py-3 font-semibold w-32">Cantidad</th>
                                <th class="px-5 py-3 font-semibold w-36">Costo Unitario</th>
                                <th class="px-5 py-3 font-semibold w-56">Notas</th>
                                <th class="px-5 py-3 font-semibold text-right w-32">Importe</th>
                                <th class="px-5 py-3 font-semibold text-center w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                            @php
                                $groupedCart = [];
                                foreach($carrito as $index => $item) {
                                    $groupedCart[$item['tipo']][$index] = $item;
                                }
                                
                                $titulosGroup = [
                                    'pieza' => '🔧 Refacciones y Piezas',
                                    'producto' => '📦 Productos y Accesorios',
                                    'consumible' => '💧 Suministros y Consumibles',
                                    'cargador' => '🔌 Cargadores',
                                    'equipo' => '💻 Equipos'
                                ];
                            @endphp

                            @forelse($groupedCart as $tipo => $itemsGroup)
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                                    <td colspan="7" class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest border-y border-slate-200 dark:border-slate-700">
                                        {{ $titulosGroup[$tipo] ?? strtoupper($tipo) }}
                                    </td>
                                </tr>
                                @foreach($itemsGroup as $index => $item)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                                        <td class="px-5 py-4 align-top">
                                            <span class="text-[10px] font-bold px-2 py-1 rounded-md inline-block uppercase tracking-wider
                                                @if($item['tipo']=='pieza') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400
                                                @elseif($item['tipo']=='producto') bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400
                                                @elseif($item['tipo']=='consumible') bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400
                                                @else bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400 @endif">
                                                {{ $item['tipo'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $item['nombre'] }}</div>
                                            
                                            @if($item['tipo'] === 'cargador')
                                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mt-2">
                                                    <input type="text" wire:model="carrito.{{ $index }}.marca" placeholder="Marca" class="w-full text-xs rounded-md border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <input type="text" wire:model="carrito.{{ $index }}.voltaje" placeholder="Voltaje" class="w-full text-xs rounded-md border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <input type="text" wire:model="carrito.{{ $index }}.amperaje" placeholder="Amperaje" class="w-full text-xs rounded-md border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <input type="text" wire:model="carrito.{{ $index }}.punta" placeholder="Punta" class="w-full text-xs rounded-md border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            <input type="number" wire:model.live.debounce.500ms="carrito.{{ $index }}.cantidad" min="1" class="w-full rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-slate-500 dark:text-slate-400 text-sm">$</span>
                                                <input type="number" wire:model.live.debounce.500ms="carrito.{{ $index }}.precio" step="0.01" class="w-full pl-6 rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            <input type="text" wire:model="carrito.{{ $index }}.notas" placeholder="Opcional..." class="w-full rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                        </td>
                                        <td class="px-5 py-4 align-top text-right font-bold text-slate-800 dark:text-slate-200">
                                            ${{ number_format(($item['cantidad'] ?? 0) * ($item['precio'] ?? 0), 2) }}
                                        </td>
                                        <td class="px-5 py-4 align-top text-center">
                                            <button wire:click="removerDelCarrito({{ $index }})" class="text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 p-1.5 rounded-lg transition-all" title="Eliminar partida">
                                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-16 text-center text-slate-500 dark:text-slate-400">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800/80 mb-4 text-slate-400 dark:text-slate-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </div>
                                        <p class="text-base font-medium text-slate-700 dark:text-slate-300">La orden de compra está vacía.</p>
                                        <p class="text-sm mt-1">Usa el buscador de arriba para agregar productos.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. FOOTER: ALMACENES, NOTAS Y TOTALES -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Izquierda: Comentarios y Almacenes (Configuración Logística) -->
                <div class="space-y-6">
                    <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-5 shadow-sm backdrop-blur-xl h-full">
                        <label class="block text-sm font-bold text-slate-800 dark:text-white mb-2">Comentarios de la Orden</label>
                        <textarea wire:model="notasCompra" rows="4" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="Añade instrucciones, condiciones de entrega u observaciones generales..."></textarea>
                    </div>
                </div>

                <!-- Derecha: Financiero, Logística y Totales -->
                <div class="space-y-6">
                    
                    @php
                        $tiposEnCarrito = collect($carrito)->pluck('tipo')->unique()->toArray();
                        $mostrarAlmacenes = count(array_intersect(['pieza', 'producto', 'consumible', 'cargador'], $tiposEnCarrito)) > 0;
                        $mostrarLote = in_array('equipo', $tiposEnCarrito);
                    @endphp

                    @if($mostrarAlmacenes || $mostrarLote)
                        <div class="bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl p-5 shadow-sm backdrop-blur-xl">
                            <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-400 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                Logística de Recepción
                            </h4>
                            
                            <div class="space-y-4">
                                @if($mostrarAlmacenes)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach(['pieza' => 'Piezas', 'producto' => 'Productos', 'consumible' => 'Consumibles', 'cargador' => 'Cargadores'] as $tipoKey => $tipoLabel)
                                            @if(in_array($tipoKey, $tiposEnCarrito))
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Destino para {{ $tipoLabel }} *</label>
                                                    <select wire:model="almacenesSeleccionados.{{ $tipoKey }}" class="block w-full rounded-xl border-emerald-200 dark:border-emerald-700/50 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                                        <option value="">Seleccione almacén...</option>
                                                        @foreach($this->almacenes as $alm)
                                                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('almacenesSeleccionados.'.$tipoKey) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                @if($mostrarLote)
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre de Lote (Equipos CEDIS)</label>
                                        <input type="text" wire:model="nombreLotePropuesto" placeholder="Opcional. Ej: Lote GDL Sep 2026" class="block w-full rounded-xl border-emerald-200 dark:border-emerald-700/50 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                        <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-1.5">Las computadoras se direccionan a CEDIS (Lotes) en automático.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-6 shadow-sm backdrop-blur-xl">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 mb-6 border-b border-slate-100 dark:border-slate-800 pb-6">
                            <div class="w-full sm:w-1/2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">I.V.A.</label>
                                <select wire:model.live="tipoIva" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <option value="INCLUIDO">I.V.A. Incluido (16%)</option>
                                    <option value="MAS_IVA">Precios + I.V.A. (16%)</option>
                                    <option value="EXENTO">Exento de I.V.A.</option>
                                </select>
                            </div>
                            
                            <div class="w-full sm:w-1/2 text-right space-y-2.5">
                                <div class="flex justify-between text-sm text-slate-600 dark:text-slate-400">
                                    <span>Subtotal:</span>
                                    <span class="font-medium text-slate-900 dark:text-white w-32">${{ number_format($subtotal, 2) }} {{ $moneda }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-slate-600 dark:text-slate-400">
                                    <span>I.V.A.:</span>
                                    <span class="font-medium text-slate-900 dark:text-white w-32">${{ number_format($iva, 2) }} {{ $moneda }}</span>
                                </div>
                                <div class="flex justify-between text-xl text-slate-900 dark:text-white font-black pt-3 border-t border-slate-100 dark:border-slate-800">
                                    <span>Total:</span>
                                    <span class="w-40">${{ number_format($total, 2) }} {{ $moneda }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Partidas: <span class="font-bold text-slate-800 dark:text-slate-200">{{ count($carrito) }}</span>
                            </div>
                            <button wire:click="guardarOrden" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all {{ empty($carrito) ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5' }}" {{ empty($carrito) ? 'disabled' : '' }}>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Guardar Orden
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>
    @endif
    </div>
</x-tb-background>

