<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-toast />

        {{-- TOPBAR GLASS --}}
        <x-topbar
            title="Historial de Entradas de Mercancía"
            chip="Ventas · Recepciones y Restock"
            description="Registro histórico y trazabilidad de toda la mercancía recibida y cargada a inventario."
        >
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('ventas.entradas.crear') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold
                           bg-[#FF9521] hover:bg-[#e0821b] text-slate-950
                           shadow-lg shadow-[#FF9521]/20 active:scale-95 transition-all duration-150"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Registrar Entrada</span>
                </a>
            </div>
        </x-topbar>

        <!-- Filtros Glass -->
        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                        Buscar Proveedor o Folio
                    </label>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre, folio..."
                           class="w-full px-4 py-2.5 rounded-2xl text-sm
                                  bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                  text-slate-800 dark:text-slate-200 placeholder-slate-400
                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Desde</label>
                    <input wire:model.live="fecha_inicio" type="date"
                           class="w-full px-4 py-2.5 rounded-2xl text-sm
                                  bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                  text-slate-800 dark:text-slate-200
                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Hasta</label>
                    <input wire:model.live="fecha_fin" type="date"
                           class="w-full px-4 py-2.5 rounded-2xl text-sm
                                  bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                  text-slate-800 dark:text-slate-200
                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <!-- Lista de Entradas Glass -->
        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl overflow-hidden">
            <ul role="list" class="divide-y divide-slate-200/60 dark:divide-white/5">
                @forelse($entradas as $entrada)
                    <li class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150">
                        <div class="px-6 py-5 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider font-mono bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                                        Entrada #{{ $entrada->id }}
                                    </span>
                                    @if($entrada->folio_factura)
                                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400">
                                            Folio: <strong class="text-slate-700 dark:text-slate-200">{{ $entrada->folio_factura }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Total: ${{ number_format($entrada->total_estimado, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $entrada->proveedor->display_name ?? 'Compra Local / Sin Proveedor' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span>{{ $entrada->totalPiezas() }} artículos recibidos</span>
                                </div>
                                <div class="flex items-center gap-2 sm:justify-end">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $entrada->fecha->format('d/m/Y') }} · {{ $entrada->registradoPor->name ?? 'Sistema' }}</span>
                                </div>
                            </div>

                            <!-- Despliegue de items resumido -->
                            <div class="text-xs text-slate-600 dark:text-slate-400 bg-slate-100/80 dark:bg-slate-950/40 rounded-2xl p-3 border border-slate-200/60 dark:border-white/5">
                                <span class="font-bold text-slate-700 dark:text-slate-300">Detalle:</span>
                                @foreach($entrada->items as $item)
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $item->cantidad }}× {{ $item->producto->nombre ?? 'Producto Desconocido' }}</span>
                                    @if(!$loop->last) &bull; @endif
                                @endforeach
                            </div>
                        </div>
                    </li>
                @empty
                    <li>
                        <div class="px-6 py-16 text-center text-slate-400 text-sm">
                            No hay registros de entradas de mercancía que coincidan con la búsqueda.
                        </div>
                    </li>
                @endforelse
            </ul>

            <div class="px-6 py-4 border-t border-slate-200/80 dark:border-white/10 bg-slate-100/60 dark:bg-slate-950/40">
                {{ $entradas->links() }}
            </div>
        </div>
    </div>
</x-tb-background>
