<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard General') }} 
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 dark:bg-gray-900">
        <div class="sm:px-6 lg:px-8">
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
                    {{-- =================================================== --}}
                    {{-- COLUMNA IZQUIERDA Y CENTRAL (Tu diseño) --}}
                    {{-- =================================================== --}}
                    <div class="lg:col-span-2 space-y-6">
            
                        {{-- Fila de 3 Tarjetas de Estadísticas --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
                            {{-- Tarjeta 1: Equipos Hechos Hoy (¡Con clases dark:!) --}}
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Equipos Hechos (Hoy)</h3>
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A1.5 1.5 0 0118 21.75H6a1.5 1.5 0 01-1.499-1.632z" />
                                    </svg>
                                </div>
                                <div class="mt-2">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $kpis['equiposHoy'] }}</p>
                                    <div class="flex items-center text-sm">
                                        <span class="font-semibold text-green-500">{{ $kpis['hoy_change'] }}</span>
                                        <span class="ml-2 text-gray-400">Hoy</span>
                                    </div>
                                </div>
                            </div>
            
                            {{-- Tarjeta 2: Equipos Hechos Semana (¡Con clases dark:!) --}}
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Equipos Hechos (Semana)</h3>
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.75A.75.75 0 013 4.5h.75M5.25 12v.75a.75.75 0 01-.75.75H3.75v-.75a.75.75 0 01.75-.75h.75m0 0v-.75a.75.75 0 01.75-.75h.75v.75a.75.75 0 01-.75.75h-.75M9 4.5v.75a.75.75 0 01-.75.75H7.5v-.75a.75.75 0 01.75-.75h.75m0 0v-.75a.75.75 0 01.75-.75h.75v.75a.75.75 0 01-.75.75h-.75m-1.5 7.5v.75a.75.75 0 01-.75.75H7.5v-.75a.75.75 0 01.75-.75h.75m0 0v-.75a.75.75 0 01.75-.75h.75v.75a.75.75 0 01-.75.75h-.75M15 4.5v.75a.75.75 0 01-.75.75h-.75v-.75a.75.75 0 01.75-.75h.75m0 0v-.75a.75.75 0 01.75-.75h.75v.75a.75.75 0 01-.75.75h-.75m-1.5 7.5v.75a.75.75 0 01-.75.75h-.75v-.75a.75.75 0 01.75-.75h.75m0 0v-.75a.75.75 0 01.75-.75h.75v.75a.75.75 0 01-.75.75h-.75m-1.5 7.5v.75a.75.75 0 01-.75.75h-.75v-.75a.75.75 0 01.75-.75h.75m0 0v-.75a.75.75 0 01.75-.75h.75v.75a.75.75 0 01-.75.75h-.75" />
                                    </svg>
                                </div>
                                <div class="mt-2">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $kpis['equiposSemana'] }}</p>
                                    <div class="flex items-center text-sm">
                                        <span class="font-semibold text-green-500">{{ $kpis['semana_change'] }}</span>
                                        <span class="ml-2 text-gray-400">Esta semana</span>
                                    </div>
                                </div>
                            </div>
            
                            {{-- Tarjeta 3: Equipos Hechos Mes (¡Con clases dark:!) --}}
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Equipos Hechos (Mes)</h3>
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="mt-2">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $kpis['equiposMes'] }}</p>
                                    <div class="flex items-center text-sm">
                                        <span class="font-semibold text-green-500">{{ $kpis['mes_change'] }}</span>
                                        <span class="ml-2 text-gray-400">Este mes</span>
                                    </div>
                                </div>
                            </div>
                        </div> {{-- Fin de la fila de 3 tarjetas --}}
            
                        {{-- Tarjeta Gráfica de Línea (¡Con clases dark:!) --}}
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Equipos por Semana (Mes de {{ $currentMonthName }})</h3>
                            <div id="line-chart" class="mt-4"></div>
                        </div>
            
                        {{-- Tarjeta Gráfica de Barras (¡Con clases dark:!) --}}
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Producción por Técnico</h3>
                            <div id="bar-chart" class="mt-4"></div>
                        </div>
            
                    </div> 
            
                    {{-- =================================================== --}}
                    {{-- COLUMNA DERECHA (¡Con clases dark:!) --}}
                    {{-- =================================================== --}}
                    <div class="lg:col-span-1 space-y-6">
            
                        {{-- Tarjeta Gráfica Radial --}}
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Tasa de Aprobación</h3>
                            <div id="radial-chart" class="mt-4"></div>
                        </div>
            
                        {{-- Tarjeta de Tabla "Breakdown" --}}
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Detalle de Inventario</h3>
                            <div class="space-y-4">
                                @foreach($breakdown as $item)
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $item['label'] }}</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-white">{{ $item['value'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
            
                    </div> 
            
                </div> {{-- Fin del grid principal --}}
            </div>
        </div>
    </div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    // ========= TEMA LEÍDO DESDE LA CLASE "dark" DEL <html> =========
    const getTheme = () => {
        const isDark = document.documentElement.classList.contains("dark");
        return {
            isDark,
            mode: isDark ? "dark" : "light",
            text: isDark ? "#E5E7EB" : "#374151",   // gris-200 / gris-700
            grid: isDark ? "#4B5563" : "#E5E7EB",   // gris-600 / gris-200
            track: isDark ? "#111827" : "#E5E7EB"   // para fondo del radial
        };
    };

    // Datos que vienen de Laravel
    const lineData    = @json($lineChart);     // { labels: [...], data: [...] }
    const tecnicoData = @json($tecnicoChart);  // { labels: [...], data: [...] }
    const radialData  = @json($radialPercent); // número (porcentaje)

    // ========= CONFIG BASE (ANIMACIONES / SOMBRAS) =========
    const chartBase = {
        animations: {
            enabled: true,
            easing: "easeinout",
            speed: 800,
            animateGradually: {
                enabled: true,
                delay: 120
            },
            dynamicAnimation: {
                enabled: true,
                speed: 350
            }
        },
        toolbar: { show: false },
        background: "transparent",
        dropShadow: {
            enabled: true,
            top: 2,
            left: 0,
            blur: 4,
            opacity: 0.12
        }
    };

    // ========= FACTORÍAS DE OPCIONES SEGÚN EL TEMA =========

    // 1) LÍNEA / ÁREA: Equipos por semana
    const getLineOptions = (t) => {
        const mainColor = t.isDark ? "#6366F1" : "#1718FF"; // azul marca en claro
        return {
            series: [{
                name: "Equipos",
                data: lineData.data
            }],
            chart: {
                ...chartBase,
                height: 260,
                type: "area" // area para relleno suave + línea visible
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
                hover: {
                    size: 7
                }
            },
            fill: {
                type: "gradient",
                gradient: {
                    shade: t.isDark ? "dark" : "light",
                    type: "vertical",
                    opacityFrom: 0.25,
                    opacityTo: 0,
                    stops: [0, 100]
                }
            },
            xaxis: {
                categories: lineData.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: t.text }
                }
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
                y: {
                    formatter: val => `${val} equipos`
                }
            },
            legend: {
                labels: { colors: t.text }
            }
        };
    };

    // 2) BARRAS: Producción por técnico
    const getBarOptions = (t) => {
        const mainColor = t.isDark ? "#1D1B5F" : "#1718FF"; // oscuro vs azul marca
        const gradientTo = t.isDark ? "#4F46E5" : "#3440FF"; // tono más claro para degradado
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
                    borderRadius: 6,
                    dataLabels: {
                        position: "top"
                    }
                }
            },
            dataLabels: {
                enabled: false   // sin números encima
            },
            colors: [mainColor],
            fill: {
                type: "gradient",
                gradient: {
                    shade: t.isDark ? "dark" : "light",
                    type: "vertical",
                    gradientToColors: [gradientTo],
                    stops: [0, 60, 100],
                    opacityFrom: 0.9,
                    opacityTo: 0.75
                }
            },
            states: {
                hover: {
                    filter: {
                        type: "lighten",
                        value: 0.08
                    }
                },
                active: {
                    filter: {
                        type: "lighten",
                        value: 0.12
                    }
                }
            },
            xaxis: {
                categories: tecnicoData.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: t.text }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: t.text },
                    formatter: value => Math.round(value)
                }
            },
            tooltip: {
                theme: t.mode,
                y: {
                    formatter: val => `${val} equipos`
                }
            },
            legend: {
                labels: { colors: t.text }
            }
        };
    };

    // 3) RADIAL: Tasa de aprobación
    const getRadialOptions = (t) => {
        const mainColor = t.isDark ? "#2563EB" : "#1718FF";
        const gradTo    = t.isDark ? "#60A5FA" : "#516CFF";
        return {
            series: [radialData],
            chart: {
                ...chartBase,
                type: "radialBar",
                height: 300,
                dropShadow: {
                    enabled: true,
                    top: 0,
                    left: 0,
                    blur: 6,
                    opacity: 0.18
                }
            },
            theme: { mode: t.mode },
            labels: ["Aprobados"],
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
                        opacity: 0.4
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            fontSize: "0.90rem",
                            offsetY: -8,
                            color: t.text
                        },
                        value: {
                            fontSize: "2rem",
                            fontWeight: "700",
                            offsetY: 10,
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
                    stops: [0, 50, 100]
                }
            },
            colors: [mainColor]
        };
    };

    // ========= CREACIÓN INICIAL DE GRÁFICAS =========
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

    // ========= FUNCIÓN PARA REAPLICAR TEMA COMPLETO =========
    const updateChartsTheme = () => {
        const t = getTheme();
        lineChart.updateOptions(getLineOptions(t));
        barChart.updateOptions(getBarOptions(t));
        radialChart.updateOptions(getRadialOptions(t));
    };

    // ========= OBSERVADOR DE CAMBIO DE CLASE "dark" EN <html> =========
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