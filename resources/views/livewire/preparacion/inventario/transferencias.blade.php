<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        {{-- TOPBAR MAESTRO --}}
        <x-topbar
            title="Transferencias de Inventario"
            chip="Inventario · Movimientos Globales"
            description="Consulta, seguimiento y gestión de traspaso de stock entre talleres, sucursales y piso de venta."
        >
            @if(auth()->check() && auth()->user()->puedeCrearTransferencias())
            <x-slot name="right">
                <a href="{{ route('transferencias.crear') }}"
                   class="px-5 py-2.5 rounded-2xl bg-[#FF9521] hover:bg-[#e6841b] text-white text-xs font-bold shadow-lg shadow-orange-500/25 transition-all flex items-center gap-1.5 hover:-translate-y-0.5">
                    <span>+</span> Nueva Transferencia
                </a>
            </x-slot>
            @endif
        </x-topbar>

        {{-- FLASH MESSAGES / FEEDBACK --}}
        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm backdrop-blur-md shadow-lg shadow-emerald-950/20">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        {{-- TARJETAS DE ESTADÍSTICAS / FILTROS RÁPIDOS --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            @foreach([
                ['key' => 'todos',     'label' => 'Total',      'value' => $stats['total'],      'color' => 'text-slate-100',  'dot' => 'bg-slate-400'],
                ['key' => 'BORRADOR',  'label' => 'Borrador',   'value' => $stats['borrador'],   'color' => 'text-slate-300',  'dot' => 'bg-slate-500'],
                ['key' => 'ENVIADA',   'label' => 'En tránsito', 'value' => $stats['enviadas'],   'color' => 'text-[#FF9521]',  'dot' => 'bg-[#FF9521] shadow-md shadow-orange-500/50'],
                ['key' => 'ACEPTADA',  'label' => 'Aceptadas',  'value' => $stats['aceptadas'],  'color' => 'text-emerald-400','dot' => 'bg-emerald-400 shadow-md shadow-emerald-500/50'],
                ['key' => 'RECHAZADA', 'label' => 'Rechazadas', 'value' => $stats['rechazadas'], 'color' => 'text-rose-400',   'dot' => 'bg-rose-400 shadow-md shadow-rose-500/50'],
            ] as $s)
            @php $activo = $filtroEstado === $s['key']; @endphp
            <button wire:click="$set('filtroEstado', '{{ $s['key'] }}')"
                    class="rounded-3xl p-4 sm:p-5 text-left transition-all duration-300 backdrop-blur-xl border
                           {{ $activo
                               ? 'bg-slate-900/95 border-[#FF9521] ring-2 ring-[#FF9521]/40 shadow-xl shadow-orange-500/10 -translate-y-1'
                               : 'bg-white/80 dark:bg-slate-950/60 border-slate-200/80 dark:border-white/10 hover:border-slate-400 dark:hover:border-slate-600 hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ $s['label'] }}
                    </span>
                    <span class="w-2.5 h-2.5 rounded-full {{ $s['dot'] }}"></span>
                </div>
                <p class="text-2xl sm:text-3xl font-black mt-2 {{ $s['color'] }}">
                    {{ $s['value'] }}
                </p>
            </button>
            @endforeach
        </div>

        {{-- BARRA DE BÚSQUEDA Y FILTRO --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </span>
                <input wire:model.live.debounce.350ms="search" id="filtro-busqueda-transferencias"
                       type="text" placeholder="Buscar por ID, almacén de origen o destino, creador…"
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-sm
                              bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10
                              text-slate-800 dark:text-slate-100 placeholder-slate-400
                              focus:outline-none focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] backdrop-blur-xl transition"/>
            </div>
            <select wire:model.live="filtroEstado" id="filtro-estado-transferencias"
                    class="px-4 py-2.5 rounded-2xl text-sm bg-white/80 dark:bg-slate-950/60
                           border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-200
                           focus:outline-none focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] backdrop-blur-xl transition">
                <option value="todos">Todos los estados</option>
                <option value="BORRADOR">Borrador</option>
                <option value="ENVIADA">Enviada (en tránsito)</option>
                <option value="ACEPTADA">Aceptada</option>
                <option value="RECHAZADA">Rechazada</option>
            </select>
        </div>

        {{-- TABLA GLASSMORPHISM --}}
        <div class="rounded-3xl overflow-hidden border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-950/60 backdrop-blur-2xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-slate-800/80
                                   text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-900/40">
                            <th class="px-6 py-4"># Folio</th>
                            <th class="px-6 py-4">Ruta (Origen → Destino)</th>
                            <th class="px-6 py-4 text-center">Artículos</th>
                            <th class="px-6 py-4 hidden lg:table-cell">Creado por</th>
                            <th class="px-6 py-4 hidden sm:table-cell">Fecha</th>
                            <th class="px-6 py-4 text-center">Estatus</th>
                            <th class="px-6 py-4 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/50">
                        @forelse($transferencias as $tf)
                        <tr class="hover:bg-slate-100/50 dark:hover:bg-slate-900/50 transition-colors duration-150">
                            
                            {{-- ID --}}
                            <td class="px-6 py-4 font-mono font-bold text-[#FF9521]">
                                #{{ $tf->id }}
                            </td>

                            {{-- Ruta --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold
                                                 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200">
                                        {{ $tf->origen?->nombre ?? 'Sin almacén' }}
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-[#FF9521] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold
                                                 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200">
                                        {{ $tf->destino?->nombre ?? 'Sin almacén' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Artículos --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-xl text-xs font-bold
                                             bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200">
                                    📦 {{ $tf->detalles->count() }}
                                </span>
                            </td>

                            {{-- Creado por --}}
                            <td class="px-6 py-4 hidden lg:table-cell text-xs font-medium text-slate-600 dark:text-slate-400">
                                {{ $tf->creador?->nombre ?? '—' }}
                            </td>

                            {{-- Fecha --}}
                            <td class="px-6 py-4 hidden sm:table-cell text-xs text-slate-500 font-mono">
                                {{ $tf->created_at?->format('d/m/Y H:i') }}
                            </td>

                            {{-- Estatus --}}
                            <td class="px-6 py-4 text-center">
                                @php
                                    $estatusLabel = match($tf->estatus) {
                                        'BORRADOR'  => 'Borrador',
                                        'ENVIADA'   => 'En tránsito',
                                        'ACEPTADA'  => 'Aceptada',
                                        'RECHAZADA' => 'Rechazada',
                                        default     => $tf->estatus,
                                    };
                                    $estatusColor = match($tf->estatus) {
                                        'BORRADOR'  => 'bg-slate-500/10 text-slate-400 border-slate-700',
                                        'ENVIADA'   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                        'ACEPTADA'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                        'RECHAZADA' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                        default     => 'bg-slate-500/10 text-slate-400',
                                    };
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $estatusColor }}">
                                    {{ $estatusLabel }}
                                </span>
                            </td>

                            {{-- Acción --}}
                            <td class="px-6 py-4 text-right">
                                <button wire:click="verDetalle({{ $tf->id }})"
                                        id="btn-detalle-tf-{{ $tf->id }}"
                                        title="Ver detalle"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold
                                               bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800
                                               border border-slate-200 dark:border-slate-800
                                               text-slate-700 dark:text-slate-200 hover:text-[#FF9521] dark:hover:text-[#FF9521]
                                               transition-all flex items-center gap-1.5 ml-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver Detalle
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium">No se encontraron transferencias que coincidan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transferencias->hasPages())
            <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-900/30">
                {{ $transferencias->links() }}
            </div>
            @endif
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- MODAL DETALLE OPERATIVO                                           --}}
    {{-- ================================================================ --}}
    @if($modalDetalle && $detalle)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         x-data @keydown.escape.window="$wire.cerrarDetalle()">
        <div class="w-full max-w-xl max-h-[85vh] overflow-y-auto rounded-3xl bg-slate-900 border border-slate-700/70 shadow-2xl animate-in fade-in zoom-in-95 duration-200">

            {{-- HEADER MODAL --}}
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-950/70 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#FF9521]/15 border border-[#FF9521]/30 flex items-center justify-center text-[#FF9521]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            Transferencia <span class="font-mono text-[#FF9521]">#{{ $detalle->id }}</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $detalle->created_at?->format('d/m/Y H:i') }}
                            @if($detalle->creador)· Creada por <span class="text-slate-200 font-semibold">{{ $detalle->creador->nombre }}</span>@endif
                        </p>
                    </div>
                </div>
                <button wire:click="cerrarDetalle"
                        class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    ✕
                </button>
            </div>

            <div class="p-6 space-y-5">

                {{-- FEEDBACK MESSAGE --}}
                @if($mensajeFeedback)
                    <div class="p-4 rounded-2xl text-xs {{ $tipoFeedback === 'success' ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/15 border border-rose-500/30 text-rose-400' }}">
                        {{ $mensajeFeedback }}
                    </div>
                @endif

                {{-- RUTA + ESTATUS --}}
                <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <span class="px-2.5 py-1 rounded-xl bg-slate-800 border border-slate-700 font-bold text-slate-200">
                            {{ $detalle->origen?->nombre ?? 'Desconocido' }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-[#FF9521]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="px-2.5 py-1 rounded-xl bg-slate-800 border border-slate-700 font-bold text-slate-200">
                            {{ $detalle->destino?->nombre ?? 'Desconocido' }}
                        </span>
                    </div>

                    @php
                        $sc = match($detalle->estatus) {
                            'BORRADOR'  => 'bg-slate-500/10 text-slate-400 border-slate-700',
                            'ENVIADA'   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                            'ACEPTADA'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                            'RECHAZADA' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                            default     => 'bg-slate-500/10 text-slate-400',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $sc }}">
                        {{ $detalle->estatus }}
                    </span>
                </div>

                @if($detalle->observaciones)
                <div class="px-4 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs">
                    <p class="font-bold text-[#FF9521] uppercase mb-1 text-[10px]">Observaciones:</p>
                    <p class="text-amber-200/90 leading-relaxed">{{ $detalle->observaciones }}</p>
                </div>
                @endif

                {{-- ARTÍCULOS --}}
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                        Artículos en este paquete ({{ $detalle->detalles->count() }})
                    </p>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/50 overflow-hidden divide-y divide-slate-800/60">
                        @foreach($detalle->detalles as $item)
                        <div class="p-3 text-xs flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0
                                            {{ $item->movible_type && str_contains($item->movible_type, 'Equipo')
                                                ? 'bg-[#FF9521]/15 text-[#FF9521]'
                                                : 'bg-violet-500/15 text-violet-400' }}">
                                    @if($item->movible_type && str_contains($item->movible_type, 'Equipo'))
                                        💻
                                    @else
                                        📦
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-white">
                                        @if($item->movible)
                                            {{ $item->movible->nombre ?? ($item->movible->marca . ' ' . $item->movible->modelo) }}
                                        @else
                                            Artículo #{{ $item->movible_id }}
                                        @endif
                                    </p>
                                    @if(isset($item->movible->numero_serie))
                                    <p class="font-mono text-slate-400 text-[11px]">Serie: {{ $item->movible->numero_serie }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 font-bold text-xs">
                                × {{ $item->cantidad ?? 1 }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- FORMULARIO DE RECHAZO --}}
                @if($mostrarConfirmarRechazo)
                    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 space-y-3">
                        <label class="block text-xs font-bold text-rose-300 uppercase tracking-wider">
                            Motivo del rechazo de la transferencia:
                        </label>
                        <textarea wire:model="motivoRechazo"
                                  rows="3"
                                  placeholder="Indica el motivo para que el almacén de origen reciba de vuelta la mercancía..."
                                  class="w-full px-3 py-2.5 rounded-xl text-xs bg-slate-950/80 border border-rose-500/40 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                        @error('motivoRechazo')
                            <p class="text-xs text-rose-400 font-medium">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-2 pt-1">
                            <button wire:click="cancelarRechazo"
                                    type="button"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white transition">
                                Cancelar
                            </button>
                            <button wire:click="confirmarRechazo"
                                    type="button"
                                    class="px-4 py-1.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 transition">
                                Confirmar Rechazo
                            </button>
                        </div>
                    </div>
                @endif

                @if($detalle->aprobador)
                <p class="text-[11px] text-slate-500 text-center font-mono">
                    {{ $detalle->estatus === 'ACEPTADA' ? 'Aceptada' : 'Procesada' }}
                    por <span class="font-semibold text-slate-400">{{ $detalle->aprobador->nombre }}</span>
                    · {{ $detalle->aprobada_at?->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>

            {{-- FOOTER MODAL --}}
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between flex-wrap gap-3">
                <button wire:click="cerrarDetalle"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white transition">
                    Cerrar
                </button>

                <div class="flex items-center gap-2">
                    @if($detalle->estatus === 'BORRADOR' && $puedeGestionarOrigen)
                        <button wire:click="enviar"
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#FF9521] hover:bg-[#e6841b] text-white shadow-lg shadow-orange-500/20 transition-all flex items-center gap-1.5">
                            <span wire:loading.remove wire:target="enviar">Enviar Transferencia ➔</span>
                            <span wire:loading wire:target="enviar">Enviando...</span>
                        </button>
                    @endif

                    @if($detalle->estatus === 'ENVIADA' && ! $mostrarConfirmarRechazo)
                        @if($puedeGestionarDestino)
                            <button wire:click="activarRechazo"
                                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition">
                                Rechazar
                            </button>
                            <button wire:click="aceptar"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                                <span wire:loading.remove wire:target="aceptar">✓ Confirmar y Recibir</span>
                                <span wire:loading wire:target="aceptar">Recibiendo...</span>
                            </button>
                        @elseif($puedeGestionarOrigen)
                            <button wire:click="cancelarTransferencia"
                                    wire:confirm="¿Deseas cancelar esta transferencia?"
                                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white transition">
                                Cancelar Envío
                            </button>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>
    @endif

</x-tb-background>
</div>