<div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">
    <x-toast />

    <x-topbar
        title="Catálogo de Equipos"
        chip="Preparación · Gestión de modelos"
    >
        <div class="flex items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 group-focus-within:text-[#FF9521] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar marca o modelo..."
                       class="block w-64 pl-10 pr-4 py-2 bg-white/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-[#FF9521] focus:border-transparent outline-none transition-all">
            </div>
        </div>
    </x-topbar>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Formulario de Edición/Creación --}}
        <div class="lg:col-span-1">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-6 shadow-sm sticky top-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-[#FF9521]/10 flex items-center justify-center">
                        <svg class="w-5 h-4 text-[#FF9521]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    {{ $editingId ? 'Editar Modelo' : 'Nuevo Modelo' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-500 uppercase ml-1">Marca</label>
                        <input type="text" wire:model="marca" placeholder="Ej: Dell"
                               class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-[#FF9521] transition-all">
                        @error('marca') <p class="text-[0.7rem] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-500 uppercase ml-1">Modelo</label>
                        <input type="text" wire:model="modelo" placeholder="Ej: Latitude 7490"
                               class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-[#FF9521] transition-all">
                        @error('modelo') <p class="text-[0.7rem] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-500 uppercase ml-1">Tipo de Equipo</label>
                        <select wire:model="tipo_equipo"
                                class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-[#FF9521] transition-all">
                            <option value="">Seleccionar tipo...</option>
                            <option value="Laptop">Laptop</option>
                            <option value="Desktop">Desktop</option>
                            <option value="All-in-one">All-in-one</option>
                            <option value="Monitor">Monitor</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Servidor">Servidor</option>
                        </select>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="submit" class="flex-1 bg-[#FF9521] hover:bg-[#e6861d] text-white font-bold py-2.5 rounded-2xl transition-all shadow-lg shadow-[#FF9521]/20">
                            {{ $editingId ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="cancelEdit" class="px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-200 transition-all">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Lista del Catálogo --}}
        <div class="lg:col-span-2">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800/60 rounded-3xl overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                            <th class="px-6 py-4 text-[0.7rem] font-bold uppercase tracking-wider text-slate-500">Marca / Modelo</th>
                            <th class="px-6 py-4 text-[0.7rem] font-bold uppercase tracking-wider text-slate-500 text-center">Vínculos</th>
                            <th class="px-6 py-4 text-[0.7rem] font-bold uppercase tracking-wider text-slate-500 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($items as $item)
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $item->marca }}</span>
                                        <span class="text-xs text-slate-500">{{ $item->modelo }}</span>
                                        @if($item->tipo_equipo)
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[0.6rem] font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 w-fit">
                                                {{ $item->tipo_equipo }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <div class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 flex flex-col items-center min-w-[60px]" title="Lotes vinculados">
                                            <span class="text-[0.6rem] text-slate-400 uppercase font-bold">Lotes</span>
                                            <span class="text-sm font-bold {{ $item->lotes_vinculados_count > 0 ? 'text-emerald-500' : 'text-slate-400' }}">
                                                {{ $item->lotes_vinculados_count }}
                                            </span>
                                        </div>
                                        <div class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 flex flex-col items-center min-w-[60px]" title="Equipos individuales vinculados">
                                            <span class="text-[0.6rem] text-slate-400 uppercase font-bold">Eq.</span>
                                            <span class="text-sm font-bold {{ $item->equipos_vinculados_count > 0 ? 'text-[#FF9521]' : 'text-slate-400' }}">
                                                {{ $item->equipos_vinculados_count }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <button wire:click="edit({{ $item->id }})" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-[#FF9521] hover:bg-[#FF9521]/10 transition-all" title="Editar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        
                                        @if($item->lotes_vinculados_count == 0 && $item->equipos_vinculados_count == 0)
                                            <button wire:click="confirmDelete({{ $item->id }})" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-red-500 hover:bg-red-500/10 transition-all" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h14" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-500 italic">
                                    No se encontraron modelos que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/30">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmación de Eliminación --}}
    @if($confirmingDeletionId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 max-w-sm w-full shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-center text-slate-800 dark:text-slate-100 mb-2">¿Eliminar del catálogo?</h3>
                <p class="text-sm text-center text-slate-500 dark:text-slate-400 mb-8">Esta acción no se puede deshacer. Solo se eliminan modelos que no tienen equipos registrados.</p>
                <div class="flex gap-3">
                    <button wire:click="$set('confirmingDeletionId', null)" class="flex-1 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-200 transition-all">
                        Cancelar
                    </button>
                    <button wire:click="delete" class="flex-1 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-bold transition-all shadow-lg shadow-red-600/20">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
