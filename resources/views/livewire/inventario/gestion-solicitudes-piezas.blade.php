<div>
    <x-tb-background>
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Gestión de Solicitudes · Gerente"
            chip="Vista activa"
            description="Administra las solicitudes de piezas de los técnicos"
        />

        {{-- Alertas --}}
        @if (session()->has('success'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        @if (session()->has('warning'))
            <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ session('warning') }}</p>
            </div>
        @endif

        @php
            $estatusOptions = [
                ['value' => 'TODOS',              'label' => 'Todos',                    'count' => null],
                ['value' => 'PENDIENTE',          'label' => 'Pendientes',               'count' => $contadores['pendientes']],
                ['value' => 'SURTIDA_INVENTARIO', 'label' => 'Asignadas',                'count' => $contadores['surtidas']],
                ['value' => 'PENDIENTE_COMPRA',   'label' => 'Pend. compra',             'count' => $contadores['pendientes_compra']],
                ['value' => 'COMPRADA',           'label' => 'Compradas',                'count' => $contadores['compradas']],
                ['value' => 'EN_CALIDAD',         'label' => 'En calidad',               'count' => $contadores['en_calidad']],
                ['value' => 'TERMINADOS',         'label' => 'Terminados',               'count' => $contadores['terminados']],
                ['value' => 'FALLO_PIEZA',        'label' => 'Reasignación pendiente',   'count' => $contadores['fallo_pieza']],
                ['value' => 'CANCELADA',          'label' => 'Canceladas',               'count' => $contadores['canceladas']],
            ];
        @endphp

        <div
            class="rounded-2xl
                bg-white/80 dark:bg-slate-950/70
                border border-slate-200/80 dark:border-white/10
                backdrop-blur-xl dark:backdrop-blur-2xl
                shadow-md shadow-slate-900/10
                dark:shadow-lg dark:shadow-slate-900/30
                transition-all duration-300
                hover:-translate-y-1
                hover:shadow-lg hover:shadow-indigo-500/20
                dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
                hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50"
        >
            <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Filtros
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                        Combina estatus, técnico y búsqueda para ubicar una solicitud.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                                    <p class="hidden sm:block text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                                        Mostrando
                                        <span class="font-bold text-slate-900 dark:text-slate-50">{{ $solicitudes->total() }}</span>
                                        solicitud(es)
                                        @if($busqueda)
                                            para “<span class="font-semibold">{{ $busqueda }}</span>”
                                        @endif
                                    </p>

                                    <button type="button" wire:click="exportarSeleccion" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-900 text-white text-xs font-medium shadow-lg hover:bg-slate-800">
                                        Exportar
                                    </button>
                                </div>
            </div>

            <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-slate-200/60 dark:border-slate-800/80">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                        Estatus
                    </label>
                    <select
                        wire:model.live="filtroEstatus"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                            border border-white/60 dark:border-slate-600/70
                            text-sm sm:text-base text-slate-900 dark:text-slate-100
                            focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="TODOS">Todos</option>
                        @foreach($estatusOptions as $option)
                            @if($option['value'] !== 'TODOS')
                                <option value="{{ $option['value'] }}">
                                    {{ $option['label'] }} ({{ $option['count'] }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                        Técnico
                    </label>
                    <select
                        wire:model.live="filtroTecnico"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                            border border-white/60 dark:border-slate-600/70
                            text-sm sm:text-base text-slate-900 dark:text-slate-100
                            focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="TODOS">Todos</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec['id'] }}">
                                {{ $tec['nombre'] }} {{ $tec['apellido_paterno'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                        Pieza
                    </label>
                    <select
                        wire:model.live="filtroPieza"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                            border border-white/60 dark:border-slate-600/70
                            text-sm sm:text-base text-slate-900 dark:text-slate-100
                            focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="TODOS">Todos</option>
                        @foreach($piezas as $p)
                            <option value="{{ $p['id'] }}">
                                {{ $p['categoria'] ? $p['categoria'] . ' — ' : '' }}{{ $p['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                        Búsqueda rápida
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-lg">
                            🔍
                        </span>

                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busqueda"
                            placeholder="Técnico, equipo, pieza o categoría..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm sm:text-base rounded-2xl
                                bg-white/80 dark:bg-slate-900/60
                                border border-white/60 dark:border-slate-700/70
                                text-slate-900 dark:text-slate-100
                                placeholder:text-slate-400 dark:placeholder:text-slate-500
                                shadow-md shadow-slate-900/10 dark:shadow-xl dark:shadow-slate-950/60
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70 focus:border-blue-500/70
                                backdrop-blur-xl"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Solicitudes --}}
        <div
            class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-500/20 dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
        >
            <div class="overflow-x-auto">
                <table class="min-w-[1280px] w-full text-sm sm:text-base text-left">
                    <thead class="bg-slate-100 border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800/80">
                        <tr>
                                                <th class="px-4 py-3">
                                                    <input type="checkbox" wire:model="selectPage" class="h-4 w-4 text-blue-600 rounded border-slate-300">
                                                </th>
                                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Solicitud</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Técnico</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Fechas</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Estado</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-right">Acciones</th>
                                            </tr>
                                        </thead>

                    <tbody>
                        @forelse($solicitudes as $solicitud)
                            @php
                                $equipo = $solicitud->equipo ?? $solicitud->asignacionEquipo?->equipo;
                                $tecnico = $solicitud->solicitadoPor;
                                $reasignado = $solicitud->reasignadoA;
                                $pieza = $solicitud->catalogoPieza;
                                $intentoNum = $solicitud->intentos->count();

                                if ($solicitud->estatus === 'CONFIRMADA') {
                                    if ($solicitud->funciono) {
                                        $badgeColor = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                                        $labelEstatus = 'En calidad';
                                    } else {
                                        $badgeColor = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                                        $labelEstatus = 'Pieza fallida';
                                    }
                                } elseif ($solicitud->estatus === 'REQUIERE_REASIGNACION') {
                                    $badgeColor = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                                    $labelEstatus = 'Reasignación pendiente';
                                } else {
                                    $badgeColor = match($solicitud->estatus) {
                                        'PENDIENTE'              => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'SURTIDA_INVENTARIO'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'PENDIENTE_COMPRA'       => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                        'COMPRADA'               => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                        'CANCELADA'              => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'REQUIERE_REASIGNACION'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                        default                  => 'bg-slate-100 text-slate-600',
                                    };
                                    $labelEstatus = \App\Models\SolicitudPieza::labelsEstatus()[$solicitud->estatus] ?? $solicitud->estatus;
                                }
                            @endphp

                            <tr class="border-b border-slate-200 dark:border-slate-800/80 hover:bg-white/60 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="px-4 py-4 align-top w-12">
                                    <input type="checkbox" wire:model="selected" value="{{ $solicitud->id }}" class="h-4 w-4 text-blue-600 rounded border-slate-300">
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-semibold text-slate-900 dark:text-slate-50">
                                                {{ $solicitud->titulo_solicitud }}
                                            </h3>
                                            @if(($solicitud->cantidad ?? 1) > 1)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                    ×{{ $solicitud->cantidad }}
                                                </span>
                                            @endif
                                            @if($intentoNum > 0)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                    {{ $intentoNum }} intento(s)
                                                </span>
                                            @endif
                                        </div>

                                        @if($pieza)
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                                <span class="font-medium text-slate-700 dark:text-slate-300">Pieza:</span>
                                                {{ $pieza->categoria }} — {{ $pieza->nombre }}
                                            </p>
                                        @elseif(!$solicitud->catalogo_pieza_id && ($solicitud->categoria_solicitada_texto || $solicitud->detalle_solicitado_texto))
                                            <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 space-y-0.5">
                                                @if($solicitud->categoria_solicitada_texto)
                                                    <p><span class="font-medium text-slate-700 dark:text-slate-300">Categoría:</span> {{ $solicitud->categoria_solicitada_texto }}</p>
                                                @endif
                                                @if($solicitud->detalle_solicitado_texto)
                                                    <p><span class="font-medium text-slate-700 dark:text-slate-300">Detalle:</span> {{ $solicitud->detalle_solicitado_texto }}</p>
                                                @endif
                                            </div>
                                        @endif

                                        @if($solicitud->notas_respuesta)
                                            <p class="text-xs sm:text-sm text-indigo-600 dark:text-indigo-300">
                                                <span class="font-medium">Notas:</span> {{ $solicitud->notas_respuesta }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="space-y-1">
                                        @if($equipo)
                                            <p class="font-mono text-sm text-slate-900 dark:text-slate-50">
                                                {{ $equipo->numero_serie }}
                                            </p>
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                                {{ $equipo->marca }} {{ $equipo->modelo }}
                                            </p>
                                        @else
                                            <span class="text-slate-400">Sin equipo</span>
                                        @endif

                                        @if($solicitud->inventarioPieza)
                                            <p class="text-xs text-emerald-600 dark:text-emerald-300">
                                                Pieza asignada #{{ $solicitud->inventarioPieza->id }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="space-y-1">
                                        @if($tecnico)
                                            <p class="font-medium text-slate-900 dark:text-slate-50">
                                                {{ $tecnico->nombre }} {{ $tecnico->apellido_paterno }}
                                            </p>
                                        @endif
                                        @if($reasignado && $reasignado->id !== $tecnico?->id)
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                                Reasignado a: {{ $reasignado->nombre }} {{ $reasignado->apellido_paterno }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top whitespace-nowrap">
                                    <div class="space-y-1 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                        <p>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">Solicitada:</span>
                                            {{ $solicitud->created_at->format('d/m/Y H:i') }}
                                        </p>
                                        <p>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">Respondida:</span>
                                            {{ $solicitud->respondida_en ? $solicitud->respondida_en->format('d/m/Y H:i') : 'Pendiente' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs sm:text-sm border font-semibold {{ $badgeColor }}">
                                        {{ $labelEstatus }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 align-top text-right">
                                    @php
                                        $tieneAcciones = $solicitud->puedeSerSurtidaDesdeInventario()
                                            || $solicitud->puedeSerGestionada()
                                            || $solicitud->puedeCancelarse();
                                    @endphp

                                    @if($tieneAcciones)
                                        <x-dropdown align="right" width="64" contentClasses="py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                            <x-slot name="trigger">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-900 text-white text-xs font-medium shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition-all"
                                                >
                                                    <span>Acciones</span>
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </x-slot>

                                            <x-slot name="content">
                                                @if($solicitud->puedeSerSurtidaDesdeInventario())
                                                    <button
                                                        type="button"
                                                        wire:click="abrirModalSurtir({{ $solicitud->id }})"
                                                        class="block w-full px-4 py-2.5 text-left text-sm text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                                    >
                                                        {{ $solicitud->estatus === 'COMPRADA' ? 'Surtir compra' : 'Surtir' }}
                                                    </button>
                                                @endif

                                                @if($solicitud->puedeSerGestionada())
                                                    <button
                                                        type="button"
                                                        wire:click="abrirModalCompra({{ $solicitud->id }})"
                                                        class="block w-full px-4 py-2.5 text-left text-sm text-orange-700 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors"
                                                    >
                                                        Compra
                                                    </button>
                                                @endif

                                                @if($solicitud->puedeCancelarse())
                                                    <button
                                                        type="button"
                                                        wire:click="abrirModalCancelar({{ $solicitud->id }})"
                                                        class="block w-full px-4 py-2.5 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                                                    >
                                                        Cancelar
                                                    </button>
                                                @endif
                                            </x-slot>
                                        </x-dropdown>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center">
                                    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="mt-4 text-slate-500 dark:text-slate-400">No hay solicitudes para mostrar</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $solicitudes->links() }}
            </div>
        </div>

    </div>

    {{-- Modal: Reasignar con pieza --}}
    @if($modalSurtir)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
              wire:click.self="cerrarModales">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">

                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Reasignar con pieza</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Selecciona la pieza del inventario y el técnico que la instalará</p>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Info del equipo/solicitud --}}
                    @if($solicitudSeleccionada)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-300 space-y-1">
                            <p><span class="font-medium">Pieza solicitada:</span> {{ $solicitudSeleccionada->titulo_solicitud }}</p>
                            @if(($solicitudSeleccionada->cantidad ?? 1) > 1)
                                <p class="flex items-center gap-1">
                                    <span class="font-medium">Cantidad requerida:</span>
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-bold">
                                        {{ $solicitudSeleccionada->cantidad }} unidades
                                    </span>
                                </p>
                            @endif
                            @php $eq = $solicitudSeleccionada->equipo ?? $solicitudSeleccionada->asignacionEquipo?->equipo; @endphp
                            @if($eq)
                                <p><span class="font-medium">Equipo:</span> {{ $eq->numero_serie }} — {{ $eq->marca }} {{ $eq->modelo }}</p>
                            @endif
                            @if($solicitudSeleccionada->solicitadoPor)
                                <p><span class="font-medium">Solicitó:</span>
                                   {{ $solicitudSeleccionada->solicitadoPor->nombre }}
                                   {{ $solicitudSeleccionada->solicitadoPor->apellido_paterno }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Seleccionar pieza --}}
                    @if($piezasDisponibles && count($piezasDisponibles) > 0)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Entrada de stock <span class="text-red-500">*</span>
                            </label>
                            @if(!$solicitudSeleccionada?->catalogo_pieza_id)
                                @php
                                    $catInferida = $solicitudSeleccionada->categoria_solicitada_texto;
                                    $catInferida = $solicitudSeleccionada->categoria_solicitada_texto !== 'Otro'
                                        ? $solicitudSeleccionada->categoria_solicitada_texto
                                        : null;
                                @endphp
                                <p class="text-xs text-amber-600 dark:text-amber-400 mb-2">
                                    Solicitud libre —
                                    @if($catInferida)
                                        mostrando stock de categoría <strong>{{ $catInferida }}</strong>.
                                    @else
                                        elige la pieza que mejor corresponda al pedido del técnico.
                                    @endif
                                </p>
                            @endif
                            <select wire:model="piezaSeleccionada"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                           border border-slate-200 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100
                                           focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                                <option value="">-- Selecciona una entrada --</option>
                                @foreach($piezasDisponibles as $pieza)
                                    <option value="{{ $pieza->id }}">
                                        @if($pieza->catalogoPieza)
                                            [{{ $pieza->catalogoPieza->categoria }}] {{ $pieza->catalogoPieza->nombre }} —
                                        @endif
                                        {{ $pieza->almacen->nombre ?? 'Sin almacén' }}
                                        · {{ $pieza->cantidad_disponible }} disp.
                                        @if($pieza->numero_serie) · S/N: {{ $pieza->numero_serie }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('piezaSeleccionada')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-sm text-yellow-700 dark:text-yellow-300">
                            @php $cantReq = $solicitudSeleccionada?->cantidad ?? 1; @endphp
                            @if($cantReq > 1)
                                No hay stock con suficientes unidades disponibles (se necesitan <strong>{{ $cantReq }}</strong>).
                            @else
                                No hay stock disponible en inventario.
                            @endif
                            Registra la compra primero o marca como pendiente de compra.
                        </div>
                    @endif

                    {{-- Seleccionar técnico que instalará --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Técnico que instalará la pieza <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="tecnicoReasignadoId"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                       border border-slate-200 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                            <option value="">-- Selecciona un técnico --</option>
                            @foreach($tecnicos as $tec)
                                <option value="{{ $tec['id'] }}">
                                    {{ $tec['nombre'] }} {{ $tec['apellido_paterno'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('tecnicoReasignadoId')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Puntos para el técnico --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Puntos para el técnico <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">
                            Define cuántos puntos recibirá el técnico al instalar esta pieza correctamente.
                        </p>
                        <input type="number" wire:model="puntosOverride"
                               min="0.01" step="0.01"
                               placeholder="Ej: 1.5"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                      border border-slate-200 dark:border-slate-700
                                      text-slate-900 dark:text-slate-100
                                      placeholder:text-slate-400
                                      focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm" />
                        @error('puntosOverride')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Notas (opcional)
                        </label>
                        <textarea wire:model="notasRespuesta" rows="2"
                                  placeholder="Observaciones..."
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400
                                         focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm"></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="cerrarModales"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm transition-all">
                        Cancelar
                    </button>
                    <button wire:click="surtirDeInventario"
                            @if(!$piezasDisponibles || count($piezasDisponibles) === 0) disabled @endif
                            class="px-6 py-2 rounded-lg bg-amber-500 hover:bg-amber-600
                                   text-white font-medium shadow-lg shadow-amber-500/30 text-sm transition-all
                                   disabled:opacity-40 disabled:cursor-not-allowed">
                        Reasignar con pieza
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Pendiente de Compra --}}
    @if($modalCompra)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">

                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Pendiente de Compra</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">La pieza se gestionará por compra</p>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Notas (opcional)
                        </label>
                        <textarea wire:model="notasRespuesta" rows="3"
                                  placeholder="Ej: Se solicitará al proveedor..."
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400
                                         focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm"></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="cerrarModales"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm transition-all">
                        Cancelar
                    </button>
                    <button wire:click="marcarPendienteCompra"
                            class="px-6 py-2 rounded-lg bg-orange-500 hover:bg-orange-600
                                   text-white font-medium shadow-lg shadow-orange-500/30 text-sm transition-all">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Cancelar --}}
    @if($modalCancelar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">

                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Cancelar Solicitud</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Esta acción no se puede deshacer</p>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Motivo de cancelación <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="motivoCancelacion" rows="4"
                                  placeholder="Explica por qué se cancela esta solicitud..."
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400
                                         focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"></textarea>
                        @error('motivoCancelacion')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="cerrarModales"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm transition-all">
                        Volver
                    </button>
                    <button wire:click="cancelarSolicitud"
                            class="px-6 py-2 rounded-lg bg-red-500 hover:bg-red-600
                                   text-white font-medium shadow-lg shadow-red-500/30 text-sm transition-all">
                        Cancelar Solicitud
                    </button>
                </div>
            </div>
        </div>
    @endif

        </div>
    </x-tb-background>
</div>
