<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Nueva transferencia"
            chip="Inventario · Transferencias"
            description="Crea transferencias de inventario entre almacenes."
        >
            <x-slot name="right">
                <a href="{{ route('inventario.transferencias') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                          bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700
                          text-xs font-medium text-slate-700 dark:text-slate-200
                          hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                    ← Volver
                </a>
            </x-slot>
        </x-topbar>

        <div class="max-w-6xl mx-auto space-y-8">



    {{-- TARJETA PRINCIPAL --}}
    <div class="bg-white/70 dark:bg-slate-950/40 backdrop-blur-xl
                border border-white/20 dark:border-slate-700/50
                rounded-3xl p-8 shadow-xl space-y-8">

        {{-- ================================================================ --}}
        {{-- SECCIÓN 1: ALMACENES                                              --}}
        {{-- ================================================================ --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Detalle de la transferencia
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Origen --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Almacén Origen
                    </label>
                    <select wire:model.live="almacen_origen_id"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-blue-500/70">
                        <option value="">Seleccionar...</option>
                        @foreach($almacenesOrigen as $almacen)
                            <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                        @endforeach
                    </select>
                    @error('almacen_origen_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Destino --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Almacén Destino
                    </label>
                    <select wire:model="almacen_destino_id"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-blue-500/70">
                        <option value="">Seleccionar...</option>
                        @foreach($almacenesDestino as $almacen)
                            <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                        @endforeach
                    </select>
                    @error('almacen_destino_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha (informativa) --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Fecha
                    </label>
                    <input type="text" value="{{ now()->format('d/m/Y') }}" disabled
                        class="w-full rounded-2xl bg-slate-100 dark:bg-slate-800
                               border border-white/60 dark:border-slate-600/70
                               text-slate-500 dark:text-slate-400 px-3 py-2">
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- SECCIÓN 2: EQUIPOS SERIALIZADOS                                   --}}
        {{-- ================================================================ --}}
        <div class="space-y-4 border-t border-slate-200 dark:border-slate-800 pt-6">

            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Equipos
                <span class="text-sm font-normal text-slate-400">(por número de serie)</span>
            </h2>

            {{-- Buscador de serial --}}
            <div class="flex gap-3 items-start">
                <div class="flex-1">
                    <input type="text"
                        wire:model="serialBusqueda"
                        wire:keydown.enter="agregarSerial"
                        placeholder="Escribe el número de serie y presiona Enter o Agregar..."
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-blue-500/70 px-4 py-2.5">
                    @if($errorSerial)
                        <p class="text-xs text-red-500 mt-1">{{ $errorSerial }}</p>
                    @endif
                </div>
                <button type="button" wire:click="agregarSerial"
                    class="px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700
                           text-white font-semibold shadow-md shadow-blue-600/30
                           whitespace-nowrap transition-all">
                    + Agregar
                </button>
            </div>

            {{-- Lista de equipos agregados --}}
            @if(count($equiposAgregados) > 0)
                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-900/80">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Serie</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Marca</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Modelo</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Specs</th>
                                <th class="px-4 py-2 text-center font-semibold text-slate-600 dark:text-slate-300">Cant.</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equiposAgregados as $i => $eq)
                                <tr class="border-t border-slate-200 dark:border-slate-700
                                           hover:bg-white/50 dark:hover:bg-slate-800/50 transition">
                                    <td class="px-4 py-2 font-mono font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $eq['serie'] }}
                                    </td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-300">{{ $eq['marca'] }}</td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-300">{{ $eq['modelo'] }}</td>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 text-xs">{{ $eq['descripcion'] }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-xs font-bold">1</span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" wire:click="quitarEquipo({{ $i }})"
                                            class="text-red-500 hover:text-red-700 font-semibold text-xs transition">
                                            Quitar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500 italic">
                    Aún no has agregado equipos.
                </p>
            @endif
        </div>

        {{-- ================================================================ --}}
        {{-- SECCIÓN 3: CONSUMIBLES GENÉRICOS                                  --}}
        {{-- ================================================================ --}}
        <div class="space-y-4 border-t border-slate-200 dark:border-slate-800 pt-6">

            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Consumibles / Accesorios
                <span class="text-sm font-normal text-slate-400">(cargadores, cables, etc.)</span>
            </h2>

            @if(!$almacen_origen_id)
                <p class="text-sm text-amber-500">Selecciona primero el almacén origen para ver los consumibles disponibles.</p>
            @else
                {{-- Selector de consumible + cantidad --}}
                <div class="flex gap-3 items-start flex-wrap">
                    <div class="flex-1 min-w-48">
                        <select wire:model="consumibleSeleccionado"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                   border border-white/60 dark:border-slate-600/70
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-blue-500/70">
                            <option value="">Seleccionar consumible...</option>
                            @foreach($this->consumiblesDisponibles as $inv)
                                <option value="{{ $inv->consumible_id }}">
                                    {{ $inv->consumible->nombre }} — Stock: {{ $inv->cantidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-28">
                        <input type="number" min="1" wire:model="consumibleCantidad"
                            placeholder="Cant."
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                   border border-white/60 dark:border-slate-600/70
                                   text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-blue-500/70 px-3 py-2.5">
                    </div>

                    <button type="button" wire:click="agregarConsumible"
                        class="px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700
                               text-white font-semibold shadow-md shadow-blue-600/30
                               whitespace-nowrap transition-all">
                        + Agregar
                    </button>
                </div>

                @if($errorConsumible)
                    <p class="text-xs text-red-500">{{ $errorConsumible }}</p>
                @endif

                {{-- Lista de consumibles agregados --}}
                @if(count($consumiblesAgregados) > 0)
                    <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100 dark:bg-slate-900/80">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Consumible</th>
                                    <th class="px-4 py-2 text-center font-semibold text-slate-600 dark:text-slate-300">Cantidad</th>
                                    <th class="px-4 py-2 text-center font-semibold text-slate-600 dark:text-slate-300">Stock disponible</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consumiblesAgregados as $i => $cons)
                                    <tr class="border-t border-slate-200 dark:border-slate-700
                                               hover:bg-white/50 dark:hover:bg-slate-800/50 transition">
                                        <td class="px-4 py-2 font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $cons['nombre'] }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="px-3 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50
                                                         text-blue-800 dark:text-blue-200 text-xs font-bold">
                                                {{ $cons['cantidad'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-center text-slate-500 dark:text-slate-400 text-xs">
                                            {{ $cons['stock'] }} en almacén
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" wire:click="quitarConsumible({{ $i }})"
                                                class="text-red-500 hover:text-red-700 font-semibold text-xs transition">
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-slate-400 dark:text-slate-500 italic">
                        Aún no has agregado consumibles.
                    </p>
                @endif
            @endif
        </div>

        {{-- ================================================================ --}}
        {{-- SECCIÓN 4: OBSERVACIONES                                          --}}
        {{-- ================================================================ --}}
        <div class="space-y-3 border-t border-slate-200 dark:border-slate-800 pt-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Observaciones
            </h2>
            <textarea wire:model="observaciones" rows="3"
                class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                       border border-white/60 dark:border-slate-600/70
                       text-slate-900 dark:text-slate-100
                       focus:ring-2 focus:ring-blue-500/70 resize-none px-4 py-3"
                placeholder="Notas adicionales sobre esta transferencia..."></textarea>
        </div>

        {{-- Error global de items --}}
        @error('items')
            <p class="text-sm text-red-500 font-semibold">{{ $message }}</p>
        @enderror

        {{-- ================================================================ --}}
        {{-- RESUMEN RÁPIDO                                                    --}}
        {{-- ================================================================ --}}
        @if(count($equiposAgregados) > 0 || count($consumiblesAgregados) > 0)
            <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/50
                        border border-slate-200 dark:border-slate-700 p-4 flex gap-6">
                <div class="text-sm text-slate-600 dark:text-slate-300">
                    <span class="font-bold text-slate-900 dark:text-slate-50">{{ count($equiposAgregados) }}</span>
                    equipo(s) serializado(s)
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-300">
                    <span class="font-bold text-slate-900 dark:text-slate-50">{{ count($consumiblesAgregados) }}</span>
                    tipo(s) de consumible(s)
                </div>
            </div>
        @endif

        {{-- BOTÓN GUARDAR --}}
        <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
            <button wire:click="guardar"
                wire:loading.attr="disabled"
                class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700
                       text-white font-semibold shadow-lg shadow-blue-600/40
                       transition-all duration-200 disabled:opacity-50">
                <span wire:loading.remove wire:target="guardar">Guardar Borrador</span>
                <span wire:loading wire:target="guardar">Guardando...</span>
            </button>
        </div>

    </div>
</div>
        </div>

    </div>
</x-tb-background>
</div>
