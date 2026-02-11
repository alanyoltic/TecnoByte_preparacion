<div class="space-y-6">



    {{-- BARRA DE ACCIONES MASIVAS --}}
    <div
        class="rounded-2xl
               bg-slate-900/90
               border border-slate-800/80
               text-slate-50
               px-4 py-3
               flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
    >
        <div class="flex items-center gap-3">
            <div class="inline-flex items-center gap-2 text-xs sm:text-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="font-semibold">Gestión masiva activa</span>
            </div>
            <span class="text-xs text-slate-400">

            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 justify-end">


            
@php
    $tieneSeleccion = !empty($selected) && count($selected) > 0;
@endphp

<button
    type="button"
    wire:click="exportarSeleccionWord"
    wire:loading.attr="disabled"
    @disabled(!$tieneSeleccion)
    class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5
           text-xs sm:text-sm font-semibold text-white transition
           {{ $tieneSeleccion
                ? 'bg-blue-600 hover:bg-blue-500 shadow-md shadow-blue-500/30'
                : 'bg-slate-600/60 cursor-not-allowed opacity-60' }}"
>
    Exportar Word
</button>

<button
    type="button"
    wire:click="exportarSeleccionPdf"
    wire:loading.attr="disabled"
    @disabled(!$tieneSeleccion)
    class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5
           text-xs sm:text-sm font-semibold text-white transition
           {{ $tieneSeleccion
                ? 'bg-slate-900 hover:bg-slate-800 shadow-md shadow-slate-900/30'
                : 'bg-slate-600/60 cursor-not-allowed opacity-60' }}"
>
    Exportar PDF
</button>












        </div>
    </div>

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

    {{-- FILTROS (BÁSICOS + AVANZADOS) --}}
    <div
        x-data="{ openAvanzados: false }"
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
        <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Filtros
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                    Combina filtros básicos y avanzados para preparar tus reportes.
                </p>
            </div>

            <div class="flex items-center gap-3 text-xs sm:text-sm">
                <p class="hidden sm:block text-slate-600 dark:text-slate-300">
                    Mostrando
                    <span class="font-bold text-slate-900 dark:text-slate-50">{{ $equipos->total() }}</span>
                    registro(s)
                    @if($search)
                        para “<span class="font-semibold">{{ $search }}</span>”
                    @endif
                </p>


                {{-- NUEVO: Limpiar filtros --}}
        <button
            type="button"
            wire:click="resetFiltros"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                   bg-slate-100/80 dark:bg-slate-900/80
                   text-[0.75rem] font-semibold text-slate-700 dark:text-slate-100
                   border border-slate-300/70 dark:border-slate-700/80
                   hover:bg-slate-200/80 dark:hover:bg-slate-800
                   transition-colors"
        >
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M4 4a1 1 0 011-1h10a1 1 0 01.8 1.6L12 10.25V15a1 1 0 01-.553.894l-3 1.5A1 1 0 017 16.5v-6.25L3.2 4.6A1 1 0 014 4z"/>
            </svg>
            Limpiar filtros
        </button>

                {{-- Botón mostrar/ocultar filtros avanzados --}}
                <button
                    type="button"
                    @click="openAvanzados = !openAvanzados"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                           bg-slate-900/90 dark:bg-slate-900/80
                           text-[0.75rem] font-semibold text-slate-100
                           border border-slate-700/80
                           shadow shadow-slate-900/40
                           hover:bg-slate-800"
                >
                    <span>Filtros avanzados</span>
                    <svg
                        class="w-3 h-3 transform transition-transform duration-150"
                        :class="openAvanzados ? 'rotate-180' : 'rotate-0'"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd"
                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0
                              111.414 1.414l-4 4a1 1 0
                              01-1.414 0l-4-4a1 1 0
                              010-1.414z"
                              clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Campos de filtros BÁSICOS --}}
        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-slate-200/60 dark:border-slate-800/80">

            {{-- Estatus --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                    Estatus general
                </label>
                <select
                    wire:model.change="filtroEstado"
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
                    wire:model.change="filtroLote"
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
                    wire:model.change="filtroProveedor"
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

            {{-- Registros por página --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                    Registros por página
                </label>
                <select
                    wire:model.live.debounce.150ms="perPage"
                    class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm sm:text-base text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        {{-- FILTROS AVANZADOS --}}
        <div
            class="px-5 pt-0 pb-4 space-y-4"
            x-show="openAvanzados"
            x-transition
        >
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">

                {{-- Rango de fechas --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Fecha desde
                    </label>
                    <input
                        type="date"
                        wire:model.change="fechaDesde"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Fecha hasta
                    </label>
                    <input
                        type="date"
                        wire:model.change="fechaHasta"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                </div>



                {{-- Área / tienda --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Área / tienda
                    </label>
                    <select
                        wire:model.change="filtroArea"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="todos">Todas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- GPU --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        GPU
                    </label>
                    <select
                        wire:model.change="filtroGpu"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="todos">Todas</option>
                        <option value="dedicada">Con dedicada</option>
                        <option value="sin_dedicada">Sin dedicada</option>
                    </select>
                </div>

                {{-- Salud batería --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Salud de batería
                    </label>
                    <select
                        wire:model.change="filtroBateria"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="todos">Todas</option>
                        <option value="baja">Baja (&lt; 70%)</option>
                        <option value="media">Media (70–89%)</option>
                        <option value="alta">Alta (≥ 90%)</option>
                    </select>
                </div>

                {{-- Sistema operativo --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Sistema operativo
                    </label>
                    <select
                        wire:model.change="filtroSO"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="todos">Todos</option>
                        @foreach ($sistemasOperativos as $so)
                            <option value="{{ $so }}">{{ $so }}</option>
                        @endforeach
                    </select>
                </div>
                                {{-- Técnico --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Técnico
                    </label>

                    <select
                        wire:model.live="tecnico_id"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                            border border-white/60 dark:border-slate-600/70
                                            text-sm text-slate-900 dark:text-slate-100
                                            focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                    >
                        <option value="">Todos</option>

                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico['id'] }}">
                                {{ $tecnico['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
                        <th class="px-3 py-3">
                            <input
                                type="checkbox"
                                wire:model.live="selectPage"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                        </th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Lote</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Proveedor</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Serie</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Área</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">S.O.</th>
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
                    @endphp

                    <tr class="
                        border-b border-slate-200 dark:border-slate-800/80
                        hover:bg-white/60 dark:hover:bg-slate-800/60
                        transition-colors">

                        {{-- Checkbox selección --}}
                        <td class="px-3 py-3 align-top">
                            <input
                                type="checkbox"
                                value="{{ $equipo->id }}"
                                wire:model.live="selected"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                        </td>

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
                        </td>


                        {{-- Área --}}
                        <td class="px-4 py-3 align-top">
                            <span class="text-xs sm:text-sm text-slate-900 dark:text-slate-100">
                                {{ $equipo->area_tienda ?? '—' }}
                            </span>
                        </td>

                        {{-- S.O. --}}
                        <td class="px-4 py-3 align-top">
                            <span class="text-xs sm:text-sm text-slate-900 dark:text-slate-100">
                                {{ $equipo->sistema_operativo ?? '—' }}
                            </span>
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

                        {{-- Acciones (solo admin/ceo, igual que en Inventario Listo si quieres) --}}
                        <td class="px-4 py-3 align-top text-right whitespace-nowrap">
                            @php
                                                        
                                $user = auth()->user();
                                $puedeEditar = $user && $user->tienePermiso('equipos.editar');
                            @endphp

                            @php
                                $user = auth()->user();
                                $puedeVerResumen = $user && $user->tienePermiso('equipos.editar'); // o crea un permiso 'equipos.ver_resumen'
                            @endphp

                            @if($puedeVerResumen)
                                <button
                                    type="button"
                                    wire:click="abrirResumen({{ $equipo->id }})"
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl
                                        bg-blue-600 hover:bg-blue-500
                                        text-xs font-semibold text-white
                                        shadow shadow-blue-500/40
                                        transition"
                                >
                                    Resumen
                                </button>
                            @endif

                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-sm sm:text-lg text-slate-400 dark:text-slate-500">
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


 {{-- MODAL RESUMEN --}}
<div
    x-data="{ open: @entangle('modalResumen') }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
>
    {{-- overlay --}}
    <div
        class="absolute inset-0 bg-black/60"
        @click="$wire.cerrarResumen()"
    ></div>

    {{-- panel --}}
    <div
        class="relative w-full max-w-xl rounded-2xl
               bg-slate-950/80 backdrop-blur-xl
               border border-white/10
               shadow-2xl shadow-slate-950/60
               p-6"
        @keydown.escape.window="$wire.cerrarResumen()"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-100">
                    {{ $resumenTitulo }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Resumen rápido para publicación / ficha
                </p>
            </div>

            <button
                type="button"
                class="w-9 h-9 rounded-full bg-white/5 hover:bg-white/10
                       border border-white/10 text-slate-200 flex items-center justify-center"
                wire:click="cerrarResumen"
            >✕</button>
        </div>

        <div class="mt-5 space-y-2">
            @foreach($resumenLineas as $l)
                <div class="flex gap-3 text-sm text-slate-200">
                    <div class="w-6 text-center shrink-0">
                        {{ $l['icon'] ?? '•' }}
                    </div>
                    <div class="leading-snug">
                        <span class="font-semibold">{{ $l['label'] }}:</span>
                        {{ $l['text'] }}
                    </div>
                </div>
            @endforeach

        </div>

        <div class="mt-6 flex justify-end">
            <button
                type="button"
                wire:click="cerrarResumen"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                       bg-blue-600 hover:bg-blue-500
                       text-sm font-semibold text-white
                       shadow shadow-blue-500/40 transition"
            >
                Listo
            </button>
        </div>
    </div>
</div>





    
</div>

