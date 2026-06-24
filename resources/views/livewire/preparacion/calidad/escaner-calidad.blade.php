<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6"
         x-data="{
            focusScanner() {
                setTimeout(() => {
                    let input = document.getElementById('escaner-input');
                    if(input) input.focus();
                }, 50);
            }
         }"
         @focus-scanner.window="focusScanner()"
         x-init="focusScanner()">

        <x-toast />

        <x-topbar
            title="Escáner Automático de Calidad"
            chip="Preparación · Calidad"
            description="Modo de validación rápida por código de barras o ID interno."
        >
            <x-slot:right>
                @if($modo !== 'ESCANEAR')
                <button wire:click="resetEscaneo"
                        class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver a escanear
                </button>
                @endif
            </x-slot:right>
        </x-topbar>

        <div class="w-full max-w-5xl mx-auto flex flex-col items-center justify-center">

            {{-- ESTADO 1: ESCANEAR --}}
            @if($modo === 'ESCANEAR')
            <div class="w-full max-w-xl text-center space-y-6">
                <div class="p-8 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-700 shadow-xl relative overflow-hidden transition hover:border-blue-500/50">
                    
                    <div class="mx-auto w-20 h-20 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>

                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Escanea un equipo</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">El sistema buscará automáticamente el número de serie o ID.</p>

                    <form @submit.prevent="$wire.buscarEquipo($refs.escanerInput.value)">
                        <input type="text"
                               x-ref="escanerInput"
                               wire:model.defer="numeroSerie"
                               id="escaner-input"
                               autocomplete="off"
                               class="w-full text-center text-2xl font-bold px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner placeholder:text-slate-300 dark:placeholder:text-slate-600 transition-all"
                               placeholder="Ej: TB-102030 o 777">
                        <button type="submit" class="mt-4 w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-base shadow-lg shadow-blue-500/30 transition-all">
                            Buscar Equipo
                        </button>
                    </form>
                    
                    @if($error)
                        <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl border border-red-200 dark:border-red-800/30 text-sm font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $error }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ESTADO 2: CONFIRMAR EQUIPO --}}
            @if($modo === 'CONFIRMAR' && $equipo)
            <div class="w-full max-w-2xl animate-in fade-in zoom-in-95 duration-300">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-5 border-b border-slate-200 dark:border-slate-700 text-center">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">¿Es este el equipo correcto?</h2>
                        <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $equipo->numero_serie }}</div>
                        <div class="text-sm text-slate-500 mt-1">ID: {{ $equipo->id }}</div>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Modelo</p>
                            <p class="text-base font-semibold text-slate-800 dark:text-slate-200">{{ $equipo->loteModelo?->catalogoEquipo?->modelo ?? 'Desconocido' }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $equipo->loteModelo?->catalogoEquipo?->marca }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Técnico que preparó</p>
                            @php
                                $ae = $equipo->asignacionEquipos->first();
                                $tecnico = $ae ? $ae->tecnico : null;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold">
                                    {{ $tecnico ? substr($tecnico->nombre, 0, 1) : '?' }}
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-slate-800 dark:text-slate-200">{{ $tecnico ? ($tecnico->nombre . ' ' . $tecnico->apellido_paterno) : 'Sin técnico' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Terminado: {{ $ae && $ae->fin_en ? \Carbon\Carbon::parse($ae->fin_en)->format('d M, h:i A') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex gap-4 justify-center">
                        <button wire:click="confirmar(false)" class="flex-1 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition">
                            Cancelar
                        </button>
                        <button wire:click="confirmar(true)" class="flex-1 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md shadow-blue-500/20 transition">
                            Sí, es este
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- ESTADO 3: ELEGIR ACCIÓN --}}
            @if($modo === 'ELEGIR_ACCION' && $equipo)
            <div class="w-full max-w-lg animate-in fade-in slide-in-from-bottom-8 duration-300">
                <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden text-center p-8">
                    
                    <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>

                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Equipo: {{ $equipo->numero_serie }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 mb-8">Elige el dictamen de calidad para este equipo.</p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        {{-- BOTÓN RECHAZAR (Izquierda) --}}
                        <button wire:click="seleccionarAccion('rechazar')"
                                class="flex-1 px-6 py-3 rounded-lg bg-red-600 hover:bg-red-500 active:bg-red-700 text-white font-bold shadow-lg shadow-red-500/25 transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Rechazar
                        </button>

                        {{-- BOTÓN APROBAR (Derecha) --}}
                        <button wire:click="seleccionarAccion('aprobar')"
                                class="flex-1 px-6 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold shadow-lg shadow-emerald-500/25 transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Aprobar
                        </button>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <button wire:click="resetEscaneo" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition underline">
                            Cancelar y escanear otro equipo
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- ESTADO 4A: FORMULARIO APROBAR --}}
            @if($modo === 'FORMULARIO_APROBAR' && $equipo)
            <div class="w-full max-w-4xl animate-in fade-in slide-in-from-bottom-8 duration-300">
                <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-emerald-50/50 dark:bg-emerald-900/10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                ✅
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-50">Aprobar equipo</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $equipo->marca ?? '' }} {{ $equipo->modelo ?? '' }} — {{ $equipo->numero_serie }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- ENSAMBLE Y ESTÉTICA --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">🔧 ENSAMBLE Y ESTÉTICA</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $ensambleItems = [
                                        'carcasa_ensamblada' => 'Carcasas bien ensambladas',
                                        'tornillos_completos' => 'Tornillos completos/visibles',
                                        'pantalla_colocada' => 'Pantalla correctamente colocada',
                                        'teclado_touchpad' => 'Teclado y touchpad firmes',
                                        'limpieza_general' => 'Limpieza general correcta',
                                        'estetica_coincide' => 'Estética coincide con clasificación',
                                    ];
                                @endphp
                                @foreach($ensambleItems as $key => $label)
                                    <label class="flex items-start gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- FUNCIONAMIENTO BÁSICO --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">⚡ FUNCIONAMIENTO BÁSICO</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $funcItems = [
                                        'enciende' => 'Enciende correctamente',
                                        'bios_detecta' => 'BIOS detecta hardware',
                                        'windows_inicia' => 'Windows inicia correctamente',
                                        'temperatura_normal' => 'Temperatura normal en reposo',
                                        'audio_funcional' => 'Audio funcional',
                                        'wifi_funcional' => 'WiFi funcional',
                                        'usb_funcional' => 'USB funcional',
                                        'camara_mic' => 'Cámara/Micrófono funcional',
                                        'carga_bateria' => 'Carga batería correctamente',
                                    ];
                                @endphp
                                @foreach($funcItems as $key => $label)
                                    <label class="flex items-start gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- BATERÍA (solo laptops) --}}
                        @php
                            $hasBattery = ($equipo->baterias && $equipo->baterias->count() > 0) || (strtolower($equipo->tipo_equipo ?? '') !== '' && strpos(strtolower($equipo->tipo_equipo), 'lap') !== false);
                        @endphp
                        @if($hasBattery)
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">🔋 BATERÍA</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @php
                                        $batteryItems = [
                                            'bateria_detectada' => 'Batería detectada',
                                            'bateria_cargando' => 'Cargando correctamente',
                                            'sin_inflado' => 'Sin inflado visible',
                                        ];
                                    @endphp
                                    @foreach($batteryItems as $key => $label)
                                        <label class="flex items-start gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- PRESENTACIÓN / VENTA --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">📦 PRESENTACIÓN / VENTA</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $presentacionItems = [
                                        'accesorios' => 'Accesorios correctos',
                                        'etiquetas' => 'Etiquetas correctas',
                                        'equipo_listo' => 'Equipo listo para inventario/venta',
                                    ];
                                @endphp
                                @foreach($presentacionItems as $key => $label)
                                    <label class="flex items-start gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <input type="checkbox" wire:model="qSalioBien.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- CALIFICACIÓN Y NOTAS --}}
                        <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Calificación general (opcional)</label>
                            <select wire:model="calificacion" class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70 border border-slate-300/70 dark:border-slate-600/70 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                                <option value="">Sin calificar</option>
                                @foreach(\App\Models\ValidacionCalidad::calificaciones() as $k => $lab)
                                    <option value="{{ $k }}">{{ $lab }}</option>
                                @endforeach
                            </select>

                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mt-2">Notas (opcional)</label>
                            <textarea wire:model="notasValidacion" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700/80 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/70"></textarea>

                            @if($error)
                                <div class="mt-2 p-2 bg-red-50 text-red-600 rounded-lg text-sm">{{ $error }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 items-center">
                        <button wire:click="$set('modo', 'ELEGIR_ACCION')" class="px-4 py-2.5 rounded-lg border border-slate-300/70 dark:border-slate-700/80 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all">Cancelar</button>
                        <button wire:click="validarEquipo" class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-500/25 transition-all">
                            Aprobar ✓
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- ESTADO 4B: FORMULARIO RECHAZAR --}}
            @if($modo === 'FORMULARIO_RECHAZAR' && $equipo)
            <div class="w-full max-w-4xl animate-in fade-in slide-in-from-bottom-8 duration-300">
                <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-red-50/50 dark:bg-red-900/10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                ❌
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-50">Rechazar equipo</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $equipo->marca ?? '' }} {{ $equipo->modelo ?? '' }} — {{ $equipo->numero_serie }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 p-4">
                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                Marca los defectos encontrados y agrega un motivo claro para que el técnico pueda corregir rápido.
                            </p>
                        </div>

                        {{-- DEFECTOS --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">¿Qué defectos encontraste?</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $defectItems = [
                                        'bateria_danada' => 'Batería dañada',
                                        'pantalla_rota' => 'Pantalla rota',
                                        'carcasa_danada' => 'Carcasa dañada',
                                        'teclado_defectuoso' => 'Teclado defectuoso',
                                        'software_corrupto' => 'Software corrupto',
                                    ];
                                @endphp
                                @foreach($defectItems as $key => $label)
                                    <label class="flex items-start gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <input type="checkbox" wire:model="qSalioMal.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- QUÉ SALIÓ BIEN --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Qué salió bien (opcional)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $okItems = [
                                        'pantalla_ok' => 'Pantalla en buen estado',
                                        'bateria_ok' => 'Batería en buen estado',
                                        'carcasa_ok' => 'Carcasa en buen estado',
                                        'software_ok' => 'Software estable',
                                    ];
                                @endphp
                                @foreach($okItems as $key => $label)
                                    <label class="flex items-start gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <input type="checkbox" wire:model="qSalioBienRechazo.{{ $key }}" value="{{ $label }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- MOTIVO --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Motivo del rechazo (obligatorio)</label>
                            <textarea wire:model="motivoRechazo" placeholder="Describe los problemas encontrados..." rows="4" class="w-full px-4 py-2.5 rounded-lg bg-white/80 dark:bg-slate-900/60 border border-slate-300/70 dark:border-slate-700/80 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/70"></textarea>

                            @if($error)
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $error }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 items-center">
                        <button wire:click="$set('modo', 'ELEGIR_ACCION')" class="px-4 py-2.5 rounded-lg border border-slate-300/70 dark:border-slate-700/80 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all">Cancelar</button>
                        
                        @if(!$motivoRechazo)
                            <button class="px-6 py-2.5 rounded-lg bg-red-400 text-white text-sm font-bold opacity-50 cursor-not-allowed" title="Agrega un motivo para habilitar">Rechazar</button>
                        @else
                            <button wire:click="rechazarEquipo" class="px-6 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-500/25 transition-all">Rechazar ✕</button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-tb-background>
