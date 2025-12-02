<div class="space-y-6">

    {{-- FILA SUPERIOR: RESUMEN + BUSCADOR --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- TARJETAS RESUMEN — ESTILO GLOW REAL --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">

            {{-- TOTAL EQUIPOS — Glow azul --}}
            <div
                class="rounded-2xl
                       bg-white/80 dark:bg-slate-950/60
                       border border-slate-200/80 dark:border-white/10
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       px-4 py-3
                       shadow-md shadow-slate-900/10
                       dark:shadow-lg dark:shadow-slate-900/30
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:shadow-lg hover:shadow-sky-500/20
                       dark:hover:shadow-2xl dark:hover:shadow-sky-500/25
                       hover:border-sky-400/70 dark:hover:border-sky-300/50"
            >
                <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                    Total equipos
                </p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                    {{ $stats['total'] ?? 0 }}
                </p>
            </div>

            {{-- EN REVISIÓN — Glow amarillo --}}
            <div
                class="rounded-2xl
                       bg-amber-50/90 dark:bg-amber-950/40
                       border border-amber-200/80 dark:border-amber-500/70
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       px-4 py-3
                       shadow-md shadow-amber-900/10
                       dark:shadow-lg dark:shadow-amber-900/30
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:shadow-lg hover:shadow-amber-500/40
                       dark:hover:shadow-2xl dark:hover:shadow-amber-400/50
                       hover:border-amber-400/70"
            >
                <p class="text-xs sm:text-sm font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                    En revisión
                </p>
                <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                    {{ $stats['en_revision'] ?? 0 }}
                </p>
            </div>

            {{-- APROBADOS — Glow verde --}}
            <div
                class="rounded-2xl
                       bg-emerald-50/90 dark:bg-emerald-950/40
                       border border-emerald-200/80 dark:border-emerald-500/70
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       px-4 py-3
                       shadow-md shadow-emerald-900/10
                       dark:shadow-lg dark:shadow-emerald-900/30
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:shadow-lg hover:shadow-emerald-500/40
                       dark:hover:shadow-2xl dark:hover:shadow-emerald-400/50
                       hover:border-emerald-400/70"
            >
                <p class="text-xs sm:text-sm font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                    Aprobados
                </p>
                <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                    {{ $stats['aprobados'] ?? 0 }}
                </p>
            </div>

            {{-- FINALIZADOS — Glow morado --}}
            <div
                class="rounded-2xl
                       bg-indigo-50/90 dark:bg-indigo-950/40
                       border border-indigo-200/80 dark:border-indigo-500/70
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       px-4 py-3
                       shadow-md shadow-indigo-900/10
                       dark:shadow-lg dark:shadow-indigo-900/30
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:shadow-lg hover:shadow-indigo-500/40
                       dark:hover:shadow-2xl dark:hover:shadow-indigo-400/50
                       hover:border-indigo-400/70"
            >
                <p class="text-xs sm:text-sm font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">
                    Finalizados
                </p>
                <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">
                    {{ $stats['finalizados'] ?? 0 }}
                </p>
            </div>

        </div>

        {{-- Buscador --}}
        <div class="w-full lg:w-80">
            <label class="block text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                Búsqueda rápida
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-lg">
                    🔍
                </span>

                <input
                    type="text"
                    wire:model.live.debounce.500ms="search"
                    placeholder="Serie, marca, modelo, tipo..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm sm:text-base rounded-2xl
                           bg-white/80 dark:bg-slate-900/60
                           border border-white/60 dark:border-slate-700/70
                           text-slate-900 dark:text-slate-100
                           placeholder:text-slate-400 dark:placeholder:text-slate-500
                           shadow-md shadow-slate-900/10 dark:shadow-xl dark:shadow-slate-950/60
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70 focus:border-blue-500/70
                           backdrop-blur-xl"
                >
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div
        class="rounded-2xl
            bg-white/80 dark:bg-slate-950/70
            border border-slate-200/80 dark:border-white/10
            backdrop-blur-xl dark:backdrop-blur-2xl
            shadow-md shadow-slate-900/10
            dark:shadow-lg dark:shadow-slate-900/30
            transition-all duration-300
            hover:-translate-y-1
            hover:shadow-lg hover:shadow-indigo-500/20
            dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
            hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50"
    >
        <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
            <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-100">
                Filtros
            </h3>
            <p class="hidden sm:block text-sm sm:text-base text-slate-600 dark:text-slate-300">
                Mostrando
                <span class="font-bold text-slate-900 dark:text-slate-50">{{ $equipos->total() }}</span>
                registro(s)
                @if($search)
                    para “<span class="font-semibold">{{ $search }}</span>”
                @endif
            </p>
        </div>

        {{-- Campos de filtros --}}
        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

            {{-- Estatus --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                    Estatus general
                </label>
                <select
                    wire:model.live="filtroEstado"
                    class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm sm:text-base text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                >
                    <option value="todos">Todos</option>
                    <option value="En Revisión">En Revisión</option>
                    <option value="Aprobado">Aprobado</option>
                    <option value="Pendiente Pieza">Pendiente Pieza</option>
                    <option value="Pendiente Garantía">Pendiente Garantía</option>
                    <option value="Pendiente Deshueso">Pendiente Deshueso</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>

            {{-- Lote --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                    Lote
                </label>
                <select
                    wire:model.live="filtroLote"
                    class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm sm:text-base text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                >
                    <option value="todos">Todos los lotes</option>
                    @foreach ($lotes as $lote)
                        <option value="{{ $lote->id }}">
                            Lote {{ $lote->nombre_lote }}
                            @if($lote->fecha_llegada)
                                - {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Proveedor --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                    Proveedor
                </label>
                <select
                    wire:model.live="filtroProveedor"
                    class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm sm:text-base text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                >
                    <option value="todos">Todos los proveedores</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}">
                            {{ $proveedor->nombre_empresa }}
                            @if($proveedor->abreviacion)
                                ({{ $proveedor->abreviacion }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- TABLA DE EQUIPOS --}}
    <div
        class="rounded-2xl
            bg-white/80 dark:bg-slate-950/80
            border border-slate-200/80 dark:border-white/10
            backdrop-blur-xl dark:backdrop-blur-2xl
            shadow-md shadow-slate-900/10
            dark:shadow-lg dark:shadow-slate-900/30
            overflow-hidden
            transition-all duration-300
            hover:-translate-y-1
            hover:shadow-lg hover:shadow-indigo-500/20
            dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
            hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm sm:text-base text-left">

                <thead class="bg-slate-100 border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800/80">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Lote</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Proveedor</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Serie</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Estatus</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Registrado por</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($equipos as $equipo)

                    @php
                        $loteModelo = $equipo->loteModelo ?? null;
                        $lote = $loteModelo->lote ?? null;
                        $proveedor = $lote->proveedor ?? null;
                        $usuario = $equipo->registradoPor ?? null;

                        $codigoBarra = $equipo->numero_serie ?? $equipo->id;
                    @endphp

                    <tr class="
                        border-b border-slate-200 dark:border-slate-800/80
                        hover:bg-white/60 dark:hover:bg-slate-800/60
                        transition-colors">

                        {{-- Lote --}}
                        <td class="px-4 py-3 align-top">
                            <span class="font-semibold text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                {{ $lote->nombre_lote ?? '—' }}
                            </span>
                            @if($lote?->fecha_llegada)
                                <div class="text-xs sm:text-sm text-slate-400">
                                    {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>

                        {{-- Proveedor --}}
                        <td class="px-4 py-3 align-top">
                            <span class="text-sm sm:text-base text-slate-900 dark:text-slate-100">
                                {{ $proveedor->nombre_empresa ?? '—' }}
                            </span>
                            @if($proveedor?->abreviacion)
                                <div class="text-xs sm:text-sm text-slate-400">
                                    {{ $proveedor->abreviacion }}
                                </div>
                            @endif
                        </td>

                        {{-- Serie --}}
                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            <span class="font-mono text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                {{ $equipo->numero_serie }}
                            </span>
                        </td>

                        {{-- Equipo --}}
                        <td class="px-4 py-3 align-top min-w-[220px]">
                            <span class="text-sm sm:text-base font-semibold text-slate-900 dark:text-slate-50">
                                {{ $equipo->marca }} {{ $equipo->modelo }}
                            </span>
                            @if($equipo->tipo_equipo)
                                <div class="text-xs sm:text-sm text-slate-400 uppercase">
                                    {{ $equipo->tipo_equipo }}
                                </div>
                            @endif
                        </td>

                        {{-- Estatus --}}
                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            @php
                                $estado = $equipo->estatus_general ?? 'Sin estatus';
                                $badge = match ($estado) {
                                    'En Revisión'        => 'bg-amber-100 text-amber-900 border-amber-300',
                                    'Aprobado'           => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                    'Pendiente Pieza'    => 'bg-yellow-100 text-yellow-900 border-yellow-300',
                                    'Pendiente Garantía' => 'bg-blue-100 text-blue-900 border-blue-300',
                                    'Pendiente Deshueso' => 'bg-purple-100 text-purple-900 border-purple-300',
                                    'Finalizado'         => 'bg-slate-200 text-slate-900 border-slate-400',
                                    default              => 'bg-slate-100 text-slate-900 border-slate-300',
                                };
                            @endphp

                            <span class="inline-flex px-3 py-1 rounded-full text-xs sm:text-sm border font-semibold {{ $badge }}">
                                {{ $estado }}
                            </span>
                        </td>

                        {{-- Registrado por --}}
                        <td class="px-4 py-3 align-top">
                            <span class="text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                {{ $usuario->nombre ?? $usuario->email ?? '—' }}
                            </span>
                            @if($usuario?->email)
                                <div class="text-xs sm:text-sm text-slate-500">
                                    {{ $usuario->email }}
                                </div>
                            @endif
                        </td>

                        {{-- Fecha --}}
                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            @if($equipo->created_at)
                                <span class="text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                    {{ $equipo->created_at->format('d/m/Y') }}
                                </span>
                                <span class="block text-xs sm:text-sm text-slate-400">
                                    {{ $equipo->created_at->format('H:i') }}
                                </span>
                            @else
                                <span class="text-sm sm:text-base text-slate-400">—</span>
                            @endif
                        </td>
<td class="px-3 py-3 align-top text-right">

    {{-- 1. LÓGICA PHP (Smart ID) --}}
    @php
        $iniNom = $usuario ? substr($usuario->nombre, 0, 1) : 'X';
        $iniApe = $usuario ? substr($usuario->apellido_paterno, 0, 1) : 'X';
        $iniciales = strtoupper($iniNom . $iniApe);
        $fechaSmart = now()->format('dmY'); 
        $provSmart = isset($proveedor) && $proveedor->abreviacion 
                     ? $proveedor->abreviacion 
                     : substr($proveedor->nombre_empresa ?? 'XX', 0, 2);
        $smartID = $iniciales . $fechaSmart . strtoupper($provSmart);
    @endphp

    {{-- 2. BOTÓN IMPRIMIR --}}
    <button
        type="button"
        onclick="imprimirEtiquetaFinal('{{ $equipo->id }}')"
        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-xl
               bg-blue-600 hover:bg-blue-500 text-white shadow transition-all"
    >
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
            </path>
        </svg>
        Imprimir
    </button>

    {{-- 3. ETIQUETA OCULTA (DISEÑO NUEVO TIPO IMAGEN) --}}
    <div id="etiqueta-source-{{ $equipo->id }}" class="hidden font-['Inter',sans-serif]">

        {{-- Contenedor de etiqueta: 74mm x 50mm --}}
        <div class="bg-white border-2 border-blue-700 box-border overflow-hidden relative flex flex-col"
             style="width: 74mm; height: 50mm; padding: 1.5mm;">

            {{-- Encabezado negro --}}
            <div
                class="bg-black text-white text-center mb-1 shrink-0"
                style="
                    height: 6mm;                 /* ← Ajusta aquí el grosor de la barra */
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                "
            >

                <h1
                    class="titulo-equipo"
                    style="
                        font-family: 'Arial Black', Arial, sans-serif;
                        font-weight: 900;
                        font-size: 12pt;      
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        white-space: nowrap;
                        margin: 0;
                        padding: 0;
                    "
                >
                    {{ \Illuminate\Support\Str::upper(
                        ($equipo->marca . ' ' . $equipo->modelo) ?: 'SIN MODELO'
                    ) }}
                </h1>

            </div>



            {{-- Contenido principal --}}
            <div class="flex gap-2 px-1" style="height: 22mm;">

                           
                                {{-- Logo Tecnobyte (imagen real) --}}
                        <div class="flex flex-col items-center justify-center"
                            style="
                                width: 29mm;
                                margin-top: 10mm;   /* ↓ Bajar un poquito más el logo */
                            ">
                            <img src="{{ asset('images/logo-tecnobyte.png') }}"
                                style="
                                    width: 25mm;       /* ← Más grande (antes 20mm) */
                                    object-fit: contain;
                                    margin-bottom: 0.5mm;
                                ">
                        </div>



                                {{-- ESPECIFICACIONES CON FORMATO EXACTO Y DINÁMICAS --}}
                      @php
                        // ===== CPU =====
                        $cpuRaw     = strtoupper(trim($equipo->procesador_modelo ?? ''));
                        $freqManual = strtoupper(trim($equipo->procesador_frecuencia ?? '')); // ← NUEVO
                        $textoCpu   = 'I5- 8265U 1.60 GHz'; // fallback

                        $segmento = null;
                        $freq     = null;

                        if ($cpuRaw) {
                            // Capturar segmento tipo "I7-8650U"
                            if (preg_match('/(I[3579]\s*-?\s*\d{3,4}[A-Z]?)/', $cpuRaw, $mCpu)) {
                                // Normalizar: I7- 8650U
                                $segmento = strtoupper(str_replace(' ', '', $mCpu[1]));
                                $segmento = preg_replace('/(I[3579])\-?(\d)/', '$1- $2', $segmento);
                            } else {
                                // Si no matchea el patrón, usamos el texto tal cual
                                $segmento = $cpuRaw;
                            }

                            // Intentar leer GHz DESDE el modelo (por si lo traes ahí)
                            if (preg_match('/(\d+(\.\d+)?)\s*GHZ/i', $cpuRaw, $mFreq)) {
                                $freq = strtoupper($mFreq[1] . ' GHz');
                            }
                        }

                        // Si no encontramos GHz en el modelo, usamos la columna nueva
                        if (!$freq && $freqManual !== '') {
                            $freq = $freqManual;  // Ej: "1.90 GHz"
                        }

                        // Construir texto final: EJ "I7- 8650U 1.90 GHz"
                        if ($segmento) {
                            $textoCpu = trim($segmento . ($freq ? ' ' . $freq : ''));
                        }

                        // ===== DISCO: capacidad + tipo =====
                        $capacidad = strtoupper(trim($equipo->almacenamiento_principal_capacidad ?? ''));
                        $tipoAlm   = strtoupper(trim($equipo->almacenamiento_principal_tipo ?? ''));
                        if ($capacidad && $tipoAlm) {
                            $textoDisco = "$capacidad $tipoAlm";
                        } elseif ($capacidad) {
                            $textoDisco = $capacidad;
                        } else {
                            $textoDisco = '256 GB M2';
                        }

                        // ===== RAM: cantidad + RAM + tipo =====
                        $ramCant = strtoupper(trim($equipo->ram_total ?? ''));
                        if ($ramCant && !str_contains($ramCant, 'GB')) {
                            $ramCant .= ' GB';
                        }

                        $ramTipo = strtoupper(trim($equipo->ram_tipo ?? ''));
                        if ($ramCant && $ramTipo) {
                            $textoRam = "$ramCant RAM $ramTipo";
                        } elseif ($ramCant) {
                            $textoRam = "$ramCant RAM";
                        } else {
                            $textoRam = "8 GB RAM DDR3";
                        }

                        // ===== SISTEMA OPERATIVO =====
                        $textoSO = strtoupper($equipo->sistema_operativo ?? 'WINDOWS 10 PRO');

                        // ===== TOUCH SOLO SI SÍ ES =====
                        $mostrarTouch = (bool) ($equipo->pantalla_es_touch ?? false);


                        // ===== GPU DEDICADA =====
                    $gpuModelo = strtoupper(trim($equipo->grafica_dedicada_modelo ?? ''));
                    $gpuVRAM   = strtoupper(trim($equipo->grafica_dedicada_vram ?? ''));

                    // Solo mostrar si alguno tiene información
                    $mostrarGPU = ($gpuModelo !== '' || $gpuVRAM !== '');

                    // Construir texto final GPU (ej: "NVIDIA MX130 2GB")
                    if ($mostrarGPU) {
                        if ($gpuModelo && $gpuVRAM) {
                            $textoGPU = $gpuModelo . ' ' . $gpuVRAM;  
                        } elseif ($gpuModelo) {
                            $textoGPU = $gpuModelo;
                        } else {
                            $textoGPU = $gpuVRAM; // caso raro pero soportado
                        }
                    }

                    @endphp



                        <div
                            class="flex flex-col justify-center text-gray-900 uppercase"
                                style="
                                    width: 33mm;
                                    height: 22.6mm;
                                    margin-top: 2mm;
                                    margin-left: 2mm;  /* base pequeña */
                                    font-size: 9pt;
                                    line-height: 1.15;
                                    font-family: 'Bahnschrift SemiCondensed', 'Bahnschrift', 'Segoe UI', sans-serif;
                                    font-weight: 400;

                                    /* DESPLAZAMIENTO REAL A LA DERECHA */
                                    transform: translateX(2mm);
                                "
                        >

                        <p>{{ Str::limit($textoCpu, 32) }}</p>
                        <p>{{ Str::limit($textoDisco, 32) }}</p>
                        <p>{{ Str::limit($textoRam, 32) }}</p>
                        <p>{{ Str::limit($textoSO, 32) }}</p>
                        @if($mostrarGPU)
                            <p>{{ Str::limit($textoGPU, 32) }}</p>
                        @endif

                        @if($mostrarTouch)
                            <p>TOUCH</p>
                        @endif

                        </div>



            </div>

            {{-- Sección inferior --}}
            <div class="flex justify-between items-end px-1 mt-1 shrink-0" style="height: 14mm;">

                {{-- Serial y código --}}
                <div class="flex flex-col justify-end pb-1">
                    <p class="font-weight: 400 text-gray-900 leading-none"
                        style="
                                font-size: 12px;
                                font-family: 'Century Gothic', 'Gothic', sans-serif;
                                letter-spacing: 0.5px;
                        ">
                            {{ $smartID }}
                        </p>

                    <p class="text-[8px] font-bold text-orange-500 leading-none mt-0.5"
                       style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                
                    </p>
                </div>

                {{-- Código de barras dinámico --}}
                <div class="flex flex-col items-center"
                    style="width: 60mm; margin-left: 6mm;">

                    <svg class="barcode-target w-full"
                         data-serie="{{ $equipo->numero_serie ?? $equipo->id }}">
                    </svg>
                </div>
            </div>

        </div>
    </div>
</td>


                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm sm:text-lg text-slate-400 dark:text-slate-500">
                            No se encontraron equipos con los filtros actuales.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        {{-- Paginación --}}
        <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/40">
            {{ $equipos->links() }}
        </div>

    </div>



<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');

    /* Quitar CUALQUIER borde/sombra del área de impresión */
    #area-impresion-final,
    #area-impresion-final * {
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    @media print {
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
        }

        /* Solo se muestra el contenedor de impresión */
        body > *:not(#area-impresion-final) {
            display: none !important;
        }

        /* Página exactamente tamaño etiqueta */
        @page {
            size: 74mm 50mm;
            margin: 0;
        }

        /* Contenedor de impresión: pantalla completa, fondo blanco, SIN marco */
        #area-impresion-final {
            position: fixed !important;
            inset: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Forzar colores (azul/naranja del diseño) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

{{-- Librería de códigos de barras --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
    function imprimirEtiquetaFinal(id) {
        const fuente = document.getElementById('etiqueta-source-' + id);
        if (!fuente) {
            alert('Error: No se encuentra la etiqueta');
            return;
        }

        // Crear / reutilizar el área de impresión
        let area = document.getElementById('area-impresion-final');
        if (!area) {
            area = document.createElement('div');
            area.id = 'area-impresion-final';
            document.body.appendChild(area);
        }

        // Limpiar y configurar el contenedor (overlay blanco centrado)
        area.innerHTML = '';
        area.style.position = 'fixed';
        area.style.inset = '0';
        area.style.margin = '0';
        area.style.padding = '0';
        area.style.background = 'white';
        area.style.display = 'flex';
        area.style.alignItems = 'center';
        area.style.justifyContent = 'center';
        area.style.zIndex = '9999';

        // Clonar SOLO el contenido de la etiqueta (74x50mm)
        const etiqueta = fuente.firstElementChild.cloneNode(true);
        area.appendChild(etiqueta);

        // Generar el código de barras
const svg = area.querySelector('.barcode-target');
if (svg) {
    try {
        const serie = svg.dataset.serie || '';

        // 1) Generar el código igual que antes pero con barras un poco más finas
JsBarcode(svg, serie, {
    format: "CODE128",

    width: 0.6,          
    height: 15,

    displayValue: true,
    text: '*' + serie + '*',
    fontSize: 9,         
    fontOptions: "bold",
    textAlign: "center",
    textMargin: 1,
    margin: 0
});





    } catch (e) {
        console.error(e);
    }
}



        // Cuando termine de imprimir, ocultamos el overlay y limpiamos
        window.onafterprint = function () {
            area.style.display = 'none';
            area.innerHTML = '';
            window.onafterprint = null; // limpiar handler
        };

        // Lanzar impresión
        window.print();
    }
</script>


<script>
    // Auto-ajustar tamaño del título para que siempre quepa en una sola línea
    document.querySelectorAll('.titulo-equipo').forEach(function (el) {
        const parentWidth = el.parentElement.clientWidth;
        let size = parseFloat(window.getComputedStyle(el).fontSize);
        const minSize = 8; // tamaño mínimo en pt para no verse ridículo

        while (el.scrollWidth > parentWidth && size > minSize) {
            size -= 0.5;                    // bajar de medio punto en medio punto
            el.style.fontSize = size + 'pt';
        }
    });
</script>


</div>




