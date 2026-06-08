<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Editar lote"
            chip="Lotes · Editar lote"
            description="Edita equipos, piezas y características de un lote seleccionado."
        />

        <div class="bg-white/80 dark:bg-slate-950/60
                    border border-slate-200/80 dark:border-white/10
                    backdrop-blur-xl dark:backdrop-blur-2xl
                    rounded-2xl
                    shadow-md shadow-slate-900/10
                    dark:shadow-lg dark:shadow-slate-900/30
                    px-6 py-6 sm:px-8
                    transition-all duration-300 ease-out
                    space-y-6">

    {{-- ===== DATOS DEL LOTE ===== --}}
    <div class="space-y-4">
        <p class="text-[0.75rem] font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
            Datos del lote
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Nombre lote --}}
            <div class="space-y-1.5">
                <label class="text-sm text-slate-700 dark:text-slate-300">
                    Nombre de lote <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       wire:model="nombre_lote"
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
                <label class="text-sm text-slate-700 dark:text-slate-300">
                    Proveedor <span class="text-red-400">*</span>
                </label>

                <select wire:model="proveedor_id"
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
                <label class="text-sm text-slate-700 dark:text-slate-300">
                    Fecha de llegada
                </label>

                <input type="date"
                       wire:model="fecha_llegada"
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
    <div class="border-t border-slate-200 dark:border-slate-800/70 pt-1"></div>

    {{-- ===== MODELOS RECIBIDOS ===== --}}
    <div class="space-y-4">

        <div class="flex items-center justify-between">
            <p class="text-[0.75rem] font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                Modelos recibidos en este lote
            </p>


        </div>

        @error('modelos')
            <p class="text-xs text-red-400 mt-0.5">{{ $message }}</p>
        @enderror

        <div class="space-y-4">
            @foreach($modelos as $index => $modelo)
                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_80px_100px_minmax(0,1fr)_36px_32px] gap-3 items-end"
                     wire:key="modelo-{{ $index }}">

                    {{-- Selector de Marca (Filtro) --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Marca</label>
                        @endif
                        <select wire:model.live="modelos.{{ $index }}.temp_marca"
                                class="w-full px-3 py-2 rounded-xl
                                       bg-slate-50 dark:bg-slate-800/60
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 text-sm
                                       focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                            <option value="">— Seleccionar —</option>
                            @foreach($marcas as $mName)
                                <option value="{{ $mName }}">{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Modelo Oficial --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Modelo Oficial</label>
                        @endif
                        <select wire:model="modelos.{{ $index }}.catalogo_equipo_id"
                                class="w-full px-3 py-2 rounded-xl
                                       bg-slate-50 dark:bg-slate-800/60
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 text-sm
                                       focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none
                                       {{ empty($modelo['temp_marca']) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ empty($modelo['temp_marca']) ? 'disabled' : '' }}>
                            <option value="">— Seleccionar modelo —</option>
                            @foreach($modelo['modelos_filtrados'] ?? [] as $mCat)
                                <option value="{{ $mCat['id'] }}">{{ $mCat['modelo'] }}</option>
                            @endforeach
                        </select>
                        @error('modelos.'.$index.'.catalogo_equipo_id')
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
                            <label class="text-sm text-slate-700 dark:text-slate-300">Cantidad</label>
                        @endif
                        <input type="number"
                               wire:model="modelos.{{ $index }}.cantidad_recibida"
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

                    {{-- Valor unitario --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-sm text-slate-700 dark:text-slate-300">Valor unit. ($)</label>
                        @endif
                        <input type="number"
                               wire:model="modelos.{{ $index }}.valor_unitario"
                               min="0" step="0.01"
                               placeholder="0.00"
                               class="w-full px-3 py-2 rounded-xl
                                      bg-white/70 dark:bg-slate-900/40
                                      border border-slate-300/80 dark:border-slate-700
                                      text-slate-900 dark:text-slate-100 text-sm
                                      focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                        @error('modelos.'.$index.'.valor_unitario')
                            <p class="text-[0.65rem] text-red-400 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Clasificación --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-sm text-slate-700 dark:text-slate-300">Clasificación</label>
                        @endif
                        <select wire:model="modelos.{{ $index }}.clasificacion_puntos_id"
                                class="w-full px-3 py-2 rounded-xl
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 text-sm
                                       focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                            <option value="">— sin clasificar —</option>
                            @foreach($clasificaciones as $c)
                                <option value="{{ $c->id }}">
                                    Tipo {{ $c->clave }} ({{ $c->puntos_base }}pts)
                                </option>
                            @endforeach
                        </select>
                        @if(!empty($modelo['id']) && (int)($modelo['equipos_registrados'] ?? 0) > 0)
                            <p class="text-[0.6rem] text-amber-400 mt-0.5">
                                Cambiar solo afecta equipos sin puntos
                            </p>
                        @endif
                        @error('modelos.'.$index.'.clasificacion_puntos_id')
                            <p class="text-[0.65rem] text-red-400 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Series --}}
                    <div class="space-y-1.5">
                        @if($index === 0)
                            <label class="text-sm text-slate-700 dark:text-slate-300">Serie</label>
                        @endif
                        @php $serieCount = count(array_filter(array_map('trim', $modelos[$index]['numeros_serie'] ?? []), fn($s) => $s !== '')); @endphp
                        <button type="button" wire:click="abrirModalSeries({{ $index }})"
                            class="w-9 h-[38px] rounded-xl flex items-center justify-center
                                   bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700
                                   text-slate-500 dark:text-slate-400
                                   hover:border-[#FF9521] hover:text-[#FF9521] transition
                                   {{ $serieCount > 0 ? 'border-[#FF9521] text-[#FF9521]' : '' }}"
                            title="Números de serie">
                            <span class="text-xs font-bold">{{ $serieCount > 0 ? $serieCount : '#' }}</span>
                        </button>
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

        {{-- Total del lote (reactivo vía $wire.modelos) --}}
        <div class="flex justify-end mt-3"
             x-data="{
                 get total() {
                     const rows = $wire.modelos || [];
                     const sum = rows.reduce((acc, r) => {
                         const v = parseFloat(r.valor_unitario) || 0;
                         const c = parseInt(r.cantidad_recibida) || 0;
                         return acc + v * c;
                     }, 0);
                     return sum.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
                 }
             }">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-xl
                        bg-slate-800/60 border border-slate-700 text-sm">
                <span class="text-slate-400">Valor neto del lote:</span>
                <span class="font-bold text-emerald-400" x-text="total"></span>
            </div>
        </div>

    </div>

    {{-- BOTÓN ACTUALIZAR --}}
    <div class="pt-2 flex items-center justify-end gap-3">

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

    {{-- ── Modal: números de serie ─────────────────────────────────────────── --}}
    @if($modalSeries && $serialesIndex >= 0)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-700
                        shadow-2xl p-6 space-y-4">

                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Números de serie —
                        {{ $modelos[$serialesIndex]['marca'] ?? '' }}
                        {{ $modelos[$serialesIndex]['modelo'] ?? '' }}
                    </h3>
                    <button wire:click="cerrarModalSeries"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">✕</button>
                </div>

                {{-- Series ya en DB (read-only) --}}
                @php $existentes = $this->seriesExistentes(); @endphp
                @if(!empty($existentes))
                    <div class="space-y-1">
                        <p class="text-xs text-slate-400 font-medium">Ya registradas (EN ESPERA):</p>
                        @foreach($existentes as $ex)
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg
                                        bg-slate-100/60 dark:bg-slate-800/40
                                        border border-slate-200 dark:border-slate-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-mono">{{ $ex }}</span>
                                <span class="ml-auto text-[0.6rem] text-emerald-500">EN ESPERA</span>
                            </div>
                        @endforeach
                    </div>
                    <hr class="border-slate-200 dark:border-slate-700">
                    <p class="text-xs text-slate-400">Nuevas series a agregar:</p>
                @else
                    <p class="text-xs text-slate-400">
                        Máximo {{ $modelos[$serialesIndex]['cantidad_recibida'] ?? 0 }} series.
                        Deja vacíos los campos que no apliquen.
                    </p>
                @endif

                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @foreach($modelos[$serialesIndex]['numeros_serie'] ?? [] as $si => $s)
                        <div class="flex gap-2 items-center" wire:key="mser-{{ $serialesIndex }}-{{ $si }}">
                            <span class="text-xs text-slate-400 w-5 text-right shrink-0">{{ $si + 1 }}</span>
                            <input type="text"
                                   wire:model="modelos.{{ $serialesIndex }}.numeros_serie.{{ $si }}"
                                   placeholder="Número de serie..."
                                   class="flex-1 rounded-xl px-3 py-2 text-sm
                                          bg-white/70 dark:bg-slate-900/40
                                          border border-slate-300/80 dark:border-slate-700
                                          text-slate-900 dark:text-slate-100
                                          focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                            @if(count($modelos[$serialesIndex]['numeros_serie'] ?? []) > 1)
                                <button wire:click="quitarSerieModal({{ $si }})"
                                    class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-900/20
                                           border border-rose-300/50 text-rose-500
                                           flex items-center justify-center
                                           hover:bg-rose-100 transition shrink-0">✕</button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center pt-2">
                    @php
                        $maxSeries    = (int)($modelos[$serialesIndex]['cantidad_recibida'] ?? 0);
                        $currentCount = count($modelos[$serialesIndex]['numeros_serie'] ?? []);
                    @endphp
                    @if($currentCount < $maxSeries)
                        <button wire:click="agregarSerieModal"
                            class="text-xs text-[#FF9521] hover:underline transition">
                            + Agregar serie
                        </button>
                    @else
                        <span class="text-xs text-slate-400">Límite alcanzado ({{ $maxSeries }})</span>
                    @endif

                    <button wire:click="cerrarModalSeries"
                        class="px-4 py-2 rounded-xl text-sm font-semibold
                               bg-gradient-to-r from-[#FF9521] to-[#e07d10]
                               text-white shadow hover:shadow-md transition">
                        Listo
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

        </div>

    </div>
</x-tb-background>
</div>
