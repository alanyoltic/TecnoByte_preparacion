{{-- resources/views/livewire/avisos/index.blade.php --}}
<x-tb-background>
    <div class="relative min-h-screen overflow-hidden
                bg-gradient-to-br
                from-slate-100 via-slate-100 to-slate-200
                dark:from-slate-950 dark:via-[#020617] dark:to-slate-950">



        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

            {{-- Header --}}
            <div class="relative overflow-hidden mb-6 rounded-3xl
                        bg-white/80 dark:bg-slate-950/70
                        border border-slate-200/80 dark:border-white/10
                        shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
                        backdrop-blur-xl dark:backdrop-blur-2xl
                        px-6 sm:px-8 lg:px-10 py-4 sm:py-5">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">

                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Avisos del sistema
                            </h2>

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                        text-[0.7rem] font-semibold tracking-wide
                                        bg-[#FF9521]/10 text-[#FF9521]
                                        border border-[#FF9521]/40">
                                Solo Admin / CEO
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                            Crea avisos manuales para mostrarlos en el carrusel del Dashboard.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 sm:gap-3">
                        <button
                            type="button"
                            wire:click="openCreate"
                            class="inline-flex items-center gap-2
                                px-3.5 py-1.5 rounded-full text-xs font-medium
                                bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                text-white
                                shadow-lg shadow-blue-800/60
                                backdrop-blur-xl
                                transition-all duration-200
                                hover:shadow-blue-500/80 hover:-translate-y-0.5"
                        >
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                            Nuevo aviso
                        </button>
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
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
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
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                >
                                    <option value="activos">Activos</option>
                                    <option value="inactivos">Inactivos</option>
                                    <option value="todos">Todos</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($avisos as $a)
                                <div class="relative overflow-hidden rounded-2xl
                                            bg-white/70 dark:bg-slate-950/55
                                            border border-slate-200/70 dark:border-white/10
                                            backdrop-blur-xl
                                            p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center
                                                        bg-slate-900/5 dark:bg-white/5
                                                        border border-slate-200/60 dark:border-white/10">
                                                <span class="text-lg">{{ $a->icono ?? '📌' }}</span>
                                            </div>

                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                                        {{ $a->titulo }}
                                                    </p>

                                                    @if($a->pinned)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                    text-[0.65rem] font-semibold tracking-wide
                                                                    bg-[#FF9521]/10 text-[#FF9521]
                                                                    border border-[#FF9521]/30">
                                                            FIJADO
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
                                                                bg-slate-900/5 dark:bg-white/5
                                                                text-slate-700 dark:text-slate-200
                                                                border border-slate-200/60 dark:border-white/10">
                                                        {{ $a->color }}
                                                    </span>
                                                </div>

                                                <p class="text-xs text-slate-600 dark:text-slate-300">
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

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                wire:click="togglePinned({{ $a->id }})"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                title="Fijar / Desfijar"
                                            >
                                                📌
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="toggleActive({{ $a->id }})"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                title="Activar / Desactivar"
                                            >
                                                {{ $a->is_active ? '✅' : '⛔' }}
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="openEdit({{ $a->id }})"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                title="Editar"
                                            >
                                                ✏️
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="delete({{ $a->id }})"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                    border border-rose-200/50 dark:border-rose-400/15
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-rose-600 dark:text-rose-300
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-rose-500/10"
                                                title="Eliminar"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-600 dark:text-slate-300">
                                    No hay avisos.
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
                            <li>• Solo se muestran en el Dashboard los avisos <span class="font-semibold">activos</span>.</li>
                            <li>• <span class="font-semibold">Desde/Hasta</span> controlan cuándo aparecen.</li>
                            <li>• Los <span class="font-semibold">fijados</span> salen primero en el carrusel.</li>
                            <li>• Recomendado: máximo <span class="font-semibold">6 a 10</span> avisos activos.</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

        {{-- Modal --}}
        @if($modalOpen)
            <div class="fixed inset-0 z-[200] flex items-center justify-center px-4">
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

                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Título
                                </label>
                                <input
                                    wire:model.defer="titulo"
                                    type="text"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                />
                                @error('titulo') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Icono (emoji)
                                </label>
                                <input
                                    wire:model.defer="icono"
                                    type="text"
                                    placeholder="💡"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                />
                                @error('icono') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Tag
                                </label>
                                <select
                                    wire:model.defer="tag"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-8
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                >
                                    <option value="INFO">INFO</option>
                                    <option value="IMPORTANTE">IMPORTANTE</option>
                                    <option value="TIP">TIP</option>
                                    <option value="META">META</option>
                                </select>
                                @error('tag') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Color
                                </label>
                                <select
                                    wire:model.defer="color"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-8
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                >
                                    <option value="slate">slate</option>
                                    <option value="amber">amber</option>
                                    <option value="blue">blue</option>
                                    <option value="emerald">emerald</option>
                                    <option value="rose">rose</option>
                                </select>
                                @error('color') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Texto
                            </label>
                            <textarea
                                wire:model.defer="texto"
                                rows="4"
                                class="w-full text-sm rounded-xl
                                    border border-slate-300/80 dark:border-white/15
                                    bg-white/80 text-slate-800
                                    dark:bg-slate-950/80 dark:text-slate-100
                                    py-2 pl-3 pr-3
                                    shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                    focus:outline-none focus:ring-2
                                    focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                            ></textarea>
                            @error('texto') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Mostrar desde (opcional)
                                </label>
                                <input
                                    wire:model.defer="starts_at"
                                    type="datetime-local"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                />
                                @error('starts_at') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Mostrar hasta (opcional)
                                </label>
                                <input
                                    wire:model.defer="ends_at"
                                    type="datetime-local"
                                    class="w-full text-sm rounded-xl
                                        border border-slate-300/80 dark:border-white/15
                                        bg-white/80 text-slate-800
                                        dark:bg-slate-950/80 dark:text-slate-100
                                        py-2 pl-3 pr-3
                                        shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                        focus:outline-none focus:ring-2
                                        focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                                />
                                @error('ends_at') <div class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" wire:model.defer="is_active" class="rounded border-slate-300 dark:border-white/20">
                                    Publicado
                                </label>

                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" wire:model.defer="pinned" class="rounded border-slate-300 dark:border-white/20">
                                    Fijado
                                </label>
                            </div>

                            <div class="flex items-center gap-2 justify-end">
                                <button
                                    type="button"
                                    wire:click="closeModal"
                                    class="inline-flex items-center gap-2
                                        px-3.5 py-2 rounded-full text-xs font-medium
                                        bg-white/70 dark:bg-slate-950/60
                                        border border-slate-200/80 dark:border-white/10
                                        text-slate-800 dark:text-slate-100
                                        transition-all duration-200
                                        hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="button"
                                    wire:click="save"
                                    class="inline-flex items-center gap-2
                                        px-3.5 py-2 rounded-full text-xs font-medium
                                        bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                        text-white
                                        shadow-lg shadow-blue-800/60
                                        backdrop-blur-xl
                                        transition-all duration-200
                                        hover:shadow-blue-500/80 hover:-translate-y-0.5"
                                >
                                    Guardar
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endif
</x-tb-background>

