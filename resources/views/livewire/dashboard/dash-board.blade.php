<x-tb-background poll="refreshDashboard" :glows="$glows">
    
    
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
        <div class="relative">
            <x-topbar
                title="Dashboard General"
                chip="Mes de {{ $currentMonthName }}"
                description="Meta mensual cumplida: {{ $radialPercent }}% · {{ $viejoSistema ? 'Equipos: '.(($kpis['equiposMes'] ?? 0)) : 'Puntos: '.($breakdown[1]['value'] ?? 0) }}"
            >
                <x-slot:right>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 sm:gap-3">

                        @if($monthFinished)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                        text-[0.7rem] font-medium tracking-wide
                                        bg-amber-500/10 text-amber-600 dark:text-amber-300
                                        border border-amber-400/40">
                                ⚠ Este mes ya terminó
                            </span>
                        @endif

                        {{-- Selector mes --}}
                        <select
                            wire:model.live="selectedMonthValue"
                            class="text-xs sm:text-sm rounded-xl
                                border border-slate-300/80 dark:border-white/15
                                bg-white/80 text-slate-800
                                dark:bg-slate-950/80 dark:text-slate-100
                                py-1.5 pl-2 pr-8
                                shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                focus:outline-none focus:ring-2
                                focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                        >
                            @foreach($monthsOptions as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>


                        

                        {{-- Selector colaborador (solo admin/ceo) --}}
                        @if(!$isTecnico && !empty($colaboradores))
                            <select
                                wire:model.live="selectedColaboradorId"
                                class="text-xs sm:text-sm rounded-xl
                                    border border-slate-300/80 dark:border-white/15
                                    bg-white/80 text-slate-800
                                    dark:bg-slate-950/80 dark:text-slate-100
                                    py-1.5 pl-2 pr-8
                                    shadow-inner shadow-slate-200/80 dark:shadow-black/40
                                    focus:outline-none focus:ring-2
                                    focus:ring-[#FF9521]/60 focus:border-[#FF9521]"
                            >
                                <option value="">Todos los colaboradores</option>
                                @foreach($colaboradores as $col)
                                    <option value="{{ $col['id'] }}">{{ $col['nombre'] }}</option>
                                @endforeach
                            </select>
                        @endif

                        <button
                            type="button"
                            wire:click="refreshDashboard"
                            class="hidden sm:inline-flex items-center gap-2
                                px-3.5 py-1.5 rounded-full text-xs font-medium
                                bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                text-white
                                shadow-lg shadow-blue-800/60
                                backdrop-blur-xl
                                transition-all duration-200
                                hover:shadow-blue-500/80 hover:-translate-y-0.5"
                        >
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                            Vista del mes
                        </button>

                        @if(!$viejoSistema && ($esLiderGerente || $isTecnico))
                        <button
                            type="button"
                            wire:click="descargarReportePuntos"
                            class="hidden sm:inline-flex items-center gap-2
                                px-3.5 py-1.5 rounded-full text-xs font-medium
                                bg-emerald-600/90 text-white
                                shadow-lg shadow-emerald-900/40
                                backdrop-blur-xl
                                transition-all duration-200
                                hover:bg-emerald-500 hover:shadow-emerald-700/60 hover:-translate-y-0.5"
                            title="Descargar historial de puntos de este periodo"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Exportar Puntos
                        </button>
                        @endif

                    </div>
                </x-slot:right>
            </x-topbar>


             {{-- ✅ Dots centrados dentro del Topbar --}}
    <div
        class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-center"
    >
        <div
        x-data="{ slide: 0 }"
        @tb-slide-changed.window="slide = $event.detail.slide"
            class="pointer-events-auto inline-flex items-center gap-2
                   px-2 py-1 rounded-full
                   bg-white/50 dark:bg-slate-950/45
                   border border-slate-200/60 dark:border-white/10
                   backdrop-blur-xl shadow-sm shadow-slate-900/10 dark:shadow-black/30"
            x-data
        >
            {{-- Dot: Dashboard --}}
            <button
                type="button"
                @click="$dispatch('tb-dashboard-tab', { tab: 'dashboard' })"
                class="w-2.5 h-2.5 rounded-full transition-all duration-200"
                :class="slide === 0
                    ? 'bg-[#2563EB] shadow-[0_0_0_4px_rgba(37,99,235,0.18)]'
                    : 'bg-slate-300/70 dark:bg-white/15 hover:bg-slate-400/70 dark:hover:bg-white/25'"
                aria-label="Dashboard"
            ></button>


            {{-- Dot: Empleado del mes --}}
            <button
                type="button"
                @click="$dispatch('tb-dashboard-tab', { tab: 'empleado' })"
                class="w-2.5 h-2.5 rounded-full transition-all duration-200"
                :class="slide === 1
                    ? 'bg-[#2563EB] shadow-[0_0_0_4px_rgba(37,99,235,0.18)]'
                    : 'bg-slate-300/70 dark:bg-white/15 hover:bg-slate-400/70 dark:hover:bg-white/25'"
                aria-label="Empleado del mes"
            ></button>

        </div>
    </div>
</div>


        {{-- CONTENIDO --}}
<div
    x-data="{
        slide: 0,
        total: 2,
        durations: [5 * 60 * 1000, 2 * 60 * 1000], // [dashboard, empleado]

        scheduleAuto(){
            // Evitar timers duplicados (por re-render Livewire o navegacion interna)
            if (window.__TB_DASH_CAROUSEL_TIMER__) {
                clearTimeout(window.__TB_DASH_CAROUSEL_TIMER__);
                window.__TB_DASH_CAROUSEL_TIMER__ = null;
            }

            const ms = this.durations?.[this.slide] ?? 0;
            if (!ms) return;

            const tick = () => {
                // Si la pestaña esta oculta, no avanzamos el carrusel
                if (document.hidden) {
                    window.__TB_DASH_CAROUSEL_TIMER__ = setTimeout(tick, 1000);
                    return;
                }

                this.go(this.slide === 0 ? 1 : 0);
            };

            window.__TB_DASH_CAROUSEL_TIMER__ = setTimeout(tick, ms);
        },

        init(){
            // Siempre iniciar en Dashboard (no persistir ultima vista)
            this.slide = 0;

            // Limpiar cualquier ?tab=... previo (por bookmarks o navegacion anterior)
            const url = new URL(window.location.href);
            if (url.searchParams.has('tab')) {
                url.searchParams.delete('tab');
                window.history.replaceState({}, '', url);
            }

            // Exponer estado global para los dots del topbar
            window.__TB_ACTIVE_TAB__ = this.slide === 0 ? 'dashboard' : 'empleado';
            window.__TB_DASH_CAROUSEL_INSTANCE__ = this;

            // Al iniciar (si estás en dashboard), ajusta charts
            this.$nextTick(() => {
                if (this.slide === 0) window.TB_DASH_RESIZE?.();
            });

            // Auto-rotacion: 5 min dashboard -> 2 min empleado -> ...
            this.scheduleAuto();

            // Reanudar el timer al volver a la pestaña
            if (!window.__TB_DASH_VIS_LISTENER__) {
                window.__TB_DASH_VIS_LISTENER__ = true;
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) window.__TB_DASH_CAROUSEL_INSTANCE__?.scheduleAuto?.();
                });
            }
        },

        go(i){
            this.slide = (i + this.total) % this.total;

            // Actualizar estado global (para pintar dots)
            window.__TB_ACTIVE_TAB__ = this.slide === 0 ? 'dashboard' : 'empleado';

            // Al volver al dashboard, reajusta charts
            if (this.slide === 0){
                this.$nextTick(() => window.TB_DASH_RESIZE?.());
            }
            this.$dispatch('tb-slide-changed', { slide: this.slide });

            // Si el usuario cambia manualmente, reinicia el conteo desde el slide actual
            this.scheduleAuto();
        },

        // 🔹 Evento que viene desde los dots del Topbar
        setFromTopbar(tab){
            if (tab === 'dashboard') this.go(0);
            if (tab === 'empleado')  this.go(1);
        }
    }"
    @tb-dashboard-tab.window="setFromTopbar($event.detail.tab)"
>




            {{-- Slides --}}
            <div class="relative rounded-3xl">

                {{-- Confeti: solo para el empleado del mes al entrar --}}
                @php
                    $empleadoMesId = data_get($empleadoMes, 'id');
                @endphp
                @if(auth()->id() && !empty($empleadoMesId) && (int) auth()->id() === (int) $empleadoMesId)
                    <div
                        x-data="{
                            show: false,
                            hasPlayed: false,
                            start(){
                                if (this.hasPlayed) return;
                                const key = 'tb_confetti_empleado_mes_{{ $selectedMonthValue }}_{{ auth()->id() }}';
                                const storage = window.localStorage;
                                const seen = storage.getItem(key);
                                if (seen === '1') return;
                                storage.setItem(key, '1');
                                this.hasPlayed = true;
                                this.show = true;
                                setTimeout(() => { this.show = false; }, 4500);
                            }
                        }"
                        @tb-dashboard-tab.window="if ($event.detail.tab === 'empleado') start()"
                        class="pointer-events-none absolute inset-0 z-30 overflow-visible"
                    >
                        <div x-show="show" x-transition.opacity.duration.250ms class="absolute inset-0 overflow-visible">
                            @for($i = 0; $i < 90; $i++)
                                <span
                                    class="tb-confetti-piece"
                                    style="--x: {{ rand(-18, 18) }}vw; --y: {{ rand(-10, -2) }}vh; --tx: {{ rand(-60, 60) }}vw; --ty: {{ rand(84, 138) }}vh; --d: {{ rand(0, 1200) }}ms; --s: {{ rand(10, 22) }}px; --r: {{ rand(0, 360) }}deg; --c: hsl({{ rand(0, 360) }}, 92%, 60%); --br: {{ rand(0, 100) < 25 ? '9999px' : '2px' }};"
                                ></span>
                            @endfor
                        </div>
                    </div>
                @endif

                <div class="overflow-hidden rounded-3xl">
                    <div
                        class="flex transition-transform duration-500 ease-out"
                        :style="`transform: translateX(-${slide * 100}%);`"
                    >

                    {{-- ===================== SLIDE 1: DASHBOARD ACTUAL ===================== --}}
                    <section class="w-full shrink-0">

                        {{-- ACCESOS RÁPIDOS (solo gerentes/gestión inventario) --}}
                        @if(auth()->user()?->tienePermiso('prep.inventario.gestion'))
                        <div class="mb-6 rounded-2xl bg-white/80 dark:bg-slate-950/60
                                    border border-slate-200/80 dark:border-white/10
                                    backdrop-blur-xl shadow-md px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-3">
                                Accesos rápidos · Inventario
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                                <a href="{{ route('preparacion.catalogo-piezas') }}"
                                   class="flex flex-col items-center justify-center gap-1.5 rounded-xl px-3 py-4
                                          bg-blue-50 dark:bg-blue-900/20 border border-blue-200/70 dark:border-blue-700/40
                                          text-blue-700 dark:text-blue-300
                                          hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:-translate-y-0.5
                                          transition-all duration-200 text-center">
                                    <span class="text-xl">📦</span>
                                    <span class="text-xs font-semibold leading-tight">Catálogo &amp; Compras</span>
                                    <span class="text-[0.65rem] text-blue-500 dark:text-blue-400">Registrar entradas</span>
                                </a>

                                <a href="{{ route('inventario.compras') }}"
                                   class="flex flex-col items-center justify-center gap-1.5 rounded-xl px-3 py-4
                                          bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200/70 dark:border-indigo-700/40
                                          text-indigo-700 dark:text-indigo-300
                                          hover:bg-indigo-100 dark:hover:bg-indigo-900/40 hover:-translate-y-0.5
                                          transition-all duration-200 text-center">
                                    <span class="text-xl">🧾</span>
                                    <span class="text-xs font-semibold leading-tight">Historial Compras</span>
                                    <span class="text-[0.65rem] text-indigo-500 dark:text-indigo-400">Ver registros</span>
                                </a>

                                <a href="{{ route('inventario.gestion') }}"
                                   class="flex flex-col items-center justify-center gap-1.5 rounded-xl px-3 py-4
                                          bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/70 dark:border-emerald-700/40
                                          text-emerald-700 dark:text-emerald-300
                                          hover:bg-emerald-100 dark:hover:bg-emerald-900/40 hover:-translate-y-0.5
                                          transition-all duration-200 text-center">
                                    <span class="text-xl">🗄️</span>
                                    <span class="text-xs font-semibold leading-tight">Gestión General</span>
                                    <span class="text-[0.65rem] text-emerald-500 dark:text-emerald-400">Stock y piezas</span>
                                </a>

                                <a href="{{ route('inventario.solicitudes.gestionar') }}"
                                   class="flex flex-col items-center justify-center gap-1.5 rounded-xl px-3 py-4
                                          bg-amber-50 dark:bg-amber-900/20 border border-amber-200/70 dark:border-amber-700/40
                                          text-amber-700 dark:text-amber-300
                                          hover:bg-amber-100 dark:hover:bg-amber-900/40 hover:-translate-y-0.5
                                          transition-all duration-200 text-center">
                                    <span class="text-xl">🔧</span>
                                    <span class="text-xs font-semibold leading-tight">Solicitudes Piezas</span>
                                    <span class="text-[0.65rem] text-amber-500 dark:text-amber-400">Gestionar técnicos</span>
                                </a>

                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                            <div class="lg:col-span-2 space-y-6">

                                {{-- KPIs --}}
                                @php
                                    $cardClass = "group relative
                                                bg-white/80 dark:bg-slate-950/60
                                                border border-slate-200/80 dark:border-white/10
                                                backdrop-blur-xl dark:backdrop-blur-2xl
                                                rounded-2xl
                                                shadow-md shadow-slate-900/10
                                                dark:shadow-lg dark:shadow-slate-900/30
                                                px-4 py-5 flex flex-col gap-2
                                                transition-all duration-300 ease-out
                                                will-change-transform
                                                hover:-translate-y-1
                                                hover:shadow-lg hover:shadow-indigo-500/20
                                                dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25";

                                    $cardGlow = "after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2
                                                after:-bottom-3 after:w-[85%] after:h-10 after:rounded-full
                                                after:bg-gradient-to-r after:from-transparent after:via-[#3B82F6]/0 after:to-transparent
                                                after:blur-2xl after:opacity-0 after:transition-opacity after:duration-300
                                                after:-z-10
                                                group-hover:after:opacity-100 group-hover:after:via-[#3B82F6]/25";

                                    $cardGlow2 = "before:content-[''] before:absolute before:left-1/2 before:-translate-x-1/2
                                                before:-bottom-4 before:w-[75%] before:h-8 before:rounded-full
                                                before:bg-gradient-to-r before:from-transparent before:via-[#FF9521]/0 before:to-transparent
                                                before:blur-2xl before:opacity-0 before:transition-opacity before:duration-300
                                                before:-z-10
                                                group-hover:before:opacity-100 group-hover:before:via-[#FF9521]/18";

                                    $panelClass = "group relative
                                                bg-white/80 dark:bg-slate-950/60
                                                border border-slate-200/80 dark:border-white/10
                                                backdrop-blur-xl dark:backdrop-blur-2xl
                                                rounded-2xl
                                                shadow-md shadow-slate-900/10
                                                dark:shadow-lg dark:shadow-slate-900/30
                                                transition-all duration-300 ease-out
                                                will-change-transform
                                                hover:-translate-y-1
                                                hover:shadow-lg hover:shadow-indigo-500/20
                                                dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25";

                                    $panelGlow = "after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2
                                                after:-bottom-3 after:w-[85%] after:h-10 after:rounded-full
                                                after:bg-gradient-to-r after:from-transparent after:via-[#3B82F6]/0 after:to-transparent
                                                after:blur-2xl after:opacity-0 after:transition-opacity after:duration-300
                                                after:-z-10
                                                group-hover:after:opacity-100 group-hover:after:via-[#3B82F6]/22";

                                    $panelGlow2 = "before:content-[''] before:absolute before:left-1/2 before:-translate-x-1/2
                                                before:-bottom-4 before:w-[75%] before:h-8 before:rounded-full
                                                before:bg-gradient-to-r before:from-transparent before:via-[#FF9521]/0 before:to-transparent
                                                before:blur-2xl before:opacity-0 before:transition-opacity before:duration-300
                                                before:-z-10
                                                group-hover:before:opacity-100 group-hover:before:via-[#FF9521]/14";

                                    $labelClass = "text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400";
                                    $valueClass = "text-3xl font-bold text-slate-900 dark:text-slate-50";
                                    $badgeClass = "inline-flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-500";
                                 @endphp

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                    <div class="{{ $cardClass }} {{ $cardGlow }} {{ $cardGlow2 }}">
                                        <span class="{{ $labelClass }}">Equipos Hechos ({{ $labelDia }})</span>
                                        <span class="{{ $valueClass }}">{{ $kpis['equiposHoy'] ?? 0 }}</span>
                                        @php
                                            $change = $kpis['hoy_change'] ?? '0%';
                                            $isPositive = str_contains($change, '+');
                                            $colorClass = $isPositive
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400';
                                        @endphp

                                        

                                        <span class="inline-flex items-center text-xs font-semibold {{ $colorClass }}">

                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                            {{ $kpis['hoy_change'] ?? '' }}
                                        </span>
                                    </div>

                                    <div class="{{ $cardClass }} {{ $cardGlow }} {{ $cardGlow2 }}">
                                        <span class="{{ $labelClass }}">Equipos Hechos ({{ $labelSemana }})</span>
                                        <span class="{{ $valueClass }}">{{ $kpis['equiposSemana'] ?? 0 }}</span>
                                        @php
                                            $change = $kpis['semana_change'] ?? '0%';
                                            $isPositive = str_contains($change, '+');
                                            $colorClass = $isPositive
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400';
                                        @endphp

                                        <span class="inline-flex items-center text-xs font-semibold {{ $colorClass }}">



                                        
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                            {{ $kpis['semana_change'] ?? '' }}
                                        </span>
                                    </div>

                                    <div class="{{ $cardClass }} {{ $cardGlow }} {{ $cardGlow2 }}">
                                        <span class="{{ $labelClass }}">Equipos Hechos ({{ $labelMes }})</span>
                                        <span class="{{ $valueClass }}">{{ $kpis['equiposMes'] ?? 0 }}</span>
                                        @php
                                            $change = $kpis['mes_change'] ?? '0%';
                                            $isPositive = str_contains($change, '+');
                                            $colorClass = $isPositive
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400';
                                        @endphp

                                    <span class="inline-flex items-center text-xs font-semibold {{ $colorClass }}"> 

                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                            {{ $kpis['mes_change'] ?? '' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Line Chart --}}
                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                            Equipos por Semana
                                        </h3>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $currentMonthName }}
                                        </span>
                                    </div>

                                    <div id="line-chart" class="mt-4 min-h-[260px] overflow-hidden" wire:ignore></div>
                                </div>

                                {{-- Bar Chart --}}
                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $isTecnico ? 'Comparativa de tu producción' : 'Producción por Técnico' }}
                                        </h3>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Distribución de equipos</span>
                                    </div>

                                    <div id="bar-chart" class="mt-4 min-h-[260px] overflow-hidden" wire:ignore></div>
                                </div>

                            </div>

                            {{-- Columna derecha --}}
                            <div class="lg:col-span-1 space-y-6">

                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <div class="flex items-start justify-between mb-1">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                            Avance de Meta Mensual
                                        </h3>
                                        @if($esLiderGerente && !$viejoSistema)
                                        <div class="flex items-center gap-2">
                                            <button wire:click="$set('showModalLideres', true)"
                                                    title="Configurar líderes que trabajan como técnicos"
                                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                                           bg-slate-700/60 hover:bg-emerald-500/20
                                                           border border-slate-600/60 hover:border-emerald-500/50
                                                           text-slate-400 hover:text-emerald-500 text-xs transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                                </svg>
                                                Líderes
                                            </button>
                                            <button wire:click="abrirModalMeta"
                                                    title="Editar meta mensual"
                                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                                           bg-slate-700/60 hover:bg-[#FF9521]/20
                                                           border border-slate-600/60 hover:border-[#FF9521]/50
                                                           text-slate-400 hover:text-[#FF9521] text-xs transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-.707.464l-3.5 1 1-3.5a2 2 0 01.464-.707z"/>
                                                </svg>
                                                Editar meta
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">
                                        Progreso general de la cuota mensual.
                                    </p>

                                    <div id="radial-chart" class="mt-2 min-h-[290px]" wire:ignore></div>
                                </div>

                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-3">
                                        Detalle de Meta Mensual
                                    </h3>

                                    @if($viejoSistema)
                                    <div class="mb-3 px-3 py-2 rounded-lg bg-amber-500/15 border border-amber-500/40 text-amber-300 text-xs leading-snug">
                                        Datos históricos — este periodo usa el sistema anterior (conteo de equipos). Desde abril 2026 se mide en puntos por clasificación.
                                    </div>
                                    @endif

                                    <div class="space-y-3">
                                        @foreach($breakdown as $item)
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-slate-700 dark:text-slate-300">{{ $item['label'] }}</span>
                                                <span class="font-semibold text-slate-900 dark:text-slate-50">{{ $item['value'] }}</span>
                                            </div>

                                            @if (!$loop->last)
                                                <div class="h-px bg-gradient-to-r from-transparent via-slate-300/70 dark:via-slate-600/70 to-transparent"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- CARRUSEL DE AVISOS --}}
                                @php
                                    $avisosData = $avisos ?? [];

                                    if ($avisosData instanceof \Illuminate\Support\Collection) {
                                        $avisosData = $avisosData->values()->toArray();
                                    }

                                    $avisosData = array_map(function ($a) {
                                        if (is_object($a) && method_exists($a, 'toArray')) return $a->toArray();
                                        if (is_object($a)) return (array) $a;
                                        return $a;
                                    }, is_array($avisosData) ? $avisosData : []);
                                @endphp

                                <div
                                    x-data="{
                                        items: @js($avisosData),
                                        index: 0,
                                        dir: 1,
                                        timer: null,
                                        autoplay: true,
                                        reducedMotion: false,

                                        init(){
                                            this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                                            this.autoplay = this.items.length > 1 && !this.reducedMotion;
                                            if (this.autoplay) this.start();
                                        },
                                        start(){
                                            if (!this.autoplay || this.items.length <= 1 || this.reducedMotion) return;
                                            this.stop();
                                            this.timer = setInterval(() => this.next(), 9000);
                                        },
                                        stop(){ if (this.timer) clearInterval(this.timer); this.timer = null; },
                                        toggleAutoplay(){
                                            if (this.items.length <= 1 || this.reducedMotion) return;
                                            this.autoplay = !this.autoplay;
                                            if (this.autoplay) this.start();
                                            else this.stop();
                                        },

                                        next(){
                                            if (!this.items.length) return;
                                            this.dir = 1;
                                            this.index = (this.index + 1) % this.items.length;
                                        },
                                        prev(){
                                            if (!this.items.length) return;
                                            this.dir = -1;
                                            this.index = (this.index - 1 + this.items.length) % this.items.length;
                                        },
                                        goto(i){
                                            if (!this.items.length) return;
                                            this.dir = i > this.index ? 1 : -1;
                                            this.index = i;
                                        },

                                        badgeClasses(color){
                                            switch(color){
                                                case 'amber': return 'bg-amber-500/10 text-amber-300 border-amber-400/30';
                                                case 'emerald': return 'bg-emerald-500/10 text-emerald-300 border-emerald-400/30';
                                                case 'blue': return 'bg-blue-500/10 text-blue-300 border-blue-400/30';
                                                case 'rose': return 'bg-rose-500/10 text-rose-300 border-rose-400/30';
                                                default: return 'bg-slate-500/10 text-slate-200 border-white/10';
                                            }
                                        },
                                        panelClasses(color){
                                            switch(color){
                                                case 'amber': return 'border-amber-300/50 dark:border-amber-500/30 bg-gradient-to-br from-amber-50/80 to-yellow-50/60 dark:from-amber-900/20 dark:to-yellow-900/10';
                                                case 'emerald': return 'border-emerald-300/50 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-50/80 to-teal-50/60 dark:from-emerald-900/20 dark:to-teal-900/10';
                                                case 'blue': return 'border-blue-300/50 dark:border-blue-500/30 bg-gradient-to-br from-blue-50/80 to-cyan-50/60 dark:from-blue-900/20 dark:to-cyan-900/10';
                                                case 'rose': return 'border-rose-300/50 dark:border-rose-500/30 bg-gradient-to-br from-rose-50/80 to-pink-50/60 dark:from-rose-900/20 dark:to-pink-900/10';
                                                default: return 'border-slate-200/70 dark:border-white/15 bg-white/70 dark:bg-slate-900/35';
                                            }
                                        }
                                    }"
                                    x-init="init()"
                                    @mouseenter="stop()"
                                    @mouseleave="if (autoplay) start()"
                                    role="region"
                                    aria-label="Avisos del sistema"
                                    :aria-live="autoplay ? 'off' : 'polite'"
                                    class="group relative overflow-hidden
                                        bg-white/80 dark:bg-slate-950/60
                                        border border-slate-200/80 dark:border-white/10
                                        backdrop-blur-xl dark:backdrop-blur-2xl
                                        rounded-2xl
                                        shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30
                                        p-5 lg:p-6
                                        transition-all duration-300 ease-out
                                        hover:-translate-y-1
                                        hover:shadow-lg hover:shadow-indigo-500/20
                                        dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
                                >
                                    {{-- Brillo inferior trasero --}}
                                    <div class="pointer-events-none absolute -bottom-14 left-10 right-10 h-24
                                                bg-gradient-to-r from-transparent via-indigo-500/25 to-transparent
                                                blur-2xl opacity-0 transition-opacity duration-300
                                                group-hover:opacity-100"></div>

                                    {{-- Header --}}
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                                Avisos
                                            </h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-semibold tracking-wide border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/80 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300">
                                                <span x-text="items.length"></span>&nbsp;activos
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2" x-show="items.length > 1">
                                            <button
                                                type="button"
                                                @click="toggleAutoplay()"
                                                class="inline-flex items-center justify-center rounded-full px-2.5 h-8 text-xs
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                :aria-label="autoplay ? 'Pausar rotación de avisos' : 'Reanudar rotación de avisos'"
                                                x-text="autoplay ? 'Pausar' : 'Reanudar'"
                                            ></button>
                                            <span x-show="!autoplay" class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-semibold border border-amber-300/60 dark:border-amber-500/30 bg-amber-50/80 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">
                                                En pausa
                                            </span>

                                            <button
                                                type="button"
                                                @click="prev()"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                aria-label="Aviso anterior"
                                            >‹</button>

                                            <button
                                                type="button"
                                                @click="next()"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
                                                aria-label="Siguiente aviso"
                                            >›</button>
                                        </div>
                                    </div>

                                    {{-- Slide --}}
                                    <template x-if="items.length">
                                        <div class="relative overflow-hidden">
                                            <div class="relative min-h-[110px]">

                                                <template x-for="(it, i) in items" :key="i">
                                                    <div
                                                        x-show="index === i"
                                                        class="absolute inset-0"
                                                        x-transition:enter="transition transform ease-out duration-400"
                                                        x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100"
                                                        x-transition:leave="transition transform ease-in duration-300"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                    >
                                                        <div
                                                            class="space-y-3 rounded-2xl border p-3.5 sm:p-4 relative overflow-hidden"
                                                            :class="[panelClasses(it.color), reducedMotion ? '' : (dir === 1
                                                                ? 'animate-[slideInFromRight_.4s_ease-out]'
                                                                : 'animate-[slideInFromLeft_.4s_ease-out]')]"
                                                        >
                                                            <div class="pointer-events-none absolute -right-6 -top-6 w-20 h-20 rounded-full bg-white/45 dark:bg-white/5 blur-xl"></div>
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="flex items-start gap-3">
                                                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center
                                                                                bg-white/75 dark:bg-white/5
                                                                                border border-slate-200/70 dark:border-white/10 shadow-sm">
                                                                        <span class="text-lg" x-text="it.icono ?? '📌'"></span>
                                                                    </div>

                                                                    <div class="space-y-1">
                                                                        <p class="text-[0.95rem] font-semibold text-slate-900 dark:text-slate-50" x-text="it.titulo"></p>
                                                                        <p class="text-[0.8rem] leading-relaxed text-slate-700 dark:text-slate-300" x-text="it.texto"></p>
                                                                    </div>
                                                                </div>

                                                                <span
                                                                    class="inline-flex items-center px-2 py-0.5 rounded-full
                                                                        text-[0.65rem] font-semibold border"
                                                                    :class="badgeClasses(it.color)"
                                                                    x-text="it.tag"
                                                                ></span>
                                                            </div>

                                                            {{-- Dots --}}
                                                            <div class="flex items-center justify-between gap-2 pt-1">
                                                                <span class="text-[0.68rem] text-slate-600 dark:text-slate-300" x-show="items.length > 1" x-text="`Aviso ${index + 1} de ${items.length}`"></span>
                                                                <div class="flex items-center gap-1.5" x-show="items.length > 1">
                                                                <template x-for="(dot, di) in items" :key="di">
                                                                    <button
                                                                        type="button"
                                                                        @click="goto(di)"
                                                                        class="w-2.5 h-2.5 rounded-full transition-all duration-200"
                                                                        :class="index === di
                                                                            ? 'bg-[#FF9521] shadow-[0_0_0_4px_rgba(255,149,33,0.15)]'
                                                                            : 'bg-slate-300/70 dark:bg-white/15'"
                                                                        :aria-label="`Ir al aviso ${di + 1}`"
                                                                    ></button>
                                                                </template>
                                                            </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                            </div>
                                        </div>
                                    </template>

                                    {{-- Empty --}}
                                    <template x-if="!items.length">
                                        <div class="text-sm text-slate-600 dark:text-slate-300">
                                            No hay avisos por el momento.
                                        </div>
                                    </template>

                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- ========================= --}}
                    {{--  EMPLEADO DEL MES · HERO  --}}
                    {{-- ========================= --}}
                    {{-- ========================= --}}
                
                <section class="w-full shrink-0">
                    <div class="w-full px-4 sm:px-6 lg:px-8">
                        <div class="min-h-[calc(100vh-220px)] w-full flex items-center justify-center">
                            <div class="w-full max-w-xl sm:max-w-2xl mx-auto">

                                <div class="group relative overflow-hidden
                                    bg-white/80 dark:bg-slate-950/60
                                    border border-slate-200/80 dark:border-white/10
                                    backdrop-blur-xl dark:backdrop-blur-2xl
                                    rounded-2xl
                                    shadow-md shadow-slate-900/10
                                    dark:shadow-lg dark:shadow-slate-900/30
                                    transition-all duration-300 ease-out
                                    will-change-transform
                                    hover:-translate-y-1
                                    hover:shadow-lg hover:shadow-indigo-500/20
                                    dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
                                    p-7 sm:p-9
                                ">
                                    {{-- Brillos --}}
                                    <div class="pointer-events-none absolute -bottom-14 left-10 right-10 h-24
                                                bg-gradient-to-r from-transparent via-indigo-500/25 to-transparent
                                                blur-2xl opacity-0 transition-opacity duration-300
                                                group-hover:opacity-100"></div>

                                    <div class="pointer-events-none absolute -top-16 -left-16 w-72 h-72 rounded-full
                                                bg-[#2563EB]/16 blur-3xl"></div>

                                    <div class="pointer-events-none absolute -bottom-24 -right-20 w-80 h-80 rounded-full
                                                bg-[#FF9521]/12 blur-3xl"></div>

                                    {{-- Header --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl
                                                        bg-slate-900/5 dark:bg-white/5
                                                        border border-slate-200/60 dark:border-white/10">
                                                ⭐
                                            </span>

                                            <div>
                                                <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-50 leading-tight">
                                                    Empleado del mes
                                                </h3>
                                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                                                    Reconocimiento destacado
                                                </p>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full
                                                    text-xs font-medium tracking-wide
                                                    bg-blue-500/10 text-blue-700 dark:text-blue-300
                                                    border border-blue-400/30">
                                            {{ $currentMonthName ?? 'Mes actual' }}
                                        </span>
                                    </div>

                                    {{-- Avatar + Nombre --}}
                                    <div class="mt-8 flex flex-col items-center text-center">
                                        <div class="relative">
                                            <div class="absolute inset-0 rounded-[34px]
                                                        bg-gradient-to-r from-[#2563EB]/35 via-transparent to-[#FF9521]/25
                                                        blur-2xl"></div>

                                            <div class="relative w-40 h-40 sm:w-44 sm:h-44 rounded-[34px]
                                                        bg-white/60 dark:bg-slate-950/60
                                                        border border-slate-200/70 dark:border-white/10
                                                        shadow-xl shadow-slate-900/10 dark:shadow-black/35
                                                        overflow-hidden
                                                        flex items-center justify-center">
                                                {{-- placeholder --}}
@if(!empty($empleadoMes['foto_perfil']))
    <img
        src="{{ asset('storage/' . $empleadoMes['foto_perfil']) }}"
        alt="Foto empleado del mes"
        class="w-full h-full object-cover"
        loading="lazy"
    >
@else
    {{-- placeholder --}}
    <svg class="w-20 h-20 text-slate-400/70 dark:text-slate-300/30" viewBox="0 0 24 24" fill="none">
        <path d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5Z" fill="currentColor"/>
        <path d="M20 22c0-4.418-3.582-8-8-8s-8 3.582-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
@endif

                                            </div>
                                        </div>

                                        <p class="mt-5 text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-50">
                                            {{ $empleadoMes['nombre'] ?? 'Pendiente' }}
                                        </p>

                                        <div class="mt-5 w-full max-w-xl mx-auto">
                                            <div class="relative overflow-hidden rounded-2xl px-6 py-5
                                                        bg-white/60 dark:bg-white/5
                                                        border border-slate-200/70 dark:border-white/10
                                                        shadow-sm shadow-slate-900/5 dark:shadow-black/25">
                                                <span class="pointer-events-none absolute top-1 left-3
                                                             text-4xl leading-none font-serif
                                                             text-indigo-500/20 dark:text-indigo-300/20">&ldquo;</span>
                                                <span class="pointer-events-none absolute bottom-1 right-3
                                                             text-4xl leading-none font-serif
                                                             text-indigo-500/20 dark:text-indigo-300/20">&rdquo;</span>
                                                <p class="text-sm sm:text-base text-slate-700 dark:text-slate-200 leading-relaxed tracking-[0.01em] whitespace-pre-line px-5">
                                                    {{ $empleadoMes['mensaje'] ?? 'Aquí irá el empleado del mes' }}
                                                </p>
                                            </div>
                                        </div>



                                    </div>
                                        {{-- (Opcional) Botón dentro de la tarjeta --}}
                                        <div class="mt-8 flex justify-center">
                                            @if($puedeConfigurarEmpleadoMes)
                                                <button
                                                    type="button"
                                                    wire:click="openEmpleadoModal"
                                                    class="inline-flex items-center gap-2
                                                        px-4 py-2 rounded-full text-sm font-semibold
                                                        bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                                                        text-white shadow-lg shadow-blue-800/60
                                                        backdrop-blur-xl transition-all duration-200
                                                        hover:shadow-blue-500/80 hover:-translate-y-0.5"
                                                >
                                                    Configurar empleado del mes
                                                </button>
                                            @endif
                                        </div>

    @if($puedeConfigurarEmpleadoMes && !empty($empleadoMes))
    <button
        type="button"
        x-data
        @click.prevent="if (confirm('¿Seguro que deseas quitar el empleado del mes?')) { $wire.quitarEmpleadoDelMes() }"
        class="inline-flex items-center gap-2
               px-4 py-2 rounded-full text-sm font-semibold
               bg-rose-600/90 text-white
               shadow-lg shadow-rose-900/40
               transition-all duration-200
               hover:bg-rose-500 hover:shadow-rose-700/60"
    >
        Quitar empleado
    </button>
@endif




                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>




                




                    </div>
                </div>
            </div>
        </div>

    </div>




    {{-- ── Modal: Editar Meta Mensual ──────────────────────────────────── --}}
    @if($showModalMeta)
    <div class="fixed inset-0 z-[998] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             wire:click="$set('showModalMeta', false)"></div>

        <div class="relative w-full max-w-2xl rounded-2xl
                    bg-white/90 dark:bg-slate-950/70
                    border border-slate-200/80 dark:border-white/10
                    shadow-2xl shadow-black/40 flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200/60 dark:border-white/10 shrink-0">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                        Editar meta mensual
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Periodo: {{ $selectedMonthValue }}
                    </p>
                </div>
                <button wire:click="$set('showModalMeta', false)"
                        class="w-8 h-8 rounded-full border border-slate-300/60 dark:border-white/10
                               flex items-center justify-center text-slate-500 hover:text-red-400 transition text-sm">
                    ✕
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto px-6 py-5 space-y-5">

                {{-- Meta total global --}}
                <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/60 space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Meta total del periodo
                    </p>
                    <div class="flex items-center gap-3">
                        <input type="number"
                               wire:model="editMetaTotal"
                               min="1"
                               class="w-40 px-4 py-2 rounded-xl
                                      bg-slate-900/60 border border-slate-600
                                      text-slate-100 text-sm font-semibold
                                      focus:ring-2 focus:ring-[#FF9521] outline-none">
                        <button wire:click="recalcularMetaTotal"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl
                                       bg-slate-700/80 hover:bg-slate-600/80 border border-slate-600
                                       text-slate-300 text-xs transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M4.582 9A8 8 0 0120 15M19.418 15A8 8 0 014 9"/>
                            </svg>
                            Recalcular desde individuales
                        </button>
                    </div>
                    @error('editMetaTotal')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="text-[0.7rem] text-slate-500">
                        Puedes editar la meta total directamente, o ajustar las metas individuales y presionar "Recalcular".
                    </p>
                </div>

                {{-- Metas individuales por técnico --}}
                @if(count($editMetasTecnicos) > 0)
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Meta individual por técnico
                    </p>
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        @foreach($editMetasTecnicos as $idx => $tec)
                        <div class="flex items-center justify-between gap-4 px-4 py-2.5 rounded-xl
                                    bg-slate-800/30 border border-slate-700/40">
                            <span class="text-sm text-slate-300 flex-1 truncate">
                                {{ $tec['nombre'] }}
                            </span>
                            <div class="flex items-center gap-2 shrink-0">
                                <input type="number"
                                       wire:model="editMetasTecnicos.{{ $idx }}.meta_puntos"
                                       min="0" step="0.5"
                                       class="w-24 px-3 py-1.5 rounded-lg text-sm text-center
                                              bg-slate-900/60 border border-slate-600
                                              text-slate-100
                                              focus:ring-2 focus:ring-[#FF9521] outline-none">
                                <span class="text-[0.65rem] text-slate-500">pts</span>
                            </div>
                        </div>
                        @error('editMetasTecnicos.'.$idx.'.meta_puntos')
                            <p class="text-xs text-red-400 px-4">{{ $message }}</p>
                        @enderror
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-sm text-slate-500 text-center py-4">
                    No hay técnicos activos en este periodo.
                </p>
                @endif

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4
                        border-t border-slate-200/60 dark:border-white/10 shrink-0">
                <button wire:click="$set('showModalMeta', false)"
                        class="px-4 py-2 rounded-xl text-sm text-slate-400
                               border border-slate-600/60 hover:border-slate-500 transition">
                    Cancelar
                </button>
                <button wire:click="guardarMeta"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl
                               bg-[#FF9521] hover:bg-orange-500 text-white text-sm font-semibold
                               shadow-lg shadow-orange-900/40 transition">
                    Guardar meta
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: Configurar Líderes como Técnicos ──────────────────────── --}}
    @if($showModalLideres)
    <div class="fixed inset-0 z-[998] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             wire:click="$set('showModalLideres', false)"></div>

        <div class="relative w-full max-w-2xl rounded-2xl
                    bg-white/90 dark:bg-slate-950/70
                    border border-slate-200/80 dark:border-white/10
                    shadow-2xl shadow-black/40 flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200/60 dark:border-white/10 shrink-0">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">
                        Líderes trabajando como técnicos
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Marca los líderes que trabajan como técnicos de forma permanente
                    </p>
                </div>
                <button wire:click="$set('showModalLideres', false)"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <livewire:dashboard.configurar-lideres-tecnicos lazy />
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200/60 dark:border-white/10 shrink-0">
                <button wire:click="$set('showModalLideres', false)"
                        class="px-4 py-2 rounded-full text-sm font-semibold
                               bg-white/60 dark:bg-white/5 border border-slate-200/60 dark:border-white/10
                               hover:bg-slate-100 dark:hover:bg-white/10 transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif

      @if($showEmpleadoModal)
<div class="fixed inset-0 z-[999] flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEmpleadoModal"></div>

    <div class="relative w-full max-w-lg rounded-2xl p-6
        bg-white/90 dark:bg-slate-950/70
        border border-slate-200/80 dark:border-white/10
        shadow-2xl shadow-black/40">

        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Configurar empleado del mes</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300">Mes: {{ $selectedMonthValue }}</p>
            </div>
            <button class="w-9 h-9 rounded-full border border-white/10" wire:click="closeEmpleadoModal">✕</button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Colaborador
                </label>

                <select wire:model="empleadoMesUserId"
                    class="mt-2 w-full rounded-xl border border-slate-300/80 dark:border-white/15
                    bg-white/80 text-slate-800 dark:bg-slate-950/80 dark:text-slate-100
                    py-2 px-3 shadow-inner focus:outline-none focus:ring-2 focus:ring-[#FF9521]/60">
                    <option value="">Selecciona...</option>
                    @foreach($colaboradores as $col)
                        <option value="{{ $col['id'] }}">{{ $col['nombre'] }}</option>
                    @endforeach
                </select>
                @error('empleadoMesUserId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Mensaje / Motivo (opcional)
                </label>
                <textarea wire:model="empleadoMesMensaje" rows="3"
                    class="mt-2 w-full rounded-xl border border-slate-300/80 dark:border-white/15
                    bg-white/80 text-slate-800 dark:bg-slate-950/80 dark:text-slate-100
                    py-2 px-3 shadow-inner focus:outline-none focus:ring-2 focus:ring-[#FF9521]/60"
                    placeholder="Ej: Excelente productividad y calidad este mes..."></textarea>
                @error('empleadoMesMensaje') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button wire:click="closeEmpleadoModal"
                    class="px-4 py-2 rounded-full text-sm font-semibold
                    bg-white/60 dark:bg-white/5 border border-slate-200/60 dark:border-white/10">
                    Cancelar
                </button>

                <button wire:click="saveEmpleadoDelMes"
                    class="px-4 py-2 rounded-full text-sm font-semibold
                    bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                    text-white shadow-lg shadow-blue-800/60">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</x-tb-background>

@push('scripts')
    <style>
        .tb-confetti-piece{
            position: absolute;
            left: 50%;
            top: 4vh;
            width: var(--s, 14px);
            height: calc(var(--s, 14px) * 0.55);
            background: var(--c, #3B82F6);
            border-radius: var(--br, 2px);
            opacity: 0;
            filter: drop-shadow(0 10px 14px rgba(0,0,0,.22));
            will-change: transform, opacity;
            animation: tbConfettiBurst 4.4s cubic-bezier(.18,.82,.2,1) forwards;
            animation-delay: var(--d, 0ms);
        }
        @keyframes tbConfettiBurst{
            0%   { opacity: 0; transform: translate3d(var(--x, 0vw), var(--y, -8vh), 0) rotate(var(--r, 0deg)) scale(.9); }
            10%  { opacity: 1; }
            100% { opacity: 0; transform: translate3d(calc(var(--x, 0vw) + var(--tx, 0vw)), var(--ty, 118vh), 0) rotate(calc(var(--r, 0deg) + 980deg)) scale(.98); }
        }
        @media (prefers-reduced-motion: reduce){
            .tb-confetti-piece{ animation: none !important; display:none !important; }
        }
    </style>
    <script>
        (() => {
            if (window.__TB_DASH_CHARTS__) return;
            window.__TB_DASH_CHARTS__ = true;

            let lineChart = null;
            let barChart = null;
            let radialChart = null;
            let lineRebuildTask = Promise.resolve();

            let bootAnimating = true;
            let initializing = false;
            let lastPayload = null;

            const getTheme = () => {
                const isDark = document.documentElement.classList.contains("dark");
                return {
                    isDark,
                    mode: isDark ? "dark" : "light",
                    text: isDark ? "#E5E7EB" : "#374151",
                    grid: isDark ? "#4B5563" : "#E5E7EB",
                    track: isDark ? "#020617" : "#E5E7EB",
                };
            };

            const chartBase = {
                redrawOnParentResize: true,
                redrawOnWindowResize: true,
                animations: {
                    enabled: true,
                    easing: "easeinout",
                    speed: 700,
                    animateGradually: { enabled: true, delay: 90 },
                    dynamicAnimation: { enabled: true, speed: 420 }
                },
                toolbar: { show: false },
                background: "transparent",
                dropShadow: { enabled: true, top: 4, left: 0, blur: 10, opacity: 0.18 }
            };

            const pruneTooltipNodes = () => {
                const tips = Array.from(document.querySelectorAll('.apexcharts-tooltip'));
                if (tips.length <= 3) return;
                tips.slice(0, tips.length - 3).forEach((el) => el.remove());
            };

            const lineTooltip = (t) => ({
                enabled: true,
                theme: t.mode,
                shared: false,
                intersect: false,
                followCursor: true,
                y: { formatter: (v) => `${Math.round(Number(v || 0))} equipos` }
            });

            const escapeHtml = (val) => String(val ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const barTooltip = (t) => ({
                enabled: true,
                theme: t.mode,
                shared: false,
                intersect: true,
                followCursor: true,
                custom: ({ dataPointIndex, w }) => {
                    const categories = w?.globals?.labels || w?.config?.xaxis?.categories || [];
                    const label = categories[dataPointIndex] ?? '';

                    const s0Name = w?.config?.series?.[0]?.name ?? 'Serie 1';
                    const s1Name = w?.config?.series?.[1]?.name ?? 'Serie 2';

                    const s0 = Number(w?.globals?.series?.[0]?.[dataPointIndex] ?? 0);
                    const s1 = Number(w?.globals?.series?.[1]?.[dataPointIndex] ?? 0);

                    const textColor = t.isDark ? '#E5E7EB' : '#1F2937';
                    const muted = t.isDark ? '#94A3B8' : '#6B7280';
                    const bg = t.isDark ? 'rgba(2,6,23,0.96)' : 'rgba(255,255,255,0.98)';
                    const border = t.isDark ? 'rgba(148,163,184,0.35)' : 'rgba(100,116,139,0.25)';

                    return `
                        <div style="min-width:210px;padding:10px 12px;border-radius:10px;background:${bg};border:1px solid ${border};box-shadow:0 8px 30px rgba(0,0,0,.22);color:${textColor};font-size:12px;line-height:1.35;">
                            <div style="margin-bottom:8px;font-weight:700;color:${muted};">${escapeHtml(label)}</div>
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin:4px 0;">
                                <span style="display:inline-flex;align-items:center;gap:6px;"><span style="width:8px;height:8px;border-radius:999px;background:#2563EB;"></span>${escapeHtml(s0Name)}</span>
                                <strong>${Math.round(s0)} equipos</strong>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin:4px 0;">
                                <span style="display:inline-flex;align-items:center;gap:6px;"><span style="width:8px;height:8px;border-radius:999px;background:#FF9521;"></span>${escapeHtml(s1Name)}</span>
                                <strong>${Math.round(s1)} equipos</strong>
                            </div>
                        </div>
                    `;
                }
            });

            const resetChartIfDetached = (chart, expectedEl) => {
                if (!chart) return null;
                if (!expectedEl || !document.body.contains(expectedEl) || chart.el !== expectedEl) {
                    try { chart.destroy(); } catch (e) {}
                    return null;
                }
                return chart;
            };

            const rebuildLineChart = (payload, t) => {
                lineRebuildTask = lineRebuildTask.then(async () => {
                    const elLine = document.querySelector("#line-chart");
                    if (!elLine) return;

                    try { lineChart?.destroy?.(); } catch (e) {}

                    lineChart = new ApexCharts(elLine, getLineOptions(t, payload.lineChart));
                    await lineChart.render();

                    const data = payload.lineChart?.data || [];
                    await replaySeries(
                        lineChart,
                        [{ name: "Equipos", data: data.map(() => 0) }],
                        [{ name: "Equipos", data }]
                    );

                    // resize manejado centralmente en initCharts
                });

                return lineRebuildTask;
            };

            const getLineOptions = (t, lineData) => {
                const mainColor = t.isDark ? "#6366F1" : "#2563EB";
                return {
                    series: [{ name: "Equipos", data: lineData?.data || [] }],
                    chart: { ...chartBase, height: 260, type: "area" },
                    theme: { mode: t.mode },
                    colors: [mainColor],
                    grid: { borderColor: t.grid, strokeDashArray: 3 },
                    dataLabels: { enabled: false },
                    stroke: { curve: "smooth", width: 3, colors: [mainColor] },
                    markers: { size: 4, strokeWidth: 0, hover: { size: 7 } },
                    fill: {
                        type: "gradient",
                        gradient: {
                            shade: t.isDark ? "dark" : "light",
                            type: "vertical",
                            opacityFrom: 0.3,
                            opacityTo: 0,
                            stops: [0, 40, 100]
                        }
                    },
                    xaxis: {
                        categories: lineData?.labels || [],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: t.text } }
                    },
                    yaxis: { labels: { style: { colors: t.text }, formatter: v => Math.round(v) } },
                    tooltip: lineTooltip(t),
                    legend: { labels: { colors: t.text } }
                };
            };

            const getBarOptions = (t, tecnicoData, isTecnico) => {
                const colorActual   = t.isDark ? "#2563EB" : "#1D4ED8";
                const colorAnterior = "#FF9521";

                return {
                    series: [
                        { name: isTecnico ? "Este año (tú)" : "Este año (equipo)", data: tecnicoData?.series?.actual || [] },
                        { name: isTecnico ? "Año anterior (tú)" : "Año anterior (equipo)", data: tecnicoData?.series?.anterior || [] }
                    ],
                    chart: { ...chartBase, type: "bar", height: 260 },
                    theme: { mode: t.mode },
                    grid: { borderColor: t.grid, strokeDashArray: 3 },
                    plotOptions: { bar: { columnWidth: "45%", borderRadius: 8 } },
                    dataLabels: { enabled: false },
                    colors: [colorActual, colorAnterior],
                    fill: { opacity: 0.9 },
                    xaxis: {
                        categories: tecnicoData?.labels || [],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: t.text } }
                    },
                    yaxis: { labels: { style: { colors: t.text }, formatter: v => Math.round(v) } },
                    tooltip: barTooltip(t),
                    legend: { labels: { colors: t.text } }
                };
            };

            const getRadialOptions = (t, value) => {
                const mainColor = "#2563EB";
                const gradTo    = t.isDark ? "#60A5FA" : "#93C5FD";

                return {
                    series: [Number(value || 0)],
                    chart: { ...chartBase, type: "radialBar", height: 290 },
                    theme: { mode: t.mode },
                    labels: ["Meta cumplida"],
                    plotOptions: {
                        radialBar: {
                            hollow: { size: "68%", background: "transparent" },
                            track: { background: t.track, strokeWidth: "100%", margin: 4, opacity: 0.55 },
                            dataLabels: {
                                name: { show: true, fontSize: "0.9rem", letterSpacing: "0.06em", offsetY: -10, color: t.text },
                                value:{ fontSize: "2.1rem", fontWeight: 700, offsetY: 8, formatter: v => `${v}%`, color: t.text }
                            }
                        }
                    },
                    fill: {
                        type: "gradient",
                        gradient: {
                            shade: t.isDark ? "dark" : "light",
                            type: "vertical",
                            gradientToColors: [gradTo],
                            stops: [0, 40, 100]
                        }
                    },
                    colors: [mainColor]
                };
            };

            const replaySeries = async (chart, zeroSeries, finalSeries) => {
                await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
                chart.updateSeries(zeroSeries, false);
                await new Promise(r => setTimeout(r, 120));
                chart.updateSeries(finalSeries, true);
            };

            const initCharts = async (payload) => {
                if (typeof ApexCharts === "undefined") return;
                if (initializing) return;

                const elLine = document.querySelector("#line-chart");
                const elBar  = document.querySelector("#bar-chart");
                const elRad  = document.querySelector("#radial-chart");
                if (!elLine || !elBar || !elRad) return;

                initializing = true;

                const t = getTheme();

                lineChart = resetChartIfDetached(lineChart, elLine);
                barChart = resetChartIfDetached(barChart, elBar);
                radialChart = resetChartIfDetached(radialChart, elRad);

                try {
                    if (!lineChart) {
                        await rebuildLineChart(payload, t);
                    }

                    if (!barChart) {
                        barChart = new ApexCharts(elBar, getBarOptions(t, payload.tecnicoChart, payload.isTecnico));
                        await barChart.render();

                        const a = payload.tecnicoChart?.series?.actual || [];
                        const b = payload.tecnicoChart?.series?.anterior || [];
                        await replaySeries(
                            barChart,
                            [
                                { name: payload.isTecnico ? "Este año (tú)" : "Este año (equipo)", data: a.map(() => 0) },
                                { name: payload.isTecnico ? "Año anterior (tú)" : "Año anterior (equipo)", data: b.map(() => 0) }
                            ],
                            [
                                { name: payload.isTecnico ? "Este año (tú)" : "Este año (equipo)", data: a },
                                { name: payload.isTecnico ? "Año anterior (tú)" : "Año anterior (equipo)", data: b }
                            ]
                        );
                    }

                    if (!radialChart) {
                        radialChart = new ApexCharts(elRad, getRadialOptions(t, payload.radialPercent));
                        await radialChart.render();
                        await replaySeries(radialChart, [0], [Number(payload.radialPercent || 0)]);
                    }
                } finally {
                    initializing = false;
                }

                pruneTooltipNodes();

                bootAnimating = false;

                // Forzar recálculo completo de la capa de eventos tras animaciones CSS
                [150, 400, 750].forEach((ms) => {
                    setTimeout(() => {
                        try { window.dispatchEvent(new Event('resize')); } catch (e) {}
                    }, ms);
                });

                // ✅ Helper global para re-ajustar charts (cuando el slide vuelve a mostrarse)
                window.TB_DASH_RESIZE = () => {
                    try {
                        lineChart?.resize?.();
                        barChart?.resize?.();
                        radialChart?.resize?.();
                    } catch(e) {}
                };

                // No forzar update inmediato después de init para no cortar
                // la animación de entrada de las gráficas.
            };

            const updateCharts = (payload) => {
                const elLine = document.querySelector("#line-chart");
                const elBar  = document.querySelector("#bar-chart");
                const elRad  = document.querySelector("#radial-chart");

                lineChart = resetChartIfDetached(lineChart, elLine);
                barChart = resetChartIfDetached(barChart, elBar);
                radialChart = resetChartIfDetached(radialChart, elRad);

                if (!lineChart || !barChart || !radialChart) {
                    initCharts(payload);
                    return;
                }

                const t = getTheme();

                lineChart.updateOptions({
                    theme: { mode: t.mode },
                    grid: { borderColor: t.grid, strokeDashArray: 3 },
                    xaxis: { categories: payload.lineChart?.labels || [], labels: { style: { colors: t.text } } },
                    yaxis: { labels: { style: { colors: t.text } } },
                    tooltip: lineTooltip(t)
                }, false, false);
                lineChart.updateSeries([{ name: "Equipos", data: payload.lineChart?.data || [] }], true);

                barChart.updateOptions({
                    theme: { mode: t.mode },
                    grid: { borderColor: t.grid, strokeDashArray: 3 },
                    xaxis: { categories: payload.tecnicoChart?.labels || [], labels: { style: { colors: t.text } } },
                    yaxis: { labels: { style: { colors: t.text } } },
                    tooltip: barTooltip(t)
                }, false, false);
                barChart.updateSeries([
                    { name: payload.isTecnico ? "Este año (tú)" : "Este año (equipo)", data: payload.tecnicoChart?.series?.actual || [] },
                    { name: payload.isTecnico ? "Año anterior (tú)" : "Año anterior (equipo)", data: payload.tecnicoChart?.series?.anterior || [] }
                ], true);

                radialChart.updateSeries([Number(payload.radialPercent || 0)], true);
                pruneTooltipNodes();

                // Mantiene activo el hover del chart de línea tras updates Livewire
                requestAnimationFrame(() => {
                    try { lineChart?.resize?.(); } catch (e) {}
                });
            };

            const boot = () => {
                const initial = {
                    lineChart: @js($lineChart),
                    tecnicoChart: @js($tecnicoChart),
                    radialPercent: @js($radialPercent),
                    isTecnico: @js($isTecnico),
                };
                lastPayload = initial;
                initCharts(initial);
            };

            boot();

            document.addEventListener("livewire:init", boot);
            document.addEventListener("livewire:navigated", boot);

            window.addEventListener("focus", () => {
                try { window.TB_DASH_RESIZE?.(); } catch (e) {}
            });

            document.addEventListener("visibilitychange", () => {
                if (document.visibilityState === "visible") {
                    try { window.TB_DASH_RESIZE?.(); } catch (e) {}
                }
            });

            window.addEventListener("dashboard-data-updated", (event) => {
                lastPayload = event.detail;
                if (bootAnimating) return;
                updateCharts(lastPayload);
            });

        })();
    </script>
@endpush
