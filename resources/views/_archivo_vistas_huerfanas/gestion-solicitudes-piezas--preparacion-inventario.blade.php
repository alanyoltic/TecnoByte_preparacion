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
                    Gestión de Solicitudes
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Administra las solicitudes de piezas de los técnicos
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

        @if (session()->has('warning'))
            <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ session('warning') }}
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
                               {{ $filtroEstatus === 'PENDIENTE' 
                                   ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    ⏳ Pendientes
                    @if($contadores['pendientes'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['pendientes'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('SURTIDA_INVENTARIO')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstatus === 'SURTIDA_INVENTARIO' 
                                   ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    📦 Surtidas
                    @if($contadores['surtidas'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['surtidas'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('PENDIENTE_COMPRA')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstatus === 'PENDIENTE_COMPRA' 
                                   ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    🛒 Pendiente Compra
                    @if($contadores['pendientes_compra'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['pendientes_compra'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('CONFIRMADA')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstatus === 'CONFIRMADA' 
                                   ? 'bg-green-500 text-white shadow-lg shadow-green-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    ✓ Confirmadas
                    @if($contadores['confirmadas'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['confirmadas'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('CANCELADA')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstatus === 'CANCELADA' 
                                   ? 'bg-red-500 text-white shadow-lg shadow-red-500/30' 
                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    ✗ Canceladas
                    @if($contadores['canceladas'] > 0)
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-white/30 text-xs">
                            {{ $contadores['canceladas'] }}
                        </span>
                    @endif
                </button>

                <button wire:click="cambiarFiltro('TODAS')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                               {{ $filtroEstatus === 'TODAS' 
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
                                        {{ $solicitud->nombre_pieza }}
                                    </h3>
                                    
                                    @php
                                        $equipo = $solicitud->equipo_relacionado;
                                    @endphp
                                    
                                    <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-400">
                                        @if($equipo)
                                            <p>
                                                <span class="font-medium">Equipo:</span> 
                                                {{ $equipo->numero_serie }} - {{ $equipo->modelo ?? 'N/A' }}
                                            </p>
                                        @endif
                                        <p>
                                            <span class="font-medium">Técnico:</span> 
                                            {{ $solicitud->solicitadoPor->nombre }} {{ $solicitud->solicitadoPor->apellido_paterno }}
                                        </p>
                                        <p>
                                            <span class="font-medium">Solicitada:</span> 
                                            {{ $solicitud->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    {{-- Descripción libre --}}
                                    @if($solicitud->descripcion_libre && !$solicitud->catalogo_pieza_id)
                                        <div class="mt-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                                <span class="font-medium">Descripción:</span><br>
                                                {{ $solicitud->descripcion_libre }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Notas de respuesta --}}
                                    @if($solicitud->notas_respuesta)
                                        <div class="mt-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                                <span class="font-medium">Notas del gerente:</span><br>
                                                {{ $solicitud->notas_respuesta }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Pieza asignada --}}
                                    @if($solicitud->inventarioPieza)
                                        <div class="mt-3 p-3 rounded-lg bg-green-50 dark:bg-green-900/20">
                                            <p class="text-sm text-green-700 dark:text-green-300">
                                                <span class="font-medium">Pieza asignada:</span> 
                                                #{{ $solicitud->inventarioPieza->id }}
                                                @if($solicitud->inventarioPieza->almacen)
                                                    - Almacén: {{ $solicitud->inventarioPieza->almacen->nombre }}
                                                @endif
                                                @if($solicitud->inventarioPieza->numero_serie)
                                                    (S/N: {{ $solicitud->inventarioPieza->numero_serie }})
                                                @endif
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Estado de confirmación --}}
                                    @if($solicitud->estatus === 'CONFIRMADA')
                                        <div class="mt-3 p-3 rounded-lg {{ $solicitud->funciono ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                                            <p class="text-sm {{ $solicitud->funciono ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                                <span class="font-medium">Confirmación técnico:</span> 
                                                {{ $solicitud->funciono ? '✓ Funcionó correctamente' : '✗ No funcionó' }}
                                                @if($solicitud->notas_confirmacion)
                                                    <br>{{ $solicitud->notas_confirmacion }}
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Estado y Acciones --}}
                        <div class="flex flex-col items-end gap-3">
                            {{-- Badge de Estado --}}
                            <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                       {{ $solicitud->estatus === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                                       {{ $solicitud->estatus === 'SURTIDA_INVENTARIO' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                       {{ $solicitud->estatus === 'PENDIENTE_COMPRA' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : '' }}
                                       {{ $solicitud->estatus === 'COMPRADA' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : '' }}
                                       {{ $solicitud->estatus === 'CONFIRMADA' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                       {{ $solicitud->estatus === 'CANCELADA' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                {{ $solicitud->icono_estado }} {{ $solicitud::labelsEstatus()[$solicitud->estatus] }}
                            </span>

                            {{-- Botones de Acción --}}
                            @if($solicitud->puedeSerGestionada())
                                <div class="flex flex-col gap-2">
                                    <button wire:click="abrirModalSurtir({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 
                                                   text-white text-sm font-medium shadow-lg shadow-green-500/30
                                                   transition-all whitespace-nowrap">
                                        ✓ Surtir de Inventario
                                    </button>
                                    <button wire:click="abrirModalCompra({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 
                                                   text-white text-sm font-medium shadow-lg shadow-orange-500/30
                                                   transition-all whitespace-nowrap">
                                        🛒 Marcar para Compra
                                    </button>
                                    <button wire:click="abrirModalCancelar({{ $solicitud->id }})"
                                            class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 
                                                   text-white text-sm font-medium shadow-lg shadow-red-500/30
                                                   transition-all whitespace-nowrap">
                                        ✗ Cancelar
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
                        No hay solicitudes {{ $filtroEstatus !== 'TODAS' ? strtolower(\App\Models\SolicitudPieza::labelsEstatus()[$filtroEstatus] ?? '') : '' }}
                    </p>
                </div>
            @endforelse

            {{-- Paginación --}}
            <div class="mt-6">
                {{ $solicitudes->links() }}
            </div>
        </div>

    </div>

    {{-- Modal Surtir --}}
    @if($modalSurtir)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('modalSurtir', false)">
            <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Surtir del Inventario
                    </h3>
                </div>

                <div class="p-6 space-y-4">
                    <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-800">
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            <span class="font-medium">Pieza:</span> {{ $solicitudSeleccionada->nombre_pieza }}<br>
                            @if($solicitudSeleccionada->equipo_relacionado)
                                <span class="font-medium">Equipo:</span> {{ $solicitudSeleccionada->equipo_relacionado->numero_serie }}<br>
                            @endif
                            <span class="font-medium">Técnico:</span> {{ $solicitudSeleccionada->solicitadoPor->nombre }}
                        </p>
                    </div>

                    @if($piezasDisponibles->isNotEmpty())
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
                                        Pieza #{{ $pieza->id }}
                                        @if($pieza->almacen) - {{ $pieza->almacen->nombre }} @endif
                                        @if($pieza->numero_serie) (S/N: {{ $pieza->numero_serie }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $piezasDisponibles->count() }} pieza(s) disponible(s) en stock
                            </p>
                        </div>
                    @else
                        <div class="p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                No hay piezas disponibles en inventario. Considera marcarla como pendiente de compra.
                            </p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Notas adicionales (opcional)
                        </label>
                        <textarea wire:model="notasRespuesta"
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
                    <button wire:click="$set('modalSurtir', false)"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700
                                   transition-all">
                        Cancelar
                    </button>
                    @if($piezasDisponibles->isNotEmpty())
                        <button wire:click="surtirDeInventario"
                                class="px-6 py-2 rounded-lg bg-green-500 hover:bg-green-600 
                                       text-white font-medium shadow-lg shadow-green-500/30
                                       transition-all">
                            Surtir Pieza
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Compra --}}
    @if($modalCompra)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('modalCompra', false)">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Marcar como Pendiente de Compra
                    </h3>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Esta pieza será marcada para compra externa ya que no está disponible en inventario.
                    </p>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Notas adicionales (opcional)
                        </label>
                        <textarea wire:model="notasRespuesta"
                                  rows="3"
                                  placeholder="Ej: Proveedor recomendado, precio estimado..."
                                  class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400 dark:placeholder:text-slate-500
                                         focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="$set('modalCompra', false)"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700
                                   transition-all">
                        Cancelar
                    </button>
                    <button wire:click="marcarPendienteCompra"
                            class="px-6 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 
                                   text-white font-medium shadow-lg shadow-orange-500/30
                                   transition-all">
                        Marcar para Compra
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Cancelar --}}
    @if($modalCancelar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('modalCancelar', false)">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 shadow-2xl">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Cancelar Solicitud
                    </h3>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Motivo de cancelación *
                        </label>
                        <textarea wire:model="motivoCancelacion"
                                  rows="4"
                                  placeholder="Explica por qué se cancela esta solicitud..."
                                  class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800
                                         border border-slate-200 dark:border-slate-700
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400 dark:placeholder:text-slate-500
                                         focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        @error('motivoCancelacion')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                    <button wire:click="$set('modalCancelar', false)"
                            class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700
                                   transition-all">
                        Volver
                    </button>
                    <button wire:click="cancelarSolicitud"
                            class="px-6 py-2 rounded-lg bg-red-500 hover:bg-red-600 
                                   text-white font-medium shadow-lg shadow-red-500/30
                                   transition-all">
                        Cancelar Solicitud
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
