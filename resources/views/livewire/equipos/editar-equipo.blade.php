<div
    class="bg-white/80 dark:bg-slate-950/60
           border border-slate-200/80 dark:border-white/10
           backdrop-blur-xl dark:backdrop-blur-2xl
           rounded-2xl
           shadow-md shadow-slate-900/10
           dark:shadow-lg dark:shadow-slate-900/30
           px-4 py-5 sm:px-6 sm:py-6
           transition-all duration-300 ease-out
           hover:-translate-y-1
           hover:shadow-lg hover:shadow-indigo-500/20
           dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
    wire:key="editar-equipo-{{ $equipo_id ?? 'na' }}"
>
    {{-- Título principal --}}
    <div class="mb-5">
        <h3 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-50">
            Editar equipo
        </h3>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Actualiza los datos del equipo. Los campos marcados con * son obligatorios.
        </p>
    </div>

    {{-- Mensajes --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded-xl border border-emerald-200/60 dark:border-emerald-400/20
                    bg-emerald-50/70 dark:bg-emerald-900/20
                    px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-xl border border-rose-200/60 dark:border-rose-400/20
                    bg-rose-50/70 dark:bg-rose-900/20
                    px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="solicitarGuardar" class="space-y-8 text-slate-900 dark:text-slate-100">

        @include('livewire.equipos._form', [
            'mode'                   => 'edit',
            'form'                   => $form,
            'proveedores'            => $proveedores ?? [],
            'lotes'                  => $lotes ?? [],
            'modelosLote'            => $modelosLote ?? [],
            'lotesTerminadosIds'     => $lotesTerminadosIds ?? [],
            'monitorEntradasOptions' => $monitorEntradasOptions ?? [],
        ])

        {{-- BOTÓN FINAL --}}
        <div class="flex items-center justify-end pt-2">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-medium
                    bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                    text-white
                    shadow-md shadow-blue-800/60 hover:shadow-lg hover:shadow-blue-500/80
                    backdrop-blur-md
                    transition-all duration-200
                    hover:-translate-y-0.5"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar equipo</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

    </form>

    {{-- ================================================ --}}
    {{--  MODAL — x-teleport + x-data propio              --}}
    {{--  El modal tiene su propio estado independiente   --}}
    {{--  y escucha el evento de window para abrirse      --}}
    {{-- ================================================ --}}
    <template x-teleport="body">
        <div
            x-data="{
                abierto: false,
                motivo: '',
                motivoError: false,
                cambiosCriticos: [],
                cambiosGenerales: [],
                accion: '',

                cerrar() {
                    this.abierto     = false;
                    this.motivo      = '';
                    this.motivoError = false;
                },

                confirmar() {
                    if (!this.motivo.trim()) {
                        this.motivoError = true;
                        return;
                    }
                    this.motivoError = false;
                    window.dispatchEvent(new CustomEvent('modal-confirmacion-aceptada', {
                        detail: { motivo: this.motivo }
                    }));
                    this.cerrar();
                }
            }"
            @abrir-modal-confirmacion.window="
                cambiosCriticos  = $event.detail.cambiosCriticos;
                cambiosGenerales = $event.detail.cambiosGenerales;
                accion           = $event.detail.accion;
                motivo           = '';
                motivoError      = false;
                abierto          = true;
            "
            @keydown.escape.window="cerrar()"
            x-show="abierto"
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center"
            x-cloak
        >
            {{-- Overlay --}}
            <div
                class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
                @click="cerrar()"
            ></div>

            {{-- Caja modal --}}
            <div
                class="relative w-[92%] max-w-lg max-h-[90vh] overflow-y-auto
                       rounded-2xl border border-white/10
                       bg-slate-950/80 backdrop-blur-2xl
                       shadow-2xl shadow-slate-950/60"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-white/10 flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center w-7 h-7 rounded-full
                                         bg-amber-500/15 border border-amber-400/30">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24"
                                     stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0
                                           2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898
                                           0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </span>
                            <h4 class="text-base font-semibold text-slate-50">Confirmar cambios</h4>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5 ml-9">
                            Se detectaron cambios que requieren justificación antes de guardar.
                        </p>
                    </div>
                    <button type="button" @click="cerrar()"
                        class="flex-shrink-0 w-8 h-8 rounded-full bg-white/5 hover:bg-white/10
                               border border-white/10 text-slate-300 flex items-center justify-center
                               transition mt-0.5">
                        ✕
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4 space-y-4">

                    {{-- Acción --}}
                    <div class="flex items-center gap-2">
                        <span class="text-[0.7rem] font-semibold uppercase tracking-widest text-slate-400">
                            Acción detectada
                        </span>
                        <div class="h-px flex-1 bg-white/10"></div>
                        <span class="text-[0.7rem] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full
                                     border border-amber-400/30 bg-amber-500/10 text-amber-300"
                              x-text="accion"></span>
                    </div>

                    {{-- Cambios críticos --}}
                    <template x-if="cambiosCriticos.length > 0">
                        <div class="space-y-2">
                            <p class="text-[0.7rem] font-semibold uppercase tracking-widest text-rose-400/80">
                                Cambios críticos
                            </p>
                            <div class="rounded-xl border border-rose-500/20 bg-rose-950/20
                                        divide-y divide-rose-500/10 overflow-hidden">
                                <template x-for="c in cambiosCriticos" :key="c.campo">
                                    <div class="px-3 py-2.5 grid grid-cols-3 gap-2 items-start text-xs">
                                        <span class="text-slate-400 font-medium" x-text="c.etiqueta"></span>
                                        <span class="text-rose-300/80 line-through truncate" x-text="c.de || '—'"></span>
                                        <span class="text-emerald-300 font-semibold truncate" x-text="c.a || '—'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Cambios generales --}}
                    <template x-if="cambiosGenerales.length > 0">
                        <div class="space-y-2">
                            <p class="text-[0.7rem] font-semibold uppercase tracking-widest text-slate-400/70">
                                Otros cambios incluidos
                            </p>
                            <div class="rounded-xl border border-white/10 bg-white/5
                                        divide-y divide-white/5 overflow-hidden">
                                <template x-for="c in cambiosGenerales" :key="c.campo">
                                    <div class="px-3 py-2.5 grid grid-cols-3 gap-2 items-start text-xs">
                                        <span class="text-slate-400 font-medium" x-text="c.etiqueta"></span>
                                        <span class="text-slate-500 line-through truncate" x-text="c.de || '—'"></span>
                                        <span class="text-slate-200 truncate" x-text="c.a || '—'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Motivo --}}
                    <div class="space-y-1.5 pt-1">
                        <label class="block text-xs font-semibold text-slate-200">
                            Motivo del cambio <span class="text-rose-400">*</span>
                        </label>
                        <textarea
                            x-model="motivo"
                            @input="if(motivo.trim()) motivoError = false"
                            rows="3"
                            placeholder="Explica brevemente el motivo de esta modificación…"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-100
                                   bg-slate-900/60 backdrop-blur-sm placeholder:text-slate-500
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/60
                                   transition resize-none"
                            :class="motivoError
                                ? 'border-rose-500/60 focus:ring-rose-500/40'
                                : 'border-white/10 focus:border-indigo-500/40'"
                        ></textarea>
                        <p x-show="motivoError" x-transition.opacity class="text-xs text-rose-400">
                            El motivo es obligatorio para continuar.
                        </p>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="px-5 py-4 border-t border-white/10 flex items-center justify-between gap-3">
                    <span class="text-xs text-slate-500">
                        Esta acción quedará registrada en la auditoría del equipo.
                    </span>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" @click="cerrar()"
                            class="rounded-full px-4 py-2 text-sm font-medium
                                   bg-white/5 hover:bg-white/10 border border-white/10
                                   text-slate-200 transition">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmar()"
                            class="rounded-full px-4 py-2 text-sm font-medium
                                   bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                   text-white shadow-md shadow-blue-800/60
                                   hover:shadow-blue-500/80 hover:-translate-y-0.5
                                   transition-all duration-200">
                            Confirmar y guardar
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </template>

</div>