<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6 max-w-5xl mx-auto">
        <x-toast />

        {{-- TOPBAR GLASS --}}
        <x-topbar
            title="Nuevo Despacho a Ventas"
            chip="Preparación ➔ Ventas"
            description="Selecciona los equipos FINALIZADOS y accesorios comerciales que se enviarán a Ventas."
        >
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('preparacion.despachos-ventas') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                           bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-800
                           text-slate-700 dark:text-slate-200 border border-slate-300/60 dark:border-white/10
                           shadow-sm hover:shadow transition-all duration-150 active:scale-95"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Volver a Despachos</span>
                </a>
            </div>
        </x-topbar>

        {{-- STEPS INDICATOR --}}
        <div class="relative overflow-hidden rounded-2xl bg-white/70 dark:bg-slate-900/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl p-4 shadow-lg shadow-slate-900/5 dark:shadow-2xl">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                @foreach([1 => '1. Configuración', 2 => '2. Selección de Equipos', 3 => '3. Resumen y Envío'] as $num => $label)
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold transition-all duration-200
                                    {{ $paso >= $num
                                        ? 'bg-[#FF9521] text-slate-950 shadow-md shadow-[#FF9521]/30 ring-2 ring-[#FF9521]/30'
                                        : 'bg-slate-200 dark:bg-slate-800 text-slate-500 border border-slate-300 dark:border-slate-700' }}">
                            @if($paso > $num)
                                ✓
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-xs sm:text-sm font-semibold transition-colors duration-200
                                    {{ $paso >= $num ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if($num < 3)
                        <div class="flex-1 h-0.5 mx-3 sm:mx-4 rounded transition-colors duration-200
                                    {{ $paso > $num ? 'bg-[#FF9521]' : 'bg-slate-300 dark:bg-slate-800' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ERROR GLOBAL --}}
        @if($mensajeError)
            <div class="flex items-center gap-3 px-5 py-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm backdrop-blur-md shadow-lg shadow-rose-950/20 animate-shake">
                <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="flex-1 font-medium">{{ $mensajeError }}</div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- PASO 1: Configurar destino --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($paso === 1)
            <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                        bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 sm:p-8 space-y-6">

                <div class="border-b border-slate-200/80 dark:border-white/10 pb-4">
                    <h2 class="font-bold text-slate-900 dark:text-white text-lg flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                        Destino del Despacho
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Indica a qué sucursal y almacén físico de Ventas se enviarán los equipos finalizados.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Sucursal --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Sucursal de Destino <span class="text-[#FF9521]">*</span>
                        </label>
                        <select wire:model.live="sucursalDestinoId"
                                class="w-full px-4 py-3 rounded-2xl text-sm
                                       bg-slate-50 dark:bg-slate-950/70
                                       border border-slate-300 dark:border-slate-700
                                       text-slate-800 dark:text-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                            <option value="">— Selecciona una sucursal —</option>
                            @foreach($this->sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursalDestinoId') <p class="text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Almacén --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Almacén de Destino <span class="text-[#FF9521]">*</span>
                        </label>
                        <select wire:model.live="almacenDestinoId"
                                @disabled(!$sucursalDestinoId)
                                class="w-full px-4 py-3 rounded-2xl text-sm
                                       bg-slate-50 dark:bg-slate-950/70
                                       border border-slate-300 dark:border-slate-700
                                       text-slate-800 dark:text-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all
                                       disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— Selecciona un almacén —</option>
                            @foreach($this->almacenesDestino as $alm)
                                <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                            @endforeach
                        </select>
                        @error('almacenDestinoId') <p class="text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Motivo opcional --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        Motivo / Observaciones del Envío (opcional)
                    </label>
                    <textarea wire:model="motivo" rows="3"
                              placeholder="Ej: Equipos liberados por control de calidad listos para exhibición..."
                              class="w-full px-4 py-3 rounded-2xl text-sm resize-none
                                     bg-slate-50 dark:bg-slate-950/70
                                     border border-slate-300 dark:border-slate-700
                                     text-slate-800 dark:text-slate-200
                                     placeholder-slate-400 dark:placeholder-slate-500
                                     focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-200/80 dark:border-white/10">
                    <button wire:click="avanzarPaso1"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl
                                   bg-[#FF9521] hover:bg-[#e0821b]
                                   text-slate-950 font-bold text-sm shadow-lg shadow-[#FF9521]/20
                                   active:scale-95 transition-all duration-150">
                        <span>Continuar a Selección</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- PASO 2: Seleccionar equipos y accesorios --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($paso === 2)
            <div class="space-y-6">

                {{-- Banner Resumen del Despacho Borrador --}}
                @if($despachoActual)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 rounded-2xl
                                bg-slate-900/80 border border-slate-700/80 backdrop-blur-xl shadow-xl">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider font-mono bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                                {{ $despachoActual->folio }}
                            </span>
                            <span class="text-slate-400 text-sm">Destino:</span>
                            <span class="text-slate-100 font-semibold text-sm">{{ $despachoActual->almacenDestino?->nombre }}</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Equipos en lista: <span class="text-[#FF9521] font-bold text-sm">{{ count($equiposSeleccionados) }}</span>
                        </div>
                    </div>
                @endif

                {{-- Buscador de Equipos FINALIZADOS --}}
                <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                            bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 space-y-4">

                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                                Buscar Equipos FINALIZADOS
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Busca laptops aprobadas por calidad disponibles en el almacén de preparación.
                            </p>
                        </div>
                    </div>

                    <div class="relative">
                        <svg class="absolute left-4 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.400ms="busquedaEquipo"
                               type="text"
                               placeholder="Buscar por número de serie, marca o modelo..."
                               class="w-full pl-11 pr-4 py-3 rounded-2xl text-sm
                                      bg-slate-50 dark:bg-slate-950/70
                                      border border-slate-300 dark:border-slate-700
                                      text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                    </div>

                    {{-- Resultados de búsqueda de equipos --}}
                    @if(strlen($busquedaEquipo) >= 2)
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @forelse($this->equiposDisponibles as $eq)
                                <div class="flex items-center justify-between px-4 py-3 rounded-2xl
                                            bg-slate-100/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60
                                            hover:border-[#FF9521]/40 transition-all duration-150">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $eq->marca }} {{ $eq->modelo }}</span>
                                            @if($eq->tipo_equipo)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-700 text-slate-300 uppercase">{{ $eq->tipo_equipo }}</span>
                                            @endif
                                            @if($eq->lote_nombre)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#FF9521]/20 text-[#FF9521] border border-[#FF9521]/30">
                                                    📦 {{ $eq->lote_nombre }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                            SN: <span class="text-slate-700 dark:text-slate-200 font-semibold">{{ $eq->numero_serie }}</span>
                                        </div>
                                    </div>
                                    <button wire:click="agregarEquipo({{ $eq->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold
                                                   bg-[#FF9521]/15 hover:bg-[#FF9521] text-[#FF9521] hover:text-slate-950
                                                   border border-[#FF9521]/40 transition-all duration-150">
                                        + Agregar
                                    </button>
                                </div>
                            @empty
                                <div class="p-4 text-center text-slate-400 text-xs bg-slate-900/30 rounded-2xl border border-dashed border-slate-800">
                                    No se encontraron equipos FINALIZADOS con ese criterio en el almacén de preparación.
                                </div>
                            @endforelse
                        </div>
                    @elseif($busquedaEquipo)
                        <p class="text-slate-400 text-xs italic">Escribe al menos 2 caracteres para iniciar la búsqueda.</p>
                    @endif
                </div>

                {{-- Tabla de Equipos Seleccionados --}}
                <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                            bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-white/10 flex items-center justify-between">
                        <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#FF9521]"></span>
                            Equipos en este Despacho
                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                                {{ count($equiposSeleccionados) }}
                            </span>
                        </h2>
                    </div>

                    @if(empty($equiposSeleccionados))
                        <div class="px-6 py-10 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto text-slate-500/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Aún no has agregado ningún equipo. Utiliza el buscador superior para incluir laptops.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400
                                               bg-slate-100/60 dark:bg-slate-950/40 border-b border-slate-200/80 dark:border-white/10">
                                        <th class="px-6 py-3 text-left">Equipo / Serie</th>
                                        <th class="px-6 py-3 text-right">Precio Sugerido ($)</th>
                                        <th class="px-6 py-3 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                                    @foreach($equiposSeleccionados as $i => $item)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                            <td class="px-6 py-3.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['marca'] }} {{ $item['modelo'] }}</span>
                                                    @if(!empty($item['tipo_equipo']))
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-700 text-slate-300 uppercase">{{ $item['tipo_equipo'] }}</span>
                                                    @endif
                                                    @if(!empty($item['lote_nombre']))
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#FF9521]/20 text-[#FF9521] border border-[#FF9521]/30">
                                                            📦 {{ $item['lote_nombre'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">SN: {{ $item['numero_serie'] }}</div>
                                            </td>
                                            <td class="px-6 py-3.5 text-right">
                                                <input wire:change="actualizarPrecio({{ $i }}, $event.target.value)"
                                                       type="number" step="0.01" min="0"
                                                       value="{{ $item['precio_sugerido'] ?? '' }}"
                                                       placeholder="0.00"
                                                       class="w-32 px-3 py-1.5 rounded-xl text-sm text-right
                                                              bg-slate-50 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-700
                                                              text-slate-900 dark:text-slate-100 placeholder-slate-400
                                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                                            </td>
                                            <td class="px-6 py-3.5 text-right">
                                                <button wire:click="quitarEquipo({{ $item['equipo_id'] }})"
                                                        class="text-rose-400 hover:text-rose-300 text-xs font-semibold hover:underline">
                                                    Quitar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Buscador y Lista de Accesorios y Productos Comerciales --}}
                <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                            bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 space-y-4">

                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                Accesorios y Monitores (Comercial)
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Teclados, mouse, cables, monitores o productos para venta.
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                            {{ count($productosSeleccionados) }} artículo(s) agregado(s)
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <svg class="absolute left-4 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input wire:model.live.debounce.400ms="busquedaProducto"
                                   type="text"
                                   placeholder="Buscar por nombre, SKU o categoría..."
                                   class="w-full pl-11 pr-4 py-2.5 rounded-2xl text-sm
                                          bg-slate-50 dark:bg-slate-950/70
                                          border border-slate-300 dark:border-slate-700
                                          text-slate-800 dark:text-slate-200 placeholder-slate-400
                                          focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition-all">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-slate-400 whitespace-nowrap uppercase tracking-wider">Cantidad:</label>
                            <input wire:model="cantidadProducto"
                                   type="number" min="1"
                                   class="w-24 px-3 py-2 rounded-2xl text-sm text-center font-bold
                                          bg-slate-50 dark:bg-slate-950/70
                                          border border-slate-300 dark:border-slate-700
                                          text-slate-800 dark:text-slate-200
                                          focus:outline-none focus:ring-2 focus:ring-[#FF9521]">
                        </div>
                    </div>

                    {{-- Resultados de búsqueda de productos --}}
                    @if(strlen($busquedaProducto) >= 2)
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @forelse($this->productosDisponibles as $prod)
                                <div class="flex items-center justify-between px-4 py-2.5 rounded-2xl
                                            bg-slate-100/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60
                                            hover:border-purple-400/40 transition-colors">
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $prod->nombre }}</div>
                                        <div class="text-xs text-slate-400">
                                            @if($prod->sku) SKU: {{ $prod->sku }} · @endif
                                            ${{ number_format($prod->precio_venta, 2) }}
                                        </div>
                                    </div>
                                    <button wire:click="agregarProducto({{ $prod->id }})"
                                            wire:loading.attr="disabled"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold
                                                   bg-purple-500/20 text-purple-300 border border-purple-500/30
                                                   hover:bg-purple-500/30 transition-colors">
                                        + Agregar
                                    </button>
                                </div>
                            @empty
                                <div class="p-3 text-center text-slate-400 text-xs bg-slate-900/30 rounded-xl">
                                    No se encontraron productos en catálogo.
                                </div>
                            @endforelse
                        </div>
                    @endif

                    {{-- Tabla de productos agregados --}}
                    @if(!empty($productosSeleccionados))
                        <div class="pt-3 border-t border-slate-200/80 dark:border-white/10 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-white/10">
                                        <th class="py-2.5 text-left">Producto / Accesorio</th>
                                        <th class="py-2.5 text-center">Cantidad</th>
                                        <th class="py-2.5 text-right">Precio Sugerido</th>
                                        <th class="py-2.5 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                                    @foreach($productosSeleccionados as $prodItem)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20">
                                            <td class="py-3">
                                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $prodItem['nombre'] }}</span>
                                                @if($prodItem['sku'])
                                                    <span class="text-xs text-slate-400 block font-mono">{{ $prodItem['sku'] }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-center font-bold text-slate-700 dark:text-slate-200">
                                                × {{ $prodItem['cantidad'] }}
                                            </td>
                                            <td class="py-3 text-right text-slate-700 dark:text-slate-200 font-medium">
                                                ${{ number_format($prodItem['precio_sugerido'], 2) }}
                                            </td>
                                            <td class="py-3 text-right">
                                                <button wire:click="quitarProducto({{ $prodItem['producto_id'] }})"
                                                        class="text-rose-400 hover:text-rose-300 text-xs font-semibold hover:underline">
                                                    Quitar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Navegación del Paso 2 --}}
                <div class="flex items-center justify-between pt-2">
                    <button wire:click="regresarPaso"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-slate-100
                                   bg-slate-800/80 border border-slate-700/80 hover:bg-slate-800 transition-all">
                        ← Regresar a Configuración
                    </button>
                    <button wire:click="avanzarPaso2"
                            @if(empty($equiposSeleccionados) && empty($productosSeleccionados)) disabled @endif
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                                   bg-[#FF9521] hover:bg-[#e0821b]
                                   text-slate-950 font-bold text-sm shadow-lg shadow-[#FF9521]/20
                                   active:scale-95 transition-all duration-150
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>Revisar y Enviar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- PASO 3: Confirmar y enviar --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($paso === 3)
            <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                        bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl p-6 sm:p-8 space-y-6">

                <div class="border-b border-slate-200/80 dark:border-white/10 pb-4">
                    <h2 class="font-bold text-slate-900 dark:text-white text-lg flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                        Resumen y Confirmación del Despacho
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Verifica los datos antes de emitir el envío hacia el área de Ventas.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-100/80 dark:bg-slate-800/40 border border-slate-200/60 dark:border-white/5">
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold">Folio</span>
                        <p class="text-[#FF9521] font-mono font-bold text-base mt-1">{{ $despachoActual?->folio }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-100/80 dark:bg-slate-800/40 border border-slate-200/60 dark:border-white/5">
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold">Almacén Destino</span>
                        <p class="text-slate-800 dark:text-slate-100 font-semibold text-sm mt-1">{{ $despachoActual?->almacenDestino?->nombre }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-100/80 dark:bg-slate-800/40 border border-slate-200/60 dark:border-white/5">
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold">Total Artículos</span>
                        <p class="text-slate-800 dark:text-slate-100 font-bold text-sm mt-1">
                            💻 {{ count($equiposSeleccionados) }}
                            @if(count($productosSeleccionados) > 0)
                                · 📦 {{ collect($productosSeleccionados)->sum('cantidad') }}
                            @endif
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-100/80 dark:bg-slate-800/40 border border-slate-200/60 dark:border-white/5">
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold">Valor Sugerido</span>
                        @php
                            $totalEquipos = collect($equiposSeleccionados)->sum('precio_sugerido');
                            $totalProductos = collect($productosSeleccionados)->sum(fn($p) => ($p['precio_sugerido'] ?? 0) * ($p['cantidad'] ?? 1));
                        @endphp
                        <p class="text-emerald-400 font-bold text-base mt-1">
                            ${{ number_format($totalEquipos + $totalProductos, 2) }}
                        </p>
                    </div>
                </div>

                @if($motivo)
                    <div class="p-4 rounded-2xl bg-slate-100/80 dark:bg-slate-800/30 border border-slate-200/60 dark:border-white/5">
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold">Motivo / Observaciones:</span>
                        <p class="text-slate-700 dark:text-slate-300 text-sm mt-1">{{ $motivo }}</p>
                    </div>
                @endif

                <div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong class="font-bold">Aviso Operativo:</strong> Al confirmar, el despacho cambiará su estatus a <strong>ENVIADO</strong> y se notificará al área de Ventas para su recepción y validación física. Los equipos quedarán bloqueados hasta ser recibidos o rechazados.
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-200/80 dark:border-white/10">
                    <button wire:click="regresarPaso"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-slate-100
                                   bg-slate-800/80 border border-slate-700/80 hover:bg-slate-800 transition-all">
                        ← Regresar a Selección
                    </button>
                    <button wire:click="confirmarEnvio"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-7 py-3 rounded-xl
                                   bg-[#FF9521] hover:bg-[#e0821b]
                                   text-slate-950 font-bold text-sm shadow-xl shadow-[#FF9521]/25
                                   active:scale-95 transition-all duration-150">
                        <span wire:loading.remove wire:target="confirmarEnvio">✓ Confirmar y Enviar a Ventas</span>
                        <span wire:loading wire:target="confirmarEnvio" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Enviando...
                        </span>
                    </button>
                </div>
            </div>
        @endif

    </div>
</x-tb-background>
