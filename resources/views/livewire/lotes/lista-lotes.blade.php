<div class="space-y-6 relative">

    <x-toast />

    {{-- FILA SUPERIOR: RESUMEN + BUSCADOR --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- TARJETAS RESUMEN --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">

            {{-- TOTAL LOTES --}}
            <div class="rounded-2xl
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
                        hover:border-sky-400/70 dark:hover:border-sky-300/50">
                <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                    Total lotes
                </p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                    {{ $stats['total'] ?? 0 }}
                </p>
            </div>

            {{-- CON FECHA --}}
            <div class="rounded-2xl
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
                        hover:border-amber-400/70">
                <p class="text-xs sm:text-sm font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                    Con fecha
                </p>
                <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                    {{ $stats['con_fecha'] ?? 0 }}
                </p>
            </div>

            {{-- SIN FECHA --}}
            <div class="rounded-2xl
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
                        hover:border-emerald-400/70">
                <p class="text-xs sm:text-sm font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                    Sin fecha
                </p>
                <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                    {{ $stats['sin_fecha'] ?? 0 }}
                </p>
            </div>

            {{-- PROVEEDORES --}}
            <div class="rounded-2xl
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
                        hover:border-indigo-400/70">
                <p class="text-xs sm:text-sm font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">
                    Proveedores
                </p>
                <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">
                    {{ $stats['proveedores'] ?? 0 }}
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
                    placeholder="Lote, proveedor..."
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

    {{-- FILTROS (igual que inventario listo) --}}
    <div class="rounded-2xl
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
                <span class="font-bold text-slate-900 dark:text-slate-50">{{ $lotes->total() }}</span>
                registro(s)
                @if($search)
                    para “<span class="font-semibold">{{ $search }}</span>”
                @endif
            </p>
        </div>

        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

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
                    @foreach ($proveedores as $prov)
                        <option value="{{ $prov->id }}">
                            {{ $prov->nombre_empresa }}
                            @if($prov->abreviacion)
                                ({{ $prov->abreviacion }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Por página --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                    Resultados
                </label>
                <select
                    wire:model.live="perPage"
                    class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm sm:text-base text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                >
                    <option value="10">10 por página</option>
                    <option value="20">20 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>

        </div>
    </div>

    {{-- TABLA LOTES (estilo inventario listo) --}}
    <div class="rounded-2xl
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
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Fecha llegada</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($lotes as $lote)
                    <tr class="border-b border-slate-200 dark:border-slate-800/80
                               hover:bg-white/60 dark:hover:bg-slate-800/60 transition-colors">

                        <td class="px-4 py-3 align-top">
                            <span class="font-semibold text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                {{ $lote->nombre_lote ?? ('Lote #' . $lote->id) }}
                            </span>
                            <div class="text-xs sm:text-sm text-slate-400">
                                ID: {{ $lote->id }}
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top">
                            <span class="text-sm sm:text-base text-slate-900 dark:text-slate-100">
                                {{ $lote->proveedor->nombre_empresa ?? '—' }}
                            </span>
                            @if($lote->proveedor?->abreviacion)
                                <div class="text-xs sm:text-sm text-slate-400">
                                    {{ $lote->proveedor->abreviacion }}
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            @if($lote->fecha_llegada)
                                <span class="text-sm sm:text-base text-slate-900 dark:text-slate-50">
                                    {{ \Carbon\Carbon::parse($lote->fecha_llegada)->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-sm sm:text-base text-slate-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top text-right">
                            <a
                                href="{{ route('lotes.edit', $lote->id) }}"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-xl
                                       bg-blue-600 hover:bg-blue-500 text-white shadow transition-all"
                            >
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm sm:text-lg text-slate-400 dark:text-slate-500">
                            No se encontraron lotes con los filtros actuales.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/40">
            {{ $lotes->links() }}
        </div>
    </div>

</div>
