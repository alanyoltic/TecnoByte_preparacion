<div class="space-y-6">

    {{-- FILA SUPERIOR: RESUMEN + BUSCADOR --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Tarjetas resumen --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">

    {{-- TOTAL EQUIPOS (Glow Azul) --}}
    <div class="
        rounded-2xl border border-slate-300/70 dark:border-slate-700/70
        bg-white/90 dark:bg-slate-900/80
        px-4 py-3 shadow-sm
        transition-all duration-300
        hover:-translate-y-1
        hover:shadow-[0_15px_35px_rgba(56,189,248,0.25)]
        hover:ring-2 hover:ring-sky-400/70 hover:border-sky-400/70
    ">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            Total equipos
        </p>
        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
            {{ $stats['total'] ?? 0 }}
        </p>
    </div>

    {{-- EN REVISIÓN (Glow Amarillo) --}}
    <div class="
        rounded-2xl border border-amber-200/80 dark:border-amber-700/70
        bg-amber-50/80 dark:bg-amber-900/30
        px-4 py-3 shadow-sm
        transition-all duration-300
        hover:-translate-y-1
        hover:shadow-[0_15px_35px_rgba(251,191,36,0.25)]
        hover:ring-2 hover:ring-amber-400/70 hover:border-amber-400/70
    ">
        <p class="text-sm font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
            En revisión
        </p>
        <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
            {{ $stats['en_revision'] ?? 0 }}
        </p>
    </div>

    {{-- APROBADOS (Glow Verde) --}}
    <div class="
        rounded-2xl border border-emerald-200/80 dark:border-emerald-700/70
        bg-emerald-50/80 dark:bg-emerald-900/30
        px-4 py-3 shadow-sm
        transition-all duration-300
        hover:-translate-y-1
        hover:shadow-[0_15px_35px_rgba(16,185,129,0.25)]
        hover:ring-2 hover:ring-emerald-400/70 hover:border-emerald-400/70
    ">
        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
            Aprobados
        </p>
        <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
            {{ $stats['aprobados'] ?? 0 }}
        </p>
    </div>

    {{-- FINALIZADOS (Glow Morado) --}}
    <div class="
        rounded-2xl border border-indigo-200/80 dark:border-indigo-700/70
        bg-indigo-50/80 dark:bg-indigo-900/30
        px-4 py-3 shadow-sm
        transition-all duration-300
        hover:-translate-y-1
        hover:shadow-[0_15px_35px_rgba(99,102,241,0.25)]
        hover:ring-2 hover:ring-indigo-400/70 hover:border-indigo-400/70
    ">
        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">
            Finalizados
        </p>
        <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">
            {{ $stats['finalizados'] ?? 0 }}
        </p>
    </div>

</div>


        {{-- Buscador --}}
        <div class="w-full lg:w-80">
            <label class="block text-base font-semibold text-slate-200/90 dark:text-slate-200 mb-2">
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
                    class="w-full pl-10 pr-4 py-2.5 text-lg rounded-xl border border-slate-300/80
                           bg-slate-900/80/90 dark:bg-slate-900/80 dark:border-slate-700/80
                           text-slate-100 dark:text-slate-100
                           placeholder:text-slate-500 dark:placeholder:text-slate-500
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70"
                >
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="rounded-2xl border  bg-white/75 dark:bg-slate-900/60 dark:border-slate-700/70  overflow-hidden     border-white/60 shadow-slate-900/30 shadow-lg  hover:shadow-indigo-500/20 hover:shadow-2xl backdrop-blur-xl  hover:-translate-y-1 transition-all duration-300">


        <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Filtros
            </h3>
            <p class="hidden sm:block text-base text-slate-600 dark:text-slate-300">
                Mostrando
                <span class="font-bold text-slate-50">
                    {{ $equipos->total() }}
                </span>
                registro(s)
                @if($search)
                    para "<span class="font-semibold">{{ $search }}</span>"
                @endif
            </p>
        </div>

        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Estatus general --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-base font-semibold text-slate-200">
                    Estatus general
                </label>
                <select
                    wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-slate-500/80 bg-slate-800/90
                           text-base text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70"
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
                <label class="text-base font-semibold text-slate-200">
                    Lote
                </label>
                <select
                    wire:model.live="filtroLote"
                    class="w-full rounded-xl border border-slate-500/80 bg-slate-800/90
                           text-base text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70"
                >
                    <option value="todos">Todos los lotes</option>
                    @foreach ($lotes as $lote)
                        <option value="{{ $lote->id }}">
                            Lote {{ $lote->nombre_lote }}
                            @if(!empty($lote->fecha_llegada))
                                - {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Proveedor --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-base font-semibold text-slate-200">
                    Proveedor
                </label>
                <select
                    wire:model.live="filtroProveedor"
                    class="w-full rounded-xl border border-slate-500/80 bg-slate-800/90
                           text-base text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70"
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

            {{-- Resumen móvil --}}
            <div class="flex items-end md:hidden">
                <p class="text-base text-slate-300">
                    Mostrando
                    <span class="font-semibold text-slate-100">
                        {{ $equipos->total() }}
                    </span>
                    registro(s)
                    @if($search)
                        para "<span class="font-semibold">{{ $search }}</span>"
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- TABLA DE EQUIPOS --}}
    <div class="rounded-2xl border  bg-white/75 dark:bg-slate-900/60 dark:border-slate-700/70  overflow-hidden     border-white/60 shadow-slate-900/30 shadow-lg  hover:shadow-indigo-500/20 hover:shadow-2xl backdrop-blur-xl  hover:-translate-y-1 transition-all duration-300">
        <div class="overflow-x-auto">
            <table class="min-w-full text-base text-left">
                <thead class="bg-slate-100 border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800/80">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Lote</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Proveedor</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Serie</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Estatus</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Registrado por</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Acciones</th>
                        
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

                        <tr class="border-b border-slate-200 dark:border-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                            {{-- Lote --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-base text-slate-900 dark:text-slate-50">
                                        {{ $lote->nombre_lote ?? ('Lote #'.$lote->id ?? 'Sin lote') }}
                                    </span>
                                    @if(!empty($lote?->fecha_llegada))
                                        <span class="text-sm text-slate-400">
                                            {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Proveedor --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-base text-slate-900 dark:text-slate-100">
                                        {{ $proveedor->nombre_empresa ?? '—' }}
                                    </span>
                                    @if(!empty($proveedor?->abreviacion))
                                        <span class="text-sm text-slate-400">
                                            {{ $proveedor->abreviacion }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Serie --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                <span class="font-mono text-base text-slate-900 dark:text-slate-50">
                                    {{ $equipo->numero_serie ?? '—' }}
                                </span>
                            </td>

                            {{-- Equipo --}}
                            <td class="px-4 py-3 align-top min-w-[220px]">
                                <div class="flex flex-col">
                                    <span class="text-base font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $equipo->marca ?? '—' }} {{ $equipo->modelo ?? '' }}
                                    </span>
                                    @if($equipo->tipo_equipo)
                                        <span class="text-sm text-slate-400 uppercase tracking-wide">
                                            {{ $equipo->tipo_equipo }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Estatus --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                @php
                                    $estado = $equipo->estatus_general ?? 'Sin estatus';
                                    $badgeClasses = match ($estado) {
                                        'En Revisión'        => 'bg-amber-100/90 text-amber-900 border-amber-300',
                                        'Aprobado'           => 'bg-emerald-100/90 text-emerald-900 border-emerald-300',
                                        'Pendiente Pieza'    => 'bg-yellow-100/90 text-yellow-900 border-yellow-300',
                                        'Pendiente Garantía' => 'bg-blue-100/90 text-blue-900 border-blue-300',
                                        'Pendiente Deshueso' => 'bg-purple-100/90 text-purple-900 border-purple-300',
                                        'Finalizado'         => 'bg-slate-50 text-slate-900 border-slate-300',
                                        default              => 'bg-slate-200/90 text-slate-800 border-slate-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-semibold {{ $badgeClasses }}">
                                    {{ $estado }}
                                </span>
                            </td>

                            {{-- Registrado por --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-base text-slate-900 dark:text-slate-50">
                                        {{ $usuario->nombre ?? $usuario->email ?? '—' }}
                                    </span>
                                    @if(!empty($usuario?->email))
                                        <span class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ $usuario->email }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Fecha --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                @if($equipo->created_at)
                                    <span class="text-base text-slate-900 dark:text-slate-50">
                                        {{ $equipo->created_at->format('d/m/Y') }}
                                    </span>
                                    <span class="block text-sm text-slate-400">
                                        {{ $equipo->created_at->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-base text-slate-400">—</span>
                                @endif
                            </td>

                            
                            {{-- Acciones --}}
                            <td class="px-3 py-3 align-top text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 text-sm rounded-xl border border-slate-400/80
                                        text-slate-100 dark:border-slate-500
                                        hover:bg-slate-100/10 dark:hover:bg-slate-800/80 transition"
                                >
                                    Ver ficha
                                </button>
                            </td>
                            
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-lg text-slate-300">
                                No se encontraron equipos con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <<div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/95 dark:bg-transparent">

            {{ $equipos->links() }}
        </div>
    </div>
</div>
