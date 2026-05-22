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

        <div class="fixed inset-0 z-[999] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="resetFormValidacion"></div>

            <div class="relative w-full max-w-2xl rounded-2xl
                        bg-white/90 dark:bg-slate-950/70
                        border border-slate-200/80 dark:border-white/10
                        shadow-2xl shadow-black/40 flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200/60 dark:border-white/10 shrink-0">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                            Aprobar Equipo
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $equipo->marca ?? '' }} {{ $equipo->modelo ?? '' }} — {{ $equipo->numero_serie ?? '' }}
                        </p>
                    </div>
                    <button wire:click="resetFormValidacion"
                            class="w-8 h-8 rounded-full border border-slate-300/60 dark:border-white/10
                                   flex items-center justify-center text-slate-500 hover:text-red-400 transition text-sm">
                        ✕
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto px-6 py-5 space-y-5">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                            ¿Qué salió bien?
                        </label>
                        <div class="space-y-2">
                            @foreach(['Pantalla perfecta', 'Batería excelente', 'Sin defectos visibles', 'Software actualizado', 'Periféricos completos'] as $item)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="checklistBien.{{ $loop->index }}"
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $item }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200/60 dark:border-white/10 shrink-0 gap-3">
                    <button wire:click="resetFormValidacion"
                            class="px-4 py-2 rounded-xl border border-slate-300/70 dark:border-slate-700/80
                                   text-sm font-semibold text-slate-700 dark:text-slate-200
                                   hover:bg-slate-100 dark:hover:bg-slate-800/50 transition">
                        Cancelar
                    </button>
                    <button wire:click="validarEquipo({{ $equipoSeleccionadoId }})"
                            class="px-4 py-2 rounded-xl
                                   bg-emerald-600 hover:bg-emerald-500 text-white
                                   text-sm font-semibold
                                   shadow-md shadow-emerald-500/30 transition">
                        Aprobar equipo
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Rechazar --}}
    @if($modalRechazar && $equipoSeleccionadoId)
        @php
            $equipo = \App\Models\Equipo::find($equipoSeleccionadoId);
        @endphp

        <div class="fixed inset-0 z-[999] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="resetFormRechazo"></div>

            <div class="relative w-full max-w-2xl rounded-2xl
                        bg-white/90 dark:bg-slate-950/70
                        border border-slate-200/80 dark:border-white/10
                        shadow-2xl shadow-black/40 flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200/60 dark:border-white/10 shrink-0">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                            Rechazar Equipo
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $equipo->marca ?? '' }} {{ $equipo->modelo ?? '' }} — {{ $equipo->numero_serie ?? '' }}
                        </p>
                    </div>
                    <button wire:click="resetFormRechazo"
                            class="w-8 h-8 rounded-full border border-slate-300/60 dark:border-white/10
                                   flex items-center justify-center text-slate-500 hover:text-red-400 transition text-sm">
                        ✕
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto px-6 py-5 space-y-5">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                            ¿Qué defectos encontraste?
                        </label>
                        <div class="space-y-2">
                            @foreach(['Batería dañada', 'Pantalla rota', 'Carcasa dañada', 'Teclado defectuoso', 'Software corrupto'] as $item)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="checklistMal.{{ $loop->index }}"
                                           class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $item }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                            Notas adicionales
                        </label>
                        <textarea wire:model="motivoRechazo"
                                  placeholder="Describe los problemas encontrados..."
                                  rows="4"
                                  class="w-full px-4 py-2.5 rounded-xl
                                         bg-white/80 dark:bg-slate-900/60
                                         border border-slate-300/70 dark:border-slate-700/80
                                         text-slate-900 dark:text-slate-100
                                         placeholder:text-slate-400 dark:placeholder:text-slate-500
                                         focus:outline-none focus:ring-2 focus:ring-red-500/70"></textarea>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200/60 dark:border-white/10 shrink-0 gap-3">
                    <button wire:click="resetFormRechazo"
                            class="px-4 py-2 rounded-xl border border-slate-300/70 dark:border-slate-700/80
                                   text-sm font-semibold text-slate-700 dark:text-slate-200
                                   hover:bg-slate-100 dark:hover:bg-slate-800/50 transition">
                        Cancelar
                    </button>
                    <button wire:click="rechazarEquipo({{ $equipoSeleccionadoId }})"
                            class="px-4 py-2 rounded-xl
                                   bg-red-600 hover:bg-red-500 text-white
                                   text-sm font-semibold
                                   shadow-md shadow-red-500/30 transition">
                        Rechazar equipo
                    </button>
                </div>
            </div>
        </div>
    @endif

</x-tb-background>
