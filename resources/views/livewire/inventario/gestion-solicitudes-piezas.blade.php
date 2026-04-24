<div>
    <x-tb-background>
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Gestión de Solicitudes"
            chip="Preparación · Inventario"
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

        {{-- Contadores --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                $tabs = [
                    ['key' => 'PENDIENTE',          'label' => 'Solicitudes pendientes', 'count' => $contadores['pendientes'],        'color' => 'yellow'],
                    ['key' => 'SURTIDA_INVENTARIO', 'label' => 'Piezas asignadas',       'count' => $contadores['surtidas'],          'color' => 'blue'],
                    ['key' => 'PENDIENTE_COMPRA',   'label' => 'Pend. compra',           'count' => $contadores['pendientes_compra'], 'color' => 'orange'],
                    ['key' => 'COMPRADA',           'label' => 'Compradas',              'count' => $contadores['compradas'],         'color' => 'purple'],
                    ['key' => 'EN_CALIDAD',         'label' => 'En calidad',             'count' => $contadores['en_calidad'],        'color' => 'emerald'],
                    ['key' => 'FALLO_PIEZA',        'label' => 'Reasignación pendiente', 'count' => $contadores['fallo_pieza'],       'color' => 'rose'],
                    ['key' => 'PASO_CALIDAD',       'label' => 'Pasaron calidad',        'count' => $contadores['paso_calidad'],      'color' => 'slate', 'disabled' => true],
                    ['key' => 'CANCELADA',          'label' => 'Canceladas',             'count' => $contadores['canceladas'],        'color' => 'red'],
                    ['key' => 'TODAS',              'label' => 'Todas',                  'count' => null,                             'color' => 'dark'],
                ];
                $colorMap = [
                    'yellow'  => ['active' => 'bg-yellow-500 text-white shadow-yellow-500/30'],
                    'blue'    => ['active' => 'bg-blue-500 text-white shadow-blue-500/30'],
                    'orange'  => ['active' => 'bg-orange-500 text-white shadow-orange-500/30'],
                    'purple'  => ['active' => 'bg-purple-500 text-white shadow-purple-500/30'],
                    'emerald' => ['active' => 'bg-emerald-500 text-white shadow-emerald-500/30'],
                    'rose'    => ['active' => 'bg-rose-500 text-white shadow-rose-500/30'],
                    'slate'   => ['active' => 'bg-slate-400 text-white'],
                    'red'     => ['active' => 'bg-red-500 text-white shadow-red-500/30'],
                    'dark'    => ['active' => 'bg-slate-700 text-white'],
                ];
            @endphp

            @foreach($tabs as $tab)
                @php
                    $colors   = $colorMap[$tab['color']];
                    $disabled = $tab['disabled'] ?? false;
                @endphp
                <button wire:click="cambiarFiltro('{{ $tab['key'] }}')"
                        @if($disabled) disabled @endif
                        class="rounded-xl p-3 text-left transition-all shadow-lg
                               {{ $filtroEstatus === $tab['key']
                                   ? $colors['active'] . ' shadow-lg'
                                   : 'bg-white/60 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-700/70 text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800' }}
                               {{ $disabled ? 'opacity-40 cursor-not-allowed' : '' }}">
                    <div class="text-2xl font-bold">{{ $tab['count'] ?? '—' }}</div>
                    <div class="text-xs font-medium mt-1 opacity-80">{{ $tab['label'] }}</div>
                    @if($disabled)
                        <div class="text-[0.6rem] opacity-50 mt-0.5">Proximamente</div>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Filtros --}}
        <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 shadow-xl shadow-slate-900/5 p-4">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Búsqueda --}}
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text"
                           wire:model.live.debounce.300ms="busqueda"
                           placeholder="Buscar por técnico, equipo, pieza..."
                           class="w-full px-4 py-2.5 pl-10 rounded-xl
                                  bg-slate-50 dark:bg-slate-800/50
                                  border border-slate-200 dark:border-slate-700
                                  text-slate-900 dark:text-slate-100
                                  placeholder:text-slate-400 dark:placeholder:text-slate-500
                                  focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button wire:click="cambiarFiltro('TODAS')"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium transition-all
                               {{ $filtroEstatus === 'TODAS'
                                   ? 'bg-slate-700 text-white shadow-lg'
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Ver todas
                </button>
            </div>
        </div>

        {{-- Lista de Solicitudes --}}
        <div class="space-y-3">
            @forelse($solicitudes as $solicitud)
                @php
                    $equipo = $solicitud->equipo ?? $solicitud->asignacionEquipo?->equipo;
                    $tecnico = $solicitud->solicitadoPor;
                    $pieza = $solicitud->catalogoPieza;
                    $intentoNum = $solicitud->intentos->count();
                    if ($solicitud->estatus === 'CONFIRMADA') {
                        if ($solicitud->funciono) {
                            $badgeColor  = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                            $labelEstatus = 'En calidad';
                        } else {
                            $badgeColor  = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                            $labelEstatus = 'Pieza fallida';
                        }
                    } elseif ($solicitud->estatus === 'REQUIERE_REASIGNACION') {
                        $badgeColor   = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                        $labelEstatus = 'Reasignación pendiente' . ($intentoNum > 0 ? " (intento {$intentoNum})" : '');
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

                <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl
                            border border-slate-200/70 dark:border-slate-700/70
                            shadow-xl shadow-slate-900/5 p-5">

                    <div class="flex items-start justify-between gap-4 flex-wrap">

                        {{-- Info --}}
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 shrink-0">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-slate-900 dark:text-white">
                                        {{ $solicitud->titulo_solicitud }}
                                    </h3>
                                    @if(($solicitud->cantidad ?? 1) > 1)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                            ×{{ $solicitud->cantidad }}
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                        {{ $labelEstatus }}
                                    </span>
                                </div>

                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-slate-600 dark:text-slate-400">
                                    @if($equipo)
                                        <p><span class="font-medium text-slate-700 dark:text-slate-300">Equipo:</span>
                                           {{ $equipo->numero_serie }} — {{ $equipo->modelo }}</p>
                                    @endif
                                    @if($tecnico)
                                        <p><span class="font-medium text-slate-700 dark:text-slate-300">Técnico:</span>
                                           {{ $tecnico->nombre }} {{ $tecnico->apellido_paterno }}</p>
                                    @endif
                                    <p><span class="font-medium text-slate-700 dark:text-slate-300">Solicitada:</span>
                                       {{ $solicitud->created_at->diffForHumans() }}</p>
                                    @if($solicitud->respondida_en)
                                        <p><span class="font-medium text-slate-700 dark:text-slate-300">Respondida:</span>
                                           {{ $solicitud->respondida_en->diffForHumans() }}</p>
                                    @endif
                                </div>

                                @if(!$solicitud->catalogo_pieza_id && ($solicitud->categoria_solicitada_texto || $solicitud->detalle_solicitado_texto))
                                    <div class="mt-2 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300">
                                        @if($solicitud->categoria_solicitada_texto)
                                            <p><span class="font-medium">Categoría:</span> {{ $solicitud->categoria_solicitada_texto }}</p>
                                        @endif
                                        @if($solicitud->detalle_solicitado_texto)
                                            <p><span class="font-medium">Detalle:</span> {{ $solicitud->detalle_solicitado_texto }}</p>
                                        @endif
                                    </div>
                                @endif

                                @if($solicitud->notas_respuesta)
                                    <div class="mt-2 p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-sm text-indigo-700 dark:text-indigo-300">
                                        <span class="font-medium">Notas:</span> {{ $solicitud->notas_respuesta }}
                                    </div>
                                @endif

                                @if($solicitud->inventarioPieza)
                                    <div class="mt-2 p-2.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-sm text-green-700 dark:text-green-300">
                                        <span class="font-medium">Pieza asignada:</span>
                                        #{{ $solicitud->inventarioPieza->id }}
                                        — Almacén: {{ $solicitud->inventarioPieza->almacen->nombre ?? 'N/A' }}
                                    </div>
                                @endif

                                {{-- Historial de intentos --}}
                                @if($solicitud->intentos->count() > 0)
                                    <div class="mt-3 space-y-1.5">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Historial de intentos</p>
                                        @foreach($solicitud->intentos as $intento)
                                            @php
                                                if (!$intento->estaCompletado()) {
                                                    $intentoBadge = ['label' => 'En instalacion', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'];
                                                } elseif ($intento->funciono) {
                                                    $intentoBadge = ['label' => 'Funciono', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'];
                                                } else {
                                                    $intentoBadge = ['label' => 'Fallo', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'];
                                                }
                                            @endphp
                                            <div class="flex items-start gap-2 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/40 text-xs">
                                                <span class="shrink-0 w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[0.6rem] font-bold text-slate-600 dark:text-slate-300">
                                                    {{ $intento->numero_intento }}
                                                </span>
                                                <div class="flex-1 min-w-0 space-y-0.5">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        @if($intento->inventarioPieza)
                                                            <span class="text-slate-700 dark:text-slate-300 font-medium">
                                                                Pieza #{{ $intento->inventarioPieza->id }}
                                                                @if($intento->inventarioPieza->almacen) — {{ $intento->inventarioPieza->almacen->nombre }} @endif
                                                            </span>
                                                        @endif
                                                        <span class="px-1.5 py-0.5 rounded-full text-[0.6rem] font-semibold {{ $intentoBadge['class'] }}">
                                                            {{ $intentoBadge['label'] }}
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-wrap gap-x-3 text-slate-400 dark:text-slate-500">
                                                        @if($intento->asignadoA)
                                                            <span>Tecnico: {{ $intento->asignadoA->nombre }} {{ $intento->asignadoA->apellido_paterno }}</span>
                                                        @endif
                                                        @if($intento->asignado_en)
                                                            <span>Asignado: {{ $intento->asignado_en->format('d/m/Y') }}</span>
                                                        @endif
                                                        @if($intento->confirmada_en)
                                                            <span>Confirmado: {{ $intento->confirmada_en->format('d/m/Y') }}</span>
                                                        @endif
                                                    </div>
                                                    @if($intento->notas_confirmacion)
                                                        <p class="text-slate-500 dark:text-slate-400 italic">{{ $intento->notas_confirmacion }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Acciones --}}
                        @if($solicitud->puedeSerSurtidaDesdeInventario() || $solicitud->puedeSerGestionada() || $solicitud->puedeCancelarse())
                            <div class="flex flex-col gap-2 shrink-0">
                                @if($solicitud->puedeSerSurtidaDesdeInventario())
                                    <button wire:click="abrirModalSurtir({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600
                                                   text-white text-sm font-medium shadow-lg shadow-amber-500/30 transition-all">
                                        {{ $solicitud->estatus === 'COMPRADA' ? 'Surtir compra' : 'Reasignar con pieza' }}
                                    </button>
                                @endif
                                @if($solicitud->puedeSerGestionada())
                                    <button wire:click="abrirModalCompra({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600
                                                   text-white text-sm font-medium shadow-lg shadow-orange-500/30 transition-all">
                                        Pend. de Compra
                                    </button>
                                @endif
                                @if($solicitud->puedeCancelarse())
                                    <button wire:click="abrirModalCancelar({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-red-100
                                                   text-slate-700 hover:text-red-700 text-sm font-medium transition-all">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl
                            border border-slate-200/70 dark:border-slate-700/70 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-slate-500 dark:text-slate-400">No hay solicitudes para mostrar</p>
                </div>
            @endforelse

            <div>{{ $solicitudes->links() }}</div>
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
                                    $descLibre   = $catInferida && $catInferida !== 'Otro'
                                        ? $catInferida . ' â€” '
                                        : '';
                                    $catInferida      = str_contains($descLibre, ' — ')
                                        ? trim(explode(' — ', $descLibre)[0])
                                        : null;
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
