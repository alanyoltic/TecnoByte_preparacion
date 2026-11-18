<x-tb-background>
    <div class="px-6 py-6 pb-24 mx-auto max-w-7xl relative z-10">
        <x-topbar />
        
        <div class="flex items-center justify-between mb-8 mt-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Cortes de Caja
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Controla tus turnos, fondos iniciales y cierres del día.
                </p>
            </div>
            <div>
                @if(!$corteActual)
                    <button wire:click="$set('modalAbrir', true)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm bg-indigo-600 hover:bg-indigo-700">
                        Abrir Caja
                    </button>
                @endif
            </div>
        </div>

        @if($corteActual)
            <div class="p-6 mb-6 bg-white/80 border shadow-sm dark:bg-slate-900/80 backdrop-blur-md border-indigo-200 dark:border-indigo-900 rounded-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                        Caja Abierta
                    </span>
                </div>
                
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Turno Actual</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-slate-500">Apertura</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $corteActual->fecha_apertura->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Fondo Inicial</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">${{ number_format($corteActual->fondo_inicial, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Ventas en Efectivo</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">+ ${{ number_format($corteActual->total_ventas_efectivo, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Calculado en Caja</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($corteActual->total_calculado, 2) }}</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button wire:click="prepararCierre" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm bg-red-600 hover:bg-red-700">
                        Realizar Corte y Cerrar Caja
                    </button>
                </div>
            </div>
        @else
            <div class="p-8 mb-6 text-center bg-white/50 border shadow-sm dark:bg-slate-900/50 backdrop-blur-md border-slate-200 dark:border-slate-800 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-white">Caja Cerrada</h3>
                <p class="mt-1 text-sm text-slate-500">Abre caja para comenzar a cobrar ventas en el Punto de Venta.</p>
                <div class="mt-6">
                    <button wire:click="$set('modalAbrir', true)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm bg-indigo-600 hover:bg-indigo-700">
                        Abrir Caja
                    </button>
                </div>
            </div>
        @endif

        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 mt-8">Historial de Cortes</h2>
        <div class="overflow-hidden bg-white/80 backdrop-blur-md border shadow-sm dark:bg-slate-900/80 border-slate-200 dark:border-slate-800 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Apertura / Cierre</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Calculado</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Declarado</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200 dark:bg-slate-900/50 dark:divide-slate-800">
                        @forelse($cortesPasados as $corte)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $corte->fecha_apertura->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs text-slate-500">Cierre: {{ $corte->fecha_cierre->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    ${{ number_format($corte->total_calculado, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    ${{ number_format($corte->total_declarado, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($corte->diferencia == 0)
                                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Cuadrado ($0.00)</span>
                                    @elseif($corte->diferencia > 0)
                                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-blue-800 bg-blue-100 rounded-full">Sobrante (+${{ number_format($corte->diferencia, 2) }})</span>
                                    @else
                                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Faltante (${{ number_format($corte->diferencia, 2) }})</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                    No tienes cortes de caja anteriores.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL ABRIR CAJA --}}
    @if($modalAbrir)
        <div class="relative z-50">
            <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4 text-center">
                    <div class="relative overflow-hidden text-left transition-all transform bg-white rounded-2xl shadow-xl dark:bg-slate-900 sm:w-full sm:max-w-sm border border-slate-200 dark:border-slate-700">
                        <div class="px-4 pt-5 pb-4 bg-white dark:bg-slate-900 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-4">Abrir Caja</h3>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Fondo Inicial ($)</label>
                                <input type="number" step="0.01" wire:model="fondoInicial" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white" placeholder="0.00">
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" wire:click="abrirCaja" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Confirmar y Abrir
                            </button>
                            <button type="button" wire:click="$set('modalAbrir', false)" class="mt-3 inline-flex justify-center w-full px-4 py-2 text-base font-medium bg-white border rounded-lg shadow-sm text-slate-700 border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CERRAR CAJA --}}
    @if($modalCerrar)
        <div class="relative z-50">
            <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4 text-center">
                    <div class="relative overflow-hidden text-left transition-all transform bg-white rounded-2xl shadow-xl dark:bg-slate-900 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-700">
                        <div class="px-4 pt-5 pb-4 bg-white dark:bg-slate-900 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-bold text-red-600 mb-4">Cerrar Caja</h3>
                            
                            <div class="mb-4 p-3 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <div class="text-sm text-slate-500">Sistema espera en caja (Fondo + Ventas):</div>
                                <div class="text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($corteActual->total_calculado, 2) }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">¿Cuánto efectivo declaras realmente?</label>
                                <input type="number" step="0.01" wire:model.live="declaradoEfectivo" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white text-xl">
                            </div>
                            
                            @if(is_numeric($declaradoEfectivo) && $declaradoEfectivo != $corteActual->total_calculado)
                                <div class="mt-2 text-sm {{ $declaradoEfectivo > $corteActual->total_calculado ? 'text-blue-500' : 'text-red-500' }}">
                                    Diferencia detectada: ${{ number_format($declaradoEfectivo - $corteActual->total_calculado, 2) }}
                                </div>
                            @endif
                        </div>
                        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" wire:click="cerrarCaja" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Declarar y Cerrar Turno
                            </button>
                            <button type="button" wire:click="$set('modalCerrar', false)" class="mt-3 inline-flex justify-center w-full px-4 py-2 text-base font-medium bg-white border rounded-lg shadow-sm text-slate-700 border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-tb-background>
