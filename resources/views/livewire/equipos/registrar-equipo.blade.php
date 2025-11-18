<div
    class="bg-white/70 dark:bg-slate-900/60
           border border-white/60 dark:border-slate-700/70 
           backdrop-blur-xl rounded-2xl shadow-lg shadow-slate-900/30
           px-4 py-5 sm:px-6 sm:py-6
           transition-all duration-300 ease-out
           hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/25"
>
    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50/90 px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Título interno --}}
    <div class="mb-4">
        <h3 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-50">
            Datos del equipo
        </h3>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Completa la información técnica y del lote.
        </p>
    </div>

    {{-- FORMULARIO --}}
    <form wire:submit.prevent="guardar" class="space-y-6 text-slate-900 dark:text-slate-100">

        {{-- Lote / Modelo --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Lote</label>
                <select
                    wire:model="lote_id"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                           bg-slate-50 dark:bg-slate-900
                           text-sm px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecciona un lote</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->id }}">{{ $lote->nombre }}</option>
                    @endforeach
                </select>
                @error('lote_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Modelo</label>
                <select
                    wire:model="modelo_id"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                           bg-slate-50 dark:bg-slate-900
                           text-sm px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    @if(empty($modelos)) disabled @endif>
                    <option value="">
                        @if(empty($modelos))
                            Selecciona primero un lote
                        @else
                            Selecciona un modelo
                        @endif
                    </option>
                    @foreach($modelos as $modelo)
                        <option value="{{ $modelo->id }}">{{ $modelo->nombre }}</option>
                    @endforeach
                </select>
                @error('modelo_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Número de serie / Procesador --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Número de serie</label>
                <input type="text"
                       wire:model.defer="numero_serie"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                              bg-slate-50 dark:bg-slate-900
                              text-sm px-3 py-2
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('numero_serie')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Procesador</label>
                <input type="text"
                       wire:model.defer="procesador"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                              bg-slate-50 dark:bg-slate-900
                              text-sm px-3 py-2
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('procesador')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- RAM / Almacenamiento --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">RAM</label>
                <input type="text"
                       wire:model.defer="ram"
                       placeholder="Ej. 8 GB DDR4"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                              bg-slate-50 dark:bg-slate-900
                              text-sm px-3 py-2
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('ram')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Almacenamiento</label>
                <input type="text"
                       wire:model.defer="almacenamiento"
                       placeholder="Ej. 256 GB SSD"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                              bg-slate-50 dark:bg-slate-900
                              text-sm px-3 py-2
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('almacenamiento')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Observaciones --}}
        <div>
            <label class="block text-sm font-medium mb-1">Observaciones</label>
            <textarea
                wire:model.defer="observaciones"
                rows="3"
                class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                       bg-slate-50 dark:bg-slate-900
                       text-sm px-3 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Notas sobre estado físico, detalles de prueba, etc."></textarea>
            @error('observaciones')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- BOTÓN --}}
        <div class="flex items-center justify-end pt-2">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-medium
                       bg-indigo-600 hover:bg-indigo-700 text-white
                       shadow-md shadow-indigo-500/40 hover:shadow-lg
                       transition-all"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar equipo</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

    </form>
</div>
