<div class="p-6 space-y-6">
    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Administración de Almacenes</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Gestión de almacenes, capacidades y responsables. Solo accesible para gerentes.
        </p>
    </div>

    {{-- TARJETAS DE ALMACÉN --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($almacenes as $almacen)
            @php
                $encargadoActual = $almacen->encargados->firstWhere('activo', 1);
            @endphp
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $almacen->nombre }}</h2>
                                @if($almacen->descripcion)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $almacen->descripcion }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                {{ $almacen->equipos_count }}
                            </p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">equipos</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Administrador del Almacén</label>
                    <select
                        wire:change="asignarAdministrador({{ $almacen->id }}, $event.target.value)"
                        class="w-full text-sm rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-200 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Sin asignar --</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id }}" {{ $encargadoActual && $encargadoActual->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->nombre }} {{ $user->apellido_paterno }}
                            </option>
                        @endforeach
                    </select>

                    <div class="mt-3">
                        @if($encargadoActual && $encargadoActual->user)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/40">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($encargadoActual->user->nombre, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                        {{ $encargadoActual->user->nombre }} {{ $encargadoActual->user->apellido_paterno }}
                                    </p>
                                    <p class="text-[10px] text-slate-500 uppercase tracking-wide">Encargado principal</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-700/40">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <p class="text-xs text-amber-700 dark:text-amber-300">Este almacén no tiene nadie asignado en este momento.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-20 gap-3 text-slate-400 dark:text-slate-500 bg-white/50 dark:bg-slate-900/30 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                <svg class="w-14 h-14 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                <p class="text-sm">No se encontraron almacenes disponibles para administrar.</p>
            </div>
        @endforelse
    </div>
</div>
