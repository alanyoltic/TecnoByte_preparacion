<div class="min-h-screen">
    {{-- Topbar --}}
    <x-topbar>
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 
                        shadow-lg shadow-blue-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    Solicitudes de Piezas
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Gestiona las solicitudes de los técnicos
                </p>
            </div>
        </div>
    </x-topbar>

    {{-- Contenido --}}
    <div class="p-6 space-y-6">

        {{-- Alertas --}}
        @if (session()->has('success'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Filtros y Búsqueda --}}
        <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl 
                    border border-slate-200/70 dark:border-slate-700/70 
                    shadow-xl shadow-slate-900/5 p-6">
            
            {{-- Tabs de Estado --}}
            <div class="flex flex-wrap gap-2 mb-6">
                <button wire:click="cambiarFiltro('PENDIENTE')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstado === 'PENDIENTE' 
                                   ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Pendientes
                    @if($contadores['pendientes'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['pendientes'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('SURTIDA')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstado === 'SURTIDA' 
                                   ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Surtidas
                    @if($contadores['surtidas'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['surtidas'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('CONFIRMADA')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstado === 'CONFIRMADA' 
                                   ? 'bg-green-500 text-white shadow-lg shadow-green-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Confirmadas
                    @if($contadores['confirmadas'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['confirmadas'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('RECHAZADA')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstado === 'RECHAZADA' 
                                   ? 'bg-red-500 text-white shadow-lg shadow-red-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Rechazadas
                    @if($contadores['rechazadas'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['rechazadas'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('TODAS')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstado === 'TODAS' 
                                   ? 'bg-slate-700 text-white' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Todas
                </button>
            </div>

            {{-- Búsqueda --}}
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.300ms="busqueda"
                       placeholder="Buscar por técnico, equipo o pieza..."
                       class="w-full px-4 py-3 pl-11 rounded-xl
                              bg-slate-50 dark:bg-slate-800/50
                              border border-slate-200 dark:border-slate-700
                              text-slate-900 dark:text-slate-100
                              placeholder:text-slate-400 dark:placeholder:text-slate-500
                              focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              transition-all">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        {{-- Lista de Solicitudes --}}
        <div class="space-y-4">
            @forelse($solicitudes as $solicitud)
                <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl 
                            border border-slate-200/70 dark:border-slate-700/70 
                            shadow-xl shadow-slate-900/5 p-6
                            hover:shadow-2xl hover:shadow-slate-900/10 transition-all">
                    
                    <div class="flex items-start justify-between gap-4">
                        {{-- Info Principal --}}
                        <div class="flex-1 space-y-3">
                            <div class="flex items-start gap-4">
                                <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                        {{ $solicitud->catalogoPieza->nombre }}
                                    </h3>
                                    <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-400">
                                        <p>
                                            <span class="font-medium">Equipo:</span> 
                                            {{ $solicitud->equipo->numero_serie }} - {{ $solicitud->equipo->modelo }}
                                        </p>
                                        <p>
                                            <span class="font-medium">Técnico:</span> 
                                            {{ $solicitud->tecnico->nombre }} {{ $solicitud->tecnico->apellido_paterno }}
                                        </p>
                                        <p>
                                            <span class="font-medium">Solicitada:</span> 
                                            {{ $solicitud->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    @if($solicitud->nota_tecnico)
                                        <div class="mt-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                                <span class="font-medium">Nota del técnico:</span><br>
                                                {{ $solicitud->nota_tecnico }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($solicitud->nota_gerente)
                                        <div class="mt-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                                <span class="font-medium">Nota del gerente:</span><br>
                                                {{ $solicitud->nota_gerente }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($solicitud->piezaInventario)
                                        <div class="mt-3 p-3 rounded-lg bg-green-50 dark:bg-green-900/20">
                                            <p class="text-sm text-green-700 dark:text-green-300">
                                                <span class="font-medium">Pieza asignada:</span> 
                                                #{{ $solicitud->piezaInventario->id }} - 
                                                Almacén: {{ $solicitud->piezaInventario->almacen->nombre ?? 'N/A' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Estado y Acciones --}}
                        <div class="flex flex-col items-end gap-3">
                            {{-- Badge de Estado --}}
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                       {{ $solicitud->estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                                       {{ $solicitud->estado === 'SURTIDA' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                       {{ $solicitud->estado === 'CONFIRMADA' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                       {{ $solicitud->estado === 'RECHAZADA' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                {{ $solicitud->estado_texto }}
                            </span>

                            {{-- Botones de Acción --}}
                            @if($solicitud->puedeSerGestionada())
                                <div class="flex gap-2">
                                    <button wire:click="abrirModalAprobar({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 
                                                   text-white text-sm font-medium shadow-lg shadow-green-500/30
                                                   transition-all">
                                        ✓ Aprobar
                                    </button>
                                    <button wire:click="abrirModalRechazar({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 
                                                   text-white text-sm font-medium shadow-lg shadow-red-500/30
                                                   transition-all">
                                        ✗ Rechazar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl 
                            border border-slate-200/70 dark:border-slate-700/70 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-slate-500 dark:text-slate-400">
                        No hay solicitudes {{ $filtroEstado !== 'TODAS' ? strtolower($filtroEstado) : '' }}
                    </p>
                </div>
            @endforelse

            {{-- Paginación --}}
            <div class="mt-6">
                {{ $solicitudes->links() }}
            </div>
        </div>

    </div>

    {{-- Modal Aprobar --}}
    @if($modalAprobar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('modalAprobar', false)">
            <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Aprobar Solicitud
                    </h3>
                </div>

                <div class="p-6 space-y-4">
                    <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-800">
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            <span class="font-medium">Pieza:</span> {{ $solicitudSeleccionada->catalogoPieza->nombre }}<br>
                            <span class="font-medium">Equipo:</span> {{ $solicitudSeleccionada->equipo->numero_serie }}<br>
                            <span class="font-medium">Técnico:</span> {{ $solicitudSeleccionada->tecnico->nombre }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Seleccionar pieza del inventario
                        </label>
                        <select wire:model="piezaSeleccionada"
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                       border border-slate-200 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($piezasDisponibles as $pieza)
                                <option value="{{ $pieza->id }}">
                                    Pieza #{{ $pieza->id }} - {{ $pieza->almacen->nombre ?? 'Sin almacén' }}
                                    @if($pieza->numero_serie) (S/N: {{ $pieza->numero_serie }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $piezasDisponibles->count() }} pieza(s) disponible(s) en stock
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Nota adicional (opcional)
                        </label>
                        <textarea wire:model="notaGerente"
                                  rows="3"
                                  placeholder="Ej: Pieza revisada y probada..."
                                  class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400 dark:placeholder:text-slate-500
                                         focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="$set('modalAprobar', false)"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700
                                   transition-all">
                        Cancelar
                    </button>
                    <button wire:click="aprobarSolicitud"
                            class="px-6 py-2 rounded-lg bg-green-500 hover:bg-green-600 
                                   text-white font-medium shadow-lg shadow-green-500/30
                                   transition-all">
                        Aprobar y Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Rechazar --}}
    @if($modalRechazar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('modalRechazar', false)">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Rechazar Solicitud
                    </h3>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Motivo del rechazo *
                        </label>
                        <textarea wire:model="motivoRechazo"
                                  rows="4"
                                  placeholder="Explica por qué se rechaza esta solicitud..."
                                  class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400 dark:placeholder:text-slate-500
                                         focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        @error('motivoRechazo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="$set('modalRechazar', false)"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700
                                   transition-all">
                        Cancelar
                    </button>
                    <button wire:click="rechazarSolicitud"
                            class="px-6 py-2 rounded-lg bg-red-500 hover:bg-red-600 
                                   text-white font-medium shadow-lg shadow-red-500/30
                                   transition-all">
                        Rechazar Solicitud
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
