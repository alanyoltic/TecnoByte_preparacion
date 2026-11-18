<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-toast />

        {{-- TOPBAR GLASS --}}
        <x-topbar
            title="Administración de Almacenes"
            chip="Ventas · Sucursales y Ubicaciones"
            description="Control de almacenes físicos y lógicos, asignación de encargados y consulta de stock en tiempo real."
        >
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('ventas.inventario') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                           bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-800
                           text-slate-700 dark:text-slate-200 border border-slate-300/60 dark:border-white/10
                           shadow-sm hover:shadow transition-all duration-150 active:scale-95"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Volver a Inventario</span>
                </a>
            </div>
        </x-topbar>

        {{-- LISTADO DE GRUPOS DE ALMACENES --}}
        @forelse($almacenesAgrupados as $grupo => $almacenes)
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b border-slate-200/80 dark:border-white/10">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                    <h2 class="text-lg font-black tracking-tight text-slate-800 dark:text-slate-100 uppercase">
                        {{ $grupo }}
                    </h2>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                        {{ count($almacenes) }} ubicaciones
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($almacenes as $almacen)
                        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl p-6 shadow-2xl
                                    flex flex-col justify-between hover:border-[#FF9521]/40 transition-all duration-200">
                            <div>
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-[#FF9521]/10 border border-[#FF9521]/20 flex items-center justify-center text-[#FF9521] shadow-inner">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $almacen->nombre }}</h3>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($almacen->sucursal)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full">
                                                        <span>📍</span> {{ $almacen->sucursal->nombre }}
                                                    </span>
                                                @endif
                                                <span class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">{{ $almacen->tipo }}</span>
                                            </div>
                                            @if($almacen->descripcion)
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $almacen->descripcion }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-3xl font-black text-emerald-500 dark:text-emerald-400">
                                            {{ $almacen->equipos_count }}
                                        </p>
                                        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">equipos</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-white/10">
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                                    Administradores / Responsables del Almacén
                                </label>

                                {{-- Lista de Encargados Actuales --}}
                                <div class="space-y-2 mb-4">
                                    @forelse($almacen->encargados as $encargado)
                                        @if($encargado->user)
                                            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-[#FF9521] flex items-center justify-center text-slate-950 text-xs font-bold shrink-0 shadow-sm">
                                                        {{ strtoupper(substr($encargado->user->nombre, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">
                                                            {{ $encargado->user->nombre }} {{ $encargado->user->apellido_paterno }}
                                                        </p>
                                                        <p class="text-[9px] text-[#FF9521] uppercase tracking-wider font-bold">Encargado de Almacén</p>
                                                    </div>
                                                </div>
                                                <button
                                                    wire:click="removerAdministrador({{ $almacen->id }}, {{ $encargado->user_id }})"
                                                    wire:confirm="¿Estás seguro de remover a este usuario de la administración del almacén?"
                                                    class="text-rose-400 hover:text-rose-300 transition-colors p-1.5 bg-rose-500/10 hover:bg-rose-500/20 rounded-xl"
                                                    title="Remover encargado"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300">
                                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <p class="text-xs font-medium">Sin encargados asignados a esta ubicación.</p>
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Asignar Nuevo Administrador --}}
                                <div class="flex gap-2 items-center">
                                    <select
                                        wire:change="agregarAdministrador({{ $almacen->id }}, $event.target.value); $event.target.value = '';"
                                        class="w-full text-xs font-medium rounded-2xl border-slate-300 dark:border-slate-700
                                               bg-slate-50 dark:bg-slate-950/70 text-slate-800 dark:text-slate-200
                                               focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent py-2.5 px-3 transition-all"
                                    >
                                        <option value="">+ Asignar nuevo encargado...</option>
                                        @foreach($usuarios as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->nombre }} {{ $user->apellido_paterno }} ({{ optional($user->role)->nombre ?? 'Sin rol' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 gap-4 text-slate-400 bg-white/60 dark:bg-slate-900/40 rounded-3xl border border-dashed border-slate-300 dark:border-slate-700">
                <div class="p-4 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                    <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold">No se encontraron almacenes configurados en el sistema.</p>
            </div>
        @endforelse
    </div>
</x-tb-background>
