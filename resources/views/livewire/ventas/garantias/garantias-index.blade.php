<x-tb-background>
    <div class="px-6 py-6 pb-24 mx-auto max-w-7xl relative z-10">
        <x-topbar />
        
        <div class="flex items-center justify-between mb-8 mt-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Garantías y Devoluciones
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Recibe equipos de clientes, reporta fallas y retorna a CEDIS.
                </p>
            </div>
            <div>
                <button wire:click="$set('modalNueva', true)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm bg-indigo-600 hover:bg-indigo-700">
                    + Nueva Recepción de Garantía
                </button>
            </div>
        </div>

        <div class="p-6 mb-6 bg-white/80 border shadow-sm dark:bg-slate-900/80 backdrop-blur-md border-slate-200 dark:border-slate-800 rounded-2xl">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Buscar (Número de Serie)</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800/80 dark:border-slate-700 dark:text-white" placeholder="TB-1234...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Estatus</label>
                    <select wire:model.live="estatusFiltro" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800/80 dark:border-slate-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="PENDIENTE_CEDIS">En camino a CEDIS</option>
                        <option value="EN_REVISION">En Revisión (CEDIS)</option>
                        <option value="REPARADO">Reparado (Regresando a Tienda)</option>
                        <option value="RECHAZADO">Rechazado</option>
                        <option value="ENTREGADO">Entregado al Cliente</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white/80 backdrop-blur-md border shadow-sm dark:bg-slate-900/80 border-slate-200 dark:border-slate-800 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Equipo</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Cliente / Venta</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Reporte</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Estatus</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-slate-500 uppercase dark:text-slate-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200 dark:bg-slate-900/50 dark:divide-slate-800">
                        @forelse($garantias as $g)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ optional($g->equipo)->numero_serie }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($g->equipo)->marca }} {{ optional($g->equipo)->modelo }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-white">{{ optional($g->cliente)->nombre ?? 'Venta Mostrador' }}</div>
                                    <div class="text-xs text-blue-500">Folio: {{ optional($g->venta)->folio }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-900 dark:text-white truncate max-w-xs" title="{{ $g->falla_reportada }}">
                                        {{ $g->falla_reportada }}
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $g->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 text-indigo-800 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                                        {{ str_replace('_', ' ', $g->estatus) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                    No hay garantías registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $garantias->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL NUEVA GARANTIA --}}
    @if($modalNueva)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                    <div class="relative overflow-hidden text-left transition-all transform bg-white rounded-2xl shadow-xl dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-700">
                        <div class="px-4 pt-5 pb-4 bg-white dark:bg-slate-900 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full dark:bg-blue-900/50 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg font-medium leading-6 text-slate-900 dark:text-white" id="modal-title">
                                        Recepción de Garantía
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        
                                        {{-- BUSQUEDA --}}
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Escanear Número de Serie</label>
                                            <div class="flex mt-1 shadow-sm rounded-md">
                                                <input type="text" wire:model="serialBuscado" class="flex-1 block w-full border-slate-300 rounded-none rounded-l-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white" placeholder="TB-...">
                                                <button type="button" wire:click="buscarPorSerie" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-r-md bg-slate-600 hover:bg-slate-700">
                                                    Buscar
                                                </button>
                                            </div>
                                            @error('serialBuscado') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- RESULTADO Y FORMULARIO --}}
                                        @if($equipoEncontrado)
                                            <div class="p-3 mt-2 rounded-lg bg-slate-50 dark:bg-slate-800">
                                                <div class="text-sm font-semibold text-slate-900 dark:text-white">Equipo Encontrado:</div>
                                                <div class="text-sm text-slate-600 dark:text-slate-400">{{ $equipoEncontrado->marca }} {{ $equipoEncontrado->modelo }} ({{ $equipoEncontrado->numero_serie }})</div>
                                                <div class="text-sm text-blue-600 mt-1 font-medium">Venta Vinculada: Folio {{ $ventaEncontrada->folio }}</div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Falla Reportada por el Cliente</label>
                                                <textarea wire:model="fallaReportada" rows="3" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white"></textarea>
                                                @error('fallaReportada') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Condiciones de Recepción</label>
                                                <p class="text-[10px] text-slate-400">Ej. Pantalla rayada, incluye cargador genérico.</p>
                                                <textarea wire:model="condicionesRecepcion" rows="2" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white"></textarea>
                                                @error('condicionesRecepcion') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 sm:flex sm:flex-row-reverse sm:px-6">
                            @if($equipoEncontrado)
                                <button type="button" wire:click="procesarRecepcionGarantia" wire:loading.attr="disabled" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Generar Retorno a CEDIS
                                </button>
                            @endif
                            <button type="button" wire:click="$set('modalNueva', false)" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium bg-white border rounded-lg shadow-sm text-slate-700 border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-tb-background>
