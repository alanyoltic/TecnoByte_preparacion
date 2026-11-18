<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-toast />

        {{-- TOPBAR GLASS --}}
        <x-topbar
            title="Inventario de Ventas"
            chip="Ventas · Stock en Tiempo Real"
            description="Supervisión global de laptops disponibles, en piso de venta, apartados y catálogo de accesorios."
        >
            <div class="flex items-center gap-2">
                @can('ventas.almacenes.ver')
                    <a
                        href="{{ route('ventas.inventario.almacenes') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                               bg-white/80 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200
                               border border-slate-300/60 dark:border-white/10 shadow-sm hover:shadow
                               hover:border-[#FF9521]/40 transition-all duration-150 active:scale-95"
                    >
                        <svg class="w-4 h-4 text-[#FF9521]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Administrar Almacenes</span>
                    </a>
                @endcan
            </div>
        </x-topbar>

        {{-- TABS DE NAVEGACIÓN GLASS --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="inline-flex p-1.5 rounded-2xl bg-white/70 dark:bg-slate-900/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl shadow-lg">
                <button wire:click="$set('tab','equipos')" id="tab-equipos"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200
                               {{ $tab === 'equipos'
                                   ? 'bg-[#FF9521] text-slate-950 shadow-md shadow-[#FF9521]/30'
                                   : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <span>💻 Laptops / Equipos</span>
                </button>
                <button wire:click="$set('tab','productos')" id="tab-productos"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200
                               {{ $tab === 'productos'
                                   ? 'bg-[#FF9521] text-slate-950 shadow-md shadow-[#FF9521]/30'
                                   : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <span>📦 Accesorios / Productos</span>
                </button>
            </div>
        </div>

        {{-- STATS CARDS GLASS --}}
        @if($tab === 'equipos')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach([
                    ['label' => 'Total en Ventas', 'value' => $stats['total'],       'badge' => 'Total',        'accent' => 'text-slate-900 dark:text-slate-50', 'ring' => 'border-slate-200 dark:border-slate-700/60'],
                    ['label' => 'Disponibles',     'value' => $stats['disponibles'], 'badge' => 'Listos',       'accent' => 'text-emerald-500 dark:text-emerald-400', 'ring' => 'border-emerald-500/30'],
                    ['label' => 'En Piso de Venta','value' => $stats['en_piso'],     'badge' => 'Exhibición',   'accent' => 'text-blue-500 dark:text-blue-400',       'ring' => 'border-blue-500/30'],
                    ['label' => 'Apartados',       'value' => $stats['apartados'],   'badge' => 'Comprometidos','accent' => 'text-[#FF9521]',                         'ring' => 'border-[#FF9521]/30'],
                ] as $s)
                    <div class="relative overflow-hidden rounded-3xl p-5
                                bg-white/80 dark:bg-slate-900/60
                                border {{ $s['ring'] }}
                                shadow-lg shadow-slate-900/5 dark:shadow-2xl
                                backdrop-blur-xl transition-all duration-200 hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $s['label'] }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $s['badge'] }}
                            </span>
                        </div>
                        <p class="text-3xl sm:text-4xl font-black {{ $s['accent'] }} mt-2 tracking-tight">
                            {{ $s['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach([
                    ['label' => 'SKUs en Catálogo', 'value' => $stats['total'],          'badge' => 'Catálogo',    'accent' => 'text-slate-900 dark:text-slate-50', 'ring' => 'border-slate-200 dark:border-slate-700/60'],
                    ['label' => 'Con Stock Activo', 'value' => $stats['con_stock'],      'badge' => 'En existencia','accent' => 'text-emerald-500 dark:text-emerald-400', 'ring' => 'border-emerald-500/30'],
                    ['label' => 'Agotados',         'value' => $stats['sin_stock'],      'badge' => 'Sin stock',   'accent' => 'text-rose-500 dark:text-rose-400',       'ring' => 'border-rose-500/30'],
                    ['label' => 'Total Unidades',   'value' => $stats['total_unidades'], 'badge' => 'Unidades',    'accent' => 'text-[#FF9521]',                         'ring' => 'border-[#FF9521]/30'],
                ] as $s)
                    <div class="relative overflow-hidden rounded-3xl p-5
                                bg-white/80 dark:bg-slate-900/60
                                border {{ $s['ring'] }}
                                shadow-lg shadow-slate-900/5 dark:shadow-2xl
                                backdrop-blur-xl transition-all duration-200 hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $s['label'] }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $s['badge'] }}
                            </span>
                        </div>
                        <p class="text-3xl sm:text-4xl font-black {{ $s['accent'] }} mt-2 tracking-tight">
                            {{ $s['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- FILTROS GLASS --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </span>
                <input wire:model.live.debounce.350ms="busqueda" id="filtro-busqueda-inventario" type="text"
                       placeholder="{{ $tab === 'equipos' ? 'Buscar por serie, marca, modelo…' : 'Buscar por nombre, SKU, marca…' }}"
                       class="w-full pl-11 pr-4 py-2.5 rounded-2xl text-sm
                              bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-white/10
                              text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500
                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent backdrop-blur-xl transition-all"/>
            </div>
            @if(count($almacenesVentas) > 1)
                <select wire:model.live="filtroAlmacen" id="filtro-almacen-inventario"
                        class="px-4 py-2.5 rounded-2xl text-sm bg-white/80 dark:bg-slate-900/60
                               border border-slate-200/80 dark:border-white/10 text-slate-800 dark:text-slate-200
                               focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent backdrop-blur-xl transition-all">
                    <option value="">Todos los almacenes de ventas</option>
                    @foreach($almacenesVentas as $alm)
                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- TABLA EQUIPOS --}}
        @if($tab === 'equipos')
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-white/10
                        bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200/80 dark:border-white/10
                                       bg-slate-100/60 dark:bg-slate-950/40 text-left">
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Equipo</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden sm:table-cell">N° Serie</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden md:table-cell">Almacén Ubicación</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right">Estatus en Área</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                            @forelse($items as $equipo)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900 dark:text-slate-100">
                                            {{ $equipo->marca }} {{ $equipo->modelo }}
                                        </p>
                                        @if($equipo->tipo_equipo)
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $equipo->tipo_equipo }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 hidden sm:table-cell">
                                        <span class="font-mono text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                            {{ $equipo->numero_serie ?? 'PENDIENTE' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell text-xs font-medium text-slate-600 dark:text-slate-300">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-[#FF9521]"></span>
                                            {{ $equipo->almacen?->nombre ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @php
                                            $areaLabel = match($equipo->estatus_area) {
                                                'DISPONIBLE_VENTA' => 'Disponible',
                                                'EN_PISO_VENTA'    => 'En Piso',
                                                'APARTADO_CLIENTE' => 'Apartado',
                                                'TRANSFERIDO'      => 'En Transferencia',
                                                default            => $equipo->estatus_area ?? '—',
                                            };
                                            $areaColor = match($equipo->estatus_area) {
                                                'DISPONIBLE_VENTA' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                                'EN_PISO_VENTA'    => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                                'APARTADO_CLIENTE' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                                'TRANSFERIDO'      => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                                default            => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $areaColor }}">
                                            {{ $areaLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center text-sm text-slate-400 dark:text-slate-500">
                                        No hay equipos en Ventas que coincidan con los filtros de búsqueda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- TABLA PRODUCTOS --}}
        @else
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-white/10
                        bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200/80 dark:border-white/10
                                       bg-slate-100/60 dark:bg-slate-950/40 text-left">
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Producto / Accesorio</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden sm:table-cell">SKU</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden md:table-cell">Almacén</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right">Existencia</th>
                                <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right hidden lg:table-cell">Precio Sugerido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                            @forelse($items as $inv)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900 dark:text-slate-100">
                                            {{ $inv->producto?->nombre ?? '—' }}
                                        </p>
                                        @if($inv->producto?->marca)
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $inv->producto->marca }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 hidden sm:table-cell">
                                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $inv->producto?->sku ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell text-xs font-medium text-slate-600 dark:text-slate-300">
                                        {{ $inv->almacen?->nombre ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex px-3 py-1 rounded-xl text-xs font-extrabold border
                                                     {{ $inv->cantidad <= 3
                                                         ? 'bg-rose-500/10 text-rose-400 border-rose-500/30'
                                                         : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' }}">
                                            {{ $inv->cantidad }} uds
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right hidden lg:table-cell font-bold text-slate-900 dark:text-slate-100">
                                        {{ $inv->producto?->precio_venta ? '$'.number_format($inv->producto->precio_venta, 2) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400 dark:text-slate-500">
                                        No hay productos registrados con stock que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- PAGINACIÓN --}}
        <div class="pt-2">
            {{ $items->links() }}
        </div>
    </div>
</x-tb-background>
