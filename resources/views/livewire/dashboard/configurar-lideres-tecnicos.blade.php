<div class="rounded-2xl bg-white/80 dark:bg-slate-950/60
            border border-slate-200/80 dark:border-white/10
            backdrop-blur-xl dark:backdrop-blur-2xl
            shadow-md shadow-slate-900/10 dark:shadow-slate-900/30
            p-6 space-y-6">

    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Líderes trabajando como técnicos
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Marca los líderes que trabajan como técnicos permanentemente en metas y métricas
            </p>
        </div>
    </div>

    {{-- Listado de líderes con toggle --}}
    @if($lideres)
        <div class="space-y-2">
            @foreach($lideres as $lider)
                <button type="button"
                    wire:click="toggleLider({{ $lider['id'] }})"
                    @class([
                        'w-full flex items-center gap-3 rounded-xl px-4 py-3 text-left transition-all duration-150 border-2',
                        'border-emerald-400 bg-emerald-50/80 dark:bg-emerald-900/20 ring-2 ring-emerald-300/50' => $lider['esTecnico'],
                        'border-slate-300/60 dark:border-slate-700 bg-white/60 dark:bg-slate-800/40 hover:border-emerald-300 hover:bg-emerald-50/30' => !$lider['esTecnico'],
                    ])>
                    <div class="flex-1">
                        <p @class([
                            'font-medium text-sm',
                            'text-emerald-700 dark:text-emerald-300' => $lider['esTecnico'],
                            'text-slate-600 dark:text-slate-300' => !$lider['esTecnico'],
                        ])>
                            {{ $lider['nombre'] }}
                        </p>
                    </div>
                    <div @class([
                        'flex items-center justify-center w-5 h-5 rounded-full border-2 text-xs',
                        'border-emerald-400 bg-emerald-500 text-white' => $lider['esTecnico'],
                        'border-slate-300 dark:border-slate-600' => !$lider['esTecnico'],
                    ])>
                        @if($lider['esTecnico'])
                            ✓
                        @endif
                    </div>
                </button>
            @endforeach
        </div>

        @php
            $lideresActivos = array_filter($lideres, fn($l) => $l['esTecnico']);
        @endphp

        @if(empty($lideresActivos))
            <p class="text-xs text-slate-400 italic text-center py-4">
                No hay líderes marcados como técnicos.
            </p>
        @else
            <div class="rounded-lg bg-emerald-50/60 dark:bg-emerald-900/10 border border-emerald-200/60 dark:border-emerald-700/30 p-3">
                <p class="text-xs text-emerald-700 dark:text-emerald-300">
                    <strong>{{ count($lideresActivos) }}</strong>
                    {{ count($lideresActivos) === 1 ? 'líder trabaja' : 'líderes trabajan' }} como técnico de forma permanente
                </p>
            </div>
        @endif
    @else
        <p class="text-xs text-slate-400 italic text-center py-4">
            No hay líderes disponibles en el sistema.
        </p>
    @endif

</div>
