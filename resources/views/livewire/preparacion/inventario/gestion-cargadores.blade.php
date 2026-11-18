<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-toast />

        {{-- TOPBAR GLASS --}}
        <x-topbar
            title="Inventario de Cargadores"
            chip="Preparación · Lotes y Accesorios"
            description="Gestión y control de cargadores independientes, voltaje, amperaje, tipo de conector y estatus."
        >
            <div class="flex items-center gap-3">
                <button wire:click="abrirModalNuevo"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold
                               bg-[#FF9521] hover:bg-[#e0821b] text-slate-950
                               shadow-lg shadow-[#FF9521]/20 active:scale-95 transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Registrar Cargador</span>
                </button>
            </div>
        </x-topbar>

        <!-- Filtros Glass -->
        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Buscar</label>
                    <input type="text" wire:model.live.debounce.300ms="search" id="search"
                           class="w-full px-4 py-2.5 rounded-2xl text-sm
                                  bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                  text-slate-800 dark:text-slate-200 placeholder-slate-400
                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all"
                           placeholder="Buscar por número de serie, marca...">
                </div>
                <div class="w-full sm:w-64">
                    <label for="filtroEstatus" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Estatus</label>
                    <select wire:model.live="filtroEstatus" id="filtroEstatus"
                            class="w-full px-4 py-2.5 rounded-2xl text-sm
                                   bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                   text-slate-800 dark:text-slate-200
                                   focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                        <option value="">Todos los estatus</option>
                        <option value="DISPONIBLE">Disponible</option>
                        <option value="ASIGNADO">Asignado</option>
                        <option value="VENDIDO">Vendido</option>
                        <option value="EN_GARANTIA">En Garantía</option>
                        <option value="SCRAP">Scrap (Baja)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabla Glass -->
        <div class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-white/10
                                   bg-slate-100/60 dark:bg-slate-950/40 text-left">
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Serie</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Marca / Especificaciones</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Estatus</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Origen / Lote</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                        @forelse($cargadores as $cargador)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                        {{ $cargador->serie ?? 'PENDIENTE' }}
                                    </span>
                                    @if($cargador->serie_fabricante)
                                        <div class="text-[10px] text-slate-400 font-mono mt-1" title="Serie del fabricante">
                                            Fab: {{ $cargador->serie_fabricante }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $cargador->marca ?? 'Genérico' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $cargador->voltaje }}V - {{ $cargador->amperaje }}A (Punta: {{ $cargador->punta }})</div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $color = match($cargador->estatus->value) {
                                            'DISPONIBLE'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            'ASIGNADO'    => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                            'VENDIDO'     => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                            'EN_GARANTIA' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                            'SCRAP'       => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                            default       => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                        {{ $cargador->estatus->getLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                                    @if($cargador->lote)
                                        <span>Lote: <strong class="text-slate-800 dark:text-slate-200">{{ $cargador->lote->nombre_lote }}</strong></span>
                                    @elseif($cargador->compraInventario)
                                        <span>Compra: <strong class="text-slate-800 dark:text-slate-200">{{ $cargador->compraInventario->folio ?? $cargador->compraInventario->id }}</strong></span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="abrirModalEditar({{ $cargador->id }})"
                                                class="p-2 rounded-xl text-slate-400 hover:text-[#FF9521] hover:bg-[#FF9521]/10 transition-colors"
                                                title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        @if($cargador->estatus->value !== 'SCRAP' && $cargador->estatus->value !== 'VENDIDO')
                                            <button wire:click="eliminar({{ $cargador->id }})"
                                                    wire:confirm="¿Estás seguro de enviar este cargador a SCRAP?"
                                                    class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                                                    title="Enviar a Scrap">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400 text-sm">
                                    No se encontraron cargadores registrados en el inventario.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-2">
            {{ $cargadores->links() }}
        </div>

        <!-- Modal Formulario Glass -->
        <div x-data="{ open: @entangle('modalAbierto') }" x-show="open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.outside="open = false"
                         class="relative transform overflow-hidden rounded-3xl bg-white/95 dark:bg-slate-900/95 border border-slate-200/80 dark:border-white/10 px-6 pt-6 pb-6 text-left shadow-2xl backdrop-blur-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div>
                            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-white/10 pb-4 mb-4">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                                    {{ $cargadorId ? 'Editar Cargador' : 'Registrar Cargador' }}
                                </h3>
                                <button @click="open = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-left">
                                <form wire:submit.prevent="guardar" class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label for="serie" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No. Serie Interna TecnoByte</label>
                                                <span class="text-[11px] text-slate-400">Autogenerada si se deja vacía</span>
                                            </div>
                                            <input type="text" wire:model="serie" id="serie" placeholder="Ej: CARG-TB18112026-1 (o dejar vacío)"
                                                   class="w-full px-4 py-2.5 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                            @error('serie') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label for="serie_fabricante" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No. Serie Fabricante (Opcional)</label>
                                                <span class="text-[11px] text-slate-400">Para garantías físicas</span>
                                            </div>
                                            <input type="text" wire:model="serie_fabricante" id="serie_fabricante" placeholder="Serie del fabricante (si viene etiquetada)..."
                                                   class="w-full px-4 py-2.5 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                            @error('serie_fabricante') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="marca" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Marca</label>
                                            <input type="text" wire:model="marca" id="marca"
                                                   class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                        </div>
                                        <div>
                                            <label for="punta" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Punta / Conector</label>
                                            <input type="text" wire:model="punta" id="punta" placeholder="Ej: Azul HP, USB-C"
                                                   class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                        </div>
                                        <div>
                                            <label for="voltaje" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Voltaje (V)</label>
                                            <input type="text" wire:model="voltaje" id="voltaje" placeholder="19.5"
                                                   class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                        </div>
                                        <div>
                                            <label for="amperaje" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Amperaje (A)</label>
                                            <input type="text" wire:model="amperaje" id="amperaje" placeholder="3.34"
                                                   class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="estatus" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Estatus</label>
                                            <select wire:model="estatus" id="estatus"
                                                    class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/70 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                                <option value="DISPONIBLE">Disponible</option>
                                                <option value="ASIGNADO">Asignado</option>
                                                <option value="EN_GARANTIA">En Garantía</option>
                                                <option value="SCRAP">Scrap (Baja)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80 dark:border-white/10">
                                        <button type="button" @click="open = false"
                                                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-[#FF9521] hover:bg-[#e0821b] shadow-lg shadow-[#FF9521]/20 transition-all duration-150 active:scale-95">
                                            Guardar Cargador
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
</x-tb-background>
