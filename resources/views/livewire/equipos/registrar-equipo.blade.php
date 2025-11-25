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

    {{-- Título principal --}}
    <div class="mb-5">
        <h3 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-50">
            Registro de equipo
        </h3>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Captura los datos principales del equipo. Los campos marcados con * son obligatorios.
        </p>
    </div>

    <form wire:submit.prevent="guardar" class="space-y-8 text-slate-900 dark:text-slate-100">

        {{-- =================== --}}
        {{--  1. DATOS BÁSICOS   --}}
        {{-- =================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Datos básicos
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Lote / Modelo recibido --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Lote
                    </label>
                        <select
    wire:model="lote_id"
    wire:change="actualizarLote($event.target.value)"
    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
        bg-slate-50 dark:bg-slate-900 text-sm px-3 py-2
        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
>
    <option value="">Selecciona un lote</option>

    @foreach($lotes as $lote)
        @php
            $fecha = $lote->fecha_llegada
                ? \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y')
                : 'Sin fecha';

            // Verificamos si este lote está en la lista de terminados
            $esTerminado = in_array($lote->id, $lotesTerminadosIds ?? []);
        @endphp

        <option
            value="{{ $lote->id }}"
            @if($esTerminado) disabled @endif
        >
            Lote {{ $lote->nombre_lote }} — {{ $fecha }}
            @if($esTerminado)
                (lote terminado)
            @endif
        </option>
    @endforeach
</select>


                    @error('lote_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                </div>

                {{-- Proveedor (se selecciona automáticamente según el lote) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Proveedor *
                    </label>
                    <select
                        wire:model="proveedor_id"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-100 dark:bg-slate-800
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-0 focus:border-slate-300"
                        disabled
                    >
                        <option value="">Selecciona un lote primero</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}">
                                {{ $prov->abreviacion }} — {{ $prov->nombre_empresa }}
                            </option>
                        @endforeach
                    </select>
                    @error('proveedor_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>




            

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Número de serie --}}
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">
                        Número de serie *
                    </label>
                    <input
                        type="text"
                        wire:model.defer="numero_serie"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('numero_serie')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Marca --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Marca
                    </label>
                    <input
                        type="text"
                        wire:model.defer="marca"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-100 dark:bg-slate-800
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-0 focus:border-slate-300"
                        readonly
                    >
                    @error('marca')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modelo (del lote seleccionado) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Modelo *
                    </label>
<select
    wire:model="lote_modelo_id"
    wire:change="actualizarModelo($event.target.value)"
    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
        bg-slate-50 dark:bg-slate-900
        text-sm px-3 py-2
        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
    @disabled(!$lote_id || empty($modelosLote))
>
    <option value="">Selecciona un modelo</option>
    @foreach($modelosLote as $m)
        <option value="{{ $m['id'] }}">
            {{ $m['marca'] }} {{ $m['modelo'] }}
        </option>
    @endforeach
</select>



                    @error('lote_modelo_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo de equipo --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipo de equipo
                    </label>
                    <select
                        wire:model.defer="tipo_equipo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Desktop">Desktop</option>
                        <option value="All in One">All in One</option>
                        <option value="Mini PC">Mini PC</option>
                        <option value="Otro">Otro</option>
                    </select>
                    @error('tipo_equipo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sistema operativo --}}
                <div>
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
                    @error('sistema_operativo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Área / Tienda --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Área / Tienda
                    </label>
                    <input
                        type="text"
                        wire:model.defer="area_tienda"
                        placeholder="Ej. Sucursal Querétaro"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('area_tienda')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>

        {{-- =========================== --}}
        {{--  2. PROCESADOR Y MEMORIA    --}}
        {{-- =========================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Procesador y memoria
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Modelo CPU --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        Procesador (modelo)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="procesador_modelo"
                        placeholder="Ej. Core i5-8250U"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('procesador_modelo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Generación --}}
                <div>
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
                    @error('procesador_generacion')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Núcleos --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Núcleos
                    </label>
                    <input
                        type="number"
                        min="1"
                        max="32"
                        wire:model.defer="procesador_nucleos"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('procesador_nucleos')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

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
                    @error('ram_total')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
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
                    @error('ram_tipo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- RAM soldada --}}
                <div class="flex items-center gap-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            RAM soldada
                        </label>
                        <div class="flex items-center gap-2 mt-1">
                            <input
                                type="checkbox"
                                wire:model="ram_es_soldada"
                                class="rounded border-slate-300 text-indigo-600 shadow-sm 
                                       focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                            >
                            <span class="text-xs text-slate-600 dark:text-slate-300">
                                Sí, trae parte de la RAM en placa
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Slots / expansión --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Slots / expansión
                    </label>
                    <input
                        type="text"
                        wire:model.defer="ram_slots_totales"
                        placeholder="Ej. 1/2 slots"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mb-1"
                    >
                    <input
                        type="text"
                        wire:model.defer="ram_expansion_max"
                        placeholder="Ej. Máx. 32 GB"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs px-3 py-1.5 mt-1
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
            </div>
        </div>

        {{-- ================== --}}
        {{--  3. PANTALLA       --}}
        {{-- ================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Pantalla
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tamaño (pulgadas)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="pantalla_pulgadas"
                        placeholder="Ej. 14”"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Resolución
                    </label>
                    <input
                        type="text"
                        wire:model.defer="pantalla_resolucion"
                        placeholder="Ej. 1920x1080"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipo de panel
                    </label>
                    <input
                        type="text"
                        wire:model.defer="pantalla_tipo"
                        placeholder="Ej. IPS, TN"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <div class="flex items-center">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Pantalla touch
                        </label>
                        <div class="flex items-center gap-2 mt-1">
                            <input
                                type="checkbox"
                                wire:model="pantalla_es_touch"
                                class="rounded border-slate-300 text-indigo-600 shadow-sm 
                                       focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                            >
                            <span class="text-xs text-slate-600 dark:text-slate-300">
                                Sí es táctil
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== --}}
        {{--  4. ALMACENAMIENTO --}}
        {{-- ================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Almacenamiento
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Principal --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Principal (capacidad)
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
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Principal (tipo)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="almacenamiento_principal_tipo"
                        placeholder="Ej. SSD NVMe"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                {{-- Secundario --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Secundario (capacidad)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="almacenamiento_secundario_capacidad"
                        placeholder="Ej. 1 TB / N/A"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Secundario (tipo)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="almacenamiento_secundario_tipo"
                        placeholder="Ej. HDD, SSD, N/A"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
            </div>
        </div>

        {{-- ================== --}}
        {{--  5. GRÁFICOS       --}}
        {{-- ================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Gráficos
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Gráfica integrada
                    </label>
                    <input
                        type="text"
                        wire:model.defer="grafica_integrada_modelo"
                        placeholder="Ej. Intel UHD"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Gráfica dedicada
                    </label>
                    <input
                        type="text"
                        wire:model.defer="grafica_dedicada_modelo"
                        placeholder="Ej. GTX 1650"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">
                        VRAM dedicada
                    </label>
                    <input
                        type="text"
                        wire:model.defer="grafica_dedicada_vram"
                        placeholder="Ej. 4 GB"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
            </div>
        </div>

        {{-- ================== --}}
        {{--  6. BATERÍA / OTROS --}}
        {{-- ================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Batería y otros
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Salud batería --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Salud batería (%)
                    </label>
                    <input
                        type="number"
                        min="30"
                        max="100"
                        wire:model.defer="bateria_salud_percent"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                {{-- Cantidad de baterías --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Batería (tipo/cantidad)
                    </label>
                    <input
                        type="text"
                        wire:model.defer="bateria_cantidad"
                        placeholder="Ej. interna, 3 celdas"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                {{-- Teclado idioma --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Idioma teclado
                    </label>
                    <input
                        type="text"
                        wire:model.defer="teclado_idioma"
                        placeholder="Ej. Español, US, N/A"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                {{-- Estatus general --}}
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
                        <option value="En Revisión">En Revisión</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Pendiente Pieza">Pendiente Pieza</option>
                        <option value="Pendiente Garantía">Pendiente Garantía</option>
                        <option value="Pendiente Deshueso">Pendiente Deshueso</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                    @error('estatus_general')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Notas generales --}}
            <div>
                <label class="block text-sm font-medium mb-1">
                    Notas generales
                </label>
                <textarea
                    wire:model.defer="notas_generales"
                    rows="3"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                           bg-slate-50 dark:bg-slate-900
                           text-sm px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Anota aquí cualquier detalle importante del equipo, golpes, reparaciones, piezas cambiadas, etc."></textarea>
                @error('notas_generales')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>


{{-- ================== --}}
{{--  PUERTOS USB       --}}
{{-- ================== --}}
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
            Puertos USB
        </span>
        <button
            type="button"
            wire:click="addPuertoUsb"
            class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium
                   bg-indigo-500/90 text-white hover:bg-indigo-600
                   shadow-sm shadow-indigo-500/40 transition"
        >
            + Añadir puerto USB
        </button>
    </div>

    @if(empty($puertos_usb))
        <p class="text-xs text-slate-500 dark:text-slate-400">
            No hay puertos USB registrados. Usa el botón "Añadir puerto USB".
        </p>
    @endif

    <div class="space-y-2">
        @foreach($puertos_usb as $index => $puerto)
            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-7 sm:col-span-6">
                    <select
                        wire:model="puertos_usb.{{ $index }}.tipo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs sm:text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Tipo de puerto</option>
                        <option value="USB 2.0">USB 2.0</option>
                        <option value="USB 3.0">USB 3.0</option>
                        <option value="USB 3.1">USB 3.1</option>
                        <option value="USB 3.2">USB 3.2</option>
                        <option value="USB-C">USB tipo C</option>
                    </select>
                </div>

                <div class="col-span-4 sm:col-span-4">
                    <input
                        type="number"
                        min="1"
                        max="10"
                        wire:model="puertos_usb.{{ $index }}.cantidad"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs sm:text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Cant."
                    >
                </div>

                <div class="col-span-1 flex justify-end">
                    <button
                        type="button"
                        wire:click="removePuertoUsb({{ $index }})"
                        class="inline-flex items-center justify-center rounded-full
                               w-7 h-7 text-xs bg-rose-500/90 text-white hover:bg-rose-600
                               shadow-sm shadow-rose-500/40"
                    >
                        ✕
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>


{{-- ================== --}}
{{--  PUERTOS DE VIDEO  --}}
{{-- ================== --}}
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
            Puertos de video
        </span>
        <button
            type="button"
            wire:click="addPuertoVideo"
            class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium
                   bg-indigo-500/90 text-white hover:bg-indigo-600
                   shadow-sm shadow-indigo-500/40 transition"
        >
            + Añadir puerto de video
        </button>
    </div>

    @if(empty($puertos_video))
        <p class="text-xs text-slate-500 dark:text-slate-400">
            No hay puertos de video registrados.
        </p>
    @endif

    <div class="space-y-2">
        @foreach($puertos_video as $index => $puerto)
            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-7 sm:col-span-6">
                    <select
                        wire:model="puertos_video.{{ $index }}.tipo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs sm:text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Tipo de puerto</option>
                        <option value="HDMI">HDMI</option>
                        <option value="Mini HDMI">Mini HDMI</option>
                        <option value="VGA">VGA</option>
                        <option value="DVI">DVI</option>
                        <option value="DisplayPort">DisplayPort</option>
                        <option value="Mini DisplayPort">Mini DisplayPort</option>
                    </select>
                </div>

                <div class="col-span-4 sm:col-span-4">
                    <input
                        type="number"
                        min="1"
                        max="10"
                        wire:model="puertos_video.{{ $index }}.cantidad"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs sm:text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Cant."
                    >
                </div>

                <div class="col-span-1 flex justify-end">
                    <button
                        type="button"
                        wire:click="removePuertoVideo({{ $index }})"
                        class="inline-flex items-center justify-center rounded-full
                               w-7 h-7 text-xs bg-rose-500/90 text-white hover:bg-rose-600
                               shadow-sm shadow-rose-500/40"
                    >
                        ✕
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>




{{-- ================== --}}
{{--  LECTORES / RANURAS --}}
{{-- ================== --}}
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
            Lectores / ranuras
        </span>
        <button
            type="button"
            wire:click="addLector"
            class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium
                   bg-indigo-500/90 text-white hover:bg-indigo-600
                   shadow-sm shadow-indigo-500/40 transition"
        >
            + Añadir lector / ranura
        </button>
    </div>

    @if(empty($lectores))
        <p class="text-xs text-slate-500 dark:text-slate-400">
            No hay lectores registrados (SD, SIM, eSATA, etc.).
        </p>
    @endif

    <div class="space-y-2">
        @foreach($lectores as $index => $lector)
            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-4 sm:col-span-3">
                    <select
                        wire:model="lectores.{{ $index }}.tipo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs sm:text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Tipo</option>
                        <option value="SD">SD</option>
                        <option value="microSD">microSD</option>
                        <option value="SIM">SIM</option>
                        <option value="eSATA">eSATA</option>
                        <option value="SmartCard">SmartCard</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="col-span-7 sm:col-span-8">
                    <input
                        type="text"
                        wire:model="lectores.{{ $index }}.detalle"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-xs sm:text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Ej. 1 lector SD frontal, combo SIM + microSD, etc."
                    >
                </div>

                <div class="col-span-1 flex justify-end">
                    <button
                        type="button"
                        wire:click="removeLector({{ $index }})"
                        class="inline-flex items-center justify-center rounded-full
                               w-7 h-7 text-xs bg-rose-500/90 text-white hover:bg-rose-600
                               shadow-sm shadow-rose-500/40"
                    >
                        ✕
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>



        {{-- BOTÓN FINAL --}}
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
