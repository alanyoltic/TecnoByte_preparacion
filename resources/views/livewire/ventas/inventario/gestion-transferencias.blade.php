<div class="p-6 space-y-5">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Transferencias</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Movimientos de stock entre almacenes del área de Ventas.
        </p>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        @foreach([
            ['label' => 'Total',      'value' => $stats['total'],      'color' => 'bg-slate-100 dark:bg-slate-800',        'text' => 'text-slate-700 dark:text-slate-200'],
            ['label' => 'Borrador',   'value' => $stats['borrador'],   'color' => 'bg-slate-50 dark:bg-slate-800/60',      'text' => 'text-slate-500 dark:text-slate-400'],
            ['label' => 'Enviadas',   'value' => $stats['enviadas'],   'color' => 'bg-amber-50 dark:bg-amber-900/30',      'text' => 'text-amber-700 dark:text-amber-300'],
            ['label' => 'Aceptadas',  'value' => $stats['aceptadas'],  'color' => 'bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-300'],
            ['label' => 'Rechazadas', 'value' => $stats['rechazadas'], 'color' => 'bg-rose-50 dark:bg-rose-900/30',       'text' => 'text-rose-700 dark:text-rose-300'],
        ] as $s)
        <button wire:click="$set('filtroEstado', '{{ $s['label'] === 'Total' ? 'todos' : strtoupper($s['label']) }}')"
                class="rounded-2xl p-4 text-left transition hover:ring-2 hover:ring-slate-300 dark:hover:ring-slate-600 {{ $s['color'] }}">
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $s['label'] }}</p>
            <p class="text-3xl font-black {{ $s['text'] }} mt-1">{{ $s['value'] }}</p>
        </button>
        @endforeach
    </div>

    {{-- FILTROS --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </span>
            <input wire:model.live.debounce.350ms="busqueda" id="filtro-busqueda-transferencias"
                   type="text" placeholder="Buscar por ID, almacén de origen o destino…"
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-sm
                          bg-white/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700
                          text-slate-800 dark:text-slate-100 placeholder-slate-400
                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 backdrop-blur-md transition"/>
        </div>
        <select wire:model.live="filtroEstado" id="filtro-estado-transferencias"
                class="px-3 py-2 rounded-xl text-sm bg-white/70 dark:bg-slate-800/60
                       border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/50 backdrop-blur-md transition">
            <option value="todos">Todos los estados</option>
            <option value="BORRADOR">Borrador</option>
            <option value="ENVIADA">Enviada (pendiente)</option>
            <option value="ACEPTADA">Aceptada</option>
            <option value="RECHAZADA">Rechazada</option>
        </select>
    </div>

    {{-- TABLA --}}
    <div class="overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50
                bg-white/60 dark:bg-slate-900/50 backdrop-blur-md shadow-xl">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200/60 dark:border-slate-700/50
                           bg-slate-50/80 dark:bg-slate-800/60 text-left">
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">#</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Origen → Destino</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden md:table-cell text-center">Artículos</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden lg:table-cell">Creado por</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden sm:table-cell">Fecha</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Estatus</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-700/40">
                @forelse($transferencias as $tf)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors duration-150">

                    {{-- ID --}}
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs text-slate-500 dark:text-slate-400">#{{ $tf->id }}</span>
                    </td>

                    {{-- Ruta --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium
                                         bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $tf->origen?->nombre ?? 'Sin almacén' }}
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium
                                         bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $tf->destino?->nombre ?? 'Sin almacén' }}
                            </span>
                        </div>
                    </td>

                    {{-- Artículos --}}
                    <td class="px-4 py-3 hidden md:table-cell text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                     bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ $tf->detalles->count() }}
                        </span>
                    </td>

                    {{-- Creado por --}}
                    <td class="px-4 py-3 hidden lg:table-cell text-xs text-slate-500 dark:text-slate-400">
                        {{ $tf->creador?->nombre ?? '—' }}
                    </td>

                    {{-- Fecha --}}
                    <td class="px-4 py-3 hidden sm:table-cell text-xs text-slate-500 dark:text-slate-400">
                        {{ $tf->created_at?->format('d/m/Y H:i') }}
                    </td>

                    {{-- Estatus --}}
                    <td class="px-4 py-3">
                        @php
                            $estatusLabel = match($tf->estatus) {
                                'BORRADOR'  => 'Borrador',
                                'ENVIADA'   => 'Enviada',
                                'ACEPTADA'  => 'Aceptada',
                                'RECHAZADA' => 'Rechazada',
                                default     => $tf->estatus,
                            };
                            $estatusColor = match($tf->estatus) {
                                'BORRADOR'  => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400',
                                'ENVIADA'   => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
                                'ACEPTADA'  => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
                                'RECHAZADA' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300',
                                default     => 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $estatusColor }}">
                            {{ $estatusLabel }}
                        </span>
                    </td>

                    {{-- Acción --}}
                    <td class="px-4 py-3 text-right">
                        <button wire:click="verDetalle({{ $tf->id }})"
                                id="btn-detalle-tf-{{ $tf->id }}"
                                title="Ver detalle"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-violet-500
                                       hover:bg-violet-50 dark:hover:bg-violet-900/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-16 text-center text-sm text-slate-400 dark:text-slate-500">
                        No se encontraron transferencias que coincidan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $transferencias->links() }}</div>


    {{-- ======== MODAL DETALLE ======== --}}
    @if($modalDetalle && $detalle)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             wire:click="cerrarDetalle"></div>

        <div class="relative w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-3xl shadow-2xl
                    bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-700/50">

            {{-- Header modal --}}
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4
                        border-b border-slate-100 dark:border-slate-800
                        bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Transferencia #{{ $detalle->id }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $detalle->created_at?->format('d/m/Y H:i') }}
                        @if($detalle->creador)· por {{ $detalle->creador->nombre }}@endif
                    </p>
                </div>
                <button wire:click="cerrarDetalle"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-5">

                {{-- Ruta + Estatus --}}
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-3 py-1.5 rounded-xl text-sm font-semibold
                                 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                        {{ $detalle->origen?->nombre ?? 'Desconocido' }}
                    </span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <span class="px-3 py-1.5 rounded-xl text-sm font-semibold
                                 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                        {{ $detalle->destino?->nombre ?? 'Desconocido' }}
                    </span>
                    @php
                        $sc = match($detalle->estatus) {
                            'BORRADOR'  => 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                            'ENVIADA'   => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
                            'ACEPTADA'  => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
                            'RECHAZADA' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300',
                            default     => 'bg-slate-100 text-slate-500',
                        };
                    @endphp
                    <span class="ml-auto px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc }}">
                        {{ $detalle->estatus }}
                    </span>
                </div>

                @if($detalle->observaciones)
                <div class="px-4 py-3 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-700/40">
                    <p class="text-xs font-medium text-amber-700 dark:text-amber-300 mb-1">Observaciones</p>
                    <p class="text-sm text-amber-800 dark:text-amber-200">{{ $detalle->observaciones }}</p>
                </div>
                @endif

                {{-- Artículos --}}
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                        Artículos ({{ $detalle->detalles->count() }})
                    </p>
                    <div class="space-y-2">
                        @foreach($detalle->detalles as $item)
                        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl
                                    bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/40">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0
                                        {{ $item->movible_type && str_contains($item->movible_type, 'Equipo')
                                            ? 'bg-blue-500/15 text-blue-500'
                                            : 'bg-violet-500/15 text-violet-500' }}">
                                @if($item->movible_type && str_contains($item->movible_type, 'Equipo'))
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($item->movible)
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">
                                    @if(method_exists($item->movible, 'getRouteKey'))
                                        {{ $item->movible->nombre ?? ($item->movible->marca . ' ' . $item->movible->modelo) }}
                                    @else
                                        Artículo #{{ $item->movible_id }}
                                    @endif
                                </p>
                                @if(isset($item->movible->numero_serie))
                                <p class="text-xs font-mono text-slate-400">{{ $item->movible->numero_serie }}</p>
                                @endif
                                @else
                                <p class="text-sm text-slate-400">Artículo no disponible</p>
                                @endif
                            </div>
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 shrink-0">
                                × {{ $item->cantidad ?? 1 }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($detalle->aprobador)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center">
                    {{ $detalle->estatus === 'ACEPTADA' ? 'Aceptada' : 'Procesada' }}
                    por <span class="font-medium text-slate-600 dark:text-slate-300">{{ $detalle->aprobador->nombre }}</span>
                    · {{ $detalle->aprobada_at?->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
