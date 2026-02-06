@php
    /** @var string $mode create|edit */
    $mode = $mode ?? 'create';

    // Siempre trabajar con $form (Form Object)
    $form = $form ?? null;
@endphp




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
                            wire:model.live="form.lote_id"
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

                        @error('form.lote_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                    </div>

                    {{-- Proveedor (se selecciona automáticamente según el lote) --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Proveedor *
                        </label>
                        <select
                            wire:model="form.proveedor_id"
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
                        @error('form.proveedor_id')
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
                            wire:model.defer="form.numero_serie"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        @error('form.numero_serie')
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
                            wire:model.defer="form.marca"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-100 dark:bg-slate-800
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-0 focus:border-slate-300"
                            readonly
                        >
                        @error('form.marca')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Modelo (del lote seleccionado) --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Modelo *
                        </label>
                        <select
                            wire:model.live="form.lote_modelo_id"

                            wire:change="actualizarModelo($event.target.value)"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @disabled(!$form->lote_id || empty($modelosLote))


                        >
                            <option value="">Selecciona un modelo</option>
                            @foreach($modelosLote as $m)
                                <option value="{{ $m['id'] }}">
                                    {{ $m['marca'] }} {{ $m['modelo'] }}
                                </option>
                            @endforeach
                        </select>

                        @error('form.lote_modelo_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tipo de equipo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Tipo de equipo <span class="text-red-500">*</span>
                        </label>

                        <select
                            wire:model.live="form.tipo_equipo"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Selecciona</option>

                            <option value="LAPTOP">LAPTOP</option>
                            <option value="ESCRITORIO">ESCRITORIO</option>
                            <option value="ALL IN ONE">ALL IN ONE</option>
                            <option value="TABLET">TABLET</option>
                            <option value="2 EN 1">2 EN 1</option>
                            <option value="MICRO PC">MICRO PC</option>
                            <option value="GAMER">GAMER</option>
                        </select>

                        @error('form.tipo_equipo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sistema operativo --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Sistema operativo
                        </label>

                        <select
                            wire:model.defer="form.sistema_operativo"
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

                        @error('form.sistema_operativo')
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
                            wire:model.defer="form.area_tienda"
                            placeholder="Ej. Sucursal Querétaro"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        @error('form.area_tienda')
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
                            wire:model.defer="form.procesador_modelo"
                            placeholder="Ej. i5-8250U"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        @error('form.procesador_modelo')
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
                            wire:model.defer="form.procesador_frecuencia"
                            placeholder="Ej. 1.90 GHz"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900
                                text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        @error('form.procesador_frecuencia')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Generación --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Generación <span class="text-red-500">*</span>
                        </label>

                        <select
                            wire:model.defer="form.procesador_generacion"
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

                        @error('form.procesador_generacion')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Núcleos --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Núcleos <span class="text-red-500">*</span>
                        </label>

                        <select
                            wire:model.defer="form.procesador_nucleos"
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

                        @error('form.procesador_nucleos')
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
                            wire:model.defer="form.ram_total"
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

                        @error('form.ram_total')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tipo RAM --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Tipo de RAM <span class="text-red-500">*</span>
                        </label>

                        <select
                            wire:model.defer="form.ram_tipo"
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

                        @error('form.ram_tipo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    

                                    {{-- RAM máxima expansión --}}
                                    <div>
                                        <label class="block text-sm font-medium mb-1">
                                            RAM máxima expansión
                                        </label>

                                        <select
                                            wire:model.live="form.ram_expansion_max"
                                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                                bg-slate-50 dark:bg-slate-900
                                                text-sm px-3 py-2
                                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            @disabled($form->ram_slots_totales === 0 || $form->ram_slots_totales === '0')



                                        >
                                            <option value="">Selecciona</option>

                                            <option value="0 GB" @disabled(! $form->ram_es_soldada)>
                                                0 GB (SIN EXPANSIÓN)
                                            </option>


                                            @foreach ([8, 12, 16, 24, 32, 48, 64, 96, 128, 192, 256, 512] as $ram)
                                                <option value="{{ $ram }} GB">{{ $ram }} GB</option>
                                            @endforeach
                                        </select>


                                        @error('form.ram_expansion_max')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Slots RAM totales --}}
                                    <div>
                                        <label class="block text-sm font-medium mb-1">
                                            Slots RAM totales
                                        </label>

<select
  wire:model.live="form.ram_slots_totales"
  class="w-full rounded-lg border border-slate-300 dark:border-slate-700
         bg-slate-50 dark:bg-slate-900
         text-sm px-3 py-2
         focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
  @disabled($form->ram_sin_slots)
>

                                        <option value="">Selecciona</option>

    @foreach(range(1,16) as $slots)
        <option value="{{ $slots }}">{{ $slots }} SLOTS</option>
    @endforeach
</select>





                                        @error('form.ram_slots_totales')
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
                                                    wire:model.live="form.ram_es_soldada"
                                                    class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm
                                                        focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                                                />



                                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                                    Marca esta opción si el equipo trae parte de la RAM soldada en placa.
                                                </p>
                                            </div>
                                        </div>

                        {{-- Cantidad de RAM soldada --}}
                        @if($form->ram_es_soldada)
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Cantidad de RAM soldada
                                </label>
                                <select
                                    wire:model.defer="form.ram_cantidad_soldada"
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
                                @error('form.ram_cantidad_soldada')
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
                                wire:model.live="form.ram_sin_slots"
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
                wire:model.defer="form.almacenamiento_principal_capacidad"
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
            @error('form.almacenamiento_principal_capacidad')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Principal (tipo de disco) --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Principal (tipo de disco)
            </label>
            <select
                wire:model.defer="form.almacenamiento_principal_tipo"
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
            @error('form.almacenamiento_principal_tipo')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Secundario (capacidad) --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Secundario (capacidad)
            </label>
            <select
                wire:model.defer="form.almacenamiento_secundario_capacidad"
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
            @error('form.almacenamiento_secundario_capacidad')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Secundario (tipo de disco) --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Secundario (tipo de disco)
            </label>
            <select
                wire:model.defer="form.almacenamiento_secundario_tipo"
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
            @error('form.almacenamiento_secundario_tipo')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>






@php
    $tipo = $tipo_equipo ?? null;
    $isLaptopLike = in_array($tipo, ['LAPTOP','2 EN 1','ALL IN ONE','TABLET'], true);
    $isPcLike     = in_array($tipo, ['ESCRITORIO','MICRO PC','GAMER'], true);
@endphp

<div class="space-y-4">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                TARJETA GRÁFICA
            </span>
            <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
        </div>

        @if($isPcLike)
            <span class="text-[0.7rem] px-2.5 py-1 rounded-full
                bg-slate-900/5 dark:bg-white/5
                border border-slate-200/60 dark:border-white/10
                text-slate-600 dark:text-slate-300">
                PC: puede no tener GPU
            </span>
        @endif
    </div>

    {{-- ===== GPU INTEGRADA (4 campos) ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        {{-- (1) Switch --}}
        <div>
            <label class="block text-sm font-medium mb-1">GPU integrada</label>

            <div class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                bg-slate-50 dark:bg-slate-900
                px-3 py-2 flex items-center justify-between gap-3 min-h-[42px]">

                <div class="leading-tight">
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        {{ $isLaptopLike ? 'Obligatoria en laptop' : 'Opcional en PC' }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ ($isLaptopLike || $form->gpu_integrada_tiene) ? 'Sí' : 'No' }}
                    </span>

                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" class="sr-only peer" wire:model.live="form.gpu_integrada_tiene" @disabled($isLaptopLike)>
                        <div class="w-11 h-6 rounded-full bg-slate-300/70 dark:bg-white/15 peer-checked:bg-indigo-500/80 transition"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white/95 dark:bg-white shadow transition peer-checked:translate-x-5"></div>
                    </label>
                </div>
            </div>

            @error('form.gpu_integrada_tiene') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- (2) Marca --}}
        <div class="@if(!($isLaptopLike || $form->gpu_integrada_tiene)) opacity-60 @endif transition">
            <label class="block text-sm font-medium mb-1">Integrada (marca)</label>

            <select wire:model.live="form.gpu_integrada_marca_mode" @disabled(!($isLaptopLike || $form->gpu_integrada_tiene))
                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                    text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                    disabled:cursor-not-allowed disabled:opacity-60">
                <option value="LISTA">Seleccionar de lista</option>
                <option value="MANUAL">Escribir manual…</option>
            </select>

            @if(($isLaptopLike || $form->gpu_integrada_tiene) && $form->gpu_integrada_marca_mode === 'LISTA')
                <select wire:model.live="form.gpu_integrada_marca"
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                        text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecciona</option>
                    <option value="INTEL">Intel</option>
                    <option value="AMD">AMD</option>
                    <option value="NVIDIA">NVIDIA</option>
                </select>
            @endif

           @if(($isLaptopLike || $form->gpu_integrada_tiene) && $form->gpu_integrada_marca_mode === 'MANUAL')

                <input type="text" wire:model.live="form.gpu_integrada_marca"
                    placeholder="Escribe la marca (ej. Qualcomm, Apple, ATI...)"
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                        text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            @endif

            @error('form.gpu_integrada_marca') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- (3) Modelo --}}
        <div class="@if(!($isLaptopLike || $form->gpu_integrada_tiene)) opacity-60 @endif transition">
            <label class="block text-sm font-medium mb-1">Integrada (modelo)</label>

            <input type="text" wire:model.live="form.gpu_integrada_modelo" @disabled(!($isLaptopLike || $form->gpu_integrada_tiene))
                placeholder="Iris Xe / Radeon Vega / etc."
                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                    text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                    disabled:cursor-not-allowed disabled:opacity-60">

            @error('form.gpu_integrada_modelo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>


    </div> {{-- ✅ CIERRA GRID INTEGRADA --}}

    {{-- ===== GPU DEDICADA (4 campos) ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        {{-- (1) Switch --}}
        <div>
            <label class="block text-sm font-medium mb-1">GPU dedicada</label>

            <div class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                bg-slate-50 dark:bg-slate-900
                px-3 py-2 flex items-center justify-between gap-3 min-h-[42px]">

                <p class="text-xs text-slate-600 dark:text-slate-300">Opcional</p>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $form->gpu_dedicada_tiene ? 'Sí' : 'No' }}
                    </span>

                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" class="sr-only peer" wire:model.live="form.gpu_dedicada_tiene">
                        <div class="w-11 h-6 rounded-full bg-slate-300/70 dark:bg-white/15 peer-checked:bg-indigo-500/80 transition"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white/95 dark:bg-white shadow transition peer-checked:translate-x-5"></div>
                    </label>
                </div>
            </div>

            @error('form.gpu_dedicada_tiene') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- (2) Marca --}}
        <div class="@if(!$form->gpu_dedicada_tiene) opacity-60 @endif transition">
            <label class="block text-sm font-medium mb-1">Dedicada (marca)</label>

            <select wire:model.live="form.gpu_dedicada_marca_mode" @disabled(! $form->gpu_dedicada_tiene)
                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                    text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                    disabled:cursor-not-allowed disabled:opacity-60">
                <option value="LISTA">Seleccionar de lista</option>
                <option value="MANUAL">Escribir manual…</option>
            </select>

            @if($form->gpu_dedicada_tiene && $form->gpu_dedicada_marca_mode === 'LISTA')
                <select wire:model.live="form.gpu_dedicada_marca"
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                        text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecciona</option>
                    <option value="NVIDIA">NVIDIA</option>
                    <option value="AMD">AMD</option>
                    <option value="INTEL">Intel</option>
                </select>
            @endif

            @if($form->gpu_dedicada_tiene && $form->gpu_dedicada_marca_mode === 'MANUAL')
                <input type="text" wire:model.live="form.gpu_dedicada_marca"
                    placeholder="Escribe la marca"
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                        text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            @endif

            @error('form.gpu_dedicada_marca') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- (3) Modelo --}}
        <div class="@if(!$form->gpu_dedicada_tiene) opacity-60 @endif transition">
            <label class="block text-sm font-medium mb-1">Dedicada (modelo)</label>

            <input type="text" wire:model.live="form.gpu_dedicada_modelo" @disabled(! $form->gpu_dedicada_tiene)
                placeholder="RTX 3050 / RX 6600..."
                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                    text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                    disabled:cursor-not-allowed disabled:opacity-60">

            @error('form.gpu_dedicada_modelo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- (4) VRAM --}}
        <div class="@if(!$form->gpu_dedicada_tiene) opacity-60 @endif transition">
            <label class="block text-sm font-medium mb-1">Dedicada (VRAM)</label>

            <div class="grid grid-cols-3 gap-2">
                <input type="number" min="0" wire:model.defer="form.gpu_dedicada_vram" @disabled(! $form->gpu_dedicada_tiene)
                    class="col-span-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                        text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        disabled:cursor-not-allowed disabled:opacity-60"
                    placeholder="4">

                <select wire:model.live="form.gpu_dedicada_vram_unidad" @disabled(! $form->gpu_dedicada_tiene)
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                        text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        disabled:cursor-not-allowed disabled:opacity-60">
                    <option value="MB">MB</option>
                    <option value="GB">GB</option>
                </select>
            </div>
        </div>

    </div> {{-- ✅ CIERRA GRID DEDICADA --}}

</div>




            
                    {{-- ================== --}}
                {{--  CONECTIVIDAD     --}}
                {{-- ================== --}}
                <div class="relative my-6">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Conectividad
                        </span>
                        <div class="flex-1 h-px bg-gradient-to-r
                            from-slate-300/70 via-slate-300/30 to-transparent
                            dark:from-white/20 dark:via-white/10 dark:to-transparent">
                        </div>
                    </div>
                </div>



            {{-- ========================= --}}
            {{--  CONECTIVIDAD + ENTRADA   --}}
            {{-- ========================= --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">



                {{-- PUERTOS DE CONECTIVIDAD --}}
                <div class="space-y-2"
                    x-data="{
                        field: 'form.puertos_conectividad',
                        options: ['WIFI','BLUETOOTH','N/A'],
                        pick: '',
                        selected: [],
                        init(){
                            // cargar valor inicial si existe
                            const v = $wire.get(this.field);
                            if(v){
                                this.selected = v.split(',').map(s => s.trim()).filter(Boolean);
                            }

                            // (opcional) blindaje: si Livewire limpia el valor, limpia chips también
                            this.$watch('selected', () => { /* no-op, solo asegura reactividad */ });
                        },
                        sync(){
                            $wire.set(this.field, (this.selected || []).join(', '), true);
                        },
                        add(){
                            const v = this.pick;
                            if(!v) return;

                            if(v === 'N/A'){
                                this.selected = ['N/A'];
                                this.pick = '';
                                this.sync();
                                return;
                            }

                            // si estaba N/A, lo quitamos
                            this.selected = (this.selected || []).filter(x => x !== 'N/A');

                            if(!this.selected.includes(v)) this.selected.push(v);

                            this.pick = '';
                            this.sync();
                        },
                        remove(v){
                            this.selected = (this.selected || []).filter(x => x !== v);
                            this.sync();
                        },
                        resetAll(){
                            this.pick = '';
                            this.selected = [];
                            $wire.set(this.field, '', true);
                        }
                    }"
                    x-on:reiniciar-ui-selects.window="resetAll()"
                >
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Puertos de conectividad <span class="text-red-500">*</span>
                    </label>

                    <select
                        x-model="pick"
                        @change="add()"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-xs sm:text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona una opción</option>
                        <template x-for="opt in options" :key="opt">
                            <option :value="opt" x-text="opt"></option>
                        </template>
                    </select>

                    <div class="flex flex-wrap gap-2">
                        <template x-if="!selected || selected.length === 0">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Sin selección</span>
                        </template>

                        <template x-for="chip in (selected || [])" :key="chip">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs
                                        border border-slate-200/70 dark:border-white/10
                                        bg-white/60 dark:bg-slate-900/40 backdrop-blur-md">
                                <span class="text-slate-700 dark:text-slate-200" x-text="chip"></span>
                                <button type="button"
                                        class="w-5 h-5 rounded-full bg-rose-500/90 text-white text-[10px]
                                            hover:bg-rose-600 flex items-center justify-center"
                                        @click="remove(chip)">
                                    ✕
                                </button>
                            </span>
                        </template>
                    </div>

                    @error('form.puertos_conectividad')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>




            {{-- DISPOSITIVOS DE ENTRADA --}}
                <div class="space-y-2"
                    x-data="{
                        field: 'form.dispositivos_entrada',
                        options: ['CAMARA WEB','LECTOR CD/DVD','TECLADO EXTENDIDO','TECLADO RETROILUMINADO','N/A'],
                        pick: '',
                        selected: [],
                        init(){
                            const v = $wire.get(this.field);
                            if(v){
                                this.selected = v.split(',').map(s => s.trim()).filter(Boolean);
                            }

                            // (opcional) blindaje
                            this.$watch('selected', () => { /* no-op */ });
                        },
                        sync(){
                            $wire.set(this.field, (this.selected || []).join(', '), true);
                        },
                        add(){
                            const v = this.pick;
                            if(!v) return;

                            if(v === 'N/A'){
                                this.selected = ['N/A'];
                                this.pick = '';
                                this.sync();
                                return;
                            }

                            this.selected = (this.selected || []).filter(x => x !== 'N/A');

                            if(!this.selected.includes(v)) this.selected.push(v);

                            this.pick = '';
                            this.sync();
                        },
                        remove(v){
                            this.selected = (this.selected || []).filter(x => x !== v);
                            this.sync();
                        },
                        resetAll(){
                            this.pick = '';
                            this.selected = [];
                            $wire.set(this.field, '', true);
                        }
                    }"
                    x-on:reiniciar-ui-selects.window="resetAll()"
                >
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Dispositivos de entrada <span class="text-red-500">*</span>
                    </label>

                    <select
                        x-model="pick"
                        @change="add()"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-xs sm:text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Selecciona una opción</option>
                        <template x-for="opt in options" :key="opt">
                            <option :value="opt" x-text="opt"></option>
                        </template>
                    </select>

                    <div class="flex flex-wrap gap-2">
                        <template x-if="!selected || selected.length === 0">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Sin selección</span>
                        </template>

                        <template x-for="chip in (selected || [])" :key="chip">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs
                                        border border-slate-200/70 dark:border-white/10
                                        bg-white/60 dark:bg-slate-900/40 backdrop-blur-md">
                                <span class="text-slate-700 dark:text-slate-200" x-text="chip"></span>
                                <button type="button"
                                        class="w-5 h-5 rounded-full bg-rose-500/90 text-white text-[10px]
                                            hover:bg-rose-600 flex items-center justify-center"
                                        @click="remove(chip)">
                                    ✕
                                </button>
                            </span>
                        </template>
                    </div>

                    @error('form.dispositivos_entrada')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
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

                {{-- 1) Solo el checkbox principal --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        ¿El equipo tiene batería?
                    </label>
                    <div class="flex items-start gap-2 mt-1">
                        <input
                            type="checkbox"
                            wire:model.live="form.bateria_tiene"
                            class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm
                                focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                        >

                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Desmarca esta opción si el equipo no tiene batería (escritorio o laptop sin batería instalada).
                        </p>
                    </div>
                    @error('form.bateria_tiene')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2) Si tiene batería -> Batería 1 + checkbox de segunda --}}
                @if($form->bateria_tiene)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                        {{-- Batería 1 - tipo --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Batería 1 – tipo
                            </label>
                            <select
                                wire:model.defer="form.bateria1_tipo"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                    bg-slate-50 dark:bg-slate-900
                                    text-sm px-3 py-2
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Selecciona</option>
                                <option value="Interna">Interna</option>
                                <option value="Externa">Externa</option>
                            </select>
                            @error('form.bateria1_tipo')
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
                                wire:model.defer="form.bateria1_salud"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                    bg-slate-50 dark:bg-slate-900
                                    text-sm px-3 py-2
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('form.bateria1_salud')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Checkbox segunda batería --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">
                                ¿Tiene segunda batería?
                            </label>
                            <div class="flex items-start gap-2 mt-1">
                        <input
                            type="checkbox"
                            wire:model.live="form.bateria2_tiene"
                            class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm
                                focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700"
                        >

                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    Actívalo si trae batería extra (dock, slice, removible adicional, etc.).
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 3) Si tiene segunda batería -> Batería 2 --}}
                    @if($form->bateria2_tiene)
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                            <div class="md:col-span-2"></div>

                            {{-- Batería 2 - tipo --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Batería 2 – tipo
                                </label>
                                <select
                                    wire:model.defer="form.bateria2_tipo"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Selecciona</option>
                                    <option value="Interna">Interna</option>
                                    <option value="Externa">Externa</option>
                                </select>
                                @error('form.bateria2_tipo')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Batería 2 - salud --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Batería 2 – salud (%)
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    wire:model.defer="form.bateria2_salud"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                @error('form.bateria2_salud')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                @endif
            </div>




    {{-- ================== --}}
    {{--  3. PANTALLA       --}}
    {{-- ================== --}}
    @php
        $tipo = trim((string) ($form->tipo_equipo ?? ''));

        $pantallaIntegrada = in_array($tipo, ['LAPTOP','2 EN 1','ALL IN ONE','TABLET'], true);
        $pantallaExterna   = in_array($tipo, ['ESCRITORIO','MICRO PC','GAMER'], true);
        $pantallaDefinida  = $pantallaIntegrada || $pantallaExterna;
        $detallesEsteticosMonitorCatalogo = [
            'RAYONES LEVES',
            'RAYONES PROFUNDOS',
            'MANCHAS / PUNTOS',
            'PIXEL MUERTO',
            'MARCO GOLPEADO',
            'BASE ROTA / FLOJA',
            'CARCASA CON GRIETAS',
            'FALTA TAPA / EMBELLECEDOR',
            'ETIQUETAS / RESIDUOS',
            'PUNTOS DE LUZ',
            'RAYONES EN DISPLAY LEVES',
            'RAYONES EN CARCASA',
            'BOTONES DESGASTADOS LEVES',
            'GRIETAS LEVES CARCASA',
            'FALTA UNA PARTE DE CARCASA',
            'PINTURA EN CARCASA',
            'BASE RAYADA',
            'BASE CON PINTURA',
        ];

        $detallesFuncionamientoMonitorCatalogo = [
            'FALLA UNA ENTRADA USB',
            'FALLA DOS ENTRADAS USB',
            'FALLA HDMI',
            'FALLA DISPLAY PORT',
            'FALLA VGA',
            'FALLA DVI',
            'FALLA ENTRADA TIPO-C',
            'FALLA EN PLUG DE AUDIO',
            'FALLA BOTONES',
        ];


    @endphp

    <div class="space-y-4" wire:key="pantalla-{{ $form->tipo_equipo ?? 'none' }}">


        {{-- HEADER --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Pantalla
            </span>
            <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>

            <span class="text-[11px] px-2 py-1 rounded-full border
                border-slate-200/70 dark:border-white/10
                bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300">
                @if(!$pantallaDefinida)
                    Sin definir
                @elseif($pantallaIntegrada)
                    Integrada
                @else
                    Externa
                @endif
            </span>
        </div>

        {{-- ================== --}}
        {{-- SIN DEFINIR        --}}
        {{-- ================== --}}
        @if(!$pantallaDefinida)

            <div class="rounded-xl border border-dashed border-slate-300/70 dark:border-slate-700/70
                        bg-white/30 dark:bg-slate-900/30
                        px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                Selecciona primero el <span class="font-semibold">Tipo de equipo</span> para definir si la pantalla es integrada o externa.
            </div>

        {{-- ================== --}}
        {{-- PANTALLA INTEGRADA --}}
        {{-- ================== --}}
        @elseif($pantallaIntegrada)

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-xs font-medium mb-1">Tamaño (pulgadas)</label>
                    <select wire:model.live="form.pantalla_pulgadas"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900 text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Selecciona</option>
                        @foreach(['10','11','11.6','12','12.3','12.5','13.3','14','15.4','15.6','16','17.3','21','22','23','23.8','24','25',] as $p)
                            <option value="{{ $p }}">{{ $p }}"</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1">Resolución</label>
                    <select wire:model.live="form.pantalla_resolucion"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900 text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Selecciona</option>
                        @foreach(['1366x768','1600x900','1920x1080','1920x1200','2560x1440','2880x1800','3200x1800','3840x2160'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <input type="checkbox" wire:model.live="form.pantalla_es_touch"
                            class="rounded border-slate-300 text-indigo-600
                                focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700">
                        Touch
                    </label>
                </div>

            </div>

        {{-- ================== --}}
        {{-- PANTALLA EXTERNA   --}}
        {{-- ================== --}}
        @else

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-xs font-medium mb-1">¿Incluye monitor?</label>
                    <select wire:model.live="form.monitor_incluido"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900 text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Selecciona</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>

                @if(($form->monitor_incluido ?? '') === 'SI')


                    <div>
                        <label class="block text-xs font-medium mb-1">Tamaño (pulgadas)</label>
                        <input type="text" wire:model.live="form.monitor_pulgadas"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900 text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder='Ej. 24"'>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Resolución</label>
                        <input type="text" wire:model.live="form.monitor_resolucion"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                bg-slate-50 dark:bg-slate-900 text-sm px-3 py-2
                                focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Ej. 1920x1080">
                    </div>

                @endif
            </div>

            @if(($form->monitor_incluido ?? '') === 'SI')


                {{-- ================== --}}
                {{-- ENTRADAS DEL MONITOR --}}
                {{-- ================== --}}
                <div class="space-y-3">

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Entradas del monitor
                        </span>

                        <button
                            type="button"
                            wire:click="addMonitorEntrada"
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium
                                bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                text-white
                                shadow-sm shadow-blue-800/40
                                backdrop-blur-md
                                transition-all duration-200
                                hover:shadow-blue-500/70 hover:-translate-y-0.5"
                        >
                            + Añadir entrada
                        </button>
                    </div>

                    @if(empty($form->monitor_entradas_rows))
                        <div class="rounded-xl border border-dashed border-slate-300/70 dark:border-slate-700/70
                                    bg-white/30 dark:bg-slate-900/30
                                    px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                            Sin entradas agregadas.
                        </div>
                    @endif

                    <div class="space-y-2">
                        @foreach($form->monitor_entradas_rows as $index => $row)

                            <div class="grid grid-cols-12 gap-3 items-center">

                                {{-- Tipo (select) --}}
                                <div class="col-span-8">
                                    <select
                                        wire:model.live="form.monitor_entradas_rows.{{ $index }}.tipo"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                            bg-slate-50 dark:bg-slate-900
                                            text-xs sm:text-sm px-3 py-2
                                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="">Tipo de entrada</option>
                                        @foreach($monitorEntradasOptions as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                    @error("form.monitor_entradas_rows.$index.tipo")
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cantidad --}}
                                <div class="col-span-3">
                                    <input
                                        type="number"
                                        min="1"
                                        wire:model.live="form.monitor_entradas_rows.{{ $index }}.cantidad"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                            bg-slate-50 dark:bg-slate-900
                                            text-xs sm:text-sm px-3 py-2
                                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="1"
                                    >
                                    @error("form.monitor_entradas_rows.$index.cantidad")
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Borrar --}}
                                <div class="col-span-1 flex justify-end">
                                    <button
                                        type="button"
                                        wire:click="removeMonitorEntrada({{ $index }})"
                                        class="w-8 h-8 rounded-full bg-rose-500/90 text-white
                                            hover:bg-rose-600 flex items-center justify-center"
                                        title="Quitar"
                                    >
                                        ✕
                                    </button>
                                </div>

                            </div>

                        @endforeach
                    </div>

                </div>

            @endif
            @if(($form->monitor_incluido ?? '') === 'SI')
                {{-- =============================== --}}
                {{-- DETALLES ESTÉTICOS (MONITOR)    --}}
                {{-- =============================== --}}
                <div class="mt-5 space-y-3"
                    x-data="{
                        open:false,
                        q:'',
                        selectedStr: @entangle('form.monitor_detalles_esteticos_checks').live,
                        get selected(){ return (this.selectedStr || '').split(',').map(s=>s.trim()).filter(Boolean); },
                        set selected(v){ this.selectedStr = (v || []).join(', '); },
                        hasNA(){ return this.selected.includes('N/A'); },
                        isOn(item){ return this.selected.includes(item); },
                        toggle(item){
                            let arr = this.selected;
                            if(item === 'N/A'){
                                this.selected = this.hasNA() ? [] : ['N/A'];
                                return;
                            }
                            if(this.hasNA()) return;
                            if(arr.includes(item)) arr = arr.filter(x=>x!==item);
                            else arr.push(item);
                            this.selected = arr;
                        },
                        clearAll(){ this.selected = []; this.q=''; },
                        openModal(){ this.open=true; this.q=''; },
                        closeModal(){ this.open=false; }
                    }"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Detalles estéticos (monitor) <span class="text-red-500">*</span>
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    {{-- Chips --}}
                    <div class="flex flex-wrap gap-2">
                        <template x-if="selected.length === 0">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Sin selección (requerido)</span>
                        </template>

                        <template x-for="chip in selected" :key="chip">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs
                                        border border-slate-200/70 dark:border-white/10
                                        bg-white/60 dark:bg-slate-900/40 backdrop-blur-md">
                                <span class="text-slate-700 dark:text-slate-200" x-text="chip"></span>
                                <button type="button"
                                        class="w-5 h-5 rounded-full bg-rose-500/90 text-white text-[10px]
                                            hover:bg-rose-600 flex items-center justify-center"
                                        @click="toggle(chip)">
                                    ✕
                                </button>
                            </span>
                        </template>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button type="button"
                                @click="openModal()"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                                    bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                    text-white shadow-sm shadow-blue-800/40 backdrop-blur-md
                                    transition-all duration-200 hover:shadow-blue-500/70 hover:-translate-y-0.5">
                            Seleccionar detalles
                        </button>

                        <button type="button"
                                x-show="selected.length"
                                @click="clearAll()"
                                class="text-xs text-slate-500 dark:text-slate-400 hover:text-rose-500 transition">
                            Limpiar
                        </button>
                    </div>

                    {{-- Modal --}}
                    <div x-show="open" x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center"
                        @keydown.escape.window="closeModal()"
                        x-cloak
                    >
                        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeModal()"></div>

                        <div class="relative w-[92%] max-w-3xl max-h-[85vh] overflow-hidden
                                    rounded-2xl border border-white/10
                                    bg-slate-950/70 backdrop-blur-2xl
                                    shadow-2xl shadow-slate-950/60">

                            <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                                <div>
                                    <h4 class="text-base font-semibold text-slate-50">Detalles estéticos (monitor)</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Marca los detalles aplicables.</p>
                                </div>

                                <button type="button" @click="closeModal()"
                                        class="w-9 h-9 rounded-full bg-white/5 hover:bg-white/10
                                            border border-white/10 text-slate-200 flex items-center justify-center">
                                    ✕
                                </button>
                            </div>

                            <div class="p-5 space-y-3">
                                <input type="text"
                                    x-model="q"
                                    placeholder="Buscar… (rayón, base, marco, pixel)"
                                    class="w-full rounded-lg border border-slate-700
                                            bg-slate-900 text-sm px-3 py-2 text-slate-100
                                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                                <div class="max-h-[44vh] overflow-auto pr-1 rounded-xl border border-white/10">
                                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                        {{-- N/A primero --}}
                                        <button type="button"
                                                class="w-full text-left rounded-xl px-3 py-2 text-sm
                                                    border border-slate-800 bg-slate-900/70
                                                    hover:bg-slate-800/60 transition"
                                                :class="isOn('N/A') ? 'ring-2 ring-indigo-500' : ''"
                                                @click="toggle('N/A')">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-slate-200">N/A</span>
                                                <span class="text-xs" :class="isOn('N/A') ? 'text-emerald-400' : 'text-slate-400'"
                                                    x-text="isOn('N/A') ? 'OK' : ''"></span>
                                            </div>
                                        </button>

                                        <template
                                            x-for="item in {{ json_encode($detallesEsteticosMonitorCatalogo) }}
                                                .filter(i => !q || i.toLowerCase().includes(q.toLowerCase()))"
                                            :key="item"
                                        >
                                            <button type="button"
                                                    class="w-full text-left rounded-xl px-3 py-2 text-sm
                                                        border border-slate-800 bg-slate-900/70
                                                        hover:bg-slate-800/60 transition"
                                                    :class="isOn(item) ? 'ring-2 ring-indigo-500' : ''"
                                                    @click="toggle(item)">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-slate-200" x-text="item"></span>
                                                    <span class="text-xs"
                                                        :class="isOn(item) ? 'text-emerald-400' : 'text-slate-400'"
                                                        x-text="isOn(item) ? 'OK' : ''"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-200">Otro (opcional)</label>
                                    <input type="text"
                                        wire:model.defer="form.monitor_detalles_esteticos_otro"
                                        class="w-full rounded-lg border border-slate-700
                                                bg-slate-900 text-sm px-3 py-2 text-slate-100
                                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Describe el detalle…">
                                </div>
                            </div>

                            <div class="px-5 py-4 border-t border-white/10 flex items-center justify-between">
                                <span class="text-xs text-slate-400" x-text="selected.length + ' seleccionados'"></span>
                                <button type="button" @click="closeModal()"
                                        class="rounded-full px-4 py-2 text-sm font-medium
                                            bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                            text-white shadow-md shadow-blue-800/60 hover:shadow-blue-500/80 transition">
                                    Listo
                                </button>
                            </div>

                        </div>
                    </div>
                </div>


            
                {{-- =================================== --}}
                {{-- DETALLES FUNCIONAMIENTO (MONITOR)   --}}
                {{-- =================================== --}}
                <div class="space-y-3"
                    x-data="{
                        open:false,
                        q:'',
                        selectedStr: @entangle('form.monitor_detalles_funcionamiento_checks').live,
                        get selected(){ return (this.selectedStr || '').split(',').map(s=>s.trim()).filter(Boolean); },
                        set selected(v){ this.selectedStr = (v || []).join(', '); },
                        hasNA(){ return this.selected.includes('N/A'); },
                        isOn(item){ return this.selected.includes(item); },
                        toggle(item){
                            let arr = this.selected;
                            if(item === 'N/A'){
                                this.selected = this.hasNA() ? [] : ['N/A'];
                                return;
                            }
                            if(this.hasNA()) return;
                            if(arr.includes(item)) arr = arr.filter(x=>x!==item);
                            else arr.push(item);
                            this.selected = arr;
                        },
                        clearAll(){ this.selected = []; this.q=''; },
                        openModal(){ this.open=true; this.q=''; },
                        closeModal(){ this.open=false; }
                    }"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Detalles de funcionamiento (monitor) <span class="text-red-500">*</span>
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    {{-- Chips --}}
                    <div class="flex flex-wrap gap-2">
                        <template x-if="selected.length === 0">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Sin selección (requerido)</span>
                        </template>

                        <template x-for="chip in selected" :key="chip">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs
                                        border border-slate-200/70 dark:border-white/10
                                        bg-white/60 dark:bg-slate-900/40 backdrop-blur-md">
                                <span class="text-slate-700 dark:text-slate-200" x-text="chip"></span>
                                <button type="button"
                                        class="w-5 h-5 rounded-full bg-rose-500/90 text-white text-[10px]
                                            hover:bg-rose-600 flex items-center justify-center"
                                        @click="toggle(chip)">
                                    ✕
                                </button>
                            </span>
                        </template>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button type="button"
                                @click="openModal()"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                                    bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                    text-white shadow-sm shadow-blue-800/40 backdrop-blur-md
                                    transition-all duration-200 hover:shadow-blue-500/70 hover:-translate-y-0.5">
                            Seleccionar detalles
                        </button>

                        <button type="button"
                                x-show="selected.length"
                                @click="clearAll()"
                                class="text-xs text-slate-500 dark:text-slate-400 hover:text-rose-500 transition">
                            Limpiar
                        </button>
                    </div>

                    {{-- Modal --}}
                    <div x-show="open" x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center"
                        @keydown.escape.window="closeModal()"
                        x-cloak
                    >
                        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeModal()"></div>

                        <div class="relative w-[92%] max-w-3xl max-h-[85vh] overflow-hidden
                                    rounded-2xl border border-white/10
                                    bg-slate-950/70 backdrop-blur-2xl
                                    shadow-2xl shadow-slate-950/60">

                            <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                                <div>
                                    <h4 class="text-base font-semibold text-slate-50">Detalles de funcionamiento (monitor)</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Marca las fallas aplicables.</p>
                                </div>

                                <button type="button" @click="closeModal()"
                                        class="w-9 h-9 rounded-full bg-white/5 hover:bg-white/10
                                            border border-white/10 text-slate-200 flex items-center justify-center">
                                    ✕
                                </button>
                            </div>

                            <div class="p-5 space-y-3">
                                <input type="text"
                                    x-model="q"
                                    placeholder="Buscar… (hdmi, vga, usb, botones)"
                                    class="w-full rounded-lg border border-slate-700
                                            bg-slate-900 text-sm px-3 py-2 text-slate-100
                                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                                <div class="max-h-[44vh] overflow-auto pr-1 rounded-xl border border-white/10">
                                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                        {{-- N/A primero --}}
                                        <button type="button"
                                                class="w-full text-left rounded-xl px-3 py-2 text-sm
                                                    border border-slate-800 bg-slate-900/70
                                                    hover:bg-slate-800/60 transition"
                                                :class="isOn('N/A') ? 'ring-2 ring-indigo-500' : ''"
                                                @click="toggle('N/A')">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-slate-200">N/A</span>
                                                <span class="text-xs" :class="isOn('N/A') ? 'text-emerald-400' : 'text-slate-400'"
                                                    x-text="isOn('N/A') ? 'OK' : ''"></span>
                                            </div>
                                        </button>

                                        <template
                                            x-for="item in {{ json_encode($detallesFuncionamientoMonitorCatalogo) }}
                                                .filter(i => !q || i.toLowerCase().includes(q.toLowerCase()))"
                                            :key="item"
                                        >
                                            <button type="button"
                                                    class="w-full text-left rounded-xl px-3 py-2 text-sm
                                                        border border-slate-800 bg-slate-900/70
                                                        hover:bg-slate-800/60 transition"
                                                    :class="isOn(item) ? 'ring-2 ring-indigo-500' : ''"
                                                    @click="toggle(item)">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-slate-200" x-text="item"></span>
                                                    <span class="text-xs"
                                                        :class="isOn(item) ? 'text-emerald-400' : 'text-slate-400'"
                                                        x-text="isOn(item) ? 'OK' : ''"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-200">Otro (opcional)</label>
                                    <input type="text"
                                        wire:model.defer="form.monitor_detalles_funcionamiento_otro"
                                        class="w-full rounded-lg border border-slate-700
                                                bg-slate-900 text-sm px-3 py-2 text-slate-100
                                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Describe la falla…">
                                </div>
                            </div>

                            <div class="px-5 py-4 border-t border-white/10 flex items-center justify-between">
                                <span class="text-xs text-slate-400" x-text="selected.length + ' seleccionados'"></span>
                                <button type="button" @click="closeModal()"
                                        class="rounded-full px-4 py-2 text-sm font-medium
                                            bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                            text-white shadow-md shadow-blue-800/60 hover:shadow-blue-500/80 transition">
                                    Listo
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            @endif





        @endif
    </div>










            




                        
            



            





                        {{-- Notas generales --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Notas generales
                    </label>
                    <textarea
                        wire:model.defer="form.notas_generales"
                        rows="3"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                            bg-slate-50 dark:bg-slate-900
                            text-sm px-3 py-2
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Anota aquí cualquier detalle importante del equipo, golpes, reparaciones, piezas cambiadas, etc."></textarea>
                    @error('form.notas_generales')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>




                

                {{-- ================== --}}
                {{--  PUERTOS Y EXPANSIONES     --}}
                {{-- ================== --}}
                <div class="relative my-6">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Puertos y Expansiones
                        </span>
                        <div class="flex-1 h-px bg-gradient-to-r
                            from-slate-300/70 via-slate-300/30 to-transparent
                            dark:from-white/20 dark:via-white/10 dark:to-transparent">
                        </div>
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

                @if(empty($form->puertos_usb))
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        No hay puertos USB registrados. Usa el botón "Añadir puerto USB".
                    </p>
                @endif

                @php
                    // Tipos USB ya seleccionados (lowercase, sin espacios extra)
                    $selectedTiposUsb = collect($form->puertos_usb ?? [])
                        ->pluck('tipo')
                        ->filter()
                        ->map(fn($t) => mb_strtolower(trim($t)))
                        ->values()
                        ->all();

                    $usbOptions = [
                        'USB 2.0',
                        'USB 3.0',
                        'USB 3.1',
                        'USB 3.2',
                        'USB-C',
                    ];
                @endphp

                <div class="space-y-2">
                    @foreach($form->puertos_usb as $index => $puerto)
                        @php
                            $current = mb_strtolower(trim($form->puertos_usb[$index]['tipo'] ?? ''));
                        @endphp

                        <div class="grid grid-cols-12 gap-2 items-center">
                            {{-- SELECT tipo --}}
                            <div class="col-span-7 sm:col-span-6">
                                <select
                                    wire:model="form.puertos_usb.{{ $index }}.tipo"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Tipo de puerto</option>

                                    @foreach($usbOptions as $opt)
                                        @php $optKey = mb_strtolower(trim($opt)); @endphp
                                        <option
                                            value="{{ $opt }}"
                                            @disabled(in_array($optKey, $selectedTiposUsb, true) && $current !== $optKey)
                                        >
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- INPUT cantidad --}}
                            <div class="col-span-4 sm:col-span-4">
                                <input
                                    type="number"
                                    min="1"
                                    max="10"
                                    wire:model="form.puertos_usb.{{ $index }}.cantidad"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Cant."
                                >
                            </div>

                            {{-- BOTÓN eliminar --}}
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

                @if(empty($form->puertos_video))
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        No hay puertos de video registrados.
                    </p>
                @endif

                @php
                    // lista de tipos ya seleccionados (en minúsculas)
                    $selectedTiposVideo = collect($form->puertos_video ?? [])
                        ->pluck('tipo')
                        ->filter()
                        ->map(fn($t) => mb_strtolower(trim($t)))
                        ->values()
                        ->all();

                    $videoOptions = [
                        'HDMI',
                        'Mini HDMI',
                        'VGA',
                        'DVI',
                        'DisplayPort',
                        'Mini DisplayPort',
                    ];
                @endphp

                <div class="space-y-2">
                    @foreach($form->puertos_video as $index => $puerto)
                        @php
                            $current = mb_strtolower(trim($form->puertos_video[$index]['tipo'] ?? ''));
                        @endphp

                        <div class="grid grid-cols-12 gap-2 items-center">
                            {{-- SELECT tipo --}}
                            <div class="col-span-7 sm:col-span-6">
                                <select
                                    wire:model="form.puertos_video.{{ $index }}.tipo"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Tipo de puerto</option>

                                    @foreach($videoOptions as $opt)
                                        @php $optKey = mb_strtolower(trim($opt)); @endphp
                                        <option
                                            value="{{ $opt }}"
                                            @disabled(in_array($optKey, $selectedTiposVideo, true) && $current !== $optKey)
                                        >
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- INPUT cantidad (en línea, como tu imagen buena) --}}
                            <div class="col-span-4 sm:col-span-4">
                                <input
                                    type="number"
                                    min="1"
                                    max="10"
                                    wire:model="form.puertos_video.{{ $index }}.cantidad"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Cant."
                                >
                            </div>

                            {{-- BOTÓN eliminar (alineado por fila) --}}
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





                    {{-- ====================== --}}
            {{--  SLOTS ALMACENAMIENTO  --}}
            {{-- ====================== --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Slots almacenamiento
                    </span>
                    <button
                        type="button"
                        wire:click="addSlotAlmacenamiento"
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium
                            bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                            text-white shadow-sm shadow-blue-800/40 backdrop-blur-md
                            transition-all duration-200 hover:shadow-blue-500/70 hover:-translate-y-0.5"
                    >
                        + Añadir slot almacenamiento
                    </button>
                </div>

                @if(empty($form->slots_almacenamiento))
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        No hay slots de almacenamiento registrados.
                    </p>
                @endif

                @php
                    $selectedTiposSlots = collect($form->slots_almacenamiento ?? [])
                        ->pluck('tipo')
                        ->filter()
                        ->map(fn($t) => mb_strtolower(trim($t)))
                        ->values()
                        ->all();

                    $form->slotsOptions = ['SSD','M.2','M.2 MICRO','HDD','MSATA'];
                @endphp

                <div class="space-y-2">
                    @foreach($form->slots_almacenamiento as $index => $form->slot)
                        @php
                            $current = mb_strtolower(trim($form->slots_almacenamiento[$index]['tipo'] ?? ''));
                        @endphp

                        <div class="grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-7 sm:col-span-6">
                                <select
                                    wire:model="form.slots_almacenamiento.{{ $index }}.tipo"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Tipo de slot</option>

                                    @foreach($form->slotsOptions as $opt)
                                        @php $optKey = mb_strtolower(trim($opt)); @endphp
                                        <option
                                            value="{{ $opt }}"
                                            @disabled(in_array($optKey, $selectedTiposSlots, true) && $current !== $optKey)
                                        >
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-4 sm:col-span-4">
                                <input
                                    type="number"
                                    min="1"
                                    max="10"
                                    placeholder="Cant."
                                    wire:model="form.slots_almacenamiento.{{ $index }}.cantidad"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div class="col-span-1 flex justify-end">
                                <button
                                    type="button"
                                    wire:click="removeSlotAlmacenamiento({{ $index }})"
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

                @if(empty($form->lectores))
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        No hay lectores registrados (SD, SIM, eSATA, etc.).
                    </p>
                @endif

                @php
                    $selectedTiposLectores = collect($form->lectores ?? [])
                        ->pluck('tipo')
                        ->filter()
                        ->map(fn($t) => mb_strtolower(trim($t)))
                        ->values()
                        ->all();

                    $form->lectoresOptions = ['SD','microSD','SIM','eSATA','SmartCard','Otro'];
                @endphp

                <div class="space-y-2">
                    @foreach($form->lectores as $index => $lector)
                        @php
                            $current = mb_strtolower(trim($form->lectores[$index]['tipo'] ?? ''));
                        @endphp

                        <div class="grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-4 sm:col-span-3">
                                <select
                                    wire:model="form.lectores.{{ $index }}.tipo"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Tipo</option>

                                    @foreach($form->lectoresOptions as $opt)
                                        @php $optKey = mb_strtolower(trim($opt)); @endphp
                                        <option
                                            value="{{ $opt }}"
                                            @disabled(in_array($optKey, $selectedTiposLectores, true) && $current !== $optKey)
                                        >
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-4 sm:col-span-4">
                                <input
                                    type="number"
                                    min="1"
                                    max="10"
                                    placeholder="Cant."
                                    wire:model="form.lectores.{{ $index }}.cantidad"
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                        bg-slate-50 dark:bg-slate-900
                                        text-xs sm:text-sm px-3 py-2
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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


    {{-- ================== --}}
                {{--  DETALLES ESTÉTICOS --}}
                {{-- ================== --}}
            
                @php
                    $detallesEsteticosCatalogo = [
                        'FALTA DE ANCLAJE DE TORNILLO',
                        'PUNTOS DE LUZ LEVE',
                        'RAYONES PANTALLA LEVES',
                        'MARCAS DE TECLADO EN PANTALLA',
                        'TECLADO DESGASTADO LEVE',
                        'RAYONES CARCASA SUPERIOR',
                        'RAYONES CARCASA INFERIOR',
                        'GRIETAS LEVES CARCASA SUPERIOR',
                        'GRIETAS LEVES CARCASA INFERIOR',
                        'ABOLLADURA GRADO A',
                        'ABOLLADURA GRADO B',
                        'ABOLLADURA GRADO C',
                        'FALTA UNA PARTE A LA CARCASA',
                        'MOUSE PAD DESGASTE LEVE',
                        'REGILLA DE VENTILADOR ROTA',
                        'CARCASA CON PINTURA',
                        'SIN PROTECTOR DE CAMARA',
                        'RAYONES MARCO DE DISPLAY',
                        'MARCO DE DISPLAY AGRIETADO',
                        'DESGASTE MARCO DISPLAY',
                        'DESGASTE DE CARCASA TECLADO',
                        'DESGASTE DE CARCASA SUPERIOR E INFERIOR',
                        'FALTAN GOMAS ANTIDERRAPANTE',
                        'FALTAN PEDAZOS DE GOMA ANTIDERRAPANTE',
                    ];
                @endphp

                <div class="space-y-3"
                    x-data="{
                            open:false,
                            q:'',
                            selected: @entangle('form.detalles_esteticos_checks').live,
                            hasNA(){ return (this.selected || []).includes('N/A'); },
                            isOn(item){ return (this.selected || []).includes(item); },
                            toggle(item){
                                if(!this.selected) this.selected = [];
                                if(item === 'N/A'){
                                    this.selected = this.hasNA() ? [] : ['N/A'];
                                    return;
                                }
                                if(this.hasNA()) return;
                                const i = this.selected.indexOf(item);
                                if(i === -1) this.selected.push(item);
                                else this.selected.splice(i, 1);
                            },
                            clearAll(){ this.selected = []; this.q=''; },

                            
                            openModal(){
                                this.open = true;
                                this.q = '';
                            },
                            closeModal(){
                                this.open = false;
                            }
                            }"


                >
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Detalles estéticos <span class="text-red-500">*</span>
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    {{-- Resumen (chips) --}}
                    <div class="flex flex-wrap gap-2">
                        <template x-if="!selected || selected.length === 0">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Sin selección (requerido)</span>
                        </template>

                        <template x-for="chip in (selected || [])" :key="chip">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs
                                        border border-slate-200/70 dark:border-white/10
                                        bg-white/60 dark:bg-slate-900/40 backdrop-blur-md">
                                <span class="text-slate-700 dark:text-slate-200" x-text="chip"></span>
                                <button type="button"
                                        class="w-5 h-5 rounded-full bg-rose-500/90 text-white text-[10px]
                                            hover:bg-rose-600 flex items-center justify-center"
                                        @click="toggle(chip)">
                                    ✕
                                </button>
                            </span>
                        </template>
                    </div>

                    {{-- Botón abrir modal --}}
                    <div class="flex items-center justify-between pt-1">
                        <button type="button"
                                @click="openModal()"

                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                                    bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                    text-white shadow-sm shadow-blue-800/40 backdrop-blur-md
                                    transition-all duration-200 hover:shadow-blue-500/70 hover:-translate-y-0.5"
                        >
                            Seleccionar detalles
                        </button>

                        <button type="button"
                                x-show="selected && selected.length"
                                @click="clearAll()"
                                class="text-xs text-slate-500 dark:text-slate-400 hover:text-rose-500 transition"
                        >
                            Limpiar
                        </button>
                    </div>

                    {{-- MODAL --}}
                    
                    <div x-show="open" x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center"
                        @keydown.escape.window="closeModal()"

                        x-cloak
                    >
                        {{-- overlay --}}
                        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                            @click="closeModal()"></div>

                        {{-- caja modal --}}
                        <div class="relative w-[92%] max-w-3xl max-h-[85vh] overflow-hidden
                                    rounded-2xl border border-white/10
                                    bg-slate-950/70 backdrop-blur-2xl
                                    shadow-2xl shadow-slate-950/60">

                            {{-- header --}}
                            <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                                <div>
                                    <h4 class="text-base font-semibold text-slate-50">Detalles estéticos</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Marca los detalles aplicables. Puedes buscar para hacerlo rápido.
                                    </p>
                                </div>

                                <button type="button"
                                        @click="closeModal()"

                                        class="w-9 h-9 rounded-full bg-white/5 hover:bg-white/10
                                            border border-white/10 text-slate-200 flex items-center justify-center">
                                    ✕
                                </button>
                            </div>

                            {{-- body --}}
                            <div class="p-5 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                                    <div class="md:col-span-2">
                                        <input type="text"
                                            x-model="q"
                                            placeholder="Buscar… (pantalla, carcasa, goma, abolladura)"
                                            class="w-full rounded-lg border border-slate-700
                                                    bg-slate-900 text-sm px-3 py-2 text-slate-100
                                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <button type="button"
                                            @click="toggle('N/A')"
                                            class="inline-flex items-center justify-between w-full rounded-lg px-3 py-2 text-sm
                                                border border-slate-700 bg-slate-900
                                                hover:bg-slate-800/60 transition"
                                    >
                                        <span class="text-slate-200">N/A</span>
                                        <span class="text-xs"
                                            :class="isOn('N/A') ? 'text-emerald-400' : 'text-slate-400'"
                                            x-text="isOn('N/A') ? 'Seleccionado' : '—'"></span>
                                    </button>
                                </div>

                                {{-- lista scroll --}}
                                <div class="max-h-[44vh] overflow-auto pr-1 rounded-xl border border-white/10">
                                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <template
                                            x-for="item in {{ json_encode($detallesEsteticosCatalogo) }}
                                                .filter(i => !q || i.toLowerCase().includes(q.toLowerCase()))"
                                            :key="item"
                                        >
                                            <button type="button"
                                                    class="w-full text-left rounded-xl px-3 py-2 text-sm
                                                        border border-slate-800 bg-slate-900/70
                                                        hover:bg-slate-800/60 transition"
                                                    :class="isOn(item) ? 'ring-2 ring-indigo-500' : ''"
                                                    @click="toggle(item)"
                                                    :disabled="hasNA()"
                                            >
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-slate-200" x-text="item"></span>
                                                    <span class="text-xs"
                                                        :class="isOn(item) ? 'text-emerald-400' : 'text-slate-400'"
                                                        x-text="isOn(item) ? 'OK' : ''"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- otro --}}
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-200">Otro (opcional)</label>
                                    <input type="text"
                                        wire:model.defer="form.detalles_esteticos_otro"
                                        :disabled="hasNA()"
                                        class="w-full rounded-lg border border-slate-700
                                                bg-slate-900 text-sm px-3 py-2 text-slate-100
                                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Describe el detalle…">
                                </div>
                            </div>

                            {{-- footer --}}
                            <div class="px-5 py-4 border-t border-white/10 flex items-center justify-between">
                                <span class="text-xs text-slate-400"
                                    x-text="(selected?.length || 0) + ' seleccionados'"></span>

                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="closeModal()"

                                            class="rounded-full px-4 py-2 text-sm font-medium
                                                bg-white/5 hover:bg-white/10 border border-white/10
                                                text-slate-200 transition">
                                        Cancelar
                                    </button>

                                    <button type="button"
                                            @click="closeModal()"

                                            class="rounded-full px-4 py-2 text-sm font-medium
                                                bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                                text-white shadow-md shadow-blue-800/60 hover:shadow-blue-500/80
                                                transition">
                                        Listo
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    @error('form.detalles_esteticos_checks')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>




                


                {{-- ========================== --}}
    {{--  DETALLES DE FUNCIONAMIENTO --}}
    {{-- ========================== --}}
    @php
        $detallesFuncionamientoCatalogo = [
            'CAMARA NO FUNCIONA',
            'NO FUNCIONA ENTRADA DE ETHERNET',
            'NO FUNCIONA 1 PUERTO USB',
            'NO FUNCIONAN 2 PUERTOS USB',
            'NO FUNCIONA HDMI',
            'NO FUNCIONA DISPLAY PORT',
            'NO FUNCIONA MINI DISPLAY PORT',
            'NO FUNCIONA MINI HDMI',
            'NO FUNCIONA VGA',
            'NO FUNCIONA DVI',
            'FALLA ENTRADA TIPO-C',
            'FALLA PLUG DE AUDIO',
            'RETROILUMINADO DE TECLADO NO FUNCIONA',
            'TRACKPOINT NO FUNCIONA',
            'NO FUNCIONA BARRA DE DESPLAZAMIENTO',
            'NO FUNCIONA LECTOR DE DISCO',
            'NO FUNCIONA SD',
            'LIGERA DISTORCION BOCINA DERECHA',
            'LIGERA DISTORCION BOCINA IZQUIERDA',
            'NO FUNCIONA MICROFONO',
            'NO FUNCIONA CLICS SUPERIORES',
            'NO CUENTA CON PLUMA TOUCH ORIGINAL',
            'CONTRASEÑA EN BIOS',
        ];
    @endphp

    <div class="space-y-3"
        x-data="{
            open:false,
            q:'',
            selected: @entangle('form.detalles_funcionamiento_checks').live,
            hasNA(){ return (this.selected || []).includes('N/A'); },
            isOn(item){ return (this.selected || []).includes(item); },
            toggle(item){
                if(!this.selected) this.selected = [];
                if(item === 'N/A'){
                    this.selected = this.hasNA() ? [] : ['N/A'];
                    return;
                }
                if(this.hasNA()) return;
                const i = this.selected.indexOf(item);
                if(i === -1) this.selected.push(item);
                else this.selected.splice(i, 1);
            },
            clearAll(){ this.selected = []; this.q=''; }
        }"
    >
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Detalles de funcionamiento <span class="text-red-500">*</span>
            </span>
            <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
        </div>

        {{-- Resumen (chips) --}}
        <div class="flex flex-wrap gap-2">
            <template x-if="!selected || selected.length === 0">
                <span class="text-xs text-slate-500 dark:text-slate-400">Sin selección (requerido)</span>
            </template>

            <template x-for="chip in (selected || [])" :key="chip">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs
                            border border-slate-200/70 dark:border-white/10
                            bg-white/60 dark:bg-slate-900/40 backdrop-blur-md">
                    <span class="text-slate-700 dark:text-slate-200" x-text="chip"></span>
                    <button type="button"
                            class="w-5 h-5 rounded-full bg-rose-500/90 text-white text-[10px]
                                hover:bg-rose-600 flex items-center justify-center"
                            @click="toggle(chip)">
                        ✕
                    </button>
                </span>
            </template>
        </div>

        {{-- Botón abrir modal --}}
        <div class="flex items-center justify-between pt-1">
            <button type="button"
                    @click="open=true; q='';"
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                        bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                        text-white shadow-sm shadow-blue-800/40 backdrop-blur-md
                        transition-all duration-200 hover:shadow-blue-500/70 hover:-translate-y-0.5"
            >
                Seleccionar detalles
            </button>

            <button type="button"
                    x-show="selected && selected.length"
                    @click="clearAll()"
                    class="text-xs text-slate-500 dark:text-slate-400 hover:text-rose-500 transition"
            >
                Limpiar
            </button>
        </div>

        {{-- MODAL --}}
        <div x-show="open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center"
            @keydown.escape.window="open=false"
            x-cloak
        >
            {{-- overlay --}}
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="open=false"></div>

            {{-- caja modal --}}
            <div class="relative w-[92%] max-w-3xl max-h-[85vh] overflow-hidden
                        rounded-2xl border border-white/10
                        bg-slate-950/70 backdrop-blur-2xl
                        shadow-2xl shadow-slate-950/60">

                {{-- header --}}
                <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                    <div>
                        <h4 class="text-base font-semibold text-slate-50">Detalles de funcionamiento</h4>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Marca los detalles aplicables. Puedes buscar para hacerlo rápido.
                        </p>
                    </div>

                    <button type="button"
                            @click="open=false"
                            class="w-9 h-9 rounded-full bg-white/5 hover:bg-white/10
                                border border-white/10 text-slate-200 flex items-center justify-center">
                        ✕
                    </button>
                </div>

                {{-- body --}}
                <div class="p-5 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                        <div class="md:col-span-2">
                            <input type="text"
                                x-model="q"
                                placeholder="Buscar… (usb, hdmi, audio, cámara, bios)"
                                class="w-full rounded-lg border border-slate-700
                                        bg-slate-900 text-sm px-3 py-2 text-slate-100
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <button type="button"
                                @click="toggle('N/A')"
                                class="inline-flex items-center justify-between w-full rounded-lg px-3 py-2 text-sm
                                    border border-slate-700 bg-slate-900
                                    hover:bg-slate-800/60 transition"
                        >
                            <span class="text-slate-200">N/A</span>
                            <span class="text-xs"
                                :class="isOn('N/A') ? 'text-emerald-400' : 'text-slate-400'"
                                x-text="isOn('N/A') ? 'Seleccionado' : '—'"></span>
                        </button>
                    </div>

                    {{-- lista scroll --}}
                    <div class="max-h-[44vh] overflow-auto pr-1 rounded-xl border border-white/10">
                        <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                            <template
                                x-for="item in {{ json_encode($detallesFuncionamientoCatalogo) }}
                                    .filter(i => !q || i.toLowerCase().includes(q.toLowerCase()))"
                                :key="item"
                            >
                                <button type="button"
                                        class="w-full text-left rounded-xl px-3 py-2 text-sm
                                            border border-slate-800 bg-slate-900/70
                                            hover:bg-slate-800/60 transition"
                                        :class="isOn(item) ? 'ring-2 ring-indigo-500' : ''"
                                        @click="toggle(item)"
                                        :disabled="hasNA()"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-200" x-text="item"></span>
                                        <span class="text-xs"
                                            :class="isOn(item) ? 'text-emerald-400' : 'text-slate-400'"
                                            x-text="isOn(item) ? 'OK' : ''"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- otro --}}
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-200">Otro (opcional)</label>
                        <input type="text"
                            wire:model.defer="form.detalles_funcionamiento_otro"
                            :disabled="hasNA()"
                            class="w-full rounded-lg border border-slate-700
                                    bg-slate-900 text-sm px-3 py-2 text-slate-100
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Describe el detalle…">
                    </div>
                </div>

                {{-- footer --}}
                <div class="px-5 py-4 border-t border-white/10 flex items-center justify-between">
                    <span class="text-xs text-slate-400"
                        x-text="(selected?.length || 0) + ' seleccionados'"></span>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="open=false"
                                class="rounded-full px-4 py-2 text-sm font-medium
                                    bg-white/5 hover:bg-white/10 border border-white/10
                                    text-slate-200 transition">
                            Cancelar
                        </button>

                        <button type="button"
                                @click="open=false"
                                class="rounded-full px-4 py-2 text-sm font-medium
                                    bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                    text-white shadow-md shadow-blue-800/60 hover:shadow-blue-500/80
                                    transition">
                            Listo
                        </button>
                    </div>
                </div>

            </div>
        </div>

        @error('form.detalles_funcionamiento_checks')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>





    {{-- ================== --}}
{{--  EXTRAS            --}}
{{-- ================== --}}
<div class="mt-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-slate-200/90">Extras</h3>
        <span class="text-[0.72rem] text-slate-400">Opcionales</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        {{-- Ethernet --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 dark:bg-slate-900/40 backdrop-blur-xl p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Puerto Ethernet
                </span>

                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" class="sr-only"
                           wire:model.live="form.ethernet_tiene">

                    <span class="w-10 h-5 rounded-full bg-white/10 relative transition">
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white/70 transition"
                              x-data
                              x-bind:class="$wire.form.ethernet_tiene ? 'translate-x-5 bg-white' : 'translate-x-0 bg-white/70'">
                        </span>
                    </span>

                    <span class="text-xs text-slate-300/80">
                        {{ $form->ethernet_tiene ? 'Sí' : 'No' }}
                    </span>
                </label>
            </div>

            @if($form->ethernet_tiene)
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-xs text-slate-300/80">¿Es Gigabit?</span>

                    <label class="inline-flex items-center cursor-pointer gap-2">
                        <input type="checkbox" class="sr-only"
                               wire:model.live="form.ethernet_es_gigabit">

                        <span class="w-10 h-5 rounded-full bg-white/10 relative transition">
                            <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white/70 transition"
                                  x-data
                                  x-bind:class="$wire.ethernet_es_gigabit ? 'translate-x-5 bg-white' : 'translate-x-0 bg-white/70'">
                            </span>
                        </span>

                        <span class="text-xs text-slate-300/80">
                            {{ $form->ethernet_es_gigabit ? 'Sí' : 'No' }}
                        </span>
                    </label>
                </div>
            @endif
        </div>

        {{-- Idioma teclado --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 dark:bg-slate-900/40 backdrop-blur-xl p-4 md:col-span-2">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                Idioma del teclado
            </label>

            <select
                wire:model.live="form.teclado_idioma"
                class="mt-2 w-full rounded-xl px-4 py-2.5 text-sm
                       bg-white/5 dark:bg-slate-900/40
                       border border-white/10
                       text-slate-100
                       focus:outline-none focus:ring-2 focus:ring-blue-500/50
                       backdrop-blur-xl"
            >
                <option value="N/A">N/A</option>
                <option value="ES (Latino)">ES (Latino)</option>
                <option value="ES (España)">ES (España)</option>
                <option value="EN (US)">EN (US)</option>
                <option value="EN (UK)">EN (UK)</option>
                <option value="FR">FR</option>
                <option value="DE">DE</option>
                <option value="IT">IT</option>
                <option value="PT">PT</option>
            </select>

            @error('form.teclado_idioma')
                <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>


                        {{-- ================== --}}
                {{--  ESTATUS EQUIPO   --}}
                {{-- ================== --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Estatus del equipo
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Estatus general <span class="text-red-500">*</span>
                            </label>

                            <select
                                wire:model.defer="form.estatus_general"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                                    bg-slate-50 dark:bg-slate-900
                                    text-sm px-3 py-2
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="En Revisión">En Revisión</option>
                                <option value="Pendiente Pieza">Pendiente Pieza</option>
                                <option value="Pendiente Garantía">Pendiente Garantía</option>
                                <option value="Pendiente Deshueso">Pendiente Deshueso</option>
                                <option value="Finalizado">Finalizado</option>
                            </select>

                            @error('form.estatus_general')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>


                



