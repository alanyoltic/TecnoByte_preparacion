<x-tb-background poll="refreshDashboard" :glows="$glows">
    
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
        <div class="relative">
            <x-topbar
                title="Dashboard General"
                chip="Mes de {{ $currentMonthName }}"
                description="Meta mensual cumplida: {{ $radialPercent }}% · Equipos este mes: {{ $kpis['equiposMes'] ?? 0 }}"
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

        init(){
            // Deep-link inicial ?tab=empleado
            const p = new URLSearchParams(window.location.search);
            if (p.get('tab') === 'empleado') this.slide = 1;

            // Exponer estado global para los dots del topbar
            window.__TB_ACTIVE_TAB__ = this.slide === 0 ? 'dashboard' : 'empleado';

            // Al iniciar (si estás en dashboard), ajusta charts
            this.$nextTick(() => {
                if (this.slide === 0) window.TB_DASH_RESIZE?.();
            });
        },

        go(i){
            this.slide = (i + this.total) % this.total;

            // Actualizar estado global (para pintar dots)
            window.__TB_ACTIVE_TAB__ = this.slide === 0 ? 'dashboard' : 'empleado';

            // Guardar en URL
            const url = new URL(window.location.href);
            if (this.slide === 1) url.searchParams.set('tab','empleado');
            else url.searchParams.delete('tab');
            window.history.replaceState({}, '', url);

            // Al volver al dashboard, reajusta charts
            if (this.slide === 0){
                this.$nextTick(() => window.TB_DASH_RESIZE?.());
            }
            this.$dispatch('tb-slide-changed', { slide: this.slide });

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
            <div class="relative overflow-hidden rounded-3xl">
                <div
                    class="flex transition-transform duration-500 ease-out"
                    :style="`transform: translateX(-${slide * 100}%);`"
                >

                    {{-- ===================== SLIDE 1: DASHBOARD ACTUAL ===================== --}}
                    <section class="w-full shrink-0">
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
                                        <span class="{{ $labelClass }}">Equipos Hechos (Hoy)</span>
                                        <span class="{{ $valueClass }}">{{ $kpis['equiposHoy'] ?? 0 }}</span>
                                        <span class="{{ $badgeClass }}">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                            {{ $kpis['hoy_change'] ?? '' }}
                                        </span>
                                    </div>

                                    <div class="{{ $cardClass }} {{ $cardGlow }} {{ $cardGlow2 }}">
                                        <span class="{{ $labelClass }}">Equipos Hechos (Semana)</span>
                                        <span class="{{ $valueClass }}">{{ $kpis['equiposSemana'] ?? 0 }}</span>
                                        <span class="{{ $badgeClass }}">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                            {{ $kpis['semana_change'] ?? '' }}
                                        </span>
                                    </div>

                                    <div class="{{ $cardClass }} {{ $cardGlow }} {{ $cardGlow2 }}">
                                        <span class="{{ $labelClass }}">Equipos Hechos (Mes)</span>
                                        <span class="{{ $valueClass }}">{{ $kpis['equiposMes'] ?? 0 }}</span>
                                        <span class="{{ $badgeClass }}">
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

                                    <div id="line-chart" class="mt-4 min-h-[260px]" wire:ignore></div>
                                </div>

                                {{-- Bar Chart --}}
                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $isTecnico ? 'Comparativa de tu producción' : 'Producción por Técnico' }}
                                        </h3>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Distribución de equipos</span>
                                    </div>

                                    <div id="bar-chart" class="mt-4 min-h-[260px]" wire:ignore></div>
                                </div>

                            </div>

                            {{-- Columna derecha --}}
                            <div class="lg:col-span-1 space-y-6">

                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-1">
                                        Avance de Meta Mensual
                                    </h3>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">
                                        Progreso general de la cuota mensual de equipos.
                                    </p>

                                    <div id="radial-chart" class="mt-2 min-h-[290px]" wire:ignore></div>
                                </div>

                                <div class="{{ $panelClass }} {{ $panelGlow }} {{ $panelGlow2 }} p-5 lg:p-6">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-3">
                                        Detalle de Meta Mensual
                                    </h3>

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

                                        start(){ this.stop(); this.timer = setInterval(() => this.next(), 6500); },
                                        stop(){ if (this.timer) clearInterval(this.timer); this.timer = null; },

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
                                        }
                                    }"
                                    x-init="start()"
                                    @mouseenter="stop()"
                                    @mouseleave="start()"
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
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                            Avisos
                                        </h3>

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="prev()"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                    border border-slate-200/70 dark:border-white/10
                                                    bg-white/60 dark:bg-slate-950/60
                                                    text-slate-700 dark:text-slate-200
                                                    transition-all duration-200
                                                    hover:-translate-y-0.5 hover:shadow-md hover:shadow-indigo-500/10"
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
                                                            class="space-y-3"
                                                            :class="dir === 1
                                                                ? 'animate-[slideInFromRight_.4s_ease-out]'
                                                                : 'animate-[slideInFromLeft_.4s_ease-out]'"
                                                        >
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="flex items-start gap-3">
                                                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center
                                                                                bg-slate-900/5 dark:bg-white/5
                                                                                border border-slate-200/60 dark:border-white/10">
                                                                        <span class="text-lg" x-text="it.icono ?? '📌'"></span>
                                                                    </div>

                                                                    <div class="space-y-1">
                                                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-50" x-text="it.titulo"></p>
                                                                        <p class="text-xs text-slate-600 dark:text-slate-300" x-text="it.texto"></p>
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
                                                            <div class="flex items-center gap-1.5 pt-1">
                                                                <template x-for="(dot, di) in items" :key="di">
                                                                    <button
                                                                        type="button"
                                                                        @click="goto(di)"
                                                                        class="w-2.5 h-2.5 rounded-full transition-all duration-200"
                                                                        :class="index === di
                                                                            ? 'bg-[#FF9521] shadow-[0_0_0_4px_rgba(255,149,33,0.15)]'
                                                                            : 'bg-slate-300/70 dark:bg-white/15'"
                                                                    ></button>
                                                                </template>
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

                                        <p class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ $empleadoMes['mensaje'] ?? 'Aquí irá el empleado del mes' }}
                                        </p>



                                    </div>
                                        {{-- (Opcional) Botón dentro de la tarjeta --}}
                                        <div class="mt-8 flex justify-center">
                                            @if($esAdminCeo)
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

                                        @if($esAdminCeo && !empty($empleadoMes))
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        (() => {
            if (window.__TB_DASH_CHARTS__) return;
            window.__TB_DASH_CHARTS__ = true;

            let lineChart = null;
            let barChart = null;
            let radialChart = null;

            let bootAnimating = true;
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
                animations: {
                    enabled: true,
                    easing: "easeinout",
                    speed: 900,
                    animateGradually: { enabled: true, delay: 120 },
                    dynamicAnimation: { enabled: true, speed: 650 }
                },
                toolbar: { show: false },
                background: "transparent",
                dropShadow: { enabled: true, top: 4, left: 0, blur: 10, opacity: 0.18 }
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
                    tooltip: { theme: t.mode, y: { formatter: v => `${v} equipos` } },
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
                    tooltip: { theme: t.mode, y: { formatter: v => `${v} equipos` } },
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

                const elLine = document.querySelector("#line-chart");
                const elBar  = document.querySelector("#bar-chart");
                const elRad  = document.querySelector("#radial-chart");
                if (!elLine || !elBar || !elRad) return;

                const t = getTheme();

                if (!lineChart) {
                    lineChart = new ApexCharts(elLine, getLineOptions(t, payload.lineChart));
                    await lineChart.render();

                    const data = payload.lineChart?.data || [];
                    await replaySeries(
                        lineChart,
                        [{ name: "Equipos", data: data.map(() => 0) }],
                        [{ name: "Equipos", data }]
                    );
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

                bootAnimating = false;

                // ✅ Helper global para re-ajustar charts (cuando el slide vuelve a mostrarse)
                window.TB_DASH_RESIZE = () => {
                    try {
                        lineChart?.resize?.();
                        barChart?.resize?.();
                        radialChart?.resize?.();
                    } catch(e) {}
                };

                if (lastPayload) {
                    requestAnimationFrame(() => requestAnimationFrame(() => updateCharts(lastPayload)));
                }
            };

            const updateCharts = (payload) => {
                if (!lineChart || !barChart || !radialChart) return;

                const t = getTheme();

                lineChart.updateOptions({
                    theme: { mode: t.mode },
                    grid: { borderColor: t.grid, strokeDashArray: 3 },
                    xaxis: { categories: payload.lineChart?.labels || [], labels: { style: { colors: t.text } } },
                    yaxis: { labels: { style: { colors: t.text } } },
                    tooltip: { theme: t.mode }
                }, true, true);
                lineChart.updateSeries([{ name: "Equipos", data: payload.lineChart?.data || [] }], true);

                barChart.updateOptions({
                    theme: { mode: t.mode },
                    grid: { borderColor: t.grid, strokeDashArray: 3 },
                    xaxis: { categories: payload.tecnicoChart?.labels || [], labels: { style: { colors: t.text } } },
                    yaxis: { labels: { style: { colors: t.text } } },
                    tooltip: { theme: t.mode }
                }, true, true);
                barChart.updateSeries([
                    { name: payload.isTecnico ? "Este año (tú)" : "Este año (equipo)", data: payload.tecnicoChart?.series?.actual || [] },
                    { name: payload.isTecnico ? "Año anterior (tú)" : "Año anterior (equipo)", data: payload.tecnicoChart?.series?.anterior || [] }
                ], true);

                radialChart.updateSeries([Number(payload.radialPercent || 0)], true);
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

            document.addEventListener("livewire:init", boot);
            document.addEventListener("livewire:navigated", boot);

            window.addEventListener("dashboard-data-updated", (event) => {
                lastPayload = event.detail;
                if (bootAnimating) return;
                updateCharts(lastPayload);
            });

        })();
    </script>
@endpush
