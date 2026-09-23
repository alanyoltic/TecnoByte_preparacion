<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">
        <x-topbar title="Catálogo de Artículos" 
                  chip="{{ $area === 'PREPARACION' ? 'Preparación' : 'Ventas' }}" 
                  description="Administra los catálogos base de productos y componentes." />
    
        <!-- TABS -->
        <div class="border-b border-slate-200 dark:border-white/10">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                @if($area === 'PREPARACION')
                <button wire:click="changeTab('equipos')" class="{{ $tab === 'equipos' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Catálogo de Equipos
                </button>
                <button wire:click="changeTab('piezas')" class="{{ $tab === 'piezas' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Piezas y Componentes
                </button>
                @endif
                
                @if($area === 'VENTAS')
                <button wire:click="changeTab('accesorios')" class="{{ $tab === 'accesorios' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Productos / Accesorios
                </button>
                @endif
    
                <button wire:click="changeTab('insumos')" class="{{ $tab === 'insumos' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Insumos y Suministros
                </button>
            </nav>
        </div>
    
        <!-- Filters and Actions -->
        <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl shadow-md px-5 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar..."
                               class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <select wire:model.live="categoria_filter" class="block w-full sm:w-48 py-2 pl-3 pr-10 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las Categorías</option>
                        @php
                            if ($tab === 'equipos') $cats = $tiposEquipo;
                            elseif ($tab === 'piezas') $cats = $categoriasPiezas;
                            elseif ($tab === 'accesorios') $cats = $categoriasProductos;
                            else $cats = $categoriasConsumibles;
                        @endphp
                        @foreach($cats as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
        
                <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-500 border border-transparent shadow-md shadow-indigo-800/40 text-sm font-semibold rounded-xl text-white hover:shadow-indigo-500/60 hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nuevo {{ $tab === 'equipos' ? 'Equipo' : ($tab === 'piezas' ? 'Componente' : ($tab === 'accesorios' ? 'Accesorio' : 'Insumo')) }}
                </button>
            </div>
        </div>
    
        <!-- Table -->
        <div class="bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-white/10 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Identificador</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Nombre / Descripción</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Categoría / Tipo</th>
                            @if($tab === 'accesorios')
                            <th scope="col" class="px-5 py-3 font-semibold text-right">P. Venta</th>
                            @endif
                            <th scope="col" class="px-5 py-3 font-semibold text-center">Estatus</th>
                            <th scope="col" class="px-5 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-white/5">
                        @php
                            if ($tab === 'equipos') $items = $this->equipos;
                            elseif ($tab === 'piezas') $items = $this->piezas;
                            elseif ($tab === 'accesorios') $items = $this->accesorios;
                            else $items = $this->insumos;
                        @endphp
                        
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($tab === 'equipos' || $tab === 'piezas')
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">ID: {{ $item->id }}</div>
                                        @if($tab === 'piezas' && $item->requiere_serie)
                                            <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 uppercase tracking-wider">
                                                Req. Serie
                                            </span>
                                        @endif
                                    @else
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $item->sku ?? 'Sin SKU' }}</div>
                                        <div class="text-xs text-slate-500 flex items-center mt-1">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                            {{ $item->codigo_barras ?? 'Sin código' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($tab === 'equipos')
                                        <div class="text-sm text-slate-900 dark:text-slate-100 font-bold uppercase">{{ $item->marca }} {{ $item->modelo }}</div>
                                    @else
                                        <div class="text-sm text-slate-900 dark:text-slate-100 font-semibold">{{ $item->nombre }}</div>
                                        @if($tab !== 'piezas')
                                        <div class="text-xs text-slate-500 mt-0.5">{{ Str::limit($item->descripcion, 50) }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">
                                        {{ $tab === 'equipos' ? ($tiposEquipo[$item->tipo_equipo] ?? $item->tipo_equipo) : ($cats[$item->categoria] ?? $item->categoria) }}
                                    </span>
                                </td>
                                @if($tab === 'accesorios')
                                <td class="px-5 py-4 whitespace-nowrap text-right text-sm text-slate-900 dark:text-slate-100 font-bold">
                                    ${{ number_format($item->precio_venta, 2) }}
                                </td>
                                @endif
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <button wire:click="toggleActivo({{ $item->id }}, '{{ $tab }}')" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $item->activo ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600' }}">
                                        <span class="sr-only">Toggle activo</span>
                                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ $item->activo ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit{{ ucfirst(rtrim($tab, 's')) }}({{ $item->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                    No se encontraron registros. 
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-900/50">
                {{ $items->links() }}
            </div>
        </div>
    
        <!-- Modal Dinámico -->
        @if($showModal)
        <x-modal name="modal-catalogo" :show="true">
            <div class="px-6 py-5 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $isEditing ? 'Editar' : 'Nuevo' }} {{ $tab === 'equipos' ? 'Equipo' : ($tab === 'piezas' ? 'Componente' : ($tab === 'accesorios' ? 'Accesorio' : 'Insumo')) }}
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    
                    @if($tab === 'equipos')
                        <!-- FORMULARIO EQUIPOS -->
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Marca <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="marca" placeholder="Ej. HP, Dell" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm uppercase text-slate-900 dark:text-slate-100">
                            @error('marca') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Modelo <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="modelo" placeholder="Ej. ProBook 440" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm uppercase text-slate-900 dark:text-slate-100">
                            @error('modelo') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo de Equipo <span class="text-red-500">*</span></label>
                            <select wire:model.defer="tipo_equipo" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                                @foreach($tiposEquipo as $key => $val)
                                    <option value="{{ $key }}">{{ $val }}</option>
                                @endforeach
                            </select>
                            @error('tipo_equipo') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
    
                    @else
                        <!-- FORMULARIO PIEZAS / INSUMOS / ACCESORIOS -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="nombre" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                            @error('nombre') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
    
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría <span class="text-red-500">*</span></label>
                            <select wire:model.defer="categoria" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                                @foreach($cats as $key => $val)
                                    <option value="{{ $key }}">{{ $val }}</option>
                                @endforeach
                            </select>
                            @error('categoria') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
    
                        @if($tab === 'piezas')
                            <div class="sm:col-span-2">
                                <div class="mt-2 flex items-center">
                                    <input id="requiere_serie" type="checkbox" wire:model.defer="requiere_serie" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-800">
                                    <label for="requiere_serie" class="ml-2 block text-sm font-medium text-slate-900 dark:text-slate-200">
                                        Requiere Número de Serie
                                    </label>
                                </div>
                            </div>
                        @else
                            <!-- SKU -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU / Clave Interna</label>
                                <input type="text" wire:model.defer="sku" placeholder="Auto-generado" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                            </div>
    
                            <!-- Código de Barras -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Código de Barras</label>
                                <input type="text" wire:model.defer="codigo_barras" placeholder="Escanear aquí..." class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                            </div>
                            
                            @if($tab === 'accesorios')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Venta ($) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" wire:model.defer="precio_venta" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                                    @error('precio_venta') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio Compra ($)</label>
                                    <input type="number" step="0.01" wire:model.defer="precio_compra" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Marca</label>
                                    <input type="text" wire:model.defer="marca" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100">
                                </div>
                            @endif
    
                            <!-- Descripción -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Descripción / Detalles</label>
                                <textarea wire:model.defer="descripcion" rows="3" class="mt-1 block w-full bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 dark:text-slate-100"></textarea>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-100 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-right space-x-2 rounded-b-lg flex justify-end gap-2">
                <button wire:click="$set('showModal', false)" type="button" class="inline-flex justify-center items-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    Cancelar
                </button>
                <button wire:click="save" type="button" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    Guardar
                </button>
            </div>
        </x-modal>
        @endif
    </div>
</x-tb-background>
