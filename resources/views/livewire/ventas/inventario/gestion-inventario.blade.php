<div class="p-6 space-y-5">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Inventario</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Vista general del stock disponible en el área de Ventas.
        </p>
    </div>

    {{-- TABS --}}
    <div class="flex gap-1 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800/60 w-fit">
        <button wire:click="$set('tab','equipos')" id="tab-equipos"
                class="px-5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                       {{ $tab === 'equipos'
                           ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-md'
                           : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white' }}">
            🖥 Equipos
        </button>
        <button wire:click="$set('tab','productos')" id="tab-productos"
                class="px-5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                       {{ $tab === 'productos'
                           ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-md'
                           : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white' }}">
            📦 Productos
        </button>
    </div>

    {{-- STATS --}}
    @if($tab === 'equipos')
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['label' => 'Total en Ventas', 'value' => $stats['total'],       'color' => 'bg-slate-100 dark:bg-slate-800',         'text' => 'text-slate-700 dark:text-slate-200'],
            ['label' => 'Disponibles',     'value' => $stats['disponibles'], 'color' => 'bg-emerald-50 dark:bg-emerald-900/30',   'text' => 'text-emerald-700 dark:text-emerald-300'],
            ['label' => 'En Piso',         'value' => $stats['en_piso'],     'color' => 'bg-blue-50 dark:bg-blue-900/30',         'text' => 'text-blue-700 dark:text-blue-300'],
            ['label' => 'Apartados',       'value' => $stats['apartados'],   'color' => 'bg-amber-50 dark:bg-amber-900/30',       'text' => 'text-amber-700 dark:text-amber-300'],
        ] as $s)
        <div class="rounded-2xl p-4 {{ $s['color'] }}">
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $s['label'] }}</p>
            <p class="text-3xl font-black {{ $s['text'] }} mt-1">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['label' => 'SKUs en catálogo', 'value' => $stats['total'],          'color' => 'bg-slate-100 dark:bg-slate-800',         'text' => 'text-slate-700 dark:text-slate-200'],
            ['label' => 'Con stock',         'value' => $stats['con_stock'],      'color' => 'bg-emerald-50 dark:bg-emerald-900/30',   'text' => 'text-emerald-700 dark:text-emerald-300'],
            ['label' => 'Sin stock',         'value' => $stats['sin_stock'],      'color' => 'bg-rose-50 dark:bg-rose-900/30',         'text' => 'text-rose-700 dark:text-rose-300'],
            ['label' => 'Total unidades',    'value' => $stats['total_unidades'], 'color' => 'bg-violet-50 dark:bg-violet-900/30',     'text' => 'text-violet-700 dark:text-violet-300'],
        ] as $s)
        <div class="rounded-2xl p-4 {{ $s['color'] }}">
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $s['label'] }}</p>
            <p class="text-3xl font-black {{ $s['text'] }} mt-1">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- FILTROS --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </span>
            <input wire:model.live.debounce.350ms="busqueda" id="filtro-busqueda-inventario" type="text"
                   placeholder="{{ $tab === 'equipos' ? 'Buscar por serie, marca, modelo…' : 'Buscar por nombre, SKU, marca…' }}"
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-sm
                          bg-white/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700
                          text-slate-800 dark:text-slate-100 placeholder-slate-400
                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 backdrop-blur-md transition"/>
        </div>
        @if(count($almacenesVentas) > 1)
        <select wire:model.live="filtroAlmacen" id="filtro-almacen-inventario"
                class="px-3 py-2 rounded-xl text-sm bg-white/70 dark:bg-slate-800/60
                       border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/50 backdrop-blur-md transition">
            <option value="">Todos los almacenes</option>
            @foreach($almacenesVentas as $alm)
                <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
            @endforeach
        </select>
        @endif
    </div>

    {{-- TABLA EQUIPOS --}}
    @if($tab === 'equipos')
    <div class="overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50
                bg-white/60 dark:bg-slate-900/50 backdrop-blur-md shadow-xl">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200/60 dark:border-slate-700/50
                           bg-slate-50/80 dark:bg-slate-800/60 text-left">
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Equipo</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden sm:table-cell">N° Serie</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden md:table-cell">Almacén</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Área</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-700/40">
                @forelse($items as $equipo)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors duration-150">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">
                            {{ $equipo->marca }} {{ $equipo->modelo }}
                        </p>
                        @if($equipo->tipo_equipo)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $equipo->tipo_equipo }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        <span class="font-mono text-xs text-slate-600 dark:text-slate-300">
                            {{ $equipo->numero_serie ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500 dark:text-slate-400">
                        {{ $equipo->almacen?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $areaLabel = match($equipo->estatus_area) {
                                'DISPONIBLE_VENTA' => 'Disponible',
                                'EN_PISO_VENTA'    => 'En Piso',
                                'APARTADO_CLIENTE' => 'Apartado',
                                default            => $equipo->estatus_area ?? '—',
                            };
                            $areaColor = match($equipo->estatus_area) {
                                'DISPONIBLE_VENTA' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
                                'EN_PISO_VENTA'    => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
                                'APARTADO_CLIENTE' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
                                default            => 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $areaColor }}">
                            {{ $areaLabel }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-sm text-slate-400 dark:text-slate-500">
                        No hay equipos en Ventas que coincidan con los filtros.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TABLA PRODUCTOS --}}
    @else
    <div class="overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50
                bg-white/60 dark:bg-slate-900/50 backdrop-blur-md shadow-xl">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200/60 dark:border-slate-700/50
                           bg-slate-50/80 dark:bg-slate-800/60 text-left">
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Producto</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden sm:table-cell">SKU</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden md:table-cell">Almacén</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 text-right">Stock</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 text-right hidden lg:table-cell">Precio Venta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-700/40">
                @forelse($items as $inv)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors duration-150">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">
                            {{ $inv->producto?->nombre ?? '—' }}
                        </p>
                        @if($inv->producto?->marca)
                        <p class="text-xs text-slate-400">{{ $inv->producto->marca }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        <span class="font-mono text-xs text-slate-500 dark:text-slate-400">
                            {{ $inv->producto?->sku ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500 dark:text-slate-400">
                        {{ $inv->almacen?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex px-2.5 py-1 rounded-xl text-sm font-bold
                                     {{ $inv->cantidad <= 3
                                         ? 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300'
                                         : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' }}">
                            {{ $inv->cantidad }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right hidden lg:table-cell font-semibold text-slate-700 dark:text-slate-200">
                        {{ $inv->producto?->precio_venta ? '$'.number_format($inv->producto->precio_venta, 2) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-16 text-center text-sm text-slate-400 dark:text-slate-500">
                        No hay productos con stock que coincidan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    <div>{{ $items->links() }}</div>

</div>
