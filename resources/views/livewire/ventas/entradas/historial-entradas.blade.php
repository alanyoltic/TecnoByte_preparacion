<div class="space-y-6">
    <div class="flex justify-between items-center">
        <x-topbar title="Historial de Entradas de Accesorios" 
                  chip="Ventas" 
                  description="Registro y trazabilidad de toda la mercancía recibida en el área." />
        
        <a href="{{ route('ventas.entradas.crear') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Registrar Entrada
        </a>
    </div>

    <!-- Filtros -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white p-4 rounded-lg shadow">
        <div class="w-full sm:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Proveedor o Folio</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="w-full sm:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
            <input wire:model.live="fecha_inicio" type="date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="w-full sm:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
            <input wire:model.live="fecha_fin" type="date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
    </div>

    <!-- Lista de Entradas -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($entradas as $entrada)
                <li>
                    <div class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-indigo-600 truncate">
                                        Entrada #{{ $entrada->id }} 
                                        @if($entrada->folio_factura)
                                            <span class="text-gray-500 font-normal">| Folio: {{ $entrada->folio_factura }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Total: ${{ number_format($entrada->total_estimado, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex sm:space-x-6 text-sm text-gray-500">
                                    <p class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $entrada->proveedor->display_name ?? 'Compra Local / Sin Proveedor' }}
                                    </p>
                                    <p class="mt-2 flex items-center sm:mt-0">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        {{ $entrada->totalPiezas() }} artículos recibidos
                                    </p>
                                    <p class="mt-2 flex items-center sm:mt-0">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Recibió: {{ $entrada->registradoPor->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p>
                                        Registrado el <time datetime="{{ $entrada->fecha->format('Y-m-d') }}">{{ $entrada->fecha->format('d/m/Y') }}</time>
                                    </p>
                                </div>
                            </div>
                            <!-- Despliegue de items resumido -->
                            <div class="mt-3 text-sm text-gray-500 bg-gray-50 rounded p-2">
                                <span class="font-semibold">Detalle:</span> 
                                @foreach($entrada->items as $item)
                                    {{ $item->cantidad }}x {{ $item->producto->nombre ?? 'Producto Desconocido' }} 
                                    @if(!$loop->last) &bull; @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li>
                    <div class="px-4 py-12 text-center text-gray-500">
                        No hay registros de entradas que coincidan con la búsqueda.
                    </div>
                </li>
            @endforelse
        </ul>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $entradas->links() }}
        </div>
    </div>
</div>
