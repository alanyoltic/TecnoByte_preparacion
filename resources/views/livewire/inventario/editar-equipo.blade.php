<div class="space-y-6">

    {{-- Mensaje de éxito --}}
    @if (session('status'))
        <div class="rounded-xl border border-emerald-400/70 bg-emerald-50/80 text-emerald-800 px-4 py-2 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- Resumen rápido del equipo --}}
    <div
        class="rounded-2xl
               bg-white/80 dark:bg-slate-950/80
               border border-slate-200/80 dark:border-white/10
               backdrop-blur-xl dark:backdrop-blur-2xl
               shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30
               px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
    >
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase">
                Equipo
            </p>
            <p class="text-base font-bold text-slate-900 dark:text-slate-50">
                {{ $marca }} {{ $modelo }}
            </p>
            <p class="text-xs text-slate-500">
                Serie: {{ $numero_serie ?: '—' }}
            </p>
        </div>

        <div class="text-xs text-slate-500 text-right">
            <p>Estatus actual: <span class="font-semibold text-slate-800 dark:text-slate-100">
                {{ $estatus_general ?: 'Sin estatus' }}
            </span></p>
            @if($grado)
                <p>Grado: <span class="font-semibold">{{ $grado }}</span></p>
            @endif
        </div>
    </div>

    {{-- FORMULARIO PRINCIPAL --}}
    <form wire:submit.prevent="actualizarEquipo" class="space-y-6">

        {{-- Datos básicos --}}
        <div
            class="rounded-2xl
                   bg-white/80 dark:bg-slate-950/80
                   border border-slate-200/80 dark:border-white/10
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30
                   p-4 sm:p-5 space-y-4"
        >
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">
                Datos generales
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Marca --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Marca
                    </label>
                    <input
                        type="text"
                        wire:model.defer="marca"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('marca') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Modelo --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        Modelo
                    </label>
                    <input
                        type="text"
                        wire:model.defer="modelo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('modelo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Tipo de equipo --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipo de equipo
                    </label>
                    <input
                        type="text"
                        wire:model.defer="tipo_equipo"
                        placeholder="Ej. Laptop, Desktop..."
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('tipo_equipo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Serie --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Número de serie
                    </label>
                    <input
                        type="text"
                        wire:model.defer="numero_serie"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                               font-mono"
                    >
                    @error('numero_serie') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Estatus --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Estatus general
                    </label>
                    <select
                        wire:model.defer="estatus_general"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">— Selecciona —</option>
                        <option value="En Revisión">En Revisión</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Pendiente Pieza">Pendiente Pieza</option>
                        <option value="Pendiente Garantía">Pendiente Garantía</option>
                        <option value="Pendiente Deshueso">Pendiente Deshueso</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                    @error('estatus_general') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Grado --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Grado
                    </label>
                    <input
                        type="text"
                        wire:model.defer="grado"
                        placeholder="Ej. A, B, C..."
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('grado') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Bloque CPU / RAM / Disco / SO / Touch / Gráfica --}}
        <div
            class="rounded-2xl
                   bg-white/80 dark:bg-slate-950/80
                   border border-slate-200/80 dark:border-white/10
                   backdrop-blur-xl dark:backdrop-blur-2xl
                   shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30
                   p-4 sm:p-5 space-y-4"
        >
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">
                Especificaciones principales
            </h2>

            {{-- Fila CPU --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Modelo CPU --}}
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">
                        Procesador (modelo)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="procesador_modelo"
                        placeholder="Ej. i5-8250U"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('procesador_modelo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Frecuencia CPU --}}
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">
                        Frecuencia (GHz)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="procesador_frecuencia"
                        placeholder="Ej. 1.90 GHz"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('procesador_frecuencia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Generación --}}
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">
                        Generación
                    </label>
                    <input
                        type="text"
                        wire:model.defer="procesador_generacion"
                        placeholder="Ej. 8va gen"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('procesador_generacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Fila RAM / Disco --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- RAM total --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        RAM total
                    </label>
                    <input
                        type="text"
                        wire:model.defer="ram_total"
                        placeholder="Ej. 8 GB"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('ram_total') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Tipo RAM --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipo de RAM
                    </label>
                    <input
                        type="text"
                        wire:model.defer="ram_tipo"
                        placeholder="Ej. DDR4"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('ram_tipo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Capacidad disco --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Almacenamiento
                    </label>
                    <input
                        type="text"
                        wire:model.defer="almacenamiento_principal_capacidad"
                        placeholder="Ej. 256 GB"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('almacenamiento_principal_capacidad') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Tipo disco --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipo de almacenamiento
                    </label>
                    <input
                        type="text"
                        wire:model.defer="almacenamiento_principal_tipo"
                        placeholder="Ej. SSD, M.2"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('almacenamiento_principal_tipo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- SO / Touch / Gráfica --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Sistema operativo --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        Sistema operativo
                    </label>
                    <input
                        type="text"
                        wire:model.defer="sistema_operativo"
                        placeholder="Ej. Windows 10 Pro"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('sistema_operativo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Touch --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Pantalla touch
                    </label>
                    <select
                        wire:model.defer="pantalla_es_touch"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                    @error('pantalla_es_touch') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Gráfica dedicada --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Gráfica dedicada
                    </label>
                    <input
                        type="text"
                        wire:model.defer="grafica_dedicada_modelo"
                        placeholder="Ej. MX150, GTX 1650..."
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('grafica_dedicada_modelo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                    <input
                        type="text"
                        wire:model.defer="grafica_dedicada_vram"
                        placeholder="VRAM (Ej. 2 GB)"
                        class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('grafica_dedicada_vram') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('inventario.listo') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl text-sm
                      border border-slate-300 dark:border-slate-600
                      text-slate-700 dark:text-slate-200
                      bg-white/60 dark:bg-slate-900/60
                      hover:bg-slate-100 dark:hover:bg-slate-800
                      transition-all">
                Cancelar
            </a>

            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-500
                       text-white shadow-md shadow-blue-500/30
                       transition-all"
            >
                Guardar cambios
            </button>
        </div>
    </form>
</div>
