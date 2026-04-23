<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Mis Solicitudes de Piezas"
            chip="Preparacion · Operaciones"
            description="Consulta el estado de tus solicitudes y confirma la instalacion cuando la pieza ya este colocada."
        />

        {{-- ══ TABS DE FILTRO ══════════════════════════════════════════════════ --}}
        @if($esTecnico)

            {{-- TABS PARA TECNICO --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                @php
                    $tabs = [
                        ['key' => 'PENDIENTE',              'label' => 'Por confirmar',          'count' => $contadores['por_confirmar'],  'active' => 'bg-yellow-500 text-white shadow-yellow-500/30'],
                        ['key' => 'POR_RESTOCK',            'label' => 'Pend. por restock',      'count' => $contadores['restock'],        'active' => 'bg-orange-500 text-white shadow-orange-500/30'],
                        ['key' => 'SURTIDA_INVENTARIO',     'label' => 'Listas para instalar',   'count' => $contadores['lista'],          'active' => 'bg-blue-500 text-white shadow-blue-500/30'],
                        ['key' => 'REQUIERE_REASIGNACION',  'label' => 'Pieza fallida',          'count' => $contadores['reasignacion'],   'active' => 'bg-rose-500 text-white shadow-rose-500/30'],
                        ['key' => 'EN_CALIDAD',             'label' => 'En calidad',             'count' => $contadores['en_calidad'],     'active' => 'bg-emerald-500 text-white shadow-emerald-500/30'],
                        ['key' => 'FINALIZADO_TEC',         'label' => 'Finalizadas',            'count' => $contadores['finalizado_tec'], 'active' => 'bg-slate-600 text-white'],
                        ['key' => 'TODAS',                  'label' => 'Todas',                  'count' => $contadores['todas'],          'active' => 'bg-slate-700 text-white'],
                    ];
                @endphp
                @foreach($tabs as $tab)
                    <button wire:click="cambiarFiltro('{{ $tab['key'] }}')"
                            class="rounded-xl p-3 text-left transition-all shadow-lg {{ $filtroEstatus === $tab['key'] ? $tab['active'] . ' shadow-lg' : 'bg-white/60 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-700/70 text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800' }}">
                        <span class="text-xl font-bold block mb-0.5">{{ $tab['count'] }}</span>
                        <span class="text-xs font-medium opacity-80 leading-tight block">{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </div>

        @else

            {{-- TABS PARA LIDER (igual que antes + En calidad) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                @php
                    $tabs = [
                        ['key' => 'PENDIENTE',           'label' => 'En revision',    'count' => $contadores['pendientes'],        'active' => 'bg-yellow-500 text-white shadow-yellow-500/30'],
                        ['key' => 'PENDIENTE_COMPRA',     'label' => 'Pend. compra',   'count' => $contadores['pendientes_compra'], 'active' => 'bg-orange-500 text-white shadow-orange-500/30'],
                        ['key' => 'COMPRADA',             'label' => 'Compradas',      'count' => $contadores['compradas'],         'active' => 'bg-violet-500 text-white shadow-violet-500/30'],
                        ['key' => 'SURTIDA_INVENTARIO',   'label' => 'Pieza lista',    'count' => $contadores['surtidas'],          'active' => 'bg-blue-500 text-white shadow-blue-500/30'],
                        ['key' => 'CONFIRMADA',           'label' => 'Confirmadas',    'count' => $contadores['confirmadas'],       'active' => 'bg-green-500 text-white shadow-green-500/30'],
                        ['key' => 'EN_CALIDAD',           'label' => 'En calidad',     'count' => $contadores['en_calidad'],        'active' => 'bg-emerald-500 text-white shadow-emerald-500/30'],
                        ['key' => 'CANCELADA',            'label' => 'Canceladas',     'count' => $contadores['canceladas'],        'active' => 'bg-red-500 text-white shadow-red-500/30'],
                        ['key' => 'TODAS',                'label' => 'Todas',          'count' => $contadores['todas'],             'active' => 'bg-slate-700 text-white'],
                    ];
                @endphp
                @foreach($tabs as $tab)
                    <button wire:click="cambiarFiltro('{{ $tab['key'] }}')"
                            class="rounded-xl p-3 text-left transition-all shadow-lg {{ $filtroEstatus === $tab['key'] ? $tab['active'] . ' shadow-lg' : 'bg-white/60 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-700/70 text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800' }}">
                        <span class="text-xl font-bold block mb-0.5">{{ $tab['count'] }}</span>
                        <span class="text-xs font-medium opacity-80">{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </div>

        @endif

        {{-- BUSCADOR --}}
        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.300ms="busqueda"
                   placeholder="Buscar por pieza, numero de serie o modelo..."
                   class="w-full px-4 py-2.5 pl-10 rounded-xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        {{-- LISTA DE SOLICITUDES --}}
        <div class="space-y-3">
            @forelse($solicitudes as $solicitud)
                @php
                    $equipo        = $solicitud->equipo ?? $solicitud->asignacionEquipo?->equipo;
                    $respondio     = $solicitud->respondidaPor;
                    $responsableId = $solicitud->reasignado_a_id ?: $solicitud->solicitado_por_id;
                    $esResponsable = (int) $responsableId === (int) auth()->id();
                    $fueIniciada   = $solicitud->fueIniciada();

                    // Delegada: yo la cree pero fue asignada a OTRO técnico
                    $esDelegada = $solicitud->reasignado_a_id
                                  && $solicitud->reasignado_a_id != $solicitud->solicitado_por_id
                                  && (int) $solicitud->solicitado_por_id === (int) auth()->id();

                    // Accion disponible
                    $puedeGestionar = $solicitud->estatus === 'SURTIDA_INVENTARIO' && $esResponsable && !$esDelegada;
                    $puedeEliminar  = $solicitud->estatus === 'PENDIENTE'
                                      && (int) $solicitud->solicitado_por_id === (int) auth()->id()
                                      && !$esDelegada;

                    // Badge de estatus
                    if ($esDelegada) {
                        $badge = ['label' => 'Finalizada', 'class' => 'bg-slate-200 text-slate-600 dark:bg-slate-700/80 dark:text-slate-300'];
                    } elseif ($solicitud->estatus === 'CONFIRMADA' && $solicitud->funciono) {
                        $badge = ['label' => 'En calidad', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'];
                    } else {
                        $intentoNum = $solicitud->intentos->count();
                        $badge = match($solicitud->estatus) {
                            'PENDIENTE'              => ['label' => $esTecnico ? 'Por confirmar'        : 'En revision',         'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
                            'PENDIENTE_COMPRA'       => ['label' => $esTecnico ? 'Pendiente por restock' : 'Pendiente de compra', 'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
                            'COMPRADA'               => ['label' => $esTecnico ? 'Pendiente por restock' : 'Comprada',            'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
                            'SURTIDA_INVENTARIO'     => ['label' => $fueIniciada ? 'En instalacion'     : 'Lista para instalar',  'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                            'CONFIRMADA'             => ['label' => 'Finalizada',                                                  'class' => 'bg-slate-200 text-slate-600 dark:bg-slate-700/80 dark:text-slate-300'],
                            'CANCELADA'              => ['label' => 'Cancelada',                                                   'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
                            'REQUIERE_REASIGNACION'  => ['label' => 'Pieza fallida' . ($intentoNum > 0 ? " (intento {$intentoNum})" : ''), 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'],
                            default                  => ['label' => $solicitud->estatus,                                           'class' => 'bg-slate-100 text-slate-600'],
                        };
                    }
                @endphp

                <div class="rounded-2xl backdrop-blur-xl shadow-xl shadow-slate-900/5 p-5 transition-all
                    {{ $esDelegada
                        ? 'bg-slate-50/60 dark:bg-slate-900/30 border border-slate-200/40 dark:border-slate-700/30 opacity-60'
                        : ($puedeGestionar
                            ? 'bg-white/70 dark:bg-slate-900/60 border border-amber-300/70 dark:border-amber-600/50 ring-2 ring-amber-400/40 dark:ring-amber-500/20'
                            : 'bg-white/70 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-700/70') }}">

                    <div class="flex items-start justify-between gap-4 flex-wrap">

                        {{-- CONTENIDO --}}
                        <div class="flex-1 min-w-0 space-y-3">

                            {{-- Nombre + badges --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-slate-900 dark:text-white">
                                    {{ $solicitud->nombre_pieza }}
                                </h3>
                                @if(($solicitud->cantidad ?? 1) > 1)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                        x{{ $solicitud->cantidad }} unidades
                                    </span>
                                @endif
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                @if($fueIniciada && $puedeGestionar)
                                    <span class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                        En curso
                                    </span>
                                @endif
                            </div>

                            {{-- Equipo --}}
                            @if($equipo)
                                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $equipo->numero_serie }}</span>
                                        @if($equipo->marca || $equipo->modelo)
                                            &mdash; {{ trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')) }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            {{-- Fechas --}}
                            <div class="flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
                                <span>Solicitada: <strong>{{ $solicitud->created_at->format('d/m/Y H:i') }}</strong></span>
                                @if($solicitud->respondida_en && !$esDelegada)
                                    <span>Gestionada: <strong>{{ $solicitud->respondida_en->format('d/m/Y H:i') }}</strong></span>
                                @endif
                                @if($respondio && !$esDelegada)
                                    <span>Por: <strong>{{ $respondio->nombre }} {{ $respondio->apellido_paterno }}</strong></span>
                                @endif
                            </div>

                            {{-- Delegada: texto informativo simple, sin nombres --}}
                            @if($esDelegada)
                                <p class="text-xs text-slate-400 dark:text-slate-500 italic">
                                    La instalacion fue asignada a otro tecnico. No requiere accion de tu parte.
                                </p>
                            @else
                                {{-- Descripcion libre --}}
                                @if($solicitud->descripcion_libre)
                                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/40 text-sm text-slate-700 dark:text-slate-300">
                                        {{ $solicitud->descripcion_libre }}
                                    </div>
                                @endif

                                {{-- Nota de inventario --}}
                                @if($solicitud->notas_respuesta)
                                    <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200/50 dark:border-indigo-700/30 text-sm">
                                        <p class="font-medium text-indigo-700 dark:text-indigo-300 mb-1">Nota de inventario</p>
                                        <p class="text-slate-700 dark:text-slate-300">{{ $solicitud->notas_respuesta }}</p>
                                    </div>
                                @endif

                                {{-- Pieza del inventario (solo responsable) --}}
                                @if($solicitud->inventarioPieza && $esResponsable)
                                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-700/30 text-sm">
                                        <p class="font-semibold text-blue-700 dark:text-blue-300 mb-1">Pieza del inventario</p>
                                        <p class="text-slate-700 dark:text-slate-300">
                                            Pieza #{{ $solicitud->inventarioPieza->id }}
                                            @if($solicitud->inventarioPieza->numero_serie)
                                                &mdash; S/N: {{ $solicitud->inventarioPieza->numero_serie }}
                                            @endif
                                            @if($solicitud->inventarioPieza->almacen)
                                                &mdash; {{ $solicitud->inventarioPieza->almacen->nombre }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                {{-- Historial de intentos --}}
                                @if($solicitud->intentos->count() > 0)
                                    <div class="space-y-1.5">
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
                                                        @else
                                                            <span class="text-slate-500 dark:text-slate-400">Pieza sin detalle</span>
                                                        @endif
                                                        <span class="px-1.5 py-0.5 rounded-full text-[0.6rem] font-semibold {{ $intentoBadge['class'] }}">
                                                            {{ $intentoBadge['label'] }}
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-wrap gap-x-3 text-slate-400 dark:text-slate-500">
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

                                {{-- Resultado confirmado --}}
                                @if($solicitud->estatus === 'CONFIRMADA')
                                    <div class="p-3 rounded-xl {{ $solicitud->funciono ? 'bg-green-50 dark:bg-green-900/20 border border-green-200/50 dark:border-green-700/30' : 'bg-slate-50 dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/40' }} text-sm">
                                        <p class="font-semibold {{ $solicitud->funciono ? 'text-green-700 dark:text-green-300' : 'text-slate-600 dark:text-slate-400' }}">
                                            {{ $solicitud->funciono ? 'La pieza quedo instalada. El equipo paso a calidad.' : 'La pieza fallo. Se genero una nueva solicitud automaticamente.' }}
                                        </p>
                                        @if($solicitud->notas_confirmacion)
                                            <p class="mt-1 text-slate-500 dark:text-slate-400 text-xs">{{ $solicitud->notas_confirmacion }}</p>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- ACCIONES --}}
                        @if(!$esDelegada)
                            <div class="shrink-0 flex flex-col items-end gap-2">

                                @if($puedeGestionar)
                                    <button wire:click="abrirConfirmar({{ $solicitud->id }})"
                                            class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-lg transition-all ring-2 ring-offset-2 ring-offset-transparent animate-pulse hover:animate-none
                                                {{ $fueIniciada
                                                    ? 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/40 ring-emerald-400/40'
                                                    : 'bg-amber-500 hover:bg-amber-400 shadow-amber-500/40 ring-amber-400/40' }}">
                                        {{ $fueIniciada ? 'Confirmar resultado' : 'Iniciar instalacion' }}
                                    </button>
                                @endif

                                @if($puedeEliminar)
                                    <button wire:click="abrirModalEliminar({{ $solicitud->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-medium border border-red-200 dark:border-red-800 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                @endif

                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/70 p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Sin solicitudes</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">No hay solicitudes con ese filtro.</p>
                </div>
            @endforelse

            <div>{{ $solicitudes->links() }}</div>
        </div>

    </div>
</x-tb-background>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: GESTIONAR INSTALACION (estilo MiTrabajo)                       --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if($modalConfirmar && $solicitudSeleccionada)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="cerrarModal"></div>

        <div class="relative w-full max-w-lg space-y-4">

            {{-- Info pieza + equipo --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                        border border-amber-300/60 dark:border-amber-600/40
                        backdrop-blur-xl shadow-md px-5 py-5 space-y-3">

                <p class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                    Instalacion de pieza
                </p>

                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/40 px-4 py-3">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">Pieza a instalar</p>
                        <p class="font-bold text-slate-900 dark:text-slate-50">
                            {{ $solicitudSeleccionada->catalogoPieza?->nombre ?? $solicitudSeleccionada->descripcion_libre ?? 'Sin descripcion' }}
                        </p>
                        @if($solicitudSeleccionada->inventarioPieza)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Del inventario #{{ $solicitudSeleccionada->inventarioPieza->id }}
                                @if($solicitudSeleccionada->inventarioPieza->numero_serie)
                                    &middot; S/N: <span class="font-mono">{{ $solicitudSeleccionada->inventarioPieza->numero_serie }}</span>
                                @endif
                                @if($solicitudSeleccionada->inventarioPieza->almacen)
                                    &middot; {{ $solicitudSeleccionada->inventarioPieza->almacen->nombre }}
                                @endif
                            </p>
                        @endif
                    </div>

                    @php $eq = $solicitudSeleccionada->equipo ?? $solicitudSeleccionada->asignacionEquipo?->equipo; @endphp
                    @if($eq)
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/40 px-4 py-3">
                            <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">Equipo</p>
                            <p class="font-bold text-slate-900 dark:text-slate-50">{{ $eq->marca }} {{ $eq->modelo }}</p>
                            <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $eq->numero_serie }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FASE A: No iniciada --}}
            @if(!$solicitudSeleccionada->fueIniciada())
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-5 py-6 space-y-4 text-center">

                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Cuando tengas la pieza en mano y estes listo, inicia la instalacion.
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Se registrara el tiempo desde que inicies hasta que confirmes el resultado.
                    </p>

                    <button wire:click="iniciarDesdeModal"
                            wire:loading.attr="disabled"
                            wire:target="iniciarDesdeModal"
                            class="w-full inline-flex items-center justify-center gap-2
                                   rounded-xl px-6 py-4 text-sm font-bold
                                   bg-gradient-to-r from-amber-500 to-amber-600
                                   text-white shadow-lg shadow-amber-500/40
                                   hover:shadow-amber-500/60 hover:-translate-y-0.5
                                   disabled:opacity-60 transition-all duration-200">
                        <svg wire:loading.remove wire:target="iniciarDesdeModal" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5"/>
                        </svg>
                        <span wire:loading.remove wire:target="iniciarDesdeModal">Iniciar instalacion</span>
                        <span wire:loading wire:target="iniciarDesdeModal">Iniciando...</span>
                    </button>

                    <button wire:click="cerrarModal"
                            class="w-full text-center text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors py-1">
                        Cancelar
                    </button>
                </div>

            {{-- FASE B: Ya iniciada --}}
            @else
                <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl shadow-md px-5 py-6 space-y-4">

                    <div class="flex items-center justify-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                            Instalacion en curso &middot; iniciada {{ $solicitudSeleccionada->iniciada_instalacion_en?->diffForHumans() }}
                        </p>
                    </div>

                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 text-center">
                        La pieza funciono correctamente?
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="confirmarConResultado(true)"
                                wire:loading.attr="disabled"
                                wire:target="confirmarConResultado"
                                class="flex flex-col items-center gap-2 rounded-2xl px-4 py-5
                                       bg-emerald-50 dark:bg-emerald-950/40
                                       border-2 border-emerald-400/60 dark:border-emerald-500/50
                                       text-emerald-700 dark:text-emerald-300
                                       hover:bg-emerald-100 dark:hover:bg-emerald-900/40
                                       shadow-md shadow-emerald-500/20
                                       disabled:opacity-60 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-bold">Si funciono</span>
                            <span class="text-xs text-emerald-600/70 dark:text-emerald-400/70 text-center">La pieza quedo instalada</span>
                        </button>

                        <button wire:click="confirmarConResultado(false)"
                                wire:loading.attr="disabled"
                                wire:target="confirmarConResultado"
                                class="flex flex-col items-center gap-2 rounded-2xl px-4 py-5
                                       bg-rose-50 dark:bg-rose-950/40
                                       border-2 border-rose-400/60 dark:border-rose-500/50
                                       text-rose-700 dark:text-rose-300
                                       hover:bg-rose-100 dark:hover:bg-rose-900/40
                                       shadow-md shadow-rose-500/20
                                       disabled:opacity-60 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-bold">No funciono</span>
                            <span class="text-xs text-rose-600/70 dark:text-rose-400/70 text-center">Se pedira otra pieza</span>
                        </button>
                    </div>

                    <button wire:click="cerrarModal"
                            class="w-full text-center text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors py-1">
                        Cancelar
                    </button>
                </div>
            @endif

        </div>
    </div>
@endif

{{-- MODAL ELIMINAR SOLICITUD --}}
@if($modalEliminar)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Eliminar solicitud</h3>
                    <p class="text-xs text-slate-400">Solo disponible mientras este en revision</p>
                </div>
            </div>
            <p class="text-sm text-slate-300">
                Al eliminar esta solicitud, el equipo seguira marcado como
                <strong class="text-white">Pieza Pendiente</strong> en tus asignaciones.
                Debes volver a solicitarla desde Mi Trabajo si aun la necesitas.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" wire:click="cerrarModalEliminar"
                    class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium transition-colors">
                    Cancelar
                </button>
                <button type="button" wire:click="confirmarEliminar"
                    wire:loading.attr="disabled" wire:target="confirmarEliminar"
                    class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-semibold shadow-lg shadow-red-900/20 transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="confirmarEliminar">Si, eliminar</span>
                    <span wire:loading wire:target="confirmarEliminar">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

</div>
