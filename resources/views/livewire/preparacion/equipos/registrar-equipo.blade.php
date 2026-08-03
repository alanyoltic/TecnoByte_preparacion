
<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Registrar equipo"
            chip="Preparación · Entrada"
            description="Captura los datos del equipo que ingresa a preparación."
        />

        <div
                class="bg-white/80 dark:bg-slate-950/60
                    border border-slate-200/80 dark:border-white/10
                    backdrop-blur-xl dark:backdrop-blur-2xl
                    rounded-2xl
                    shadow-md shadow-slate-900/10
                    dark:shadow-lg dark:shadow-slate-900/30
                    px-4 py-5 sm:px-6 sm:py-6
                    transition-all duration-300 ease-out"
            >



        {{-- Título principal --}}
        <div class="mb-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-50">
                    Registro de equipo
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Captura los datos principales del equipo. Los campos marcados con * son obligatorios.
                </p>
            </div>
            
            @php $allowedEmails = ['soporte@tecnobytemx.com', 'prueba@prueba.com', 'tamara.trejo@tecnobytemx.com']; @endphp
            @if($tieneEquiposPrevios)
            <div class="flex flex-col sm:flex-row items-center gap-2">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    Rellenar con plantilla:
                </span>
                <select 
                    x-data="{ isAllowed: @js(auth()->user() && in_array(auth()->user()->email, $allowedEmails)) }"
                    x-on:change="
                        if (!isAllowed && $el.value !== '') {
                            if (!confirm('⚠️ ADVERTENCIA: Estás usando una plantilla.\n\nDebes verificar 2 VECES que todos los datos coincidan exactamente con el equipo físico antes de guardar.\n\n¿Entendido?')) {
                                $el.value = '';
                                return;
                            }
                        }
                        $wire.set('equipoPlantillaId', $el.value);
                        $wire.aplicarPlantilla();
                    "
                    class="block w-full sm:w-64 rounded-md border-0 py-1.5 pl-3 pr-10 text-slate-900 dark:text-slate-100 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6 bg-white dark:bg-slate-900"
                    wire:loading.attr="disabled"
                >
                    <option value="">-- Seleccionar equipo --</option>
                    <option value="ultimo">Último equipo subido</option>
                    @if(count($opcionesPlantilla) > 0)
                        <optgroup label="De la misma asignación">
                        @foreach($opcionesPlantilla as $eq)
                            <option value="{{ $eq['id'] }}">Serie: {{ $eq['numero_serie'] }} - {{ $eq['marca'] }}</option>
                        @endforeach
                        </optgroup>
                    @endif
                </select>
                <div wire:loading wire:target="aplicarPlantilla" class="ml-2">
                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            @endif
        </div>
    {{-- DEBUG --}}





        <form wire:submit.prevent="guardar" class="space-y-8 text-slate-900 dark:text-slate-100">

           
    @include('livewire.preparacion.equipos._form', [
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

    </div>
</x-tb-background>
</div>
