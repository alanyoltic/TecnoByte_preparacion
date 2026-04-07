<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Mis Solicitudes de Piezas"
            chip="Preparacion · Operaciones"
            description="Consulta el estado de tus solicitudes y confirma la instalacion cuando la pieza ya este colocada."
        />

        @if (session()->has('success'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 text-sm font-medium text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-sm font-medium text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 shadow-xl shadow-slate-900/5 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">
                Flujo
            </p>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @php
                    $steps = [
                        ['icon' => '📋', 'label' => 'Solicitas la pieza', 'sub' => 'Se genera cuando cierras el equipo con pieza pendiente', 'color' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'],
                        ['icon' => '⏳', 'label' => 'Inventario revisa', 'sub' => 'Puede surtirla o mandarla a compra', 'color' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300'],
                        ['icon' => '🚚', 'label' => 'Compra / llegada', 'sub' => 'Cuando entra stock, la solicitud avanza', 'color' => 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300'],
                        ['icon' => '📦', 'label' => 'Pieza lista', 'sub' => 'Ya puedes recogerla e instalarla', 'color' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'],
                        ['icon' => '✅', 'label' => 'Confirmas instalacion', 'sub' => 'El equipo vuelve al flujo normal', 'color' => 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300'],
                    ];
                @endphp
                @foreach($steps as $step)
                    <div class="flex items-center gap-1.5 px-3 py-2 rounded-xl {{ $step['color'] }} border border-white/30 dark:border-white/10">
                        <span class="text-base">{{ $step['icon'] }}</span>
                        <div>
                            <p class="font-semibold">{{ $step['label'] }}</p>
                            <p class="opacity-70 text-[0.67rem]">{{ $step['sub'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            @php
                $tabs = [
                    ['key' => 'PENDIENTE', 'label' => 'En revision', 'count' => $contadores['pendientes'], 'icon' => '⏳', 'active' => 'bg-yellow-500 text-white shadow-yellow-500/30'],
                    ['key' => 'PENDIENTE_COMPRA', 'label' => 'Pend. compra', 'count' => $contadores['pendientes_compra'], 'icon' => '🛒', 'active' => 'bg-orange-500 text-white shadow-orange-500/30'],
                    ['key' => 'COMPRADA', 'label' => 'Compradas', 'count' => $contadores['compradas'], 'icon' => '🚚', 'active' => 'bg-violet-500 text-white shadow-violet-500/30'],
                    ['key' => 'SURTIDA_INVENTARIO', 'label' => 'Pieza lista', 'count' => $contadores['surtidas'], 'icon' => '📦', 'active' => 'bg-blue-500 text-white shadow-blue-500/30'],
                    ['key' => 'CONFIRMADA', 'label' => 'Confirmadas', 'count' => $contadores['confirmadas'], 'icon' => '✅', 'active' => 'bg-green-500 text-white shadow-green-500/30'],
                    ['key' => 'CANCELADA', 'label' => 'Canceladas', 'count' => $contadores['canceladas'], 'icon' => '✗', 'active' => 'bg-red-500 text-white shadow-red-500/30'],
                    ['key' => 'TODAS', 'label' => 'Todas', 'count' => array_sum($contadores), 'icon' => '≡', 'active' => 'bg-slate-700 text-white'],
                ];
            @endphp

            @foreach($tabs as $tab)
                <button wire:click="cambiarFiltro('{{ $tab['key'] }}')"
                        class="rounded-xl p-3 text-left transition-all shadow-lg {{ $filtroEstatus === $tab['key'] ? $tab['active'] . ' shadow-lg' : 'bg-white/60 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-700/70 text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800' }}">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-sm">{{ $tab['icon'] }}</span>
                        <span class="text-xl font-bold">{{ $tab['count'] }}</span>
                    </div>
                    <div class="text-xs font-medium opacity-80">{{ $tab['label'] }}</div>
                </button>
            @endforeach
        </div>

        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.300ms="busqueda"
                   placeholder="Buscar por pieza, numero de serie o modelo..."
                   class="w-full px-4 py-2.5 pl-10 rounded-xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="space-y-3">
            @forelse($solicitudes as $solicitud)
                @php
                    $equipo = $solicitud->equipo ?? $solicitud->asignacionEquipo?->equipo;
                    $respondio = $solicitud->respondidaPor;
                    $responsableId = $solicitud->reasignado_a_id ?: $solicitud->solicitado_por_id;
                    $puedeConfirmar = $solicitud->estatus === 'SURTIDA_INVENTARIO' && $responsableId === auth()->id();

                    $badge = match($solicitud->estatus) {
                        'PENDIENTE' => ['label' => 'En revision por inventario', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', 'icon' => '⏳'],
                        'PENDIENTE_COMPRA' => ['label' => 'Pendiente de compra', 'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400', 'icon' => '🛒'],
                        'COMPRADA' => ['label' => 'Compra registrada', 'class' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400', 'icon' => '🚚'],
                        'SURTIDA_INVENTARIO' => ['label' => 'Pieza lista para instalar', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => '📦'],
                        'CONFIRMADA' => ['label' => 'Instalada y confirmada', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'icon' => '✅'],
                        'CANCELADA' => ['label' => 'Cancelada', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => '✗'],
                        default => ['label' => $solicitud->estatus, 'class' => 'bg-slate-100 text-slate-600', 'icon' => '•'],
                    };
                @endphp

                <div class="rounded-2xl bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 shadow-xl shadow-slate-900/5 p-5 {{ $puedeConfirmar ? 'ring-2 ring-blue-400/60 dark:ring-blue-500/40' : '' }}">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="flex-1 min-w-0 space-y-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-lg">{{ $badge['icon'] }}</span>
                                <h3 class="font-bold text-slate-900 dark:text-white">
                                    {{ $solicitud->nombre_pieza }}
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </div>

                            @if($equipo)
                                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $equipo->numero_serie }}</span>
                                        @if($equipo->marca || $equipo->modelo)
                                            - {{ trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')) }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
                                <span>Solicitada: <strong>{{ $solicitud->created_at->format('d/m/Y H:i') }}</strong></span>
                                @if($solicitud->respondida_en)
                                    <span>Gestionada: <strong>{{ $solicitud->respondida_en->format('d/m/Y H:i') }}</strong></span>
                                @endif
                                @if($respondio)
                                    <span>Por: <strong>{{ $respondio->nombre }} {{ $respondio->apellido_paterno }}</strong></span>
                                @endif
                            </div>

                            @if($solicitud->descripcion_libre)
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/40 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $solicitud->descripcion_libre }}
                                </div>
                            @endif

                            @if($solicitud->notas_respuesta)
                                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200/50 dark:border-indigo-700/30 text-sm">
                                    <p class="font-medium text-indigo-700 dark:text-indigo-300 mb-1">Nota de inventario</p>
                                    <p class="text-slate-700 dark:text-slate-300">{{ $solicitud->notas_respuesta }}</p>
                                </div>
                            @endif

                            @if($solicitud->reasignadoA && $solicitud->reasignado_a_id !== $solicitud->solicitado_por_id)
                                <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-900/20 border border-violet-200/50 dark:border-violet-700/30 text-sm">
                                    <p class="font-medium text-violet-700 dark:text-violet-300">
                                        Instalacion asignada a {{ $solicitud->reasignadoA->nombre }} {{ $solicitud->reasignadoA->apellido_paterno }}
                                    </p>
                                </div>
                            @endif

                            @if($solicitud->inventarioPieza)
                                <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-700/30 text-sm">
                                    <p class="font-semibold text-blue-700 dark:text-blue-300 mb-1">Pieza asignada del inventario</p>
                                    <p class="text-slate-700 dark:text-slate-300">
                                        Pieza #{{ $solicitud->inventarioPieza->id }}
                                        @if($solicitud->inventarioPieza->numero_serie)
                                            - S/N: {{ $solicitud->inventarioPieza->numero_serie }}
                                        @endif
                                        @if($solicitud->inventarioPieza->almacen)
                                            - Almacen: <strong>{{ $solicitud->inventarioPieza->almacen->nombre }}</strong>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            @if($solicitud->estatus === 'CONFIRMADA')
                                <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200/50 dark:border-green-700/30 text-sm">
                                    <p class="font-semibold text-green-700 dark:text-green-300">
                                        {{ $solicitud->funciono ? 'La pieza quedo instalada y funcionando.' : 'La pieza fallo y ya se genero seguimiento.' }}
                                    </p>
                                    @if($solicitud->notas_confirmacion)
                                        <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $solicitud->notas_confirmacion }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($puedeConfirmar)
                            <div class="shrink-0">
                                <button wire:click="abrirConfirmar({{ $solicitud->id }})"
                                        class="px-5 py-2.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold shadow-lg shadow-blue-500/40 transition-all ring-2 ring-blue-400/40 ring-offset-2 ring-offset-transparent animate-pulse hover:animate-none">
                                    Confirmar instalacion
                                </button>
                                <p class="mt-1.5 text-xs text-center text-blue-600 dark:text-blue-400 font-medium">
                                    Recoge la pieza e instalala
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 p-12 text-center">
                    <span class="text-5xl block mb-4">🔍</span>
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Sin solicitudes</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        No hay solicitudes con ese filtro por ahora.
                    </p>
                </div>
            @endforelse

            <div>{{ $solicitudes->links() }}</div>
        </div>

    </div>
</x-tb-background>

@if($modalConfirmar)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         wire:click.self="cerrarModal">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Confirmar instalacion</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Indica si la pieza quedo instalada correctamente.
                </p>
            </div>

            <div class="p-6 space-y-5">
                @if($solicitudSeleccionada)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm">
                        <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $solicitudSeleccionada->nombre_pieza }}</p>
                        @php $eq = $solicitudSeleccionada->equipo ?? $solicitudSeleccionada->asignacionEquipo?->equipo; @endphp
                        @if($eq)
                            <p class="text-slate-500 dark:text-slate-400 mt-0.5">Equipo: {{ $eq->numero_serie }}</p>
                        @endif
                    </div>
                @endif

                <div class="space-y-2">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">La pieza funciono correctamente?</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="$set('funciono', true)"
                                class="py-3 rounded-xl text-sm font-semibold border-2 transition-all {{ $funciono ? 'bg-green-500 text-white border-green-500 shadow-lg shadow-green-500/30' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-green-400' }}">
                            Si, funciono
                        </button>
                        <button wire:click="$set('funciono', false)"
                                class="py-3 rounded-xl text-sm font-semibold border-2 transition-all {{ !$funciono ? 'bg-red-500 text-white border-red-500 shadow-lg shadow-red-500/30' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-red-400' }}">
                            No funciono
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Notas adicionales
                    </label>
                    <textarea wire:model="notasConfirmacion" rows="3"
                              placeholder="Comentario opcional..."
                              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
                    @error('notasConfirmacion')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                <button wire:click="cerrarModal"
                        class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm transition-all">
                    Cancelar
                </button>
                <button wire:click="confirmarInstalacion"
                        class="px-6 py-2 rounded-lg text-white font-medium text-sm shadow-lg transition-all {{ $funciono ? 'bg-green-500 hover:bg-green-600 shadow-green-500/30' : 'bg-red-500 hover:bg-red-600 shadow-red-500/30' }}">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
@endif

</div>
