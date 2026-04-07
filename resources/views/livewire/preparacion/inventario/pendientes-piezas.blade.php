<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_20rem] gap-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl px-4 py-3 shadow-md">
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                    Equipos en espera
                </p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                    {{ $stats['total_equipos'] ?? 0 }}
                </p>
            </div>

            <div class="rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-500/70 backdrop-blur-xl px-4 py-3 shadow-md">
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-200 uppercase tracking-wide">
                    Pendiente compra
                </p>
                <p class="mt-2 text-2xl font-bold text-amber-900 dark:text-amber-100">
                    {{ $stats['pendiente_compra'] ?? 0 }}
                </p>
            </div>

            <div class="rounded-2xl bg-violet-50/90 dark:bg-violet-950/40 border border-violet-200/80 dark:border-violet-500/70 backdrop-blur-xl px-4 py-3 shadow-md">
                <p class="text-sm font-semibold text-violet-800 dark:text-violet-200 uppercase tracking-wide">
                    Compradas
                </p>
                <p class="mt-2 text-2xl font-bold text-violet-900 dark:text-violet-100">
                    {{ $stats['compradas'] ?? 0 }}
                </p>
            </div>

            <div class="rounded-2xl bg-sky-50/90 dark:bg-sky-950/40 border border-sky-200/80 dark:border-sky-500/70 backdrop-blur-xl px-4 py-3 shadow-md">
                <p class="text-sm font-semibold text-sky-800 dark:text-sky-200 uppercase tracking-wide">
                    Listas para instalar
                </p>
                <p class="mt-2 text-2xl font-bold text-sky-900 dark:text-sky-100">
                    {{ $stats['surtidas'] ?? 0 }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-base font-semibold text-slate-700 dark:text-slate-200 mb-2">
                Buscar equipo o pieza
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-lg">
                    🔍
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.500ms="search"
                    placeholder="Serie, marca, modelo o pieza..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm sm:text-base rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/80 text-slate-800 dark:bg-slate-950/80 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] dark:focus:ring-indigo-500/70 dark:focus:border-indigo-500/70"
                >
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl shadow-md">
        <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Filtros
            </h3>
            <p class="hidden sm:block text-base text-slate-600 dark:text-slate-300">
                Mostrando
                <span class="font-bold text-slate-900 dark:text-slate-50">
                    {{ $piezasPendientes->total() }}
                </span>
                solicitud(es)
            </p>
        </div>

        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-base font-semibold text-slate-700 dark:text-slate-200">
                    Estatus
                </label>
                <select
                    wire:model.live="filtroEstatus"
                    class="w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/80 text-slate-800 dark:bg-slate-950/80 dark:text-slate-100 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] dark:focus:ring-indigo-500/70 dark:focus:border-indigo-500/70"
                >
                    <option value="todos">Todos</option>
                    <option value="PENDIENTE">En revision</option>
                    <option value="PENDIENTE_COMPRA">Pendiente compra</option>
                    <option value="COMPRADA">Comprada</option>
                    <option value="SURTIDA_INVENTARIO">Lista para instalar</option>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-base font-semibold text-slate-700 dark:text-slate-200">
                    Proveedor
                </label>
                <select
                    wire:model.live="filtroProveedor"
                    class="w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/80 text-slate-800 dark:bg-slate-950/80 dark:text-slate-100 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-[#FF9521] dark:focus:ring-indigo-500/70 dark:focus:border-indigo-500/70"
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

            <div class="flex items-end md:hidden">
                <p class="text-base text-slate-600 dark:text-slate-300">
                    Mostrando
                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                        {{ $piezasPendientes->total() }}
                    </span>
                    solicitud(es)
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-base text-left">
                <thead class="bg-slate-100/90 border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800/80">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Equipo</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Pieza solicitada</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Estatus</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Solicito / Responsable</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Proveedor / Lote</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Tiempo en espera</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piezasPendientes as $registro)
                        @php
                            $equipo = $registro->equipo_relacionado;
                            $loteModelo = $equipo?->loteModelo;
                            $lote = $loteModelo?->lote;
                            $proveedor = $lote?->proveedor;
                            $diasEspera = $registro->created_at ? now()->diffInDays($registro->created_at) : null;
                            $responsable = $registro->reasignadoA ?? $registro->solicitadoPor;

                            $badgeEstado = match ($registro->estatus) {
                                'PENDIENTE' => 'bg-yellow-100 text-yellow-900 border-yellow-300',
                                'PENDIENTE_COMPRA' => 'bg-amber-100 text-amber-900 border-amber-300',
                                'COMPRADA' => 'bg-violet-100 text-violet-900 border-violet-300',
                                'SURTIDA_INVENTARIO' => 'bg-sky-100 text-sky-900 border-sky-300',
                                default => 'bg-slate-200 text-slate-800 border-slate-400',
                            };

                            $labelEstado = match ($registro->estatus) {
                                'PENDIENTE' => 'En revision',
                                'PENDIENTE_COMPRA' => 'Pendiente compra',
                                'COMPRADA' => 'Comprada',
                                'SURTIDA_INVENTARIO' => 'Lista para instalar',
                                default => $registro->estatus,
                            };
                        @endphp

                        <tr class="border-b border-slate-200 dark:border-slate-800/80 hover:bg-slate-50/80 dark:hover:bg-slate-900/80 transition-colors">
                            <td class="px-4 py-3 align-top min-w-[220px]">
                                <div class="flex flex-col">
                                    <span class="text-base font-semibold text-slate-900 dark:text-slate-50">
                                        {{ trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')) ?: 'Sin equipo vinculado' }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        Serie: <span class="font-mono">{{ $equipo->numero_serie ?? 'N/A' }}</span>
                                    </span>
                                    @if($equipo?->tipo_equipo)
                                        <span class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide">
                                            {{ $equipo->tipo_equipo }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3 align-top min-w-[230px]">
                                <div class="flex flex-col gap-1">
                                    <span class="text-base text-slate-900 dark:text-slate-100">
                                        {{ $registro->nombre_pieza }}
                                    </span>
                                    @if($registro->descripcion_libre)
                                        <span class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ $registro->descripcion_libre }}
                                        </span>
                                    @endif
                                    @if($registro->notas_respuesta)
                                        <span class="text-xs text-slate-400 dark:text-slate-500">
                                            {{ $registro->notas_respuesta }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-semibold {{ $badgeEstado }}">
                                    {{ $labelEstado }}
                                </span>
                            </td>

                            <td class="px-4 py-3 align-top min-w-[220px]">
                                <div class="flex flex-col gap-1">
                                    <span class="text-base text-slate-900 dark:text-slate-100">
                                        {{ $registro->solicitadoPor?->nombre }} {{ $registro->solicitadoPor?->apellido_paterno }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        Responsable: {{ trim(($responsable?->nombre ?? '') . ' ' . ($responsable?->apellido_paterno ?? '')) ?: 'Sin asignar' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-col">
                                    <span class="text-base text-slate-900 dark:text-slate-100">
                                        {{ $proveedor->nombre_empresa ?? 'N/A' }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        Lote: {{ $lote->nombre_lote ?? ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-3 align-top whitespace-nowrap">
                                @if($diasEspera !== null)
                                    <span class="text-base text-slate-900 dark:text-slate-50">
                                        {{ $diasEspera }} dia(s)
                                    </span>
                                @else
                                    <span class="text-base text-slate-400 dark:text-slate-500">N/A</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 align-top text-right">
                                @if(auth()->user()?->tienePermiso('prep.inventario.gestion'))
                                    <a
                                        href="{{ route('inventario.solicitudes.gestionar', ['filtroEstatus' => $registro->estatus]) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-sm rounded-full bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB] text-white shadow-sm shadow-blue-800/50 backdrop-blur-md transition-all duration-200 hover:shadow-blue-500/80 hover:-translate-y-0.5"
                                    >
                                        Gestionar
                                    </a>
                                @else
                                    <span class="text-sm text-slate-400 dark:text-slate-500">Solo consulta</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-lg text-slate-500 dark:text-slate-400">
                                No hay solicitudes activas de piezas con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/70">
            {{ $piezasPendientes->links() }}
        </div>
    </div>
</div>
