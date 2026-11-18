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
                <a href="{{ auth()->user()->rutaListadoTransferencias() }}"
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
        {{-- SECCIÓN 1: ALMACENES (FILTRADOS POR SUCURSAL Y ÁREA)              --}}
        {{-- ================================================================ --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                    Detalle de la transferencia
                </h2>
                <span class="text-xs text-slate-400 font-medium">
                    Fecha: {{ now()->format('d/m/Y') }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- BLOQUE ORIGEN --}}
                <div class="rounded-2xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-5 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Origen</span>
                        </div>
                        @if(!$puedeCambiarSucursales)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                Sucursal fija: {{ auth()->user()->sucursal?->nombre ?? 'Tu Sucursal' }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Sucursal Origen --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Sucursal Origen
                            </label>
                            @if($puedeCambiarSucursales)
                                <select wire:model.live="sucursal_origen_id"
                                    class="w-full rounded-xl bg-white dark:bg-slate-950/70
                                           border border-slate-300 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 text-sm py-2 px-3
                                           focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] outline-none transition">
                                    <option value="">-- Todas las sucursales --</option>
                                    @foreach($sucursales as $suc)
                                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" value="{{ auth()->user()->sucursal?->nombre ?? 'Tu Sucursal' }}" disabled
                                    class="w-full rounded-xl bg-slate-200/50 dark:bg-slate-800/50
                                           border border-slate-300/70 dark:border-slate-700/60
                                           text-slate-600 dark:text-slate-400 text-sm py-2 px-3 cursor-not-allowed">
                            @endif
                        </div>

                        {{-- Almacén Origen --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Almacén Origen <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="almacen_origen_id"
                                class="w-full rounded-xl bg-white dark:bg-slate-950/70
                                       border border-slate-300 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 text-sm py-2 px-3
                                       focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] outline-none transition">
                                <option value="">Seleccionar almacén...</option>
                                @foreach($almacenesOrigen as $almacen)
                                    <option value="{{ $almacen->id }}">
                                        {{ $almacen->nombre }} @if($almacen->sucursal) ({{ $almacen->sucursal->nombre }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_origen_id')
                                <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BLOQUE DESTINO --}}
                <div class="rounded-2xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-5 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Destino</span>
                        </div>
                        @if(!$puedeCambiarSucursales)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Sucursal fija: {{ auth()->user()->sucursal?->nombre ?? 'Tu Sucursal' }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Sucursal Destino --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Sucursal Destino
                            </label>
                            @if($puedeCambiarSucursales)
                                <select wire:model.live="sucursal_destino_id"
                                    class="w-full rounded-xl bg-white dark:bg-slate-950/70
                                           border border-slate-300 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 text-sm py-2 px-3
                                           focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] outline-none transition">
                                    <option value="">-- Todas las sucursales --</option>
                                    @foreach($sucursales as $suc)
                                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" value="{{ auth()->user()->sucursal?->nombre ?? 'Tu Sucursal' }}" disabled
                                    class="w-full rounded-xl bg-slate-200/50 dark:bg-slate-800/50
                                           border border-slate-300/70 dark:border-slate-700/60
                                           text-slate-600 dark:text-slate-400 text-sm py-2 px-3 cursor-not-allowed">
                            @endif
                        </div>

                        {{-- Almacén Destino --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Almacén Destino <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="almacen_destino_id"
                                class="w-full rounded-xl bg-white dark:bg-slate-950/70
                                       border border-slate-300 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 text-sm py-2 px-3
                                       focus:ring-2 focus:ring-[#FF9521]/60 focus:border-[#FF9521] outline-none transition">
                                <option value="">Seleccionar almacén...</option>
                                @foreach($almacenesDestino as $almacen)
                                    <option value="{{ $almacen->id }}">
                                        {{ $almacen->nombre }} @if($almacen->sucursal) ({{ $almacen->sucursal->nombre }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_destino_id')
                                <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- SECCIÓN 2: BUSCADOR PREDICTIVO DE EQUIPOS                         --}}
        {{-- ================================================================ --}}
        <div class="space-y-4 border-t border-slate-200 dark:border-slate-800 pt-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span>Equipos</span>
                        <span class="text-xs font-normal text-slate-400">(Búsqueda predictiva o lector de código de barras)</span>
                    </h2>
                </div>
                @if(!$almacen_origen_id)
                    <span class="text-xs text-amber-500 dark:text-amber-400 font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Selecciona primero el almacén origen
                    </span>
                @endif
            </div>

            {{-- Buscador con debounce y sugerencias predictivas --}}
            <div class="relative">
                <div class="flex gap-3 items-start">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                            wire:model.live.debounce.300ms="serialBusqueda"
                            wire:keydown.enter="agregarSerial"
                            @if(!$almacen_origen_id) disabled @endif
                            placeholder="{{ $almacen_origen_id ? 'Escribe número de serie, marca o modelo (o escanea con pistola y presiona Enter)...' : 'Selecciona el almacén origen arriba para buscar equipos...' }}"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                   border border-slate-300 dark:border-slate-700
                                   text-slate-900 dark:text-slate-100 placeholder-slate-400
                                   focus:ring-2 focus:ring-[#FF9521]/70 focus:border-[#FF9521]
                                   pl-11 pr-4 py-3 outline-none transition disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    <button type="button" wire:click="agregarSerial"
                        @if(!$almacen_origen_id) disabled @endif
                        class="px-5 py-3 rounded-2xl bg-[#FF9521] hover:bg-[#e0831a]
                               text-slate-950 font-bold shadow-lg shadow-[#FF9521]/20
                               whitespace-nowrap transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        + Agregar
                    </button>
                </div>

                @if($errorSerial)
                    <p class="text-xs text-red-500 mt-2 font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errorSerial }}
                    </p>
                @endif

                {{-- DESPLEGABLE FLOTANTE DE SUGERENCIAS PREDICTIVAS --}}
                @if(strlen(trim($serialBusqueda)) >= 2 && $almacen_origen_id)
                    <div class="absolute z-30 left-0 right-0 mt-2 rounded-2xl
                                bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl
                                border border-slate-300 dark:border-slate-700/80
                                shadow-2xl p-2 max-h-72 overflow-y-auto space-y-1">
                        @forelse($equiposSugeridos as $sug)
                            <div class="flex items-center justify-between p-3 rounded-xl
                                        hover:bg-slate-100 dark:hover:bg-slate-800/80 transition
                                        border border-transparent hover:border-slate-200 dark:hover:border-slate-700/60">
                                <div class="space-y-0.5">
                                    <div class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                        <span>{{ $sug->marca }} {{ $sug->modelo }}</span>
                                        <span class="font-mono text-xs text-[#FF9521] font-semibold bg-[#FF9521]/10 px-2 py-0.5 rounded-md border border-[#FF9521]/20">
                                            SN: {{ $sug->numero_serie }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ trim(implode(' · ', array_filter([$sug->ram_total, $sug->almacenamiento_principal_capacidad, $sug->almacenamiento_principal_tipo, $sug->tipo_equipo]))) }}
                                    </div>
                                </div>
                                <button type="button"
                                    wire:click="agregarEquipoPorId({{ $sug->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                           bg-[#FF9521]/15 hover:bg-[#FF9521] text-[#FF9521] hover:text-slate-950
                                           border border-[#FF9521]/30 transition">
                                    + Agregar
                                </button>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                No se encontraron equipos en este almacén que coincidan con "<span class="font-semibold text-slate-700 dark:text-slate-200">{{ $serialBusqueda }}</span>".
                            </div>
                        @endforelse
                    </div>
                @endif
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

        {{-- BOTONES GUARDAR --}}
        @error('general')
            <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                {{ $message }}
            </div>
        @enderror

        <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button wire:click="guardar(false)"
                wire:loading.attr="disabled"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700
                       text-slate-200 font-semibold border border-slate-600/60
                       transition-all duration-200 disabled:opacity-50 text-sm">
                <span wire:loading.remove wire:target="guardar(false)">Guardar Borrador</span>
                <span wire:loading wire:target="guardar(false)">Guardando...</span>
            </button>

            <button wire:click="guardar(true)"
                wire:loading.attr="disabled"
                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#FF9521] hover:bg-[#e6841b]
                       text-white font-semibold shadow-lg shadow-orange-500/20
                       transition-all duration-200 disabled:opacity-50 text-sm">
                <span wire:loading.remove wire:target="guardar(true)">Crear y Enviar Transferencia</span>
                <span wire:loading wire:target="guardar(true)">Enviando...</span>
            </button>
        </div>

    </div>
</div>
        </div>

    </div>
</x-tb-background>
</div>
