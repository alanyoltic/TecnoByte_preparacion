<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Despachos a Ventas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Equipos FINALIZADOS enviados al área de Ventas para su aprobación.
            </p>
        </div>
        @if(auth()->user()->tienePermiso('prep.despachos.crear'))
        <a href="{{ route('preparacion.despachos-ventas.crear') }}"
           wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                  bg-gradient-to-r from-emerald-600 to-teal-600
                  text-white font-semibold text-sm shadow-lg
                  hover:from-emerald-500 hover:to-teal-500
                  transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Despacho
        </a>
        @endif
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- FILTROS --}}
    <div class="flex items-center gap-3 flex-wrap">
        <input
            wire:model.live.debounce.300ms="busqueda"
            type="text"
            placeholder="Buscar por folio..."
            class="px-3 py-2 rounded-xl text-sm
                   bg-white/10 dark:bg-slate-800/50
                   border border-white/10 dark:border-slate-700/50
                   text-slate-800 dark:text-slate-200
                   placeholder-slate-400
                   focus:outline-none focus:ring-2 focus:ring-emerald-500/50
                   w-48"
        />
        <select
            wire:model.live="filtroEstatus"
            class="px-3 py-2 rounded-xl text-sm
                   bg-white/10 dark:bg-slate-800/50
                   border border-white/10 dark:border-slate-700/50
                   text-slate-800 dark:text-slate-200
                   focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
        >
            <option value="">Todos los estados</option>
            @foreach($estatuses as $valor => $label)
                <option value="{{ $valor }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- TABLA --}}
    <div class="rounded-2xl overflow-hidden border border-white/10 dark:border-slate-700/40
                bg-white/60 dark:bg-slate-900/40 backdrop-blur-md shadow-xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/10 dark:border-slate-700/40
                           text-xs font-semibold uppercase tracking-widest
                           text-slate-500 dark:text-slate-400">
                    <th class="px-5 py-3 text-left">Folio</th>
                    <th class="px-5 py-3 text-left">Destino</th>
                    <th class="px-5 py-3 text-center">Equipos</th>
                    <th class="px-5 py-3 text-left">Estado</th>
                    <th class="px-5 py-3 text-left">Creado</th>
                    <th class="px-5 py-3 text-left">Enviado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 dark:divide-slate-700/30">
                @forelse($despachos as $despacho)
                <tr class="hover:bg-white/10 dark:hover:bg-slate-800/20 transition-colors duration-150">
                    <td class="px-5 py-3 font-mono text-emerald-400 font-semibold">{{ $despacho->folio }}</td>
                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300">
                        <div class="font-medium">{{ $despacho->sucursalDestino?->nombre ?? '—' }}</div>
                        <div class="text-xs text-slate-500">{{ $despacho->almacenDestino?->nombre ?? '—' }}</div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-emerald-500/10 text-emerald-400 font-bold text-sm">
                            {{ $despacho->equipos_count }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @php
                            $colores = [
                                'BORRADOR'  => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                'ENVIADO'   => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                'APROBADO'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'RECHAZADO' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                'CANCELADO' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                            ];
                            $color = $colores[$despacho->estatus] ?? 'bg-slate-500/10 text-slate-400';
                        @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-semibold border {{ $color }}">
                            {{ $despacho->labelEstatus }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs">
                        {{ $despacho->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs">
                        {{ $despacho->enviado_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($despacho->esBorrador())
                            <a href="{{ route('preparacion.despachos-ventas.crear') }}?continuar={{ $despacho->id }}"
                               wire:navigate
                               class="text-xs text-blue-400 hover:text-blue-300 transition-colors mr-3">
                               Editar
                            </a>
                            <button wire:click="cancelar({{ $despacho->id }})"
                                    wire:confirm="¿Cancelar este despacho?"
                                    class="text-xs text-rose-400 hover:text-rose-300 transition-colors">
                                Cancelar
                            </button>
                        @elseif($despacho->estaRechazado())
                            <span class="text-xs text-slate-500 italic" title="{{ $despacho->notas_rechazo }}">
                                Ver motivo
                            </span>
                        @else
                            <span class="text-xs text-slate-600">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        No hay despachos para mostrar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($despachos->hasPages())
        <div class="px-5 py-3 border-t border-white/10 dark:border-slate-700/40">
            {{ $despachos->links() }}
        </div>
        @endif
    </div>
</div>
