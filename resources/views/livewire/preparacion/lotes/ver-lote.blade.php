<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Ver lote (Mesa de Trabajo)"
            chip="Lotes > Ver lote"
            description="Ingresa series opcionales y guarda el lote en el sistema."
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
                    Datos del lote (Autogenerado desde Compra)
                </p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Orden Compra --}}
                    <div class="space-y-1.5">
                        <label class="text-sm text-slate-700 dark:text-slate-300">Orden de Compra</label>
                        <input type="text" readonly value="OC-{{ str_pad($lote->compra_inventario_id, 4, '0', STR_PAD_LEFT) }}"
                               class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 font-mono font-bold cursor-not-allowed">
                    </div>
                    {{-- Nombre lote --}}
                    <div class="space-y-1.5">
                        <label class="text-sm text-slate-700 dark:text-slate-300">
                            Nombre de lote
                        </label>
                        <input type="text"
                               wire:model="nombre_lote"
                               class="w-full px-4 py-2 rounded-xl
                                      bg-white/70 dark:bg-slate-900/40
                                      border border-slate-300/80 dark:border-slate-700
                                      text-slate-900 dark:text-slate-100
                                      focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none">
                    </div>

                    {{-- Proveedor --}}
                    <div class="space-y-1.5">
                        <label class="text-sm text-slate-700 dark:text-slate-300">Proveedor</label>
                        <input type="text" readonly value="{{ $proveedor_nombre }}"
                               class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 cursor-not-allowed">
                    </div>

                    {{-- Fecha llegada --}}
                    <div class="space-y-1.5">
                        <label class="text-sm text-slate-700 dark:text-slate-300">Fecha de llegada</label>
                        <input type="date" readonly value="{{ $fecha_llegada }}"
                               class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 cursor-not-allowed">
                    </div>
                </div>
            </div>

            <hr class="border-slate-200/80 dark:border-slate-700/50">

            {{-- ===== EQUIPOS Y MODELOS ===== --}}
            @if(count($modelos) > 0)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[0.75rem] font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Equipos en este lote
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($modelos as $index => $m)
                        <div wire:key="modelo-{{ $index }}"
                             class="relative bg-white/50 dark:bg-slate-900/50
                                    border border-slate-200/60 dark:border-slate-700
                                    rounded-2xl p-4 sm:p-5 shadow-sm">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-8 gap-4 items-end">
                                
                                <div class="space-y-1.5 md:col-span-1">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Marca</label>
                                    @endif
                                    <input type="text" readonly value="{{ $m['marca'] }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm cursor-not-allowed">
                                </div>
                                <div class="space-y-1.5 md:col-span-1">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Modelo</label>
                                    @endif
                                    <input type="text" readonly value="{{ $m['modelo'] }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Cant.</label>
                                    @endif
                                    <input type="number" readonly value="{{ $m['cantidad'] }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm text-center cursor-not-allowed">
                                </div>
                                
                                <div class="space-y-1.5">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Costo ($)</label>
                                    @endif
                                    <input type="text" readonly value="{{ number_format($m['valor_unitario'], 2) }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm text-center cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5 md:col-span-2">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Progreso (Reg. / Usados)</label>
                                    @endif
                                    <div class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" title="Registrados">{{ $m['registrados'] }} reg.</span>
                                        <span class="text-xs text-slate-400">|</span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400" title="Asignados a un técnico">{{ $m['usados'] }} usados</span>
                                    </div>
                                </div>

                                <div class="space-y-1.5 md:col-span-1">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">C. Puntos</label>
                                    @endif
                                    <select wire:model.defer="modelos.{{ $index }}.clasificacion_puntos_id"
                                            class="w-full px-4 py-2 rounded-xl
                                                   bg-white/70 dark:bg-slate-900/80
                                                   border border-slate-300/80 dark:border-slate-700
                                                   text-slate-900 dark:text-slate-100 text-sm
                                                   focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] outline-none transition-all">
                                        <option value="">&mdash; sin clasificar &mdash;</option>
                                        @foreach($clasificaciones as $c)
                                            <option value="{{ $c->id }}">Tipo {{ $c->clave }} ({{ number_format($c->puntos_base, 2) }}pts)</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1.5 md:col-span-1 flex justify-end">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 text-transparent block w-full text-right">Serie</label>
                                    @endif
                                    @php $serieCount = count(array_filter(array_column($m['registros_existentes'] ?? [], 'numero_serie'))) + count(array_filter(array_map('trim', $m['numeros_serie_nuevos'] ?? []))); @endphp
                                    <button type="button" wire:click="abrirModalSeries({{ $index }}, 'equipo')"
                                        class="w-10 h-[38px] rounded-xl flex items-center justify-center
                                               bg-white/70 dark:bg-slate-900/40
                                               border border-slate-300/80 dark:border-slate-700
                                               text-slate-500 dark:text-slate-400
                                               hover:border-[#FF9521] hover:text-[#FF9521] transition
                                               {{ $serieCount > 0 ? 'border-[#FF9521] text-[#FF9521]' : '' }}"
                                        title="Números de serie">
                                        <span class="text-xs font-bold">{{ $serieCount > 0 ? $serieCount : '#' }}</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <hr class="border-slate-200/80 dark:border-slate-700/50">
            @endif

            {{-- ===== PIEZAS ===== --}}
            @if(count($piezas) > 0)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[0.75rem] font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Piezas en este lote
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($piezas as $index => $p)
                        <div wire:key="pieza-{{ $index }}"
                             class="relative bg-white/50 dark:bg-slate-900/50
                                    border border-slate-200/60 dark:border-slate-700
                                    rounded-2xl p-4 sm:p-5 shadow-sm">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-4 items-end">
                                
                                <div class="space-y-1.5 md:col-span-2">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Pieza</label>
                                    @endif
                                    <input type="text" readonly value="{{ $p['nombre'] }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Cant.</label>
                                    @endif
                                    <input type="number" readonly value="{{ $p['cantidad'] }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm text-center cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Costo ($)</label>
                                    @endif
                                    <input type="text" readonly value="{{ number_format($p['costo'], 2) }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm text-center cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5 md:col-span-2">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Progreso (Reg. / Usados)</label>
                                    @endif
                                    <div class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" title="Registrados">{{ $p['registrados'] }} reg.</span>
                                        <span class="text-xs text-slate-400">|</span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400" title="Utilizados/No disponibles">{{ $p['usados'] }} usados</span>
                                    </div>
                                </div>

                                <div class="space-y-1.5 flex justify-end">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 text-transparent block w-full text-right">Serie</label>
                                    @endif
                                    @php $serieCount = count(array_filter(array_column($p['registros_existentes'] ?? [], 'numero_serie'))) + count(array_filter(array_map('trim', $p['numeros_serie_nuevos'] ?? []))); @endphp
                                    <button type="button" wire:click="abrirModalSeries({{ $index }}, 'pieza')"
                                        class="w-10 h-[38px] rounded-xl flex items-center justify-center
                                               bg-white/70 dark:bg-slate-900/40
                                               border border-slate-300/80 dark:border-slate-700
                                               text-slate-500 dark:text-slate-400
                                               hover:border-[#FF9521] hover:text-[#FF9521] transition
                                               {{ $serieCount > 0 ? 'border-[#FF9521] text-[#FF9521]' : '' }}"
                                        title="Números de serie">
                                        <span class="text-xs font-bold">{{ $serieCount > 0 ? $serieCount : '#' }}</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <hr class="border-slate-200/80 dark:border-slate-700/50">
            @endif

            {{-- ===== CARGADORES ===== --}}
            @if(count($cargadores) > 0)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[0.75rem] font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Cargadores en este lote
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($cargadores as $index => $c)
                        <div wire:key="carg-{{ $index }}"
                             class="relative bg-white/50 dark:bg-slate-900/50
                                    border border-slate-200/60 dark:border-slate-700
                                    rounded-2xl p-4 sm:p-5 shadow-sm">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-4 items-end">
                                
                                <div class="space-y-1.5 md:col-span-2">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Artículo</label>
                                    @endif
                                    <input type="text" readonly value="{{ $c['notas'] ?: 'Cargadores Genéricos' }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Cant.</label>
                                    @endif
                                    <input type="number" readonly value="{{ $c['cantidad'] }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm text-center cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Costo ($)</label>
                                    @endif
                                    <input type="text" readonly value="{{ number_format($c['costo'], 2) }}" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-none text-slate-500 text-sm text-center cursor-not-allowed">
                                </div>

                                <div class="space-y-1.5 md:col-span-2">
                                    @if($index === 0)
                                        <label class="text-sm text-slate-700 dark:text-slate-300 text-center block">Progreso (Reg. / Usados)</label>
                                    @endif
                                    <div class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" title="Registrados">{{ $c['registrados'] }} reg.</span>
                                        <span class="text-xs text-slate-400">|</span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400" title="Utilizados/No disponibles">{{ $c['usados'] }} usados</span>
                                    </div>
                                </div>

                                <div class="space-y-1.5 flex justify-end">
                                    @if($index === 0)
                                        <label class="text-[0.7rem] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 text-transparent block w-full text-right">Serie</label>
                                    @endif
                                    @php $serieCount = count(array_filter(array_column($c['registros_existentes'] ?? [], 'numero_serie'))) + count(array_filter(array_map('trim', $c['numeros_serie_nuevos'] ?? []))); @endphp
                                    <button type="button" wire:click="abrirModalSeries({{ $index }}, 'cargador')"
                                        class="w-10 h-[38px] rounded-xl flex items-center justify-center
                                               bg-white/70 dark:bg-slate-900/40
                                               border border-slate-300/80 dark:border-slate-700
                                               text-slate-500 dark:text-slate-400
                                               hover:border-[#FF9521] hover:text-[#FF9521] transition
                                               {{ $serieCount > 0 ? 'border-[#FF9521] text-[#FF9521]' : '' }}"
                                        title="Números de serie">
                                        <span class="text-xs font-bold">{{ $serieCount > 0 ? $serieCount : '#' }}</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <hr class="border-slate-200/80 dark:border-slate-700/50">
            @endif

            {{-- ===== BOTÓN GUARDAR Y ASIGNAR ===== --}}
            <div class="flex justify-end pt-4">
                <button wire:click="guardarLote"
                        class="px-6 py-3 rounded-2xl text-sm font-bold tracking-wide
                               bg-gradient-to-r from-[#FF9521] to-[#e07d10]
                               text-white shadow-lg shadow-[#FF9521]/30
                               hover:shadow-[#FF9521]/50 hover:-translate-y-0.5
                               transition-all duration-300">
                    Guardar y Asignar Lote
                </button>
            </div>

        </div>

    </div>

    {{-- MODAL SERIES --}}
    @if($modalSeries && $serialesIndex >= 0)
        @php
            if ($tipoModal === 'equipo') {
                $itemData = $modelos[$serialesIndex];
                $title = ($itemData['marca'] ?? '') . ' ' . ($itemData['modelo'] ?? '');
            } elseif ($tipoModal === 'pieza') {
                $itemData = $piezas[$serialesIndex];
                $title = $itemData['nombre'] ?? '';
            } elseif ($tipoModal === 'cargador') {
                $itemData = $cargadores[$serialesIndex];
                $title = 'Cargadores';
            }
            $pendientes = (int)($itemData['pendientes'] ?? 0);
            $existentes = $itemData['registros_existentes'] ?? [];
        @endphp
        
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="cerrarModalSeries"></div>
            <div class="relative w-full max-w-4xl rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl flex flex-col max-h-[90vh]">
                
                <div class="flex items-center gap-3 mb-4 shrink-0">
                    <div class="w-10 h-10 rounded-full bg-[#FF9521]/20 flex items-center justify-center text-[#FF9521] shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Series: {{ $title }}</h3>
                        <p class="text-xs text-slate-400">
                            @if($tipoModal === 'equipo')
                                Puedes editar las series de los equipos que a&uacute;n no est&aacute;n asignados.
                            @else
                                Puedes editar las series externas de las unidades que sigan disponibles.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="overflow-y-auto pr-2 my-2 bg-slate-900">
                    @if($tipoModal === 'equipo')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($existentes as $i => $reg)
                            <div class="flex gap-4 items-center p-3 rounded-xl border {{ $reg['bloqueado'] ? 'bg-slate-800/20 border-slate-700/30 opacity-60' : 'bg-slate-800/50 border-slate-700/50' }}">
                                <div class="w-8 text-center text-slate-500 font-mono text-sm py-2">#{{ $i+1 }}</div>
                                <div class="flex-1">
                                    <input type="text"
                                           wire:model.defer="modelos.{{ $serialesIndex }}.registros_existentes.{{ $i }}.numero_serie"
                                           placeholder="Serie (Opcional)"
                                           {{ $reg['bloqueado'] ? 'disabled' : '' }}
                                           class="w-full px-3 py-2 text-sm rounded-lg bg-slate-900 border border-slate-600 text-slate-100 uppercase focus:ring-2 focus:ring-[#FF9521] outline-none disabled:bg-slate-900/50 disabled:border-slate-800 disabled:text-slate-500 disabled:cursor-not-allowed">
                                </div>
                                @if($reg['bloqueado'])
                                <span class="text-[0.65rem] text-slate-400 font-bold px-2 uppercase bg-slate-800 rounded">Usado</span>
                                @endif
                            </div>
                            @endforeach
                            
                            @for($i = 0; $i < $pendientes; $i++)
                            <div class="flex gap-4 items-center bg-slate-800/50 p-3 rounded-xl border border-slate-700/50">
                                <div class="w-8 text-center text-[#FF9521]/70 font-mono text-sm py-2">*{{ count($existentes) + $i + 1 }}</div>
                                <div class="flex-1">
                                    <input type="text"
                                           wire:model.defer="modelos.{{ $serialesIndex }}.numeros_serie_nuevos.{{ $i }}"
                                           placeholder="Nueva Serie (Opcional)"
                                           class="w-full px-3 py-2 text-sm rounded-lg bg-slate-900 border border-slate-600 text-slate-100 uppercase focus:ring-2 focus:ring-[#FF9521] outline-none">
                                </div>
                            </div>
                            @endfor
                        </div>
                    @else
                        <table class="w-full text-left border-collapse text-sm bg-slate-800/40 rounded-xl overflow-hidden shadow-sm border border-slate-700/50">
                            <thead class="bg-slate-800/80">
                                <tr class="border-b border-slate-700 text-slate-400">
                                    <th class="px-4 py-3 w-16 text-center font-medium">#</th>
                                    <th class="px-4 py-3 font-medium">Serie Externa (F&aacute;brica)</th>
                                    <th class="px-4 py-3 font-medium">C&oacute;digo Interno</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                @foreach($existentes as $i => $reg)
                                <tr class="hover:bg-slate-800/60 {{ $reg['bloqueado'] ? 'opacity-60' : '' }}">
                                    <td class="px-4 py-4 text-center text-slate-500 font-mono">{{ $i+1 }}</td>
                                    <td class="px-4 py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            <label class="flex items-center gap-2 {{ $reg['bloqueado'] ? 'cursor-not-allowed' : 'cursor-pointer' }} text-xs">
                                                <input type="checkbox" 
                                                    {{ $reg['bloqueado'] ? 'disabled' : '' }}
                                                    wire:click="toggleExternaFormExistente({{ $i }})" 
                                                    @if($reg['tiene_externa']) checked @endif 
                                                    class="rounded border-slate-600 bg-slate-900 text-[#FF9521] focus:ring-[#FF9521] disabled:opacity-50">
                                                <span class="text-slate-300">Trae serie</span>
                                            </label>
                                            @if($reg['tiene_externa'])
                                                @if($tipoModal === 'pieza')
                                                <input type="text" {{ $reg['bloqueado'] ? 'disabled' : '' }} wire:model.defer="piezas.{{ $serialesIndex }}.registros_existentes.{{ $i }}.numero_serie" class="flex-1 px-3 py-1.5 border rounded-lg bg-slate-900 border-slate-600 text-white uppercase text-xs focus:ring-2 focus:ring-[#FF9521] outline-none disabled:opacity-50">
                                                @else
                                                <input type="text" {{ $reg['bloqueado'] ? 'disabled' : '' }} wire:model.defer="cargadores.{{ $serialesIndex }}.registros_existentes.{{ $i }}.numero_serie" class="flex-1 px-3 py-1.5 border rounded-lg bg-slate-900 border-slate-600 text-white uppercase text-xs focus:ring-2 focus:ring-[#FF9521] outline-none disabled:opacity-50">
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-500 italic flex-1">Sin serie</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-blue-400 font-mono text-xs">{{ $reg['codigo_interno'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                                
                                @for($i = 0; $i < $pendientes; $i++)
                                <tr class="hover:bg-slate-800/60">
                                    <td class="px-4 py-4 text-center text-[#FF9521]/70 font-mono">*{{ count($existentes) + $i + 1 }}</td>
                                    <td class="px-4 py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            @php 
                                                $tieneExt = $tipoModal === 'pieza' ? ($piezas[$serialesIndex]['nuevos_tiene_externa'][$i] ?? false) : ($cargadores[$serialesIndex]['nuevos_tiene_externa'][$i] ?? false);
                                            @endphp
                                            <label class="flex items-center gap-2 cursor-pointer text-xs">
                                                <input type="checkbox" wire:click="toggleExternaFormNuevo({{ $i }})" @if($tieneExt) checked @endif class="rounded border-slate-600 bg-slate-900 text-[#FF9521] focus:ring-[#FF9521]">
                                                <span class="text-slate-300">Trae serie</span>
                                            </label>
                                            @if($tieneExt)
                                                @if($tipoModal === 'pieza')
                                                <input type="text" wire:model.defer="piezas.{{ $serialesIndex }}.numeros_serie_nuevos.{{ $i }}" class="flex-1 px-3 py-1.5 border rounded-lg bg-slate-900 border-slate-600 text-white uppercase text-xs focus:ring-2 focus:ring-[#FF9521] outline-none" placeholder="Escanear...">
                                                @else
                                                <input type="text" wire:model.defer="cargadores.{{ $serialesIndex }}.numeros_serie_nuevos.{{ $i }}" class="flex-1 px-3 py-1.5 border rounded-lg bg-slate-900 border-slate-600 text-white uppercase text-xs focus:ring-2 focus:ring-[#FF9521] outline-none" placeholder="Escanear...">
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-500 italic flex-1">Sin serie</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-[#FF9521] font-mono text-xs bg-[#FF9521]/10 px-2 py-1 rounded border border-[#FF9521]/20">Autogenerada (TB-XXXX)</span>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3 shrink-0">
                    <button wire:click="cerrarModalSeries"
                            class="px-6 py-2 rounded-xl text-sm font-semibold
                                   bg-gradient-to-r from-[#FF9521] to-[#e07d10]
                                   text-white shadow-lg hover:shadow-md transition">
                        Listo
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-tb-background>
</div>
