<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        {{-- TOPBAR MAESTRO --}}
        <x-topbar
            title="Despachos a Ventas"
            chip="Preparación · Envíos a Tiendas"
            description="Equipos FINALIZADOS y productos enviados al área de Ventas para su aprobación e inventario."
        >
            @if(auth()->user()->tienePermiso('prep.despachos.crear') || auth()->user()->tienePermiso('prep.inventario.gestion') || auth()->user()->esCeoOAdmin())
            <x-slot name="right">
                <a href="{{ route('preparacion.despachos-ventas.crear') }}"
                   wire:navigate
                   class="px-5 py-2.5 rounded-2xl bg-[#FF9521] hover:bg-[#e6841b] text-white text-xs font-bold shadow-lg shadow-orange-500/25 transition-all flex items-center gap-1.5 hover:-translate-y-0.5">
                    <span>+</span> Nuevo Despacho
                </a>
            </x-slot>
            @endif
        </x-topbar>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm backdrop-blur-md shadow-lg shadow-emerald-950/20">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm backdrop-blur-md">
            {{ session('error') }}
        </div>
        @endif

        {{-- FILTROS DE BÚSQUEDA --}}
        <div class="flex items-center gap-3 flex-wrap">
            <div class="relative flex-1 max-w-xs">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </span>
                <input
                    wire:model.live.debounce.300ms="busqueda"
                    type="text"
                    placeholder="Buscar por folio..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-sm
                           bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                           text-slate-800 dark:text-slate-100 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] backdrop-blur-xl transition"
                />
            </div>

            <select
                wire:model.live="filtroEstatus"
                class="px-4 py-2.5 rounded-2xl text-sm bg-white/80 dark:bg-slate-950/60
                       border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-200
                       focus:outline-none focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] backdrop-blur-xl transition"
            >
                <option value="">Todos los estados</option>
                @foreach($estatuses as $valor => $label)
                    <option value="{{ $valor }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- TABLA GLASSMORPHISM --}}
        <div class="rounded-3xl overflow-hidden border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-950/60 backdrop-blur-2xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-slate-800/80
                                   text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-900/40">
                            <th class="px-6 py-4">Folio</th>
                            <th class="px-6 py-4">Destino</th>
                            <th class="px-6 py-4 text-center">Artículos</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4">Creado</th>
                            <th class="px-6 py-4">Enviado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/50">
                        @forelse($despachos as $despacho)
                        <tr class="hover:bg-slate-100/50 dark:hover:bg-slate-900/50 transition-colors duration-150">
                            <td class="px-6 py-4 font-mono font-bold text-[#FF9521]">
                                {{ $despacho->folio }}
                            </td>
                            <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                                <div class="font-bold text-xs">{{ $despacho->sucursalDestino?->nombre ?? '—' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $despacho->almacenDestino?->nombre ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl
                                                 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-xs" title="Equipos serializados">
                                        <span>💻</span> {{ $despacho->equipos_count }}
                                    </span>
                                    @if($despacho->productos_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl
                                                 bg-purple-500/10 border border-purple-500/20 text-purple-400 font-bold text-xs" title="Accesorios / Productos">
                                        <span>📦</span> {{ $despacho->productos_count }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $colores = [
                                        'BORRADOR'  => 'bg-slate-500/10 text-slate-400 border-slate-700',
                                        'ENVIADO'   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                        'APROBADO'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                        'RECHAZADO' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                        'CANCELADO' => 'bg-slate-500/10 text-slate-400 border-slate-700',
                                    ];
                                    $color = $colores[$despacho->estatus] ?? 'bg-slate-500/10 text-slate-400';
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                    {{ $despacho->labelEstatus }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs font-mono">
                                {{ $despacho->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs font-mono">
                                {{ $despacho->enviado_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($despacho->esBorrador())
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('preparacion.despachos-ventas.crear') }}?continuar={{ $despacho->id }}"
                                       wire:navigate
                                       class="px-3 py-1.5 rounded-xl text-xs font-semibold
                                              bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800
                                              border border-slate-200 dark:border-slate-800 text-slate-200 hover:text-[#FF9521] transition">
                                       Editar
                                    </a>
                                    <button wire:click="cancelar({{ $despacho->id }})"
                                            wire:confirm="¿Cancelar este despacho?"
                                            class="px-3 py-1.5 rounded-xl text-xs font-semibold
                                                   bg-rose-500/10 text-rose-400 border border-rose-500/30 hover:bg-rose-500/20 transition">
                                        Cancelar
                                    </button>
                                </div>
                                @elseif($despacho->estaRechazado() && $despacho->notas_rechazo)
                                <span class="text-xs text-rose-400 italic max-w-xs block text-right truncate font-medium" title="{{ $despacho->notas_rechazo }}">
                                    Motivo: {{ Str::limit($despacho->notas_rechazo, 30) }}
                                </span>
                                @else
                                <span class="text-xs text-slate-500 font-mono">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium">No hay despachos para mostrar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($despachos->hasPages())
            <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-900/30">
                {{ $despachos->links() }}
            </div>
            @endif
        </div>

    </div>
</x-tb-background>
</div>
