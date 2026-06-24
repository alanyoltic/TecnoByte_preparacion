<div>
    {{-- Botón original de dar de baja --}}
    <button
        type="button"
        wire:click="intentarBaja"
        wire:confirm="¿Seguro que deseas dar de baja este usuario? Su acceso será bloqueado."
        wire:loading.attr="disabled"
        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
            text-red-400 bg-red-500/10 border border-red-400/40
            hover:bg-red-500/80 hover:text-white transition-all duration-200"
    >
        <span wire:loading.remove wire:target="intentarBaja">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
            Dar de baja
        </span>
        <span wire:loading wire:target="intentarBaja">
            Procesando...
        </span>
    </button>

    {{-- MODAL ASISTENTE DE BAJA --}}
    @if($modalAbierto)
        <div class="fixed inset-0 z-[100] flex items-center justify-center"
             x-data
             @keydown.escape.window="$wire.cerrarModal()">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                 wire:click="cerrarModal"></div>

            {{-- Contenedor --}}
            <div class="relative w-[92%] max-w-lg rounded-2xl border border-white/10
                        bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                        shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-5 text-left">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-lg font-bold text-rose-600 dark:text-rose-500 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Asistente de Baja
                        </h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            El usuario <strong>{{ $usuario->nombre }}</strong> tiene equipos asignados.
                            Reasigna su carga de trabajo antes de desactivarlo.
                        </p>
                    </div>
                    <button wire:click="cerrarModal"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20
                               border border-white/10 text-slate-500 dark:text-slate-300
                               flex items-center justify-center transition">✕</button>
                </div>

                {{-- Resumen de equipos --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-blue-200 dark:border-blue-800/60
                                bg-blue-50/50 dark:bg-blue-900/20 px-4 py-3">
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold mb-1 uppercase tracking-wide">Equipos Vírgenes</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $equiposSinEmpezar }}</p>
                        <p class="text-[0.65rem] text-slate-500 dark:text-slate-400 mt-1 leading-tight">
                            Se regresarán automáticamente a inventario al confirmar la baja.
                        </p>
                    </div>

                    <div class="rounded-xl border border-amber-200 dark:border-amber-800/60
                                bg-amber-50/50 dark:bg-amber-900/20 px-4 py-3">
                        <p class="text-xs text-amber-600 dark:text-amber-500 font-semibold mb-1 uppercase tracking-wide">Equipos a Medias</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $equiposAMedias }}</p>
                        <p class="text-[0.65rem] text-slate-500 dark:text-slate-400 mt-1 leading-tight">
                            En proceso o esperando pieza. Debes transferirlos a alguien más.
                        </p>
                    </div>
                </div>

                {{-- Select de Técnico Heredero --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Transferir equipos a medias a: <span class="text-rose-500">*</span>
                    </label>
                    <select
                        wire:model="tecnicoReasignarId"
                        class="w-full rounded-xl px-4 py-2.5 text-sm
                               bg-white/70 dark:bg-slate-900/40
                               border border-slate-300/80 dark:border-slate-700
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50
                               outline-none appearance-none">
                        <option value="">-- Selecciona un técnico --</option>
                        @foreach($tecnicosDisponibles as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->nombre }} {{ $tecnico->apellido_paterno }} ({{ $tecnico->role?->nombre }})</option>
                        @endforeach
                    </select>
                    @error('tecnicoReasignarId')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                    <button wire:click="cerrarModal"
                        class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                               border border-slate-300/80 dark:border-slate-700
                               bg-white/60 dark:bg-slate-900/40
                               text-slate-600 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar baja
                    </button>
                    <button wire:click="confirmarBaja"
                        wire:loading.attr="disabled"
                        wire:target="confirmarBaja"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl px-5 py-2.5 text-sm font-semibold
                               bg-rose-600 hover:bg-rose-500
                               text-white shadow-md shadow-rose-800/40
                               hover:shadow-rose-500/60 hover:-translate-y-0.5
                               disabled:opacity-60 transition-all duration-200">
                        <span wire:loading.remove wire:target="confirmarBaja">Confirmar baja y reasignar</span>
                        <span wire:loading wire:target="confirmarBaja">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
