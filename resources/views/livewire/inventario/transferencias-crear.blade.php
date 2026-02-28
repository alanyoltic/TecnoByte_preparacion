<div class="max-w-6xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                Nueva Transferencia
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Crear movimiento entre almacenes
            </p>
        </div>

        <a href="{{ route('inventario.transferencias') }}"
           class="px-4 py-2 rounded-2xl
                  bg-slate-200/70 dark:bg-slate-800/70
                  text-slate-700 dark:text-slate-200
                  hover:bg-slate-300/70 dark:hover:bg-slate-700/70
                  transition-all">
            Volver
        </a>
    </div>

    {{-- TARJETA PRINCIPAL --}}
    <div class="bg-white/70 dark:bg-slate-950/40
                backdrop-blur-xl
                border border-white/20 dark:border-slate-700/50
                rounded-3xl p-8 shadow-xl space-y-8">

        {{-- SECCIÓN DETALLE --}}
        <div class="space-y-6">

            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Detalle
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Origen --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Almacén Origen
                    </label>

                    <select wire:model.live="almacen_origen_id"
                        class="w-full rounded-2xl
                               bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-blue-500/70">
                        <option value="">Seleccionar...</option>
                        @foreach($almacenesOrigen as $almacen)
                            <option value="{{ $almacen->id }}">
                                {{ $almacen->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Destino --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Almacén Destino
                    </label>

                    <select wire:model="almacen_destino_id"
                        class="w-full rounded-2xl
                               bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-blue-500/70">
                        <option value="">Seleccionar...</option>
                        @foreach($almacenesDestino as $almacen)
                            <option value="{{ $almacen->id }}">
                                {{ $almacen->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fecha --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Fecha Movimiento
                    </label>

                    <input type="date"
                        value="{{ now()->format('Y-m-d') }}"
                        disabled
                        class="w-full rounded-2xl
                               bg-slate-100 dark:bg-slate-800
                               border border-white/60 dark:border-slate-600/70
                               text-slate-500 dark:text-slate-400">
                </div>

            </div>
        </div>

<div class="space-y-6 border-t border-slate-200 dark:border-slate-800 pt-6">

    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
            Productos
        </h2>

        <button type="button"
            wire:click="agregarItem"
            class="px-4 py-2 rounded-xl
                   bg-blue-600 hover:bg-blue-700
                   text-white text-sm font-medium
                   shadow-md shadow-blue-600/40">
            + Agregar
        </button>
    </div>

    <div class="space-y-4">

        @foreach($items as $index => $item)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4
                        bg-white/70 dark:bg-slate-900/50
                        rounded-2xl p-4 border border-slate-200 dark:border-slate-700">

                {{-- Tipo --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Tipo
                    </label>
                    <select wire:model="items.{{ $index }}.tipo"
                        class="w-full rounded-2xl
                            bg-white/90 dark:bg-slate-900/70
                            border border-white/60 dark:border-slate-600/70
                            text-slate-900 dark:text-slate-100
                            focus:ring-2 focus:ring-blue-500/70">
                        <option value="equipo">Equipo</option>
                    </select>
                </div>

                {{-- Item --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Seleccionar
                    </label>

                    <select wire:model.live="items.0.item_id"
                        class="w-full rounded-2xl
                        bg-white/90 dark:bg-slate-900/70
                        border border-white/60 dark:border-slate-600/70
                        text-slate-900 dark:text-slate-100
                        focus:ring-2 focus:ring-blue-500/70">
                        
                        <option value="">-- Selecciona equipo --</option>

                        @foreach($this->equiposDisponibles as $equipo)
                            <option value="{{ $equipo->id }}">
                                ID: {{ $equipo->id }} - Serie: {{ $equipo->numero_serie }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Cantidad --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Cantidad
                    </label>

                    <input type="number"
                        min="1"
                        wire:model="items.{{ $index }}.cantidad"
                        class="w-full rounded-xl border px-3 py-2 text-sm
                               bg-white dark:bg-slate-800">
                </div>

                {{-- Eliminar --}}
                <div class="flex items-end">
                    <button type="button"
                        wire:click="eliminarItem({{ $index }})"
                        class="px-3 py-2 rounded-xl
                               bg-red-500 hover:bg-red-600
                               text-white text-sm">
                        Eliminar
                    </button>
                </div>

            </div>
        @endforeach

    </div>

</div>
        </div>

        {{-- SECCIÓN COMENTARIOS --}}
        <div class="space-y-4 border-t border-slate-200 dark:border-slate-800 pt-6">

            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Comentarios
            </h2>

            <textarea
                class="w-full h-28 rounded-2xl
                       bg-white/90 dark:bg-slate-900/70
                       border border-white/60 dark:border-slate-600/70
                       text-slate-900 dark:text-slate-100
                       focus:ring-2 focus:ring-blue-500/70
                       resize-none"
                placeholder="Notas adicionales sobre esta transferencia..."
            ></textarea>

        </div>

        {{-- BOTÓN PRINCIPAL --}}
        <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">

            <button wire:click="guardar"
                class="px-6 py-3 rounded-2xl
                       bg-blue-600 hover:bg-blue-700
                       text-white font-semibold
                       shadow-lg shadow-blue-600/40
                       transition-all duration-200">
                Guardar Borrador
            </button>

        </div>

    </div>

</div>