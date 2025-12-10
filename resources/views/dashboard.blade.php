<x-app-layout>

@php
    $isAdminOrCeo = in_array(optional(auth()->user()->role)->slug, ['admin', 'ceo']);
@endphp


    {{-- FONDO ESTILO LOGIN + CONTENIDO DASHBOARD --}}
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-100 to-slate-200
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >

        {{-- Luces estilo login (sirven para ambos modos) --}}
        @php
            // Azul superior izq
            $glow1Top  = rand(-420, -260);
            $glow1Left = rand(-320, -120);

            // Azul inferior der
            $glow2Bottom = rand(-420, -260);
            $glow2Right  = rand(-320, -120);

            // Naranja central
            $glow3Bottom      = rand(-340, -220);
            $glow3LeftPercent = rand(30, 70);
        @endphp

        <div class="pointer-events-none absolute inset-0">

            {{-- Glow azul grande superior izquierdo --}}
            <div
                id="glow-1"
                class="absolute w-[1100px] h-[1100px]
                       bg-[#1E3A8A] rounded-full blur-[240px]
                       opacity-70 md:opacity-90 mix-blend-screen"
                style="top: {{ $glow1Top }}px; left: {{ $glow1Left }}px;"
            ></div>

            {{-- Glow azul grande inferior derecho --}}
            <div
                id="glow-2"
                class="absolute w-[1000px] h-[1000px]
                       bg-[#0F1A35] rounded-full blur-[240px]
                       opacity-70 md:opacity-95 mix-blend-screen"
                style="bottom: {{ $glow2Bottom }}px; right: {{ $glow2Right }}px;"
            ></div>

            {{-- Glow naranja suave central --}}
            <div
                id="glow-3"
                class="absolute w-[850px] h-[850px]
                       bg-[#FF9521]/40 md:bg-[#FF9521]/50
                       rounded-full blur-[260px]
                       opacity-80 md:opacity-90 mix-blend-screen"
                style="bottom: {{ $glow3Bottom }}px; left: {{ $glow3LeftPercent }}%;"
            ></div>
        </div>

        {{-- Capa glass suave --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- CONTENIDO DEL DASHBOARD (HEADER + TARJETAS + GRÁFICAS) --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

            {{-- HEADER COMO TARJETA DENTRO DEL FONDO --}}
            <div
                class="relative overflow-hidden mb-6
                       rounded-3xl
                       bg-white/80 dark:bg-slate-950/70
                       border border-slate-200/80 dark:border-white/10
                       shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       px-6 sm:px-8 lg:px-10 py-4 sm:py-5"
            >
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">

                    {{-- IZQUIERDA: título y resumen --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Dashboard General
                            </h2>

                            {{-- Chip mes actual --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                       text-[0.7rem] font-semibold tracking-wide
                                       bg-[#FF9521]/10 text-[#FF9521]
                                       border border-[#FF9521]/40"
                            >
                                Mes de {{ $currentMonthName }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                            Meta mensual cumplida:
                            <span class="font-semibold text-[#FF9521]">
                                {{ $radialPercent }}%
                            </span>
                            · Equipos este mes:
                            <span class="font-semibold text-slate-900 dark:text-slate-50">
                                {{ $kpis['equiposMes'] }}
                            </span>
                        </p>
                    </div>

                    {{-- DERECHA: selector de mes + botón --}}
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 sm:gap-3">

                        @if($monthFinished)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                         text-[0.7rem] font-medium tracking-wide
                                         bg-amber-500/10 text-amber-600 dark:text-amber-300
                                         border border-amber-400/40">
                                ⚠ Este mes ya terminó
                            </span>
                        @endif

                        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
    {{-- Selector de mes --}}
    <select
        name="month"
        onchange="this.form.submit()"
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
            <option value="{{ $opt['value'] }}" @if($opt['value'] === $selectedMonthValue) selected @endif>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    {{-- Selector de colaborador (solo admin/ceo) --}}
    @if(!$isTecnico && !empty($colaboradores))
        <select
            name="colaborador"
            onchange="this.form.submit()"
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
                <option
                    value="{{ $col['id'] }}"
                    @if((string)($selectedColaboradorId ?? '') === (string)$col['id']) selected @endif
                >
                    {{ $col['nombre'] }}
                </option>
            @endforeach
        </select>
    @endif

    {{-- Botón azul --}}
    <button
        type="button"
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
        Vista resumen mensual
    </button>
</form>

                    </div>

                </div>
            </div>

            {{-- RESTO DEL DASHBOARD --}}
            <div class="py-2 lg:py-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <div class="lg:col-span-2 space-y-6">

                        {{-- ===== 3 Tarjetas KPI ===== --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            @php
                                $cardClass = "bg-white/80 dark:bg-slate-950/60
                                              border border-slate-200/80 dark:border-white/10
                                              backdrop-blur-xl dark:backdrop-blur-2xl
                                              rounded-2xl
                                              shadow-md shadow-slate-900/10
                                              dark:shadow-lg dark:shadow-slate-900/30
                                              px-4 py-5 flex flex-col gap-2
                                              transition-all duration-300 ease-out
                                              hover:-translate-y-1
                                              hover:shadow-lg hover:shadow-indigo-500/20
                                              dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25";

                                $labelClass = "text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400";
                                $valueClass = "text-3xl font-bold text-slate-900 dark:text-slate-50";
                                $badgeClass = "inline-flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-500";
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
                        </div>

                        {{-- ===== GRÁFICA Línea ===== --}}
                        <div
                            class="bg-white/80 dark:bg-slate-950/60
                                   border border-slate-200/80 dark:border-white/10
                                   backdrop-blur-xl dark:backdrop-blur-2xl
                                   rounded-2xl shadow-md shadow-slate-900/10
                                   dark:shadow-lg dark:shadow-slate-900/30
                                   p-5 lg:p-6 transition-all duration-300 ease-out
                                   hover:-translate-y-1
                                   hover:shadow-lg hover:shadow-indigo-500/20
                                   dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                    Equipos por Semana
                                </h3>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    Mes de {{ $currentMonthName }}
                                </span>
                            </div>
                            <div id="line-chart" class="mt-4"></div>
                        </div>

                        {{-- ===== GRÁFICA Barras Técnico ===== --}}
                        <div
                            class="bg-white/80 dark:bg-slate-950/60
                                   border border-slate-200/80 dark:border-white/10
                                   backdrop-blur-xl dark:backdrop-blur-2xl
                                   rounded-2xl shadow-md shadow-slate-900/10
                                   dark:shadow-lg dark:shadow-slate-900/30
                                   p-5 lg:p-6 transition-all duration-300 ease-out
                                   hover:-translate-y-1
                                   hover:shadow-lg hover:shadow-indigo-500/20
                                   dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                        @if($isTecnico)
                                    Comparativa de tu producción
                                @else
                                    Producción por Técnico
                                @endif
                                </h3>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    Distribución de equipos
                                </span>
                            </div>
                            <div id="bar-chart" class="mt-4"></div>
                        </div>

                    </div>

                    {{-- COLUMNA DERECHA --}}
                    <div class="lg:col-span-1 space-y-6">

                        {{-- RADIAL: Avance Meta Mensual --}}
                        <div
                            class="bg-white/80 dark:bg-slate-950/60
                                   border border-slate-200/80 dark:border-white/10
                                   backdrop-blur-xl dark:backdrop-blur-2xl
                                   rounded-2xl shadow-md shadow-slate-900/10
                                   dark:shadow-lg dark:shadow-slate-900/30
                                   p-5 lg:p-6 transition-all duration-300 ease-out
                                   hover:-translate-y-1
                                   hover:shadow-lg hover:shadow-indigo-500/20
                                   dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
                        >
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-1">
                                Avance de Meta Mensual
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">
                                Progreso general de la cuota mensual de equipos.
                            </p>
                            <div id="radial-chart" class="mt-2"></div>
                        </div>

                        {{-- DETALLE META MENSUAL --}}
                        <div
                            class="bg-white/80 dark:bg-slate-950/60
                                   border border-slate-200/80 dark:border-white/10
                                   backdrop-blur-xl dark:backdrop-blur-2xl
                                   rounded-2xl shadow-md shadow-slate-900/10
                                   dark:shadow-lg dark:shadow-slate-900/30
                                   p-5 lg:p-6 transition-all duration-300 ease-out
                                   hover:-translate-y-1
                                   hover:shadow-lg hover:shadow-indigo-500/20
                                   dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                    Detalle de Meta Mensual
                                </h3>
                            </div>

                            <div class="space-y-3">
                                @foreach($breakdown as $item)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-700 dark:text-slate-300">
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

                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPTS GRÁFICAS (sin cambios, ya soportan dark/light por JS) --}}
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
            const isTecnico   = @json($isTecnico);

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
                const mainColor = t.isDark ? "#6366F1" : "#2563EB";
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
    const colorActual   = t.isDark ? "#2563EB" : "#1D4ED8";  // azul
    const colorAnterior = t.isDark ? "#ff9e36ff" : "#FF9521";  // verde

    const seriesActual   = (tecnicoData.series && tecnicoData.series.actual)   || [];
    const seriesAnterior = (tecnicoData.series && tecnicoData.series.anterior) || [];

    return {
        series: [
            {
                name: isTecnico ? "Este año (tú)" : "Este año (equipo)",
                data: seriesActual
            },
            {
                name: isTecnico ? "Año anterior (tú)" : "Año anterior (equipo)",
                data: seriesAnterior
            }
        ],
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
        colors: [colorActual, colorAnterior],
        fill: {
            opacity: 0.9
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
        legend: {
            labels: { colors: t.text }
        }
    };
};


            const getRadialOptions = (t) => {
                const mainColor = t.isDark ? "#2563EB" : "#2563EB";
                const gradTo    = t.isDark ? "#60A5FA" : "#93C5FD";
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

            // Animación suave de las luces de fondo
            const glow1 = document.getElementById("glow-1");
            const glow2 = document.getElementById("glow-2");
            const glow3 = document.getElementById("glow-3");

            const randomBetween = (min, max) =>
                Math.random() * (max - min) + min;

            const prepareGlowTransitions = (el) => {
                if (!el) return;
                el.style.transition =
                    "top 18s ease-in-out, left 18s ease-in-out, bottom 18s ease-in-out, right 18s ease-in-out";
            };

            prepareGlowTransitions(glow1);
            prepareGlowTransitions(glow2);
            prepareGlowTransitions(glow3);

            const animateGlow1 = () => {
                if (!glow1) return;
                const top = randomBetween(-420, -260);
                const left = randomBetween(-340, -80);
                glow1.style.top = `${top}px`;
                glow1.style.left = `${left}px`;
            };

            const animateGlow2 = () => {
                if (!glow2) return;
                const bottom = randomBetween(-420, -260);
                const right = randomBetween(-340, -80);
                glow2.style.bottom = `${bottom}px`;
                glow2.style.right = `${right}px`;
            };

            const animateGlow3 = () => {
                if (!glow3) return;
                const bottom = randomBetween(-360, -220);
                const leftPercent = randomBetween(25, 75);
                glow3.style.bottom = `${bottom}px`;
                glow3.style.left = `${leftPercent}%`;
            };

            animateGlow1();
            animateGlow2();
            animateGlow3();

            setInterval(animateGlow1, 20000);
            setInterval(animateGlow2, 23000);
            setInterval(animateGlow3, 26000);
        });
    </script>
</x-app-layout>
