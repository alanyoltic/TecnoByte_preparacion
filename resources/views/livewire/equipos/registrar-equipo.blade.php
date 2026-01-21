
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
    >



        {{-- Título principal --}}
        <div class="mb-5">
            <h3 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-50">
                Registro de equipo
            </h3>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Captura los datos principales del equipo. Los campos marcados con * son obligatorios.
            </p>
        </div>
    {{-- DEBUG --}}





        <form wire:submit.prevent="guardar" class="space-y-8 text-slate-900 dark:text-slate-100">

           
    @include('livewire.equipos._form', [
        'mode' => 'create',
        'form' => $form,
        // si necesitas catálogos/arrays:
        'proveedores' => $proveedores ?? [],
        'lotes' => $lotes ?? [],
        'modelosLote' => $modelosLote ?? [],
        'lotesTerminadosIds' => $lotesTerminadosIds ?? [],
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
    </div>
