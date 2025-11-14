<x-app-layout>
<x-slot name="header">
    {{-- Truco: cancelamos el padding horizontal del layout para que el header se vea de lado a lado --}}
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        <div
            class="px-4 sm:px-6 lg:px-8 py-3
                   bg-gradient-to-r 
                       from-slate-100/90 via-slate-200/95 to-slate-100/90
                   dark:from-slate-900/95 dark:via-slate-950/95 dark:to-slate-900/95
                   backdrop-blur-xl
                   border-b border-slate-200/70 dark:border-slate-800/80
                   shadow-md shadow-slate-900/40"
        >
            <div class="flex items-center justify-between gap-4">

                {{-- Lado izquierdo: título + info --}}
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                            Dashboard General
                        </h2>

                        {{-- Chip mes actual --}}
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                   text-[0.65rem] font-medium tracking-wide
                                   bg-indigo-500/10 text-indigo-600
                                   dark:bg-indigo-400/15 dark:text-indigo-200
                                   border border-indigo-500/25"
                        >
                            Mes de {{ $currentMonthName }}
                        </span>
                    </div>

                    <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Meta mensual cumplida:
                        <span class="font-semibold text-indigo-600 dark:text-indigo-300">
                            {{ $radialPercent }}%
                        </span>
                        · Equipos este mes:
                        <span class="font-semibold text-slate-800 dark:text-slate-100">
                            {{ $kpis['equiposMes'] }}
                        </span>
                    </p>
                </div>

{{-- Lado derecho: selector de mes + botón --}}
<div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 sm:gap-3">

    {{-- Aviso si el mes ya terminó --}}
    @if($monthFinished)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full
                     text-[0.7rem] font-medium tracking-wide
                     bg-amber-500/10 text-amber-500
                     border border-amber-400/40">
            ⚠ Este mes ya terminó
        </span>
    @endif

    {{-- Selector de mes --}}
    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
        <select
            name="month"
            onchange="this.form.submit()"
            class="text-xs sm:text-sm rounded-xl border border-slate-300/70 dark:border-slate-700/80
                   bg-white/80 dark:bg-slate-900/80
                   text-slate-700 dark:text-slate-200
                   py-1.5 pl-2 pr-8
                   shadow-sm shadow-slate-900/10
                   focus:outline-none focus:ring-2 focus:ring-indigo-500/60 focus:border-indigo-500"
        >
            @foreach($monthsOptions as $opt)
                <option value="{{ $opt['value'] }}" @if($opt['value'] === $selectedMonthValue) selected @endif>
                    {{ $opt['label'] }}
                </option>
            @endforeach
        </select>

        {{-- Botón de acción (lo dejamos por si luego quieres usarlo) --}}
        <button
            type="button"
            class="hidden sm:inline-flex items-center gap-2
                   px-3 py-1.5 rounded-full text-xs font-medium
                   bg-indigo-500/90 hover:bg-indigo-600
                   text-white shadow-md shadow-indigo-500/40
                   transition-all duration-200
                   hover:-translate-y-0.5"
        >
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
            Vista resumen mensual
        </button>
    </form>
</div>


            </div>
        </div>
    </div>
</x-slot>



<div class="relative py-10 bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 min-h-screen overflow-hidden">
    
    
    <div class="pointer-events-none absolute inset-0 
                bg-white/10 dark:bg-white/5 
                backdrop-blur-2xl">
    </div>

    
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8">
        

            
                  <div class="py-2 lg:py-4">
                      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">



                    <div class="lg:col-span-2 space-y-6">

                        {{-- ===== 3 Tarjetas KPI ===== --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            {{-- Card base glass class --}}
                            @php
                                $cardClass = "bg-white/70 dark:bg-slate-900/60 border border-white/60 dark:border-slate-700/70 
                                              backdrop-blur-xl rounded-2xl shadow-lg shadow-slate-900/30 
                                              px-4 py-5 flex flex-col gap-2 
                                              transition-all duration-300 ease-out 
                                              hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/25";
                                $labelClass = "text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400";
                                $valueClass = "text-3xl font-bold text-slate-900 dark:text-slate-50";
                                $badgeClass = "inline-flex items-center text-xs font-semibold text-emerald-500";
                            @endphp

                            {{-- KPI Equipos Hoy --}}
                            <div class="{{ $cardClass }}">
                                <span class="{{ $labelClass }}">Equipos Hechos (Hoy)</span>
                                <span class="{{ $valueClass }}">{{ $kpis['equiposHoy'] }}</span>
                                <span class="{{ $badgeClass }}">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                    {{ $kpis['hoy_change'] }}
                                </span>
                            </div>

                            {{-- KPI Semana --}}
                            <div class="{{ $cardClass }}">
                                <span class="{{ $labelClass }}">Equipos Hechos (Semana)</span>
                                <span class="{{ $valueClass }}">{{ $kpis['equiposSemana'] }}</span>
                                <span class="{{ $badgeClass }}">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                    {{ $kpis['semana_change'] }}
                                </span>
                            </div>

                            {{-- KPI Mes --}}
                            <div class="{{ $cardClass }}">
                                <span class="{{ $labelClass }}">Equipos Hechos (Mes)</span>
                                <span class="{{ $valueClass }}">{{ $kpis['equiposMes'] }}</span>
                                <span class="{{ $badgeClass }}">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>
                                    {{ $kpis['mes_change'] }}
                                </span>
                            </div>
                        </div> {{-- FIN tarjetas KPI --}}

                        {{-- ===== GRÁFICA Línea ===== --}}
                        <div class="bg-white/70 dark:bg-slate-900/60 border border-white/60 dark:border-slate-700/70 backdrop-blur-xl rounded-2xl shadow-lg shadow-slate-900/30 p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/20">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-50">
                                    Equipos por Semana
                                </h3>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    Mes de {{ $currentMonthName }}
                                </span>
                            </div>
                            <div id="line-chart" class="mt-4"></div>
                        </div>

                        {{-- ===== GRÁFICA Barras Técnico ===== --}}
                        <div class="bg-white/70 dark:bg-slate-900/60 border border-white/60 dark:border-slate-700/70 backdrop-blur-xl rounded-2xl shadow-lg shadow-slate-900/30 p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/20">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-50">
                                    Producción por Técnico
                                </h3>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    Distribución de equipos
                                </span>
                            </div>
                            <div id="bar-chart" class="mt-4"></div>
                        </div>

                    </div> {{-- FIN col-span 2 --}}

                    {{-- ========================================= --}}
                    {{--   COLUMNA DERECHA                         --}}
                    {{-- ========================================= --}}
                    <div class="lg:col-span-1 space-y-6">

                        {{-- ===== RADIAL: Avance Meta Mensual ===== --}}
                        <div class="bg-white/70 dark:bg-slate-900/60 border border-white/60 dark:border-slate-700/70 backdrop-blur-xl rounded-2xl shadow-lg shadow-slate-900/30 p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/20">
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-50 mb-1">
                                Avance de Meta Mensual
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                                Progreso general de la cuota mensual de equipos.
                            </p>
                            <div id="radial-chart" class="mt-2"></div>
                        </div>

                        {{-- ===== DETALLE META MENSUAL ===== --}}
                        <div class="bg-white/70 dark:bg-slate-900/60 border border-white/60 dark:border-slate-700/70 backdrop-blur-xl rounded-2xl shadow-lg shadow-slate-900/30 p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/20">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-50">
                                    Detalle de Meta Mensual
                                </h3>
                            </div>

                            <div class="space-y-3">
                                @foreach($breakdown as $item)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600 dark:text-slate-300">
                                            {{ $item['label'] }}
                                        </span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $item['value'] }}
                                        </span>
                                    </div>
                                    @if (!$loop->last)
                                        <div class="h-px bg-gradient-to-r from-transparent via-slate-300/70 dark:via-slate-600/70 to-transparent"></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    </div>{{-- FIN columna derecha --}}

                </div>
            </div>
        </div>
    </div>

    {{-- ===============================================================
       SCRIPTS DE TODAS LAS GRÁFICAS (LÍNEA, BARRAS, RADIAL)
       =============================================================== --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const getTheme = () => {
                const isDark = document.documentElement.classList.contains("dark");
                return {
                    isDark,
                    mode: isDark ? "dark" : "light",
                    text: isDark ? "#E5E7EB" : "#374151",
                    grid: isDark ? "#4B5563" : "#E5E7EB",
                    track: isDark ? "#020617" : "#E5E7EB"
                };
            };

            const lineData    = @json($lineChart);
            const tecnicoData = @json($tecnicoChart);
            const radialData  = @json($radialPercent);

            const chartBase = {
                animations: {
                    enabled: true,
                    easing: "easeinout",
                    speed: 900,
                    animateGradually: {
                        enabled: true,
                        delay: 140
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 400
                    }
                },
                toolbar: { show: false },
                background: "transparent",
                dropShadow: {
                    enabled: true,
                    top: 4,
                    left: 0,
                    blur: 10,
                    opacity: 0.18
                }
            };

            const getLineOptions = (t) => {
                const mainColor = t.isDark ? "#6366F1" : "#1718FF";
                return {
                    series: [{
                        name: "Equipos",
                        data: lineData.data
                    }],
                    chart: {
                        ...chartBase,
                        height: 260,
                        type: "area"
                    },
                    theme: { mode: t.mode },
                    colors: [mainColor],
                    grid: {
                        borderColor: t.grid,
                        strokeDashArray: 3
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        curve: "smooth",
                        width: 3,
                        colors: [mainColor]
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 0,
                        hover: { size: 7 }
                    },
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
                        categories: lineData.labels,
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: t.text } }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: t.text },
                            formatter: value => Math.round(value)
                        }
                    },
                    tooltip: {
                        theme: t.mode,
                        x: { show: true },
                        y: { formatter: val => `${val} equipos` }
                    },
                    legend: { labels: { colors: t.text } }
                };
            };

            const getBarOptions = (t) => {
                const mainColor = t.isDark ? "#1D1B5F" : "#1718FF";
                const gradientTo = t.isDark ? "#4F46E5" : "#3440FF";
                return {
                    series: [{
                        name: "Equipos Registrados",
                        data: tecnicoData.data
                    }],
                    chart: {
                        ...chartBase,
                        type: "bar",
                        height: 260
                    },
                    theme: { mode: t.mode },
                    grid: {
                        borderColor: t.grid,
                        strokeDashArray: 3
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: "45%",
                            borderRadius: 8
                        }
                    },
                    dataLabels: { enabled: false },
                    colors: [mainColor],
                    fill: {
                        type: "gradient",
                        gradient: {
                            shade: t.isDark ? "dark" : "light",
                            type: "vertical",
                            gradientToColors: [gradientTo],
                            stops: [0, 40, 100],
                            opacityFrom: 0.95,
                            opacityTo: 0.8
                        }
                    },
                    states: {
                        hover: { filter: { type: "lighten", value: 0.08 } },
                        active:{ filter: { type: "lighten", value: 0.12 } }
                    },
                    xaxis: {
                        categories: tecnicoData.labels,
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: t.text } }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: t.text },
                            formatter: value => Math.round(value)
                        }
                    },
                    tooltip: {
                        theme: t.mode,
                        y: { formatter: val => `${val} equipos` }
                    },
                    legend: { labels: { colors: t.text } }
                };
            };

            const getRadialOptions = (t) => {
                const mainColor = t.isDark ? "#2563EB" : "#1718FF";
                const gradTo    = t.isDark ? "#60A5FA" : "#516CFF";
                return {
                    series: [radialData],
                    chart: {
                        ...chartBase,
                        type: "radialBar",
                        height: 290,
                        dropShadow: {
                            enabled: true,
                            top: 0,
                            left: 0,
                            blur: 14,
                            opacity: 0.28
                        }
                    },
                    theme: { mode: t.mode },
                    labels: ["Meta cumplida"],
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: "68%",
                                background: "transparent"
                            },
                            track: {
                                background: t.track,
                                strokeWidth: "100%",
                                margin: 4,
                                opacity: 0.55
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    fontSize: "0.9rem",
                                    letterSpacing: "0.06em",
                                    offsetY: -10,
                                    color: t.text
                                },
                                value: {
                                    fontSize: "2.1rem",
                                    fontWeight: 700,
                                    offsetY: 8,
                                    formatter: val => `${val}%`,
                                    color: t.text
                                }
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

            const initialTheme = getTheme();

            const lineChart = new ApexCharts(
                document.querySelector("#line-chart"),
                getLineOptions(initialTheme)
            );
            lineChart.render();

            const barChart = new ApexCharts(
                document.querySelector("#bar-chart"),
                getBarOptions(initialTheme)
            );
            barChart.render();

            const radialChart = new ApexCharts(
                document.querySelector("#radial-chart"),
                getRadialOptions(initialTheme)
            );
            radialChart.render();

            const updateChartsTheme = () => {
                const t = getTheme();
                lineChart.updateOptions(getLineOptions(t));
                barChart.updateOptions(getBarOptions(t));
                radialChart.updateOptions(getRadialOptions(t));
            };

            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.attributeName === "class") {
                        updateChartsTheme();
                        break;
                    }
                }
            });

            observer.observe(document.documentElement, { attributes: true });
        });
    </script>
</x-app-layout>
