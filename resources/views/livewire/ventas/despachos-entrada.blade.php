<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Despachos Entrantes</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Equipos enviados desde Preparación para incorporar al inventario de Ventas.
        </p>
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

    {{-- PESTAÑAS DE ESTATUS --}}
    <div class="flex items-center gap-2 flex-wrap">
        @foreach(['ENVIADO' => ['label' => 'Pendientes', 'color' => 'blue'], 'APROBADO' => ['label' => 'Aprobados', 'color' => 'emerald'], 'RECHAZADO' => ['label' => 'Rechazados', 'color' => 'rose'], '' => ['label' => 'Todos', 'color' => 'slate']] as $estatus => $info)
        <button wire:click="$set('filtroEstatus', '{{ $estatus }}')"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
                       {{ $filtroEstatus === $estatus
                           ? 'bg-'.$info['color'].'-500/20 text-'.$info['color'].'-400 border border-'.$info['color'].'-500/30'
                           : 'bg-white/5 dark:bg-slate-800/30 text-slate-500 border border-transparent hover:text-slate-300' }}">
            {{ $info['label'] }}
            @if(isset($contadores[$estatus]) && $estatus !== '')
            <span class="ml-1.5 text-xs opacity-70">({{ $contadores[$estatus] }})</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- TABLA --}}
    <div class="rounded-2xl overflow-hidden border border-white/10 dark:border-slate-700/40
                bg-white/60 dark:bg-slate-900/40 backdrop-blur-md shadow-xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/10 dark:border-slate-700/40
                           text-xs font-semibold uppercase tracking-widest text-slate-500">
                    <th class="px-5 py-3 text-left">Folio</th>
                    <th class="px-5 py-3 text-left">Creado por</th>
                    <th class="px-5 py-3 text-left">Almacén Destino</th>
                    <th class="px-5 py-3 text-center">Equipos</th>
                    <th class="px-5 py-3 text-left">Estado</th>
                    <th class="px-5 py-3 text-left">Enviado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 dark:divide-slate-700/30">
                @forelse($despachos as $despacho)
                <tr class="hover:bg-white/10 dark:hover:bg-slate-800/20 transition-colors duration-150">
                    <td class="px-5 py-3 font-mono text-emerald-400 font-semibold">{{ $despacho->folio }}</td>
                    <td class="px-5 py-3 text-slate-300">{{ $despacho->creadoPor?->nombre ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-400 text-xs">{{ $despacho->almacenDestino?->nombre ?? '—' }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-emerald-500/10 text-emerald-400 font-bold text-sm">
                            {{ $despacho->equipos_count }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @php
                            $colores = [
                                'ENVIADO'   => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                'APROBADO'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'RECHAZADO' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                            ];
                            $color = $colores[$despacho->estatus] ?? 'bg-slate-500/10 text-slate-400';
                        @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-semibold border {{ $color }}">
                            {{ $despacho->labelEstatus }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs">
                        {{ $despacho->enviado_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($despacho->estaEnviado())
                        <div class="flex items-center justify-end gap-2">
                            @can('ventas.despachos.aprobar')
                            <button wire:click="iniciarAprobacion({{ $despacho->id }})"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                           bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                           hover:bg-emerald-500/30 transition-colors">
                                ✓ Aprobar
                            </button>
                            <button wire:click="iniciarRechazo({{ $despacho->id }})"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                           bg-rose-500/20 text-rose-400 border border-rose-500/30
                                           hover:bg-rose-500/30 transition-colors">
                                ✗ Rechazar
                            </button>
                            @endcan
                        </div>
                        @elseif($despacho->estaRechazado() && $despacho->notas_rechazo)
                        <span class="text-xs text-rose-400 italic max-w-xs block text-right truncate"
                              title="{{ $despacho->notas_rechazo }}">
                            {{ Str::limit($despacho->notas_rechazo, 40) }}
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

    {{-- MODAL APROBACIÓN --}}
    @if($modalAprobacion)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm"
         x-data x-init="$el.focus()" @keydown.escape.window="$wire.cerrarModales()">
        <div class="w-full max-w-md mx-4 rounded-2xl bg-slate-900 border border-slate-700/50 shadow-2xl p-6 space-y-4">
            <h3 class="text-lg font-bold text-white">Confirmar Aprobación</h3>
            <p class="text-slate-400 text-sm">
                Al aprobar, todos los equipos de este despacho serán transferidos al almacén de destino
                y quedarán disponibles para la venta. Esta acción no se puede deshacer.
            </p>

            @if($mensajeError)
            <div class="px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                {{ $mensajeError }}
            </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="cerrarModales"
                        class="px-4 py-2 rounded-xl text-sm text-slate-400 border border-slate-700 hover:text-slate-200 transition-colors">
                    Cancelar
                </button>
                <button wire:click="confirmarAprobacion"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl text-sm font-bold
                               bg-gradient-to-r from-emerald-600 to-teal-600 text-white
                               hover:from-emerald-500 hover:to-teal-500 transition-all">
                    <span wire:loading.remove wire:target="confirmarAprobacion">✓ Aprobar Despacho</span>
                    <span wire:loading wire:target="confirmarAprobacion">Procesando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL RECHAZO --}}
    @if($modalRechazo)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm"
         x-data @keydown.escape.window="$wire.cerrarModales()">
        <div class="w-full max-w-md mx-4 rounded-2xl bg-slate-900 border border-slate-700/50 shadow-2xl p-6 space-y-4">
            <h3 class="text-lg font-bold text-white">Rechazar Despacho</h3>
            <p class="text-slate-400 text-sm">Indica el motivo del rechazo para que Preparación pueda corregirlo.</p>

            <textarea wire:model="motivoRechazo" rows="4"
                      placeholder="Ej: Los equipos no coinciden con los modelos solicitados..."
                      class="w-full px-3 py-2.5 rounded-xl text-sm resize-none
                             bg-slate-800/60 border border-slate-700/50 text-slate-200
                             placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-rose-500/50"></textarea>

            @if($mensajeError)
            <div class="px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                {{ $mensajeError }}
            </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="cerrarModales"
                        class="px-4 py-2 rounded-xl text-sm text-slate-400 border border-slate-700 hover:text-slate-200 transition-colors">
                    Cancelar
                </button>
                <button wire:click="confirmarRechazo"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl text-sm font-bold
                               bg-rose-600 text-white hover:bg-rose-500 transition-all">
                    Confirmar Rechazo
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
