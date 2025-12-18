<div class="space-y-6">

    @if (session('success'))
        <div class="px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/40
                    text-emerald-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ===== DATOS DEL LOTE ===== --}}
    <div class="space-y-4">
        <p class="text-[0.75rem] font-semibold tracking-wide text-slate-400 uppercase">
            Datos del lote
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Nombre lote --}}
            <div class="space-y-1.5">
                <label class="text-sm text-slate-300">
                    Nombre de lote <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       wire:model.defer="nombre_lote"
                       placeholder="Ej: A25"
                       class="w-full px-4 py-2 rounded-xl
                              bg-white/70 dark:bg-slate-900/40
                              border border-slate-300/80 dark:border-slate-700
                              text-slate-900 dark:text-slate-100
                              focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                @error('nombre_lote')
                    <p class="text-xs text-red-400 mt-0.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Proveedor --}}
            <div class="space-y-1.5">
                <label class="text-sm text-slate-300">
                    Proveedor <span class="text-red-400">*</span>
                </label>

                <select wire:model.defer="proveedor_id"
                        class="w-full px-4 py-2 rounded-xl
                               bg-white/70 dark:bg-slate-900/40
                               border border-slate-300/80 dark:border-slate-700
                               text-slate-900 dark:text-slate-100
                               focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                    <option value="">Selecciona un proveedor</option>
                    @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}">
                            {{ $prov->abreviacion }} - {{ $prov->nombre_empresa }}
                        </option>
                    @endforeach
                </select>

                @error('proveedor_id')
                    <p class="text-xs text-red-400 mt-0.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fecha --}}
            <div class="space-y-1.5">
                <label class="text-sm text-slate-300">
                    Fecha de llegada
                </label>

                <input type="date"
                       wire:model.defer="fecha_llegada"
                       class="w-full px-4 py-2 rounded-xl
                              bg-white/70 dark:bg-slate-900/40
                              border border-slate-300/80 dark:border-slate-700
                              text-slate-900 dark:text-slate-100
                              focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">

                @error('fecha_llegada')
                    <p class="text-xs text-red-400 mt-0.5">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- Línea --}}
    <div class="border-t border-slate-800/70 pt-1"></div>

    {{-- ===== MODELOS RECIBIDOS ===== --}}
    <div class="space-y-4">

        <div class="flex items-center justify-between">
            <p class="text-[0.75rem] font-semibold tracking-wide text-slate-400 uppercase">
                Modelos recibidos en este lote
            </p>

            <button type="button"
                    wire:click="addModeloRow"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                           bg-white/80 dark:bg-slate-900/60
                           border border-slate-300/70 dark:border-slate-700
                           text-[0.7rem] font-medium text-slate-700 dark:text-slate-100
                           hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                           transition">
                <span class="text-lg leading-none">+</span>
                <span>Agregar modelo</span>
            </button>
        </div>

        @error('modelos')
            <p class="text-xs text-red-400 mt-0.5">{{ $message }}</p>
        @enderror

        <div class="space-y-4">
            @foreach($modelos as $index => $modelo)
                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1.7fr)_minmax(0,2.1fr)_120px_32px] gap-6"
                     wire:key="modelo-{{ $index }}">

                    {{-- Marca --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-sm text-slate-300">Marca</label>
                        @endif
                        <input type="text"
                               wire:model.defer="modelos.{{ $index }}.marca"
                               placeholder="Ej: Dell"
                               class="w-full px-4 py-2 rounded-xl
                                      bg-white/70 dark:bg-slate-900/40
                                      border border-slate-300/80 dark:border-slate-700
                                      text-slate-900 dark:text-slate-100 text-sm
                                      focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                        @error('modelos.'.$index.'.marca')
                            <p class="text-[0.65rem] text-red-400 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Modelo --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-sm text-slate-300">Modelo</label>
                        @endif
                        <input type="text"
                               wire:model.defer="modelos.{{ $index }}.modelo"
                               placeholder="Ej: Precision 5770"
                               class="w-full px-4 py-2 rounded-xl
                                      bg-white/70 dark:bg-slate-900/40
                                      border border-slate-300/80 dark:border-slate-700
                                      text-slate-900 dark:text-slate-100 text-sm
                                      focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                        @error('modelos.'.$index.'.modelo')
                            <p class="text-[0.65rem] text-red-400 mt-0.5">{{ $message }}</p>
                        @enderror

                        @if(!empty($modelo['id']))
                            <p class="text-[0.65rem] text-slate-400 mt-1">
                                Registrados: {{ (int)($modelo['equipos_registrados'] ?? 0) }}
                            </p>
                        @endif
                    </div>

                    {{-- Cantidad --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-sm text-slate-300">Cantidad</label>
                        @endif
                        <input type="number"
                               wire:model.defer="modelos.{{ $index }}.cantidad_recibida"
                               min="1"
                               class="w-full px-4 py-2 rounded-xl
                                      bg-white/70 dark:bg-slate-900/40
                                      border border-slate-300/80 dark:border-slate-700
                                      text-slate-900 dark:text-slate-100 text-sm
                                      focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                        @error('modelos.'.$index.'.cantidad_recibida')
                            <p class="text-[0.65rem] text-red-400 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botón borrar --}}
                    <div class="flex items-center justify-end pt-1">
                        @if(count($modelos) > 1)
                            <button type="button"
                                    wire:click="removeModeloRow({{ $index }})"
                                    class="text-[0.7rem] text-slate-500 hover:text-red-400 transition">
                                ✕
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    </div>

    {{-- BOTÓN ACTUALIZAR --}}
    <div class="pt-2 flex items-center justify-end">
        <button
            type="button"
            wire:click="actualizarLote"
            class="inline-flex items-center justify-center gap-2
                px-6 py-2 rounded-xl
                bg-[#FF9521] text-white text-sm font-semibold
                shadow-lg shadow-orange-900/40
                hover:bg-orange-500 transition"
        >
            Actualizar lote
        </button>
    </div>


</div>
