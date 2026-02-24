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


   


    <form wire:submit.prevent="actualizar" class="space-y-8 text-slate-900 dark:text-slate-100">

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




    
<div class="mt-8 p-6 rounded-2xl bg-white/5 backdrop-blur border border-white/10">

    <h2 class="text-lg font-semibold text-white mb-4">
        Transferir Equipo
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <label class="text-sm text-gray-300">Almacén destino</label>
            <select wire:model="almacenDestinoId"
                    class="w-full mt-1 bg-black/40 border border-white/20 rounded-lg p-2 text-white">
                <option value="">Seleccione</option>
                @foreach($almacenesDisponibles as $alm)
                    <option value="{{ $alm->id }}">
                        {{ $alm->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm text-gray-300">Motivo</label>
            <input type="text"
                   wire:model="motivoTransferencia"
                   class="w-full mt-1 bg-black/40 border border-white/20 rounded-lg p-2 text-white">
        </div>

        <div class="flex items-end">
            <button wire:click="transferir"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Transferir
            </button>
        </div>

    </div>

</div>

<div class="mt-10 p-6 rounded-2xl bg-white/5 backdrop-blur border border-white/10">

    <h2 class="text-lg font-semibold text-white mb-6">
        Historial de Movimientos
    </h2>

    @forelse($equipo->movimientos as $mov)

        <div class="mb-6 border-l-4 border-blue-500 pl-4">

            <div class="text-xs text-gray-400">
                {{ $mov->created_at->format('d M Y H:i') }}
            </div>

            <div class="text-white font-semibold">
                {{ $mov->tipo }}
            </div>

            <div class="text-sm text-gray-300">
                De:
                <span class="text-orange-400">
                    {{ optional($mov->desde)->nombre ?? 'N/A' }}
                </span>

                →
                A:
                <span class="text-green-400">
                    {{ optional($mov->hacia)->nombre ?? 'N/A' }}
                </span>
            </div>

            @if($mov->motivo)
                <div class="text-xs text-gray-500 mt-1">
                    Motivo: {{ $mov->motivo }}
                </div>
            @endif

        </div>

    @empty
        <div class="text-gray-500">
            No hay movimientos registrados para este equipo.
        </div>
    @endforelse

</div>
</div>
