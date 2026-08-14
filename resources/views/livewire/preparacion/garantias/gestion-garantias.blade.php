<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        <x-topbar
            title="Garantías Externas"
            chip="Preparación · Garantías"
            description="Gestiona las garantías enviadas al proveedor. Registra reparaciones, reemplazos y rechazos."
        />

        {{-- ══════════════════════════════════════════════════════
             TARJETAS RESUMEN
        ══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- Por Enviar --}}
            <button wire:click="setTab('por_enviar')"
                class="rounded-2xl px-5 py-4 text-left transition-all duration-200
                       hover:-translate-y-1 border
                       {{ $tabActivo === 'por_enviar'
                           ? 'bg-rose-50/90 dark:bg-rose-950/40 border-rose-400 shadow-lg shadow-rose-500/20'
                           : 'bg-white/60 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-700 hover:border-rose-300' }}">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-600 dark:text-rose-300">Por Enviar</p>
                <p class="mt-2 text-3xl font-bold text-rose-700 dark:text-rose-200">{{ $this->contadores['por_enviar'] }}</p>
            </button>

            {{-- En Trámite --}}
            <button wire:click="setTab('en_tramite')"
                class="rounded-2xl px-5 py-4 text-left transition-all duration-200
                       hover:-translate-y-1 border
                       {{ $tabActivo === 'en_tramite'
                           ? 'bg-amber-50/90 dark:bg-amber-950/40 border-amber-400 shadow-lg shadow-amber-500/20'
                           : 'bg-white/60 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-700 hover:border-amber-300' }}">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">En Trámite (Enviado)</p>
                <p class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-200">{{ $this->contadores['en_tramite'] }}</p>
            </button>

            {{-- Resueltas --}}
            <button wire:click="setTab('resueltas')"
                class="rounded-2xl px-5 py-4 text-left transition-all duration-200
                       hover:-translate-y-1 border
                       {{ $tabActivo === 'resueltas'
                           ? 'bg-emerald-50/90 dark:bg-emerald-950/40 border-emerald-400 shadow-lg shadow-emerald-500/20'
                           : 'bg-white/60 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-700 hover:border-emerald-300' }}">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Resueltas</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-200">{{ $this->contadores['resueltas'] }}</p>
            </button>
        </div>

        {{-- ══════════════════════════════════════════════════════
             FILTROS
        ══════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input wire:model.live.debounce.350ms="busqueda"
                    type="text"
                    placeholder="Buscar por serial, marca o modelo..."
                    class="w-full rounded-xl px-4 py-2.5 text-sm bg-white/70 dark:bg-slate-900/40
                           border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                           focus:ring-2 focus:ring-rose-400 outline-none" />
            </div>
            <select wire:model.live="filtroProveedor"
                class="rounded-xl px-3 py-2.5 text-sm bg-white/70 dark:bg-slate-900/40
                       border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                       focus:ring-2 focus:ring-rose-400 outline-none min-w-[200px]">
                <option value="">Todos los proveedores</option>
                @foreach($this->proveedores as $prov)
                    <option value="{{ $prov->id }}">{{ $prov->display_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- ══════════════════════════════════════════════════════
             TABLA
        ══════════════════════════════════════════════════════ --}}
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 overflow-hidden
                    bg-white/60 dark:bg-slate-900/40 backdrop-blur-xl shadow-sm">

            {{-- Encabezado --}}
            <div class="grid grid-cols-12 gap-2 px-5 py-3 bg-slate-50/80 dark:bg-slate-800/60
                        border-b border-slate-200/60 dark:border-slate-700/60
                        text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <div class="col-span-3">Equipo</div>
                <div class="col-span-2">Proveedor</div>
                <div class="col-span-2">Defecto</div>
                <div class="col-span-2">Técnico / Fecha</div>
                <div class="col-span-1 text-center">Días</div>
                <div class="col-span-1 text-center">Estatus</div>
                <div class="col-span-1 text-right">Acción</div>
            </div>

            @forelse($this->garantias as $garantia)
                @php
                    $fechaReferencia = $tabActivo === 'en_tramite' && $garantia->fecha_envio 
                                       ? $garantia->fecha_envio 
                                       : $garantia->created_at;
                    $dias = (int) $fechaReferencia->diffInDays(now());
                    
                    $diasColor = match(true) {
                        $dias <= 7  => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-700',
                        $dias <= 15 => 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-700',
                        default     => 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 border-rose-200 dark:border-rose-700',
                    };
                    $estatusBadge = match($garantia->estatus) {
                        'RESUELTA'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'CANCELADA' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                        default     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                    };
                    $resolucionLabel = match($garantia->tipo_resolucion) {
                        'REPARADO'            => '🔧 Reparado',
                        'REEMPLAZADO'         => '🔄 Reemplazado',
                        'RECHAZADO_PROVEEDOR' => '❌ Rechazado',
                        default               => null,
                    };
                @endphp
                <div class="grid grid-cols-12 gap-2 px-5 py-4 items-center
                            border-b border-slate-100/80 dark:border-slate-800/60 last:border-b-0
                            hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors duration-150">

                    {{-- Equipo --}}
                    <div class="col-span-3 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                            {{ $garantia->equipo?->numero_serie ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ $garantia->equipo?->marca }} {{ $garantia->equipo?->modelo }}
                        </p>
                        <p class="text-[0.65rem] text-slate-400 dark:text-slate-500 truncate">
                            Lote: {{ $garantia->equipo?->loteModelo?->lote?->nombre ?? '—' }}
                        </p>
                    </div>

                    {{-- Proveedor --}}
                    <div class="col-span-2 min-w-0">
                        <p class="text-sm text-slate-700 dark:text-slate-300 truncate">
                            {{ $garantia->proveedor?->display_name ?? '—' }}
                        </p>
                    </div>

                    {{-- Defecto --}}
                    <div class="col-span-2 min-w-0">
                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
                            {{ $garantia->descripcion_defecto }}
                        </p>
                    </div>

                    {{-- Técnico / Fecha --}}
                    <div class="col-span-2 min-w-0">
                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">
                            {{ $garantia->reportadoPor?->nombre_inicial ?? '—' }}
                        </p>
                        <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">
                            {{ $garantia->created_at->format('d/m/Y') }}
                        </p>
                        @if($garantia->resueltoPor)
                            <p class="text-[0.65rem] text-emerald-500 dark:text-emerald-400 truncate mt-0.5">
                                ✓ {{ $garantia->resueltoPor->nombre_inicial }}
                                {{ $garantia->fecha_resolucion?->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>

                    {{-- Días --}}
                    <div class="col-span-1 flex justify-center">
                        @if($garantia->esPendiente())
                            <span class="inline-flex items-center justify-center rounded-lg border px-2 py-1 text-xs font-bold {{ $diasColor }}">
                                {{ $dias }}d
                            </span>
                        @elseif($resolucionLabel)
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $resolucionLabel }}</span>
                        @endif
                    </div>

                    {{-- Estatus --}}
                    <div class="col-span-1 flex justify-center">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[0.65rem] font-semibold uppercase {{ $estatusBadge }}">
                            {{ $garantia->estatus }}
                        </span>
                    </div>

                    {{-- Acción --}}
                    <div class="col-span-1 flex justify-end">
                        @if($tabActivo === 'por_enviar' && auth()->user()?->tienePermiso('prep.garantias.gestionar'))
                            <button wire:click="marcarComoEnviado({{ $garantia->id }})"
                                class="rounded-xl bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700
                                       text-white text-xs font-semibold px-3 py-1.5
                                       transition-colors duration-150 shadow-sm">
                                Marcar Enviado
                            </button>
                        @elseif($tabActivo === 'en_tramite' && auth()->user()?->tienePermiso('prep.garantias.gestionar'))
                            <button wire:click="abrirModal({{ $garantia->id }})"
                                class="rounded-xl bg-rose-500 hover:bg-rose-600 active:bg-rose-700
                                       text-white text-xs font-semibold px-3 py-1.5
                                       transition-colors duration-150 shadow-sm">
                                Resolver
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-2xl mb-2">📦</p>
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">No hay garantías
                        {{ $tabActivo === 'por_enviar' ? 'por enviar' : ($tabActivo === 'en_tramite' ? 'en trámite' : 'resueltas') }}
                        {{ $busqueda ? "para \"$busqueda\"" : '' }}
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div>{{ $this->garantias->links() }}</div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL DE RESOLUCIÓN
    ══════════════════════════════════════════════════════════════════ --}}
    @if($modalResolucion && $this->garantiaActual)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-init="document.body.style.overflow='hidden'"
             x-on:keydown.escape.window="$wire.cerrarModal()">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cerrarModal"></div>

            {{-- Panel --}}
            <div class="relative z-10 w-full max-w-2xl max-h-[92vh] overflow-y-auto
                        rounded-3xl border border-slate-200/80 dark:border-slate-700
                        bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl shadow-2xl p-6 space-y-5">

                {{-- Header --}}
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Resolver Garantía</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $this->garantiaActual->equipo?->numero_serie }}
                            · {{ $this->garantiaActual->equipo?->marca }}
                            {{ $this->garantiaActual->equipo?->modelo }}
                        </p>
                    </div>
                    <button wire:click="cerrarModal"
                        class="rounded-full w-8 h-8 flex items-center justify-center
                               text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800
                               transition-colors">
                        ✕
                    </button>
                </div>

                {{-- Info del reporte --}}
                <div class="rounded-xl border border-rose-200/60 bg-rose-50/40 dark:bg-rose-900/10 px-4 py-3 space-y-1">
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wide">Defecto reportado</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $this->garantiaActual->descripcion_defecto }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Reportado por <strong>{{ $this->garantiaActual->reportadoPor?->nombre_inicial }}</strong>
                        el {{ $this->garantiaActual->created_at->format('d/m/Y') }}
                        · Proveedor: <strong>{{ $this->garantiaActual->proveedor?->display_name }}</strong>
                    </p>
                </div>

                {{-- Tipo de resolución --}}
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Resultado del proveedor</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([
                            ['value' => 'REPARADO',            'label' => 'Reparado',  'desc' => 'Mismo equipo, mismo serial', 'emoji' => '🔧', 'color' => 'border-emerald-400 bg-emerald-50/80 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300'],
                            ['value' => 'REEMPLAZADO',         'label' => 'Reemplazado','desc' => 'Equipo nuevo del proveedor', 'emoji' => '🔄', 'color' => 'border-blue-400 bg-blue-50/80 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'],
                            ['value' => 'RECHAZADO_PROVEEDOR', 'label' => 'Rechazado', 'desc' => 'Proveedor no lo cubre',      'emoji' => '❌', 'color' => 'border-slate-400 bg-slate-100/80 dark:bg-slate-700/30 text-slate-700 dark:text-slate-300'],
                        ] as $opt)
                            <button type="button" wire:click="$set('tipoResolucion', '{{ $opt['value'] }}')"
                                class="rounded-xl border px-3 py-3 text-center transition-all duration-200
                                       {{ $tipoResolucion === $opt['value']
                                           ? $opt['color'].' shadow-sm'
                                           : 'border-slate-300/80 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40 hover:border-slate-400' }}">
                                <span class="block text-xl mb-1">{{ $opt['emoji'] }}</span>
                                <p class="text-sm font-semibold">{{ $opt['label'] }}</p>
                                <p class="text-[0.65rem] text-slate-500 dark:text-slate-400 mt-0.5">{{ $opt['desc'] }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Campos según tipo --}}
                @if($tipoResolucion === 'REEMPLAZADO')
                    <div class="space-y-4 rounded-xl border border-blue-200/60 bg-blue-50/30 dark:bg-blue-900/10 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">Datos del equipo nuevo</p>

                        {{-- Serial nuevo --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Número de serie nuevo <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="numeroSerieNuevo" type="text"
                                placeholder="Ej. PF3X9T2ABC"
                                class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-blue-400 outline-none uppercase" />
                        </div>

                        {{-- ¿Mismo modelo? --}}
                        <div class="flex items-center gap-3">
                            <input wire:model.live="mismoModelo" type="checkbox" id="mismo-modelo"
                                class="rounded border-slate-300 text-blue-500 focus:ring-blue-400" />
                            <label for="mismo-modelo" class="text-sm text-slate-700 dark:text-slate-300 select-none">
                                El modelo es el mismo que el original
                            </label>
                        </div>

                        @if(!$mismoModelo)
                            <div class="space-y-3 border-t border-blue-200/40 dark:border-blue-800/40 pt-3">
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Modelo diferente — seleccionar o crear</p>

                                {{-- Selector de modelo existente --}}
                                @if(count($this->modelosEnLote) > 0)
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Modelo existente en el mismo lote</label>
                                        <select wire:model.live="loteModeloNuevoId"
                                            class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                                   border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                                   focus:ring-2 focus:ring-blue-400 outline-none">
                                            <option value="">— No está en el lote, crear nuevo —</option>
                                            @foreach($this->modelosEnLote as $lm)
                                                <option value="{{ $lm['id'] }}">{{ $lm['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- Crear nuevo modelo --}}
                                @if(!$loteModeloNuevoId)
                                    <div class="space-y-3 rounded-xl border border-slate-300/60 dark:border-slate-700 bg-white/40 dark:bg-slate-900/30 px-3 py-3">
                                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Nuevo modelo (se creará en el lote)</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="space-y-1">
                                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Marca <span class="text-red-500">*</span></label>
                                                <input wire:model="nuevoModeloMarca" type="text" placeholder="Ej. HP"
                                                    class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                                           border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                                           focus:ring-2 focus:ring-blue-400 outline-none" />
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Modelo <span class="text-red-500">*</span></label>
                                                <input wire:model="nuevoModeloModelo" type="text" placeholder="Ej. ProBook 440 G9"
                                                    class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                                           border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                                           focus:ring-2 focus:ring-blue-400 outline-none" />
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Clasificación de puntos <span class="text-red-500">*</span></label>
                                            <select wire:model="nuevoModeloClasifId"
                                                class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                                       border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                                       focus:ring-2 focus:ring-blue-400 outline-none">
                                                <option value="">— Seleccionar clasificación —</option>
                                                @foreach($this->clasificaciones as $clf)
                                                    <option value="{{ $clf->id }}">{{ $clf->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                @endif

                {{-- Técnico de reingreso (Para REPARADO y REEMPLAZADO) --}}
                @if(in_array($tipoResolucion, ['REPARADO', 'REEMPLAZADO']))
                    <div class="space-y-1 rounded-xl border border-blue-200/60 bg-blue-50/30 dark:bg-blue-900/10 px-4 py-4">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Asignar equipo a técnico <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">
                            Por defecto: el técnico que reportó la garantía.
                        </p>
                        <select wire:model="tecnicoReingresoId"
                            class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                   border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                   focus:ring-2 focus:ring-blue-400 outline-none">
                            <option value="0">— Seleccionar técnico —</option>
                            @foreach($this->tecnicosDisponibles as $tec)
                                <option value="{{ $tec->id }}">{{ $tec->nombre }} {{ $tec->apellido_paterno }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Campos comunes: fecha y observaciones --}}
                @if($tipoResolucion)
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Fecha de resolución <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="fechaResolucion" type="date"
                                class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-rose-400 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Observaciones</label>
                            <textarea wire:model="observaciones" rows="2"
                                placeholder="Notas adicionales sobre la resolución..."
                                class="w-full rounded-xl px-3 py-2 text-sm bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700 text-slate-900 dark:text-slate-100
                                       focus:ring-2 focus:ring-rose-400 outline-none resize-none"></textarea>
                        </div>
                    </div>
                @endif

                {{-- Error --}}
                @if($errorModal)
                    <p class="rounded-xl border border-red-300 bg-red-50 dark:bg-red-900/20 dark:border-red-700
                               px-4 py-3 text-sm text-red-600 dark:text-red-400">
                        {{ $errorModal }}
                    </p>
                @endif

                {{-- Botones --}}
                <div class="flex justify-end gap-3 pt-2">
                    <button wire:click="cerrarModal" type="button"
                        class="rounded-xl border border-slate-300 dark:border-slate-700 px-5 py-2.5
                               text-sm font-semibold text-slate-700 dark:text-slate-300
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Cancelar
                    </button>
                    @if($tipoResolucion)
                        <button wire:click="guardarResolucion" wire:loading.attr="disabled"
                            class="rounded-xl bg-rose-500 hover:bg-rose-600 active:bg-rose-700
                                   text-white text-sm font-semibold px-6 py-2.5
                                   transition-colors duration-150 shadow-sm disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardarResolucion">Confirmar resolución</span>
                            <span wire:loading wire:target="guardarResolucion">Procesando...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-tb-background>
