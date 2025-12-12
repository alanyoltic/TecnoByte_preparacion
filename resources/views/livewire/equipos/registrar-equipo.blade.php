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
                        Tipo de equipo <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model.defer="tipo_equipo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>

                        <option value="LAPTOP">LAPTOP</option>
                        <option value="ESCRITORORIO">ESCRITORIO</option>
                        <option value="ALL IN ONE">ALL IN ONE</option>
                        <option value="TABLET">TABLET</option>
                        <option value="2 EN 1">2 EN 1</option>
                        <option value="MICRO PC">MICRO PC</option>
                        <option value="GAMER">GAMER</option>
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

                    <select
                        wire:model.defer="sistema_operativo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>

                        <!-- WINDOWS 10 -->
                        <option value="WIN 10 PRO">WIN 10 PRO</option>
                        <option value="WIN 10 PRO WORKSTATION">WIN 10 PRO WORKSTATION</option>
                        <option value="WIN 10 HOME">WIN 10 HOME</option>
                        <option value="WIN 10 PRO EDUCATION">WIN 10 PRO EDUCATION</option>
                        <option value="WIN 10 HOME SINGLE LANGUAGE">WIN 10 HOME SINGLE LANGUAGE</option>
                        <option value="WIN 10 MOBILE">WIN 10 MOBILE</option>
                        <option value="WIN 10 ENTERPRISE">WIN 10 ENTERPRISE</option>

                        <!-- WINDOWS 11 -->
                        <option value="WIN 11 PRO">WIN 11 PRO</option>
                        <option value="WIN 11 PRO WORKSTATION">WIN 11 PRO WORKSTATION</option>
                        <option value="WIN 11 HOME">WIN 11 HOME</option>
                        <option value="WIN 11 HOME SINGLE LANGUAGE">WIN 11 HOME SINGLE LANGUAGE</option>
                        <option value="WIN 11 MOBILE">WIN 11 MOBILE</option>
                        <option value="WIN 11 ENTERPRISE">WIN 11 ENTERPRISE</option>

                        <!-- WINDOWS 7 -->
                        <option value="WIN 7 STARTER">WIN 7 STARTER</option>
                        <option value="WIN 7 HOME">WIN 7 HOME</option>
                        <option value="WIN 7 PRO">WIN 7 PRO</option>
                    </select>

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

            {{-- CPU --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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
                    @error('procesador_modelo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Frecuencia CPU (GHz) --}}
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
                    @error('procesador_frecuencia')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Generación --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Generación <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model.defer="procesador_generacion"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>

                        @foreach (range(1,14) as $gen)
                            <option value="{{ $gen }}TH GEN">{{ $gen }}TH GEN</option>
                        @endforeach

                    </select>

                    @error('procesador_generacion')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Núcleos --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Núcleos <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model.defer="procesador_nucleos"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>

                        <!-- OPCIONES: 2 → 32 núcleos -->
                        <option value="32">32 NÚCLEOS</option>
                        <option value="24">24 NÚCLEOS</option>
                        <option value="20">20 NÚCLEOS</option>
                        <option value="16">16 NÚCLEOS</option>
                        <option value="14">14 NÚCLEOS</option>
                        <option value="12">12 NÚCLEOS</option>
                        <option value="10">10 NÚCLEOS</option>
                        <option value="8">8 NÚCLEOS</option>
                        <option value="6">6 NÚCLEOS</option>
                        <option value="4">4 NÚCLEOS</option>
                        <option value="2">2 NÚCLEOS</option>
                    </select>

                    @error('procesador_nucleos')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- RAM PRINCIPAL --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- RAM total --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        RAM total <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model.defer="ram_total"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>

                        {{-- 2 GB a 256 GB --}}
                        <option value="2 GB">2 GB</option>
                        <option value="4 GB">4 GB</option>
                        <option value="6 GB">6 GB</option>
                        <option value="8 GB">8 GB</option>
                        <option value="12 GB">12 GB</option>
                        <option value="16 GB">16 GB</option>
                        <option value="24 GB">24 GB</option>
                        <option value="32 GB">32 GB</option>
                        <option value="48 GB">48 GB</option>
                        <option value="64 GB">64 GB</option>
                        <option value="96 GB">96 GB</option>
                        <option value="128 GB">128 GB</option>
                        <option value="192 GB">192 GB</option>
                        <option value="256 GB">256 GB</option>
                    </select>

                    @error('ram_total')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo RAM --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipo de RAM <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model.defer="ram_tipo"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-slate-50 dark:bg-slate-900
                               text-sm px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>

                        <option value="DDR2">DDR2</option>
                        <option value="DDR3">DDR3</option>
                        <option value="DDR3L">DDR3L</option>
                        <option value="LPDDR3">LPDDR3</option>
                        <option value="DDR4">DDR4</option>
                        <option value="DDR5">DDR5</option>
                        <option value="LPDDR5X">LPDDR5X</option>
                        <option value="PC2">PC2</option>
                        <option value="PC3">PC3</option>
                        <option value="PC3L">PC3L</option>
                        <option value="PC4">PC4</option>
                    </select>

                    @error('ram_tipo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- RAM máxima expansión --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        RAM máxima expansión
                    </label>

                    <select
                        wire:model.defer="ram_expansion_max"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        @disabled($ram_sin_slots)
                    >
                        <option value="">Selecciona</option>

                        <option value="0 GB">0 GB (SIN EXPANSIÓN)</option>

                        @foreach ([8, 12, 16, 24, 32, 48, 64, 96, 128, 192, 256, 512] as $ram)
                            <option value="{{ $ram }} GB">{{ $ram }} GB</option>
                        @endforeach
                    </select>


                    @error('ram_expansion_max')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Slots RAM totales --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Slots RAM totales
                    </label>

                    <select
                        wire:model.defer="ram_slots_totales"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        @disabled($ram_sin_slots)
                    >
                        <option value="">Selecciona</option>

                        <option value="0">0 SLOTS (SIN EXPANSIÓN)</option>

                        @foreach (range(1, 8) as $slots)
                            <option value="{{ $slots }}">{{ $slots }} SLOTS</option>
                        @endforeach
                    </select>


                    @error('ram_slots_totales')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- RAM soldada / avanzada --}}
            <div class="mt-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    {{-- Checkbox RAM soldada --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            RAM soldada
                        </label>
                        <div class="flex items-start gap-2 mt-1">
                        <input
                            type="checkbox"
                            wire:click="toggleRamSoldada"
                            @checked($ram_es_soldada)
                            class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm
                                focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                        />


                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                Marca esta opción si el equipo trae parte de la RAM soldada en placa.
                            </p>
                        </div>
                    </div>

                    {{-- Cantidad de RAM soldada --}}
                    @if($ram_es_soldada)
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Cantidad de RAM soldada
                            </label>
                            <select
                                wire:model.defer="ram_cantidad_soldada"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                       bg-slate-50 dark:bg-slate-900
                                       text-sm px-3 py-2
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Selecciona</option>
                                <option value="2 GB">2 GB</option>
                                <option value="4 GB">4 GB</option>
                                <option value="6 GB">6 GB</option>
                                <option value="8 GB">8 GB</option>
                                <option value="12 GB">12 GB</option>
                                <option value="16 GB">16 GB</option>
                                <option value="24 GB">24 GB</option>
                                <option value="32 GB">32 GB</option>
                                <option value="48 GB">48 GB</option>
                                <option value="64 GB">64 GB</option>
                            </select>
                            @error('ram_cantidad_soldada')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- RAM totalmente soldada --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                RAM totalmente soldada
                            </label>
                            <div class="flex items-start gap-2 mt-1">
                                <input
                                    type="checkbox"
                                    wire:click="toggleRamSinSlots"
                                    @checked($ram_sin_slots)
                                    class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm
                                        focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                                />

                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    Marca esta opción si el equipo no tiene slots físicos de expansión
                                    (toda la RAM viene integrada en placa).
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>



                {{-- ================== --}}
        {{--  4. ALMACENAMIENTO --}}
        {{-- ================== --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
    {{-- Principal (capacidad) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            Principal (capacidad)
        </label>
        <select
            wire:model.defer="almacenamiento_principal_capacidad"
            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                   bg-slate-50 dark:bg-slate-900
                   text-sm px-3 py-2
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
            <option value="">Selecciona</option>
            <option value="N/A">N/A</option>
            <option value="64 GB">64 GB</option>
            <option value="120 GB">120 GB</option>
            <option value="128 GB">128 GB</option>
            <option value="240 GB">240 GB</option>
            <option value="250 GB">250 GB</option>
            <option value="256 GB">256 GB</option>
            <option value="320 GB">320 GB</option>
            <option value="480 GB">480 GB</option>
            <option value="500 GB">500 GB</option>
            <option value="512 GB">512 GB</option>
            <option value="750 GB">750 GB</option>
            <option value="960 GB">960 GB</option>
            <option value="1 TB">1 TB</option>
            <option value="2 TB">2 TB</option>
        </select>
        @error('almacenamiento_principal_capacidad')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Principal (tipo de disco) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            Principal (tipo de disco)
        </label>
        <select
            wire:model.defer="almacenamiento_principal_tipo"
            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                   bg-slate-50 dark:bg-slate-900
                   text-sm px-3 py-2
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
            <option value="">Selecciona</option>
            <option value="N/A">N/A</option>
            <option value="SSD">SSD</option>
            <option value="M.2">M.2</option>
            <option value="M.2 MICRO">M.2 MICRO</option>
            <option value="HDD">HDD</option>
            <option value="MSATA">MSATA</option>
        </select>
        @error('almacenamiento_principal_tipo')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Secundario (capacidad) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            Secundario (capacidad)
        </label>
        <select
            wire:model.defer="almacenamiento_secundario_capacidad"
            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                   bg-slate-50 dark:bg-slate-900
                   text-sm px-3 py-2
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
            <option value="">Selecciona</option>
            <option value="N/A">N/A</option>
            <option value="64 GB">64 GB</option>
            <option value="120 GB">120 GB</option>
            <option value="128 GB">128 GB</option>
            <option value="240 GB">240 GB</option>
            <option value="250 GB">250 GB</option>
            <option value="256 GB">256 GB</option>
            <option value="320 GB">320 GB</option>
            <option value="480 GB">480 GB</option>
            <option value="500 GB">500 GB</option>
            <option value="512 GB">512 GB</option>
            <option value="750 GB">750 GB</option>
            <option value="960 GB">960 GB</option>
            <option value="1 TB">1 TB</option>
            <option value="2 TB">2 TB</option>
        </select>
        @error('almacenamiento_secundario_capacidad')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Secundario (tipo de disco) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            Secundario (tipo de disco)
        </label>
        <select
            wire:model.defer="almacenamiento_secundario_tipo"
            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                   bg-slate-50 dark:bg-slate-900
                   text-sm px-3 py-2
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
            <option value="">Selecciona</option>
            <option value="N/A">N/A</option>
            <option value="SSD">SSD</option>
            <option value="M.2">M.2</option>
            <option value="M.2 MICRO">M.2 MICRO</option>
            <option value="HDD">HDD</option>
            <option value="MSATA">MSATA</option>
        </select>
        @error('almacenamiento_secundario_tipo')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
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
        {{--  6. BATERÍA        --}}
        {{-- ================== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Batería
                </span>
                <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
            </div>

            {{-- FILA 1: ¿Tiene batería? + Batería 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Tiene batería --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        ¿El equipo tiene batería?
                    </label>
                    <div class="flex items-start gap-2 mt-1">
                        <input
                            type="checkbox"
                            wire:click="toggleBateriaTiene"
                            @checked($bateria_tiene)
                            class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm
                                focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                        >
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Desmarca esta opción si el equipo no tiene batería
                            (escritorio o laptop sin batería instalada).
                        </p>
                    </div>
                </div>

                {{-- Batería 1 - tipo --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Batería 1 – tipo
                    </label>
                    <select
                        wire:model.defer="bateria1_tipo"
                        @disabled(!$bateria_tiene)
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona</option>
                        <option value="Interna">Interna</option>
                        <option value="Externa">Externa</option>
                    </select>
                    @error('bateria1_tipo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Batería 1 - salud --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Batería 1 – salud (%)
                    </label>
                    <input
                        type="number"
                        min="0"
                        max="100"
                        wire:model.defer="bateria1_salud"
                        @disabled(!$bateria_tiene)
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('bateria1_salud')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- FILA 2: Batería 2 opcional --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Espacio (alinear con el checkbox de arriba) --}}
                <div class="md:col-span-2">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        Si el equipo tiene una segunda batería (dock, slice, extra), puedes capturarla aquí.
                    </p>
                </div>

                {{-- Batería 2 – tipo --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Batería 2 – tipo
                    </label>
                    <select
                        wire:model.defer="bateria2_tipo"
                        @disabled(!$bateria_tiene)
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Sin segunda batería</option>
                        <option value="Interna">Interna</option>
                        <option value="Externa">Externa</option>
                    </select>
                    @error('bateria2_tipo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Batería 2 – salud --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Batería 2 – salud (%)
                    </label>
                    <input
                        type="number"
                        min="0"
                        max="100"
                        wire:model.defer="bateria2_salud"
                        @disabled(!$bateria_tiene || !$bateria2_tipo)
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    @error('bateria2_salud')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
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
                           bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                           text-white
                           shadow-sm shadow-blue-800/40
                           backdrop-blur-md
                           transition-all duration-200
                           hover:shadow-blue-500/70 hover:-translate-y-0.5"
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
                           bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                           text-white
                           shadow-sm shadow-blue-800/40
                           backdrop-blur-md
                           transition-all duration-200
                           hover:shadow-blue-500/70 hover:-translate-y-0.5"
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
                           bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                           text-white
                           shadow-sm shadow-blue-800/40
                           backdrop-blur-md
                           transition-all duration-200
                           hover:shadow-blue-500/70 hover:-translate-y-0.5"
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
