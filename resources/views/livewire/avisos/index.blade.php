{{-- resources/views/livewire/avisos/index.blade.php --}}
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">
        <x-topbar
            title="Avisos del sistema"
            chip="Sistema · Avisos"
            description="Crea avisos manuales para mostrarlos en el carrusel del dashboard."
        />

        <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                    border border-slate-200/80 dark:border-white/10
                    backdrop-blur-xl shadow-md p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide
                             bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                             border border-amber-200/70 dark:border-amber-700/50">
                    {{ $puedeGestionar ? 'Lider / Gerente / CEO' : 'Solo lectura' }}
                </span>

                @if($puedeGestionar)
                    <button
                        type="button"
                        wire:click="openCreate"
                        wire:loading.attr="disabled"
                        wire:target="openCreate"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium
                            bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/30 transition-all disabled:opacity-60"
                    >
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                        Nuevo aviso
                    </button>
                @endif
            </div>

            @php
                $usoVigentes = $maxActivosVigentes > 0 ? min(100, (int) round(($activosVigentesCount / $maxActivosVigentes) * 100)) : 0;
                $slotsDisponibles = max(0, $maxActivosVigentes - $activosVigentesCount);
            @endphp
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-xl border border-blue-200/70 dark:border-blue-500/25 bg-gradient-to-br from-blue-50/80 to-sky-50/60 dark:from-blue-900/20 dark:to-sky-900/10 p-3">
                    <p class="text-[0.7rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Vigentes en dashboard</p>
                    <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        {{ $activosVigentesCount }} / {{ $maxActivosVigentes }}
                    </p>
                    <div class="mt-2 h-2 rounded-full bg-white/70 dark:bg-slate-900/70 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-400" style="width: {{ $usoVigentes }}%"></div>
                    </div>
                    <p class="text-[0.7rem] mt-1 text-slate-600 dark:text-slate-300">{{ $usoVigentes }}% ocupado</p>
                </div>
                <div class="rounded-xl border border-emerald-200/70 dark:border-emerald-500/25 bg-gradient-to-br from-emerald-50/80 to-teal-50/60 dark:from-emerald-900/20 dark:to-teal-900/10 p-3">
                    <p class="text-[0.7rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Publicados totales</p>
                    <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $activosPublicadosCount }}</p>
                    <p class="text-[0.7rem] mt-1 text-slate-600 dark:text-slate-300">
                        Espacios vigentes disponibles: {{ $slotsDisponibles }}
                    </p>
                    @if($casiLimite)
                        <p class="text-[0.7rem] mt-1 text-amber-700 dark:text-amber-300">
                            Estás cerca del límite de avisos vigentes.
                        </p>
                    @endif
                </div>
            </div>
        </div>

            {{-- Contenido --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Listado --}}
                <div class="lg:col-span-2 space-y-6">

                    <div class="bg-white/80 dark:bg-slate-950/60
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                rounded-2xl
                                shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30
                                p-5 lg:p-6">

                        <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-4">
                            <div class="flex gap-2 w-full sm:w-auto">
                                <input
                                    wire:model.live="search"
                                    type="text"
                                    placeholder="Buscar aviso..."
                                    class="w-full sm:w-72 text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-blue-500/60 focus:border-blue-500"
                                />
                            </div>

                            <div class="flex gap-2">
                                <select
                                    wire:model.live="filter"
                                    class="text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-8
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-blue-500/60 focus:border-blue-500"
                                >
                                    <option value="activos">Publicados</option>
                                    <option value="inactivos">No publicados</option>
                                    <option value="todos">Todos</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($avisos as $a)
                                @php
                                    $colorBadgeClass = match($a->color) {
                                        'amber' => 'bg-amber-100 text-amber-700 border-amber-200/70 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700/50',
                                        'blue' => 'bg-blue-100 text-blue-700 border-blue-200/70 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700/50',
                                        'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200/70 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700/50',
                                        'rose' => 'bg-rose-100 text-rose-700 border-rose-200/70 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700/50',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200/70 dark:bg-slate-800/60 dark:text-slate-200 dark:border-slate-700/70',
                                    };

                                    $now = now();
                                    $estadoTemporal = (!$a->starts_at || $a->starts_at <= $now) && (!$a->ends_at || $a->ends_at >= $now)
                                        ? ['label' => 'Vigente', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200/70 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700/50']
                                        : (($a->starts_at && $a->starts_at > $now)
                                            ? ['label' => 'Programado', 'class' => 'bg-blue-100 text-blue-700 border-blue-200/70 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700/50']
                                            : ['label' => 'Expirado', 'class' => 'bg-rose-100 text-rose-700 border-rose-200/70 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700/50']);

                                    $accentBarClass = match($a->color) {
                                        'amber' => 'from-amber-500/85 to-yellow-400/80',
                                        'blue' => 'from-blue-500/85 to-cyan-400/80',
                                        'emerald' => 'from-emerald-500/85 to-teal-400/80',
                                        'rose' => 'from-rose-500/85 to-pink-400/80',
                                        default => 'from-slate-500/85 to-slate-400/80',
                                    };
                                    $iconWrapClass = match($a->color) {
                                        'amber' => 'bg-amber-100 dark:bg-amber-900/30 border-amber-200/70 dark:border-amber-700/50',
                                        'blue' => 'bg-blue-100 dark:bg-blue-900/30 border-blue-200/70 dark:border-blue-700/50',
                                        'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30 border-emerald-200/70 dark:border-emerald-700/50',
                                        'rose' => 'bg-rose-100 dark:bg-rose-900/30 border-rose-200/70 dark:border-rose-700/50',
                                        default => 'bg-slate-900/5 dark:bg-white/5 border-slate-200/60 dark:border-white/10',
                                    };
                                @endphp

                                <div class="relative overflow-hidden rounded-2xl
                                            bg-gradient-to-r from-white/80 to-white/55 dark:from-slate-950/65 dark:to-slate-950/45
                                            border border-slate-200/80 dark:border-white/10
                                            backdrop-blur-xl
                                            p-4">
                                    <div class="absolute left-0 top-0 h-full w-1.5 rounded-l-2xl bg-gradient-to-b {{ $accentBarClass }}"></div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center
                                                        border {{ $iconWrapClass }}">
                                                <span class="text-lg">{{ $a->icono ?? '📌' }}</span>
                                            </div>

                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="text-[0.95rem] font-semibold text-slate-900 dark:text-slate-50">
                                                        {{ $a->titulo }}
                                                    </p>

                                                    @if($a->pinned)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                    text-[0.65rem] font-semibold tracking-wide
                                                                    bg-amber-100 text-amber-700 shadow-sm
                                                                    border border-amber-200/70 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700/50">
                                                            📌 FIJADO
                                                        </span>
                                                    @endif

                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                text-[0.65rem] font-semibold tracking-wide
                                                                bg-slate-900/5 dark:bg-white/5
                                                                text-slate-700 dark:text-slate-200
                                                                border border-slate-200/60 dark:border-white/10">
                                                        {{ $a->tag }}
                                                    </span>

                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                text-[0.65rem] font-semibold tracking-wide
                                                                border {{ $colorBadgeClass }}">
                                                        {{ $a->color }}
                                                    </span>

                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                text-[0.65rem] font-semibold tracking-wide
                                                                border {{ $estadoTemporal['class'] }}">
                                                        {{ $estadoTemporal['label'] }}
                                                    </span>
                                                </div>

                                                <p class="text-[0.8rem] text-slate-700 dark:text-slate-300 leading-relaxed">
                                                    {{ $a->texto }}
                                                </p>

                                                <div class="text-[0.7rem] text-slate-500 dark:text-slate-400 pt-1">
                                                    @if($a->starts_at)
                                                        <span>Desde: {{ $a->starts_at->format('d/m/Y H:i') }}</span>
                                                    @endif
                                                    @if($a->ends_at)
                                                        <span class="ml-2">Hasta: {{ $a->ends_at->format('d/m/Y H:i') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($puedeGestionar)
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    x-on:click.prevent="if (confirm('¿Deseas cambiar el estado de fijado de este aviso?')) { $wire.togglePinned({{ $a->id }}) }"
                                                    wire:loading.attr="disabled"
                                                    wire:target="togglePinned({{ $a->id }})"
                                                    aria-label="Fijar o desfijar aviso"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                        border border-slate-200/70 dark:border-white/10
                                                        bg-white/60 dark:bg-slate-950/60
                                                        text-slate-700 dark:text-slate-200
                                                        transition-all duration-200 disabled:opacity-60
                                                        hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                    title="Fijar / Desfijar"
                                                >
                                                    📌
                                                </button>

                                                <button
                                                    type="button"
                                                    x-on:click.prevent="if (confirm('¿Deseas cambiar el estado de publicación de este aviso?')) { $wire.toggleActive({{ $a->id }}) }"
                                                    wire:loading.attr="disabled"
                                                    wire:target="toggleActive({{ $a->id }})"
                                                    aria-label="Activar o desactivar aviso"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                        border border-slate-200/70 dark:border-white/10
                                                        bg-white/60 dark:bg-slate-950/60
                                                        text-slate-700 dark:text-slate-200
                                                        transition-all duration-200 disabled:opacity-60
                                                        hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                    title="Activar / Desactivar"
                                                >
                                                    {{ $a->is_active ? '✅' : '⛔' }}
                                                </button>

                                                <button
                                                    type="button"
                                                    wire:click="openEdit({{ $a->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openEdit({{ $a->id }})"
                                                    aria-label="Editar aviso"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                        border border-slate-200/70 dark:border-white/10
                                                        bg-white/60 dark:bg-slate-950/60
                                                        text-slate-700 dark:text-slate-200
                                                        transition-all duration-200 disabled:opacity-60
                                                        hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                    title="Editar"
                                                >
                                                    ✏️
                                                </button>

                                                <button
                                                    type="button"
                                                    x-on:click.prevent="if (confirm('¿Seguro que deseas eliminar este aviso? Esta acción no se puede deshacer.')) { $wire.delete({{ $a->id }}) }"
                                                    wire:loading.attr="disabled"
                                                    wire:target="delete({{ $a->id }})"
                                                    aria-label="Eliminar aviso"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                        border border-rose-200/50 dark:border-rose-400/15
                                                        bg-white/60 dark:bg-slate-950/60
                                                        text-rose-600 dark:text-rose-300
                                                        transition-all duration-200 disabled:opacity-60
                                                        hover:-translate-y-0.5 hover:shadow-md hover:shadow-rose-500/10"
                                                    title="Eliminar"
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-600 dark:text-slate-300">
                                    @if($search !== '')
                                        No hay avisos que coincidan con "{{ $search }}".
                                    @elseif($filter === 'activos')
                                        No hay avisos activos.
                                    @elseif($filter === 'inactivos')
                                        No hay avisos inactivos.
                                    @else
                                        No hay avisos registrados.
                                    @endif
                                </div>
                            @endforelse
                        </div>

                        <div class="pt-4">
                            {{ $avisos->links() }}
                        </div>
                    </div>
                </div>

                {{-- Ayuda rápida --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white/80 dark:bg-slate-950/60
                                border border-slate-200/80 dark:border-white/10
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                rounded-2xl
                                shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30
                                p-5 lg:p-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-2">
                            Reglas de visibilidad
                        </h3>

                        <ul class="text-sm text-slate-700 dark:text-slate-300 space-y-2">
                            <li>• Solo se muestran en el Dashboard los avisos <span class="font-semibold">activos y vigentes</span>.</li>
                            <li>• <span class="font-semibold">Desde/Hasta</span> controlan cuándo aparecen.</li>
                            <li>• Los <span class="font-semibold">fijados</span> salen primero en el carrusel.</li>
                            <li>• Límite actual: <span class="font-semibold">{{ $maxActivosVigentes }}</span> avisos vigentes.</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

        {{-- Modal --}}
        @if($modalOpen)
            <div class="fixed inset-0 z-[200] flex items-center justify-center px-4" x-on:keydown.escape.window="$wire.closeModal()">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>

                <div class="relative w-full max-w-2xl rounded-3xl
                            bg-white/90 dark:bg-slate-950/80
                            border border-slate-200/80 dark:border-white/10
                            shadow-2xl shadow-slate-950/60
                            backdrop-blur-2xl
                            overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-slate-200/70 dark:border-white/10">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                {{ $editingId ? 'Editar aviso' : 'Nuevo aviso' }}
                            </h3>

                            <button
                                type="button"
                                wire:click="closeModal"
                                aria-label="Cerrar modal de aviso"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                    border border-slate-200/70 dark:border-white/10
                                    bg-white/60 dark:bg-slate-950/60
                                    text-slate-700 dark:text-slate-200
                                    transition-all duration-200
                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    @php
                        $tituloLen = mb_strlen((string) $titulo);
                        $textoLen = mb_strlen((string) $texto);
                        $tituloRecomendado = 60;
                        $textoRecomendado = 280;
                        $previewIcon = trim((string) $icono) !== '' ? $icono : '📌';
                        $previewTag = trim((string) $tag) !== '' ? $tag : 'INFO';
                        $previewColor = match(trim((string) $previewTag)) {
                            'IMPORTANTE' => 'rose',
                            'TIP' => 'blue',
                            'META' => 'emerald',
                            default => 'slate',
                        };
                        $previewTitulo = trim((string) $titulo) !== '' ? $titulo : 'Título del aviso';
                        $previewTexto = trim((string) $texto) !== '' ? $texto : 'Aquí verás una vista previa rápida del aviso tal como se percibe en dashboard.';
                        $previewBadgeClass = match($previewColor) {
                            'amber' => 'bg-amber-100 text-amber-700 border-amber-200/70 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700/50',
                            'blue' => 'bg-blue-100 text-blue-700 border-blue-200/70 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700/50',
                            'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200/70 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700/50',
                            'rose' => 'bg-rose-100 text-rose-700 border-rose-200/70 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700/50',
                            default => 'bg-slate-100 text-slate-700 border-slate-200/70 dark:bg-slate-800/60 dark:text-slate-200 dark:border-slate-700/70',
                        };
                        $previewBorderClass = match($previewColor) {
                            'amber' => 'border-amber-200/80 dark:border-amber-500/25',
                            'blue' => 'border-blue-200/80 dark:border-blue-500/25',
                            'emerald' => 'border-emerald-200/80 dark:border-emerald-500/25',
                            'rose' => 'border-rose-200/80 dark:border-rose-500/25',
                            default => 'border-slate-200/80 dark:border-white/10',
                        };
                    @endphp
                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl border border-indigo-200/70 dark:border-indigo-500/25 bg-indigo-50/70 dark:bg-indigo-900/15 p-3">
                                <p class="text-[0.65rem] uppercase tracking-wide font-semibold text-indigo-700 dark:text-indigo-300">Título recomendado</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $tituloLen }} / {{ $tituloRecomendado }}</p>
                            </div>
                            <div class="rounded-xl border border-cyan-200/70 dark:border-cyan-500/25 bg-cyan-50/70 dark:bg-cyan-900/15 p-3">
                                <p class="text-[0.65rem] uppercase tracking-wide font-semibold text-cyan-700 dark:text-cyan-300">Texto recomendado</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $textoLen }} / {{ $textoRecomendado }}</p>
                            </div>
                            <div class="rounded-xl border border-emerald-200/70 dark:border-emerald-500/25 bg-emerald-50/70 dark:bg-emerald-900/15 p-3">
                                <p class="text-[0.65rem] uppercase tracking-wide font-semibold text-emerald-700 dark:text-emerald-300">Estado actual</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $is_active ? 'Publicado' : 'Borrador' }} · {{ $pinned ? 'Fijado' : 'Normal' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Título
                                </label>
                                <input
                                    wire:model.live.debounce.250ms="titulo"
                                    type="text"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-blue-500/60 focus:border-blue-500"
                                />
                                <div class="text-[0.7rem] text-slate-500 dark:text-slate-400 text-right">
                                    {{ mb_strlen((string) $titulo) }}/120
                                </div>
                                @if($tituloLen > $tituloRecomendado)
                                    <div class="text-[0.7rem] text-amber-700 dark:text-amber-300">
                                        Recomendación: usa un título breve para mejorar lectura en carrusel.
                                    </div>
                                @endif
                                @error('titulo') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Icono (emoji)
                                </label>
                                <input
                                    wire:model.live.debounce.250ms="icono"
                                    type="text"
                                    placeholder="💡"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-blue-500/60 focus:border-blue-500"
                                />
                                @error('icono') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Tipo de aviso (Tag)
                                </label>
                                <select
                                    wire:model.live="tag"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-8
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-blue-500/60 focus:border-blue-500"
                                >
                                    <option value="INFO">INFO · General</option>
                                    <option value="IMPORTANTE">IMPORTANTE · Requiere atención</option>
                                    <option value="TIP">TIP · Recomendación rápida</option>
                                    <option value="META">META · Objetivo del equipo</option>
                                </select>
                                @error('tag') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Color
                                </label>
                                <div class="w-full rounded-xl border border-slate-300/80 dark:border-white/15 bg-white/80 dark:bg-slate-950/80 px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-4 h-4 rounded-full
                                            {{ $color === 'rose' ? 'bg-rose-500' : '' }}
                                            {{ $color === 'blue' ? 'bg-blue-500' : '' }}
                                            {{ $color === 'emerald' ? 'bg-emerald-500' : '' }}
                                            {{ $color === 'amber' ? 'bg-amber-400' : '' }}
                                            {{ $color === 'slate' ? 'bg-slate-500' : '' }}"></span>
                                        <span class="text-sm text-slate-800 dark:text-slate-100">{{ $color ?: 'slate' }}</span>
                                    </div>
                                </div>
                                @error('color') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Texto
                            </label>
                            <textarea
                                wire:model.live.debounce.300ms="texto"
                                rows="4"
                                class="w-full text-sm rounded-xl
                                    border border-slate-300/80 dark:border-white/15
                                    bg-white/80 text-slate-800
                                    dark:bg-slate-950/80 dark:text-slate-100
                                    py-2 pl-3 pr-3
                                    shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                    focus:outline-none focus:ring-2
                                    focus:ring-blue-500/60 focus:border-blue-500"
                            ></textarea>
                                <div class="text-[0.7rem] text-slate-500 dark:text-slate-400 text-right">
                                    {{ mb_strlen((string) $texto) }}/2000
                                </div>
                                @if($textoLen > $textoRecomendado)
                                    <div class="text-[0.7rem] text-amber-700 dark:text-amber-300">
                                        Consejo UX: intenta mantenerlo corto (ideal ≤ {{ $textoRecomendado }} caracteres).
                                    </div>
                                @endif
                                @error('texto') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                        <div class="rounded-2xl border {{ $previewBorderClass }} bg-gradient-to-r from-white/80 to-white/60 dark:from-slate-900/45 dark:to-slate-900/30 p-4">
                            <p class="text-[0.68rem] uppercase tracking-wide font-semibold text-slate-500 dark:text-slate-400 mb-2">
                                Vista previa rápida
                            </p>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center border border-slate-200/70 dark:border-white/10 bg-white/75 dark:bg-slate-900/65">
                                        <span class="text-lg">{{ $previewIcon }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $previewTitulo }}</p>
                                        <p class="text-xs leading-relaxed text-slate-700 dark:text-slate-300">{{ $previewTexto }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-semibold tracking-wide border {{ $previewBadgeClass }}">
                                    {{ $previewTag }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Mostrar desde (opcional)
                                </label>
                                <input
                                    wire:model.live="starts_at"
                                    type="datetime-local"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                    focus:ring-blue-500/60 focus:border-blue-500"
                                />
                                <div class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                                    Déjalo vacío para mostrarlo inmediatamente.
                                </div>
                                @error('starts_at') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Mostrar hasta (opcional)
                                </label>
                                <input
                                    wire:model.live="ends_at"
                                    type="datetime-local"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                    focus:ring-blue-500/60 focus:border-blue-500"
                                />
                                <div class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                                    Úsalo para que el aviso desaparezca automáticamente.
                                </div>
                                @error('ends_at') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" wire:model.live="is_active" class="rounded border-slate-300 dark:border-white/20">
                                    Publicado
                                </label>

                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" wire:model.live="pinned" class="rounded border-slate-300 dark:border-white/20">
                                    Fijado
                                </label>
                            </div>

                            <div class="flex items-center gap-2 justify-end">
                                <button
                                    type="button"
                                    wire:click="closeModal"
                                    wire:loading.attr="disabled"
                                    wire:target="closeModal,save"
                                    class="inline-flex items-center gap-2
                                        px-3.5 py-2 rounded-xl text-xs font-medium
                                        bg-white/70 dark:bg-slate-950/60
                                        border border-slate-200/80 dark:border-white/10
                                        text-slate-800 dark:text-slate-100
                                        transition-all duration-200 disabled:opacity-60
                                        hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="button"
                                    wire:click="save"
                                    wire:loading.attr="disabled"
                                    wire:target="save"
                                    class="inline-flex items-center gap-2
                                        px-3.5 py-2 rounded-xl text-xs font-medium
                                        bg-blue-600 hover:bg-blue-700 text-white
                                        shadow-md shadow-blue-600/30 transition-all disabled:opacity-60"
                                >
                                    Guardar
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    </div>
</x-tb-background>
