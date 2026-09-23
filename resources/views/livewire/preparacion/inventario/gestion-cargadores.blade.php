<div>
    <!-- Encabezado y Acciones -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cargadores</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Gestión del inventario de cargadores independientes.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button wire:click="abrirModalNuevo"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Registrar Cargador
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
            <input type="text" wire:model.live.debounce.300ms="search" id="search"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Serie, marca...">
        </div>
        <div class="w-full sm:w-64">
            <label for="filtroEstatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estatus</label>
            <select wire:model.live="filtroEstatus" id="filtroEstatus"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Todos</option>
                <option value="DISPONIBLE">Disponible</option>
                <option value="ASIGNADO">Asignado</option>
                <option value="VENDIDO">Vendido</option>
                <option value="EN_GARANTIA">En Garantía</option>
                <option value="SCRAP">Scrap</option>
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">Serie</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Marca / Especificaciones</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Estatus</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Origen</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-800">
                            @forelse($cargadores as $cargador)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                    {{ $cargador->serie ?? 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $cargador->marca ?? 'Genérico' }}</div>
                                    <div class="text-xs">{{ $cargador->voltaje }}V - {{ $cargador->amperaje }}A (Punta: {{ $cargador->punta }})</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @php
                                        $color = match($cargador->estatus->value) {
                                            'DISPONIBLE' => 'bg-green-100 text-green-800',
                                            'ASIGNADO' => 'bg-blue-100 text-blue-800',
                                            'VENDIDO' => 'bg-gray-100 text-gray-800',
                                            'EN_GARANTIA' => 'bg-yellow-100 text-yellow-800',
                                            'SCRAP' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $color }}">
                                        {{ $cargador->estatus->getLabel() }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    @if($cargador->lote)
                                        <span class="text-xs">Lote: {{ $cargador->lote->nombre_lote }}</span>
                                    @elseif($cargador->compraInventario)
                                        <span class="text-xs">Compra: {{ $cargador->compraInventario->folio ?? $cargador->compraInventario->id }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button wire:click="abrirModalEditar({{ $cargador->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Editar</button>
                                    @if($cargador->estatus->value !== 'SCRAP' && $cargador->estatus->value !== 'VENDIDO')
                                        <button wire:click="eliminar({{ $cargador->id }})" wire:confirm="¿Estás seguro de enviar este cargador a SCRAP?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Baja</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-sm text-gray-500">
                                    No se encontraron cargadores.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $cargadores->links() }}
    </div>

    <!-- Modal Formulario -->
    <div x-data="{ open: @entangle('modalAbierto') }" x-show="open" class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.outside="open = false" class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ $cargadorId ? 'Editar Cargador' : 'Registrar Cargador' }}
                            </h3>
                            <div class="mt-2 text-left">
                                <form wire:submit.prevent="guardar">
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <label for="serie" class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Serie</label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="serie" id="serie" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                                                @error('serie') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div>
                                            <label for="marca" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="marca" id="marca" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="punta" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Punta / Conector</label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="punta" id="punta" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600" placeholder="Ej: Azul HP, Amarilla Lenovo">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="voltaje" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Voltaje (V)</label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="voltaje" id="voltaje" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="amperaje" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amperaje (A)</label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="amperaje" id="amperaje" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="estatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estatus</label>
                                            <select wire:model="estatus" id="estatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                                                <option value="DISPONIBLE">Disponible</option>
                                                <option value="ASIGNADO">Asignado</option>
                                                <option value="EN_GARANTIA">En Garantía</option>
                                                <option value="SCRAP">Scrap (Baja)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                        <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-2 sm:text-sm">
                                            Guardar
                                        </button>
                                        <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
