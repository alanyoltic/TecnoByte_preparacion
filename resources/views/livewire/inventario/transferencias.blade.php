<div class="space-y-6 relative">

    <x-toast />

    {{-- FILA SUPERIOR: TARJETAS + BUSCADOR --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- TARJETAS RESUMEN --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">

            {{-- TOTAL --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
                        border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl px-4 py-3 shadow-md
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-sky-500/20 hover:border-sky-400/70">
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                    Total
                </p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                    {{ $stats['total'] }}
                </p>
            </div>

            {{-- BORRADOR --}}
            <div class="rounded-2xl bg-slate-50/90 dark:bg-slate-900/40
                        border border-slate-200/80 dark:border-slate-600/50
                        backdrop-blur-xl px-4 py-3 shadow-md
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-slate-400/30">
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                    Borrador
                </p>
                <p class="mt-2 text-2xl font-bold text-slate-700 dark:text-slate-200">
                    {{ $stats['borrador'] }}
                </p>
            </div>

            {{-- PENDIENTE --}}
            <div class="rounded-2xl bg-amber-50/90 dark:bg-amber-950/40
                        border border-amber-200/80 dark:border-amber-500/70
                        backdrop-blur-xl px-4 py-3 shadow-md
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-amber-500/40">
                <p class="text-xs font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                    Pendiente
                </p>
                <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                    {{ $stats['pendiente'] }}
                </p>
            </div>

            {{-- APROBADA --}}
            <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40
                        border border-emerald-200/80 dark:border-emerald-500/70
                        backdrop-blur-xl px-4 py-3 shadow-md
                        transition-all duration-300 hover:-translate-y-1
                        hover:shadow-lg hover:shadow-emerald-500/40">
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                    Aprobada
                </p>
                <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                    {{ $stats['aprobada'] }}
                </p>
            </div>

        </div>

        {{-- BUSCADOR --}}
        <div class="w-full lg:w-80">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                Busqueda rapida
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="ID, almacen, creador..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl
                           bg-white/80 dark:bg-slate-900/60
                           border border-white/60 dark:border-slate-700/70
                           text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70
                           backdrop-blur-xl">
            </div>
        </div>

    </div>

    {{-- FILTROS + BOTON NUEVA TRANSFERENCIA --}}
    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70
                border border-slate-200/80 dark:border-white/10
                backdrop-blur-xl shadow-md">

        <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80
                    flex items-center justify-between gap-4 flex-wrap">
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                Filtros
            </h3>

            <div class="flex items-center gap-4 flex-wrap">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    Mostrando
                    <span class="font-bold text-slate-900 dark:text-slate-50">
                        {{ $transferencias->total() }}
                    </span>
                    registro(s)
                    @if($search)
                        para "<span class="font-semibold">{{ $search }}</span>"
                    @endif
                </p>

                @if(auth()->check() && auth()->user()->tienePermiso('transferencias.crear'))
                    <a href="{{ route('inventario.transferencias.crear') }}"
                       class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700
                              text-white text-sm font-semibold shadow-md shadow-blue-600/30
                              transition-all whitespace-nowrap">
                        + Nueva transferencia
                    </a>
                @endif
            </div>
        </div>

        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Filtro estatus --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Estatus
                </label>
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                           border border-white/60 dark:border-slate-600/70
                           text-sm text-slate-900 dark:text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-blue-500/70">
                    <option value="todos">Todos</option>
                    <option value="BORRADOR">Borrador</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="APROBADA">Aprobada</option>
                    <option value="RECHAZADA">Rechazada</option>
                </select>
            </div>

        </div>
    </div>

    {{-- TABLA --}}
    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80
                border border-slate-200/80 dark:border-white/10
                backdrop-blur-xl shadow-md overflow-hidden">

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">

                <thead class="bg-slate-100 dark:bg-slate-950/90">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300"># ID</th>
                        <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Origen</th>
                        <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Destino</th>
                        <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Creado por</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Items</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Estatus</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($transferencias as $tr)
                    <tr class="border-b border-slate-200 dark:border-slate-800/80
                               hover:bg-white/60 dark:hover:bg-slate-800/60 transition">

                        {{-- ID --}}
                        <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-slate-50">
                            #{{ $tr->id }}
                        </td>

                        {{-- FECHA --}}
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">
                            {{ $tr->created_at->format('d/m/Y H:i') }}
                        </td>

                        {{-- ORIGEN --}}
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                         bg-orange-100 text-orange-800 border border-orange-300
                                         dark:bg-orange-900/30 dark:text-orange-200 dark:border-orange-700">
                                {{ $tr->origen->nombre ?? '—' }}
                            </span>
                        </td>

                        {{-- DESTINO --}}
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                         bg-blue-100 text-blue-800 border border-blue-300
                                         dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-700">
                                {{ $tr->destino->nombre ?? '—' }}
                            </span>
                        </td>

                        {{-- CREADO POR --}}
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                            {{ $tr->creador->name ?? '—' }}
                        </td>

                        {{-- TOTAL ITEMS --}}
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700
                                         text-slate-800 dark:text-slate-200 text-xs font-bold">
                                {{ $tr->detalles->count() }}
                            </span>
                        </td>

                        {{-- ESTATUS --}}
                        <td class="px-4 py-3 text-center">
                            @php
                                $colores = [
                                    'BORRADOR'  => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
                                    'PENDIENTE' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-200',
                                    'APROBADA'  => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-200',
                                    'RECHAZADA' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/30 dark:text-red-200',
                                ];
                                $clase = $colores[$tr->estatus] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $clase }}">
                                {{ $tr->estatus }}
                            </span>
                        </td>

                        {{-- ACCIONES --}}
                        <td class="px-4 py-3 text-center">
                            <a href="#"
                               class="px-3 py-1 rounded-xl text-xs font-semibold
                                      bg-blue-600 hover:bg-blue-700 text-white
                                      transition-all shadow-sm">
                                Ver
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            No hay transferencias registradas.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINACION --}}
        <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3
                    bg-white/80 dark:bg-slate-950/40">
            {{ $transferencias->links() }}
        </div>

    </div>

</div>