<div class="space-y-6">

    {{-- FILA SUPERIOR: RESUMEN + BUSCADOR --}}
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Tarjetas resumen --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
            {{-- Total equipos con piezas faltantes --}}
            <div class="
                rounded-2xl border border-slate-300/70 dark:border-slate-700/70
                bg-white/90 dark:bg-slate-900/80
                px-4 py-3 shadow-sm
                transition-all duration-300
                hover:-translate-y-0.5
                hover:shadow-[0_10px_24px_rgba(15,23,42,0.45)]
                hover:border-[#FF9521] dark:hover:border-indigo-400/60
            ">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                    Equipos en espera
                </p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                    {{ $stats['total_equipos'] ?? 0 }}
                </p>
            </div>

            {{-- Piezas Pendiente Compra --}}
            <div class="
                rounded-2xl border border-amber-200/80 dark:border-amber-700/70
                bg-amber-50/80 dark:bg-amber-900/30
                px-4 py-3 shadow-sm
                transition-all duration-300
                hover:-translate-y-0.5
                hover:shadow-[0_10px_24px_rgba(180,83,9,0.45)]
                hover:border-amber-400/70
            ">
                <p class="text-sm font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                    Pendiente compra
                </p>
                <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                    {{ $stats['pendiente_compra'] ?? 0 }}
                </p>
            </div>

            {{-- Piezas Compradas --}}
            <div class="
                rounded-2xl border border-sky-200/80 dark:border-sky-700/70
                bg-sky-50/80 dark:bg-sky-900/30
                px-4 py-3 shadow-sm
                transition-all duration-300
                hover:-translate-y-0.5
                hover:shadow-[0_10px_24px_rgba(14,116,144,0.45)]
                hover:border-sky-400/70
            ">
                <p class="text-sm font-semibold text-sky-700 dark:text-sky-200 uppercase tracking-wide">
                    Piezas compradas
                </p>
                <p class="mt-2 text-2xl font-bold text-sky-800 dark:text-sky-100">
                    {{ $stats['compradas'] ?? 0 }}
                </p>
            </div>

            {{-- Piezas Instaladas --}}
            <div class="
                rounded-2xl border border-emerald-200/80 dark:border-emerald-700/70
                bg-emerald-50/80 dark:bg-emerald-900/30
                px-4 py-3 shadow-sm
                transition-all duration-300
                hover:-translate-y-0.5
                hover:shadow-[0_10px_24px_rgba(22,163,74,0.45)]
                hover:border-emerald-400/70
            ">
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                    Piezas instaladas
                </p>
                <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                    {{ $stats['instaladas'] ?? 0 }}
                </p>
            </div>
        </div>

        {{-- Buscador --}}
        <div class="w-full lg:w-80">
            <label class="block text-base font-semibold text-slate-200/90 dark:text-slate-200 mb-2">
                Buscar equipo o pieza
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-lg">
                    🔍
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.500ms="search"
                    placeholder="Serie, marca, modelo, tipo, pieza..."
                    class="w-full pl-10 pr-4 py-2.5 text-lg rounded-xl border border-slate-300/80
                           bg-slate-900/80/90 dark:bg-slate-900/80 dark:border-slate-700/80
                           text-slate-100 dark:text-slate-100
                           placeholder:text-slate-500 dark:placeholder:text-slate-500
                           focus:outline-none focus:ring-2
                           focus:ring-[#FF9521] focus:border-[#FF9521]
                           dark:focus:ring-indigo-500/70 dark:focus:border-indigo-500/70"
                >
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="
        rounded-2xl border border-slate-200/80 dark:border-slate-700/80
        bg-white/95 dark:bg-slate-900/90
        shadow-sm
        transition-all duration-300
        hover:-translate-y-0.5
        hover:shadow-[0_10px_24px_rgba(15,23,42,0.45)]
        hover:border-[#FF9521] dark:hover:border-indigo-400/50
    ">
        <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Filtros
            </h3>
            <p class="hidden sm:block text-base text-slate-600 dark:text-slate-300">
                Mostrando
                <span class="font-bold text-slate-900 dark:text-slate-50">
                    {{ $piezasPendientes->total() }}
                </span>
                registro(s)
                @if($search)
                    para "<span class="font-semibold">{{ $search }}</span>"
                @endif
            </p>
        </div>

        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Estatus pieza --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-base font-semibold text-slate-700 dark:text-slate-200">
                    Estatus de pieza
                </label>
                <select
                    wire:model.live="filtroEstatus"
                    class="w-full rounded-xl border border-slate-500/80 bg-slate-800/90
                           text-base text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521]
                           dark:focus:ring-indigo-500/70 dark:focus:border-indigo-500/70"
                >
                    <option value="todos">Todos</option>
                    <option value="Pendiente Compra">Pendiente Compra</option>
                    <option value="Comprada">Comprada</option>
                    <option value="Instalada">Instalada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>

            {{-- Proveedor --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-base font-semibold text-slate-700 dark:text-slate-200">
                    Proveedor
                </label>
                <select
                    wire:model.live="filtroProveedor"
                    class="w-full rounded-xl border border-slate-500/80 bg-slate-800/90
                           text-base text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521]
                           dark:focus:ring-indigo-500/70 dark:focus:border-indigo-500/70"
                >
                    <option value="todos">Todos los proveedores</option>
                    @foreach($proveedores as $proveedor)
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
                        {{ $piezasPendientes->total() }}
                    </span>
                    registro(s)
                    @if($search)
                        para "<span class="font-semibold">{{ $search }}</span>"
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- TABLA PRINCIPAL --}}
    <div class="
        rounded-2xl border border-slate-200/80 dark:border-slate-700/80
        bg-white/95 dark:bg-slate-900/95
        shadow-sm overflow-hidden
        transition-all duration-300
        hover:-translate-y-0.5
        hover:shadow-[0_10px_24px_rgba(15,23,42,0.45)]
        hover:border-[#FF9521] dark:hover:border-indigo-400/50
    ">
        <div class="overflow-x-auto">
            <table class="min-w-full text-base text-left">
                <thead class="bg-slate-100 border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800/80">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Pieza faltante</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Estatus pieza</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Proveedor / Lote</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Tiempo en espera</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piezasPendientes as $registro)
                        @php
                            $equipo = $registro->equipo;
                            $pieza  = $registro->pieza;
                            $loteModelo = $equipo->loteModelo ?? null;
                            $lote = $loteModelo->lote ?? null;
                            $proveedor = $lote->proveedor ?? null;

                            $diasEspera = $equipo?->created_at
                                ? now()->diffInDays($equipo->created_at)
                                : null;

                            $badgeEstado = match ($registro->estatus_pieza) {
                                'Pendiente Compra' => 'bg-amber-100 text-amber-900 border-amber-300',
                                'Comprada'         => 'bg-sky-100 text-sky-900 border-sky-300',
                                'Instalada'        => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                'Cancelada'        => 'bg-slate-100 text-slate-900 border-slate-300',
                                default            => 'bg-slate-200 text-slate-800 border-slate-400',
                            };

                            $badgeUrgencia = $diasEspera === null ? null : (
                                $diasEspera >= 30 ? 'CRÍTICO' :
                                ($diasEspera >= 15 ? 'Atención' : 'Reciente')
                            );
                        @endphp

                        <tr class="border-b border-slate-200 dark:border-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                            {{-- Equipo --}}
                            <td class="px-4 py-3 align-top min-w-[220px]">
                                <div class="flex flex-col">
                                    <span class="text-base font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $equipo->marca ?? '—' }} {{ $equipo->modelo ?? '' }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        Serie: <span class="font-mono">{{ $equipo->numero_serie ?? '—' }}</span>
                                    </span>
                                    @if($equipo->tipo_equipo)
                                        <span class="text-xs text-slate-400 uppercase tracking-wide">
                                            {{ $equipo->tipo_equipo }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Pieza faltante --}}
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-col">
                                    <span class="text-base text-slate-900 dark:text-slate-100">
                                        {{ $pieza->nombre ?? '—' }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        Cantidad: {{ $registro->cantidad ?? 1 }}
                                    </span>
                                </div>
                            </td>

                            {{-- Estatus pieza --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-semibold {{ $badgeEstado }}">
                                    {{ $registro->estatus_pieza }}
                                </span>
                            </td>

                            {{-- Proveedor / Lote --}}
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-col">
                                    <span class="text-base text-slate-900 dark:text-slate-100">
                                        {{ $proveedor->nombre_empresa ?? '—' }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        Lote: {{ $lote->nombre_lote ?? '—' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Tiempo en espera --}}
                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                @if($diasEspera !== null)
                                    <span class="text-base text-slate-900 dark:text-slate-50">
                                        {{ $diasEspera }} día(s)
                                    </span>

                                    @if($badgeUrgencia)
                                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            @class([
                                                'bg-red-100 text-red-900 border border-red-300' => $badgeUrgencia === 'CRÍTICO',
                                                'bg-amber-100 text-amber-900 border border-amber-300' => $badgeUrgencia === 'Atención',
                                                'bg-emerald-100 text-emerald-900 border border-emerald-300' => $badgeUrgencia === 'Reciente',
                                            ])
                                        ">
                                            {{ $badgeUrgencia }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-base text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-3 align-top text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 text-sm rounded-xl border border-slate-400/80
                                           text-slate-800 dark:text-slate-100 dark:border-slate-500
                                           hover:bg-slate-100/70 dark:hover:bg-slate-800/80 transition"
                                >
                                    Ver ficha
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-lg text-slate-300">
                                No hay equipos en espera de piezas con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/95 dark:bg-transparent">
            {{ $piezasPendientes->links() }}
        </div>
    </div>
</div>
