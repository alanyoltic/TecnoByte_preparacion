<x-tb-background>
    <div class="px-6 py-6 pb-24 mx-auto max-w-7xl relative z-10">
        <x-topbar />
        
        <div class="flex items-center justify-between mb-8 mt-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Historial de Ventas
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Consulta el historial, cancela operaciones y gestiona las ventas realizadas.
                </p>
            </div>
            <div>
                <a href="{{ route('ventas.pos') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm bg-indigo-600 hover:bg-indigo-700">
                    Ir al Punto de Venta
                </a>
            </div>
        </div>

        <div class="p-6 mb-6 bg-white/80 border shadow-sm dark:bg-slate-900/80 backdrop-blur-md border-slate-200 dark:border-slate-800 rounded-2xl">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Buscar (Folio / Cliente)</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800/80 dark:border-slate-700 dark:text-white" placeholder="F-001...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Estatus</label>
                    <select wire:model.live="estatus" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800/80 dark:border-slate-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="COMPLETADA">Completada</option>
                        <option value="CANCELADA">Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Fecha Inicio</label>
                    <input type="date" wire:model.live="fecha_inicio" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800/80 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Fecha Fin</label>
                    <input type="date" wire:model.live="fecha_fin" class="block w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-800/80 dark:border-slate-700 dark:text-white">
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white/80 backdrop-blur-md border shadow-sm dark:bg-slate-900/80 border-slate-200 dark:border-slate-800 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Folio / Fecha</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Vendedor</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Total</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-slate-500 uppercase dark:text-slate-400">Estatus</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-slate-500 uppercase dark:text-slate-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200 dark:bg-slate-900/50 dark:divide-slate-800">
                        @forelse($ventas as $venta)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $venta->folio }}</div>
                                    <div class="text-xs text-slate-500">{{ $venta->created_at->format('d/m/Y H:i') }}</div>
                                    @if($venta->sucursal)
                                        <div class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 mt-0.5 flex items-center gap-1">
                                            <span>📍</span> {{ $venta->sucursal->nombre }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ optional($venta->cliente)->nombre ?? 'Venta Mostrador' }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($venta->cliente)->telefono ?? '--' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-white">{{ optional($venta->vendedor)->nombre }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">${{ number_format($venta->total, 2) }}</div>
                                    <div class="text-xs text-slate-500">{{ $venta->metodo_pago }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($venta->estatus === 'COMPLETADA')
                                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                            Completada
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200">
                                            Cancelada
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-1">por {{ optional(\App\Models\User::find($venta->cancelada_por_id))->nombre }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Detalles
                                    </button>
                                    @if($venta->estatus === 'COMPLETADA')
                                        <button wire:click="cancelarVenta({{ $venta->id }})" 
                                                wire:confirm="¿Estás seguro de cancelar esta venta? Los artículos retornarán a inventario."
                                                class="ml-3 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Cancelar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                    No se encontraron ventas con los filtros actuales.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $ventas->links() }}
            </div>
        </div>
    </div>
</x-tb-background>
