<div class="p-6 space-y-6 max-w-4xl mx-auto">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Nuevo Despacho a Ventas</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Selecciona los equipos FINALIZADOS que se enviarán al área de Ventas.
        </p>
    </div>

    {{-- STEPS INDICATOR --}}
    <div class="flex items-center gap-2">
        @foreach([1 => 'Configurar', 2 => 'Equipos', 3 => 'Confirmar'] as $num => $label)
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                        {{ $paso >= $num
                            ? 'bg-emerald-500 text-white'
                            : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                {{ $num }}
            </div>
            <span class="text-sm {{ $paso >= $num ? 'text-emerald-400 font-medium' : 'text-slate-500' }}">
                {{ $label }}
            </span>
            @if($num < 3)
            <div class="w-8 h-px {{ $paso > $num ? 'bg-emerald-400' : 'bg-slate-600' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ERROR GLOBAL --}}
    @if($mensajeError)
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        {{ $mensajeError }}
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- PASO 1: Configurar destino --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if($paso === 1)
    <div class="rounded-2xl border border-white/10 dark:border-slate-700/40
                bg-white/60 dark:bg-slate-900/40 backdrop-blur-md shadow-xl p-6 space-y-5">

        <h2 class="font-semibold text-slate-800 dark:text-white text-base">Destino del Despacho</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Sucursal --}}
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1.5">
                    Sucursal de Destino
                </label>
                <select wire:model.live="sucursalDestinoId"
                        class="w-full px-3 py-2.5 rounded-xl text-sm
                               bg-white/10 dark:bg-slate-800/50
                               border border-white/10 dark:border-slate-700/50
                               text-slate-800 dark:text-slate-200
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                    <option value="">— Selecciona —</option>
                    @foreach($this->sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>
                @error('sucursalDestinoId') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Almacén --}}
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1.5">
                    Almacén de Destino
                </label>
                <select wire:model.live="almacenDestinoId"
                        @disabled(!$sucursalDestinoId)
                        class="w-full px-3 py-2.5 rounded-xl text-sm
                               bg-white/10 dark:bg-slate-800/50
                               border border-white/10 dark:border-slate-700/50
                               text-slate-800 dark:text-slate-200
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/50
                               disabled:opacity-50">
                    <option value="">— Selecciona un almacén —</option>
                    @foreach($this->almacenesDestino as $alm)
                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                    @endforeach
                </select>
                @error('almacenDestinoId') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Motivo opcional --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1.5">
                Motivo / Observaciones (opcional)
            </label>
            <textarea wire:model="motivo" rows="3"
                      placeholder="Ej: Equipos listos de la semana, incluye modelos HP y Lenovo..."
                      class="w-full px-3 py-2.5 rounded-xl text-sm resize-none
                             bg-white/10 dark:bg-slate-800/50
                             border border-white/10 dark:border-slate-700/50
                             text-slate-800 dark:text-slate-200
                             placeholder-slate-500
                             focus:outline-none focus:ring-2 focus:ring-emerald-500/50"></textarea>
        </div>

        <div class="flex justify-end">
            <button wire:click="avanzarPaso1"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-gradient-to-r from-emerald-600 to-teal-600
                           text-white font-semibold text-sm shadow-lg
                           hover:from-emerald-500 hover:to-teal-500
                           transition-all duration-200">
                Continuar →
            </button>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- PASO 2: Seleccionar equipos --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if($paso === 2)
    <div class="space-y-4">

        {{-- Resumen del despacho --}}
        @if($despachoActual)
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-sm">
            <span class="text-emerald-400 font-mono font-bold">{{ $despachoActual->folio }}</span>
            <span class="text-slate-400">→</span>
            <span class="text-slate-300">{{ $despachoActual->almacenDestino?->nombre }}</span>
        </div>
        @endif

        {{-- Buscador de equipos --}}
        <div class="rounded-2xl border border-white/10 dark:border-slate-700/40
                    bg-white/60 dark:bg-slate-900/40 backdrop-blur-md shadow-xl p-5 space-y-4">

            <h2 class="font-semibold text-slate-800 dark:text-white text-base">Buscar Equipos FINALIZADOS</h2>

            <div class="relative">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.400ms="busquedaEquipo"
                       type="text"
                       placeholder="Número de serie, marca o modelo..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm
                              bg-white/10 dark:bg-slate-800/50
                              border border-white/10 dark:border-slate-700/50
                              text-slate-800 dark:text-slate-200 placeholder-slate-500
                              focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
            </div>

            {{-- Resultados de búsqueda --}}
            @if(strlen($busquedaEquipo) >= 2)
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($this->equiposDisponibles as $eq)
                <div class="flex items-center justify-between px-4 py-3 rounded-xl
                            bg-slate-800/40 border border-slate-700/30
                            hover:bg-slate-700/40 transition-colors duration-150">
                    <div>
                        <div class="font-medium text-slate-200 text-sm">
                            {{ $eq->marca }} {{ $eq->modelo }}
                        </div>
                        <div class="text-xs text-slate-400 font-mono">{{ $eq->numero_serie }}</div>
                        @if($eq->loteModelo)
                        <div class="text-xs text-slate-500">Lote: {{ $eq->loteModelo->lote?->nombre_lote ?? '—' }}</div>
                        @endif
                    </div>
                    <button wire:click="agregarEquipo({{ $eq->id }})"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                   bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                   hover:bg-emerald-500/30 transition-colors duration-150">
                        + Agregar
                    </button>
                </div>
                @empty
                <p class="text-center text-slate-500 text-sm py-4">No se encontraron equipos FINALIZADOS con ese criterio.</p>
                @endforelse
            </div>
            @elseif($busquedaEquipo)
            <p class="text-slate-500 text-xs">Escribe al menos 2 caracteres para buscar.</p>
            @endif
        </div>

        {{-- Lista de equipos seleccionados --}}
        <div class="rounded-2xl border border-white/10 dark:border-slate-700/40
                    bg-white/60 dark:bg-slate-900/40 backdrop-blur-md shadow-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-white/10 dark:border-slate-700/40 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800 dark:text-white text-base">
                    Equipos en este despacho
                    <span class="ml-2 text-emerald-400 font-bold">{{ count($equiposSeleccionados) }}</span>
                </h2>
            </div>

            @if(empty($equiposSeleccionados))
            <div class="px-5 py-10 text-center text-slate-500 text-sm">
                Aún no has agregado equipos. Búscalos arriba.
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold uppercase tracking-widest text-slate-500
                               border-b border-slate-700/30">
                        <th class="px-5 py-3 text-left">Equipo</th>
                        <th class="px-5 py-3 text-right">Precio Sugerido ($)</th>
                        <th class="px-5 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/20">
                    @foreach($equiposSeleccionados as $i => $item)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-200">{{ $item['marca'] }} {{ $item['modelo'] }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $item['numero_serie'] }}</div>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <input wire:change="actualizarPrecio({{ $i }}, $event.target.value)"
                                   type="number" step="0.01" min="0"
                                   value="{{ $item['precio_sugerido'] ?? '' }}"
                                   placeholder="Sin precio"
                                   class="w-28 px-2 py-1 rounded-lg text-sm text-right
                                          bg-slate-800/60 border border-slate-700/50
                                          text-slate-200 placeholder-slate-600
                                          focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button wire:click="quitarEquipo({{ $item['equipo_id'] }})"
                                    class="text-rose-400 hover:text-rose-300 transition-colors text-xs font-medium">
                                Quitar
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Navegación --}}
        <div class="flex items-center justify-between">
            <button wire:click="regresarPaso"
                    class="px-4 py-2 rounded-xl text-sm text-slate-400 hover:text-slate-200
                           bg-slate-800/40 border border-slate-700/30 transition-colors">
                ← Regresar
            </button>
            <button wire:click="avanzarPaso2"
                    @if(empty($equiposSeleccionados)) disabled @endif
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-gradient-to-r from-emerald-600 to-teal-600
                           text-white font-semibold text-sm shadow-lg
                           hover:from-emerald-500 hover:to-teal-500
                           transition-all duration-200
                           disabled:opacity-50 disabled:cursor-not-allowed">
                Revisar y Enviar →
            </button>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- PASO 3: Confirmar y enviar --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if($paso === 3)
    <div class="rounded-2xl border border-white/10 dark:border-slate-700/40
                bg-white/60 dark:bg-slate-900/40 backdrop-blur-md shadow-xl p-6 space-y-5">

        <h2 class="font-semibold text-slate-800 dark:text-white text-base">Resumen del Despacho</h2>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-500 uppercase tracking-widest">Folio</span>
                <p class="text-emerald-400 font-mono font-bold">{{ $despachoActual?->folio }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-500 uppercase tracking-widest">Destino</span>
                <p class="text-slate-200">{{ $despachoActual?->almacenDestino?->nombre }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-500 uppercase tracking-widest">Total equipos</span>
                <p class="text-slate-200 font-bold text-xl">{{ count($equiposSeleccionados) }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-500 uppercase tracking-widest">Valor total sugerido</span>
                <p class="text-slate-200 font-bold">
                    ${{ number_format(collect($equiposSeleccionados)->sum('precio_sugerido'), 2) }}
                </p>
            </div>
        </div>

        @if($motivo)
        <div>
            <span class="text-xs text-slate-500 uppercase tracking-widest">Motivo</span>
            <p class="text-slate-300 text-sm mt-0.5">{{ $motivo }}</p>
        </div>
        @endif

        <div class="px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm">
            <strong>⚠️ Al enviar:</strong> El despacho se marcará como ENVIADO y quedará pendiente de aprobación
            por el área de Ventas. No podrás modificarlo después de enviarlo.
        </div>

        <div class="flex items-center justify-between">
            <button wire:click="regresarPaso"
                    class="px-4 py-2 rounded-xl text-sm text-slate-400 hover:text-slate-200
                           bg-slate-800/40 border border-slate-700/30 transition-colors">
                ← Regresar
            </button>
            <button wire:click="confirmarEnvio"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                           bg-gradient-to-r from-emerald-600 to-teal-600
                           text-white font-bold text-sm shadow-lg
                           hover:from-emerald-500 hover:to-teal-500
                           transition-all duration-200">
                <span wire:loading.remove wire:target="confirmarEnvio">✓ Enviar a Ventas</span>
                <span wire:loading wire:target="confirmarEnvio">Enviando...</span>
            </button>
        </div>
    </div>
    @endif

</div>
