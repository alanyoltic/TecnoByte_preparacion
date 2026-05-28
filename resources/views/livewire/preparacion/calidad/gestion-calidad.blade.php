<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Gestión de Calidad"
            chip="Preparación · Calidad"
            description="Valida y aprueba equipos listos para ventas, o recházalos para corregir defectos."
        />

        <div class="space-y-6">

            {{-- FILA SUPERIOR: RESUMEN + BUSCADOR --}}
            <div class="flex flex-col lg:flex-row gap-6">

                {{-- TARJETAS RESUMEN -- ESTILO GLOW REAL --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 flex-1">

                    {{-- EN CALIDAD -- Glow naranja --}}
                    <div
                        class="rounded-2xl
                               bg-orange-50/90 dark:bg-orange-950/40
                               border border-orange-200/80 dark:border-orange-500/70
                               backdrop-blur-xl dark:backdrop-blur-2xl
                               px-4 py-3
                               shadow-md shadow-orange-900/10
                               dark:shadow-lg dark:shadow-orange-900/30
                               transition-all duration-300
                               hover:-translate-y-1
                               hover:shadow-lg hover:shadow-orange-500/40
                               dark:hover:shadow-2xl dark:hover:shadow-orange-400/50
                               hover:border-orange-400/70"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-orange-700 dark:text-orange-200 uppercase tracking-wide">
                            En calidad
                        </p>
                        <p class="mt-2 text-2xl font-bold text-orange-800 dark:text-orange-100">
                            {{ $stats['en_calidad'] ?? 0 }}
                        </p>
                    </div>

                    {{-- APROBADOS -- Glow verde --}}
                    <div
                        class="rounded-2xl
                               bg-emerald-50/90 dark:bg-emerald-950/40
                               border border-emerald-200/80 dark:border-emerald-500/70
                               backdrop-blur-xl dark:backdrop-blur-2xl
                               px-4 py-3
                               shadow-md shadow-emerald-900/10
                               dark:shadow-lg dark:shadow-emerald-900/30
                               transition-all duration-300
                               hover:-translate-y-1
                               hover:shadow-lg hover:shadow-emerald-500/40
                               dark:hover:shadow-2xl dark:hover:shadow-emerald-400/50
                               hover:border-emerald-400/70"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                            Aprobados
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                            {{ $stats['validados'] ?? 0 }}
                        </p>
                    </div>

                    {{-- RECHAZADOS -- Glow rojo --}}
                    <div
                        class="rounded-2xl
                               bg-red-50/90 dark:bg-red-950/40
                               border border-red-200/80 dark:border-red-500/70
                               backdrop-blur-xl dark:backdrop-blur-2xl
                               px-4 py-3
                               shadow-md shadow-red-900/10
                               dark:shadow-lg dark:shadow-red-900/30
                               transition-all duration-300
                               hover:-translate-y-1
                               hover:shadow-lg hover:shadow-red-500/40
                               dark:hover:shadow-2xl dark:hover:shadow-red-400/50
                               hover:border-red-400/70"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-red-700 dark:text-red-200 uppercase tracking-wide">
                            Rechazados
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-800 dark:text-red-100">
                            {{ $stats['rechazados'] ?? 0 }}
                        </p>
                    </div>

                </div>

                {{-- Buscador --}}
                <div class="w-full lg:w-80">
                    <label class="block text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                        Búsqueda rápida
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-lg">
                            🔍
                        </span>

                        <input
                            type="text"
                            wire:model.live.debounce.500ms="search"
                            placeholder="Serie, marca, modelo..."
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

            {{-- FILTROS BÁSICOS --}}
            <div
                x-data="{ openAvanzados: false }"
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
                            Filtra por estado de validación para revisar equipos específicos.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 text-xs sm:text-sm">
                        <p class="hidden sm:block text-slate-600 dark:text-slate-300">
                            Mostrando
                            <span class="font-bold text-slate-900 dark:text-slate-50">{{ $equipos->total() }}</span>
                            registro(s)
                            @if($search)
                                para "<span class="font-semibold">{{ $search }}</span>"
                            @endif
                        </p>

                        <button
                            type="button"
                            wire:click="$set('search', ''); $set('filtroEstado', 'en_calidad')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                bg-slate-100/80 dark:bg-slate-900/80
                                text-[0.75rem] font-semibold text-slate-700 dark:text-slate-100
                                border border-slate-300/70 dark:border-slate-700/80
                                hover:bg-slate-200/80 dark:hover:bg-slate-800
                                transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a1 1 0 011-1h10a1 1 0 01.8 1.6L12 10.25V15a1 1 0 01-.553.894l-3 1.5A1 1 0 017 16.5v-6.25L3.2 4.6A1 1 0 014 4z"/>
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </div>

                {{-- FILTROS BÁSICOS --}}
                <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- Estado --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Estado de validación
                        </label>
                        <select
                            wire:model.change="filtroEstado"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                border border-white/60 dark:border-slate-600/70
                                text-sm text-slate-900 dark:text-slate-100
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                        >
                            <option value="en_calidad">En Calidad</option>
                            <option value="validados">Aprobados</option>
                            <option value="rechazados">Rechazados</option>
                            <option value="todos">Todos</option>
                        </select>
                    </div>

                    {{-- Validado por --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Validado por
                        </label>
                        <select
                            wire:model.change="filtroValidador"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                border border-white/60 dark:border-slate-600/70
                                text-sm text-slate-900 dark:text-slate-100
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                        >
                            <option value="">Todos</option>
                            @foreach($validadores as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Registros por página --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Registros por página
                        </label>
                        <select
                            wire:model.live.debounce.150ms="perPage"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                border border-white/60 dark:border-slate-600/70
                                text-sm text-slate-900 dark:text-slate-100
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- TABLA DE EQUIPOS --}}
            <div class="rounded-2xl
                        bg-white/80 dark:bg-slate-950/70
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        shadow-md shadow-slate-900/10
                        dark:shadow-lg dark:shadow-slate-900/30
                        overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Serie</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Lote</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Técnico asignado</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Estado</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Validado por</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($equipos as $equipo)
                            @php
                                $loteModelo = $equipo->loteModelo ?? null;
                                $lote = $loteModelo?->lote ?? null;
                                $ae = $equipo->asignacionEquipos?->first();
                                $tecnico = $ae?->asignacion?->tecnico;
                                $ultimaValidacion = $equipo->validacionesCalidad?->last();
                                $validador = $ultimaValidacion?->validadoPor;
                            @endphp

                            <tr class="
                                border-b border-slate-200 dark:border-slate-800/80
                                hover:bg-white/60 dark:hover:bg-slate-800/60
                                transition-colors">

                                {{-- Serie --}}
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    <span class="font-mono text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                        {{ $equipo->numero_serie }}
                                    </span>
                                </td>

                                {{-- Equipo (Marca + Modelo) --}}
                                <td class="px-4 py-3 align-top min-w-[220px]">
                                    <span class="text-sm sm:text-base font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $equipo->marca }} {{ $equipo->modelo }}
                                    </span>
                                </td>

                                {{-- Lote --}}
                                <td class="px-4 py-3 align-top">
                                    <span class="font-semibold text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                        {{ $lote?->nombre_lote ?? '—' }}
                                    </span>
                                </td>

                                {{-- Técnico asignado --}}
                                <td class="px-4 py-3 align-top">
                                    <span class="text-sm sm:text-base text-slate-900 dark:text-slate-100">
                                        {{ $tecnico?->nombre ?? 'Sin asignar' }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    @php
                                        $estado = $equipo->estatus_area ?? 'Sin estatus';
                                        $badge = match ($estado) {
                                            'EN_CALIDAD'      => 'bg-amber-100 text-amber-900 border-amber-300',
                                            'FINALIZADO'      => 'bg-green-200 text-green-900 border-green-400',
                                            'EN_PROCESO'      => 'bg-blue-100 text-blue-900 border-blue-300',
                                            default           => 'bg-slate-100 text-slate-900 border-slate-300',
                                        };
                                    @endphp

                                    <span class="inline-flex px-3 py-1 rounded-full text-xs sm:text-sm border font-semibold {{ $badge }}">
                                        {{ \Illuminate\Support\Str::replace('_', ' ', $estado) }}
                                    </span>
                                </td>

                                {{-- Validado por --}}
                                <td class="px-4 py-3 align-top">
                                    <span class="text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                        {{ $validador?->nombre ?? '—' }}
                                    </span>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-3 align-top text-right whitespace-nowrap">
                                    @if($equipo->estatus_area === 'EN_CALIDAD')
                                        <div class="flex justify-end gap-2">
                                            <button 
                                                wire:click="abrirValidar({{ $equipo->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                                       bg-emerald-600 hover:bg-emerald-500
                                                       text-xs font-semibold text-white
                                                       shadow-md shadow-emerald-500/30
                                                       transition"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Aprobar
                                            </button>
                                            <button 
                                                wire:click="abrirRechazar({{ $equipo->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                                       bg-red-600 hover:bg-red-500
                                                       text-xs font-semibold text-white
                                                       shadow-md shadow-red-500/30
                                                       transition"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Rechazar
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <p class="text-slate-500 dark:text-slate-400">No hay equipos para mostrar</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="px-6 py-4 border-t border-slate-200/60 dark:border-slate-800/80">
                    {{ $equipos->links() }}
                </div>
            </div>

        </div>

    </div>

    {{-- MODALES --}}

    {{-- Modal Aprobar --}}
    @if($modalValidar && $equipoSeleccionadoId)
        @php
            $equipo = \App\Models\Equipo::find($equipoSeleccionadoId);
        @endphp

        <div class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cerrarModales"></div>

            <div class="relative w-full max-w-2xl rounded-2xl
                        bg-white/95 dark:bg-slate-950/80
                        border border-slate-200/70 dark:border-white/15
                        shadow-2xl shadow-black/50 flex flex-col max-h-[92vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200/60 dark:border-white/10 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            ✅
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-50">
                                Aprobar equipo
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                {{ $equipo->marca ?? '' }} {{ $equipo->modelo ?? '' }} — {{ $equipo->numero_serie ?? '' }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModales"
                            class="flex-shrink-0 w-8 h-8 rounded-full border border-slate-300/60 dark:border-slate-600/60 flex items-center justify-center text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition text-lg leading-none">
                        ✕
                    </button>
                </div>

                {{-- Body --}
                <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                    {{-- ENSAMBLE Y ESTÉTICA --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">🔧 ENSAMBLE Y ESTÉTICA</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                                $ensambleItems = [
                                    'carcasa_ensamblada' => 'Carcasas bien ensambladas',
                                    'tornillos_completos' => 'Tornillos completos/visibles',
                                    'pantalla_colocada' => 'Pantalla correctamente colocada',
                                    'teclado_touchpad' => 'Teclado y touchpad firmes',
                                    'limpieza_general' => 'Limpieza general correcta',
                                    'estetica_coincide' => 'Estética coincide con clasificación',
                                ];
                            @endphp

                            @foreach($ensambleItems as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}"
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- FUNCIONAMIENTO BÁSICO --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">⚡ FUNCIONAMIENTO BÁSICO</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                                $funcItems = [
                                    'enciende' => 'Enciende correctamente',
                                    'bios_detecta' => 'BIOS detecta hardware',
                                    'windows_inicia' => 'Windows inicia correctamente',
                                    'temperatura_normal' => 'Temperatura normal en reposo',
                                    'audio_funcional' => 'Audio funcional',
                                    'wifi_funcional' => 'WiFi funcional',
                                    'usb_funcional' => 'USB funcional',
                                    'camara_mic' => 'Cámara/Micrófono funcional',
                                    'carga_bateria' => 'Carga batería correctamente',
                                ];
                            @endphp

                            @foreach($funcItems as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}"
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- BATERÍA (solo laptops) --}}
                    @php
                        $hasBattery = ($equipo->baterias && $equipo->baterias->count() > 0) || (strtolower($equipo->tipo_equipo ?? '') !== '' && strpos(strtolower($equipo->tipo_equipo), 'lap') !== false);
                    @endphp

                    @if($hasBattery)
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">🔋 BATERÍA</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $batteryItems = [
                                        'bateria_detectada' => 'Batería detectada',
                                        'bateria_cargando' => 'Cargando correctamente',
                                        'sin_inflado' => 'Sin inflado visible',
                                    ];
                                @endphp

                                @foreach($batteryItems as $key => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}"
                                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PRESENTACIÓN / VENTA --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">📦 PRESENTACIÓN / VENTA</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                                $presentacionItems = [
                                    'accesorios' => 'Accesorios correctos',
                                    'etiquetas' => 'Etiquetas correctas',
                                    'equipo_listo' => 'Equipo listo para inventario/venta',
                                ];
                            @endphp

                            @foreach($presentacionItems as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}"
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Calificación y notas --}}
                    <div class="grid grid-cols-1 gap-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Calificación general (opcional)</label>
                        <select wire:model="calificacion" class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70 border border-white/60 dark:border-slate-600/70 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                            <option value="">Sin calificar</option>
                            @foreach(\App\Models\ValidacionCalidad::calificaciones() as $k => $lab)
                                <option value="{{ $k }}">{{ $lab }}</option>
                            @endforeach
                        </select>

                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Notas (opcional)</label>
                        <textarea wire:model="notasValidacion" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700/80 text-slate-900 dark:text-slate-100 placeholder:text-slate-400"></textarea>

                        @if($error)
                            <p class="text-sm text-red-600">{{ $error }}</p>
                        @endif
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between px-6 py-4 border-t border-slate-200/60 dark:border-white/10 shrink-0 gap-4 bg-slate-50/50 dark:bg-slate-900/20">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed sm:order-first order-last">
                        Revisa el checklist completo. Si hay dudas, agrega notas antes de aprobar.
                    </p>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <button wire:click="cerrarModales"
                                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg border border-slate-300/70 dark:border-slate-700/80 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all duration-200">
                            Cancelar
                        </button>
                        <button wire:click="validarEquipo"
                                class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-bold shadow-lg shadow-emerald-500/25 dark:shadow-emerald-900/40 transition-all duration-200 hover:shadow-emerald-500/40">
                            Aprobar ✓
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Rechazar --}}
    @if($modalRechazar && $equipoSeleccionadoId)
        @php
            $equipo = \App\Models\Equipo::find($equipoSeleccionadoId);
        @endphp

        <div class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cerrarModales"></div>

            <div class="relative w-full max-w-2xl rounded-2xl
                        bg-white/95 dark:bg-slate-950/80
                        border border-slate-200/70 dark:border-white/15
                        shadow-2xl shadow-black/50 flex flex-col max-h-[92vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200/60 dark:border-white/10 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                            ❌
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-50">
                                Rechazar equipo
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                {{ $equipo->marca ?? '' }} {{ $equipo->modelo ?? '' }} — {{ $equipo->numero_serie ?? '' }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModales"
                            class="flex-shrink-0 w-8 h-8 rounded-full border border-slate-300/60 dark:border-slate-600/60 flex items-center justify-center text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition text-lg leading-none">
                        ✕
                    </button>
                </div>

                {{-- Body --}
                <div class="overflow-y-auto px-6 py-5 space-y-5">

                    {{-- Defectos --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">¿Qué defectos encontraste?</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                                $defectItems = [
                                    'bateria_danada' => 'Batería dañada',
                                    'pantalla_rota' => 'Pantalla rota',
                                    'carcasa_danada' => 'Carcasa dañada',
                                    'teclado_defectuoso' => 'Teclado defectuoso',
                                    'software_corrupto' => 'Software corrupto',
                                ];
                            @endphp

                            @foreach($defectItems as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="qSalioMal.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Qué salió bien (opcional) --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Qué salió bien (opcional)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                                $okItems = [
                                    'pantalla_ok' => 'Pantalla en buen estado',
                                    'bateria_ok' => 'Batería en buen estado',
                                    'carcasa_ok' => 'Carcasa en buen estado',
                                    'software_ok' => 'Software estable',
                                ];
                            @endphp

                            @foreach($okItems as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="qSalioBienRechazo.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Motivo --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Motivo del rechazo (obligatorio)</label>
                        <textarea wire:model="motivoRechazo" placeholder="Describe los problemas encontrados..." rows="4" class="w-full px-4 py-2.5 rounded-lg bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700/80 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/70"></textarea>

                        @if($errorRechazo)
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $errorRechazo }}</p>
                        @endif
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between px-6 py-4 border-t border-slate-200/60 dark:border-white/10 shrink-0 gap-4 bg-slate-50/50 dark:bg-slate-900/20">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed sm:order-first order-last">
                        El técnico recibirá estas observaciones para corregir el equipo.
                    </p>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <button wire:click="cerrarModales" class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg border border-slate-300/70 dark:border-slate-700/80 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all duration-200">Cancelar</button>

                        @if(!$motivoRechazo)
                            <button class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg bg-red-400 text-white text-sm font-bold opacity-50 cursor-not-allowed" title="Agrega un motivo para habilitar">Rechazar</button>
                        @else
                            <button wire:click="rechazarEquipo" class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 active:bg-red-700 text-white text-sm font-bold shadow-lg shadow-red-500/25 dark:shadow-red-900/40 transition-all duration-200 hover:shadow-red-500/40">Rechazar ✕</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-tb-background>
