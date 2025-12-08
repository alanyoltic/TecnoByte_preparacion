<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ===== 1. MES SELECCIONADO DESDE EL HEADER =====
        $monthParam = $request->query('month');

        if ($monthParam) {
            try {
                $selectedDate = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $selectedDate = Carbon::now()->startOfMonth();
            }
        } else {
            $selectedDate = Carbon::now()->startOfMonth();
        }

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth   = $selectedDate->copy()->endOfMonth();

        // Valor para el <select>
        $selectedMonthValue = $selectedDate->format('Y-m');
        // Nombre bonito del mes (en español)
        $currentMonthName   = $selectedDate->locale('es')->translatedFormat('F Y');

        // ¿Ese mes ya terminó?
        $monthFinished = $endOfMonth->lt(Carbon::now()->endOfDay());

        // Lista de últimos 12 meses para el selector
        $monthsOptions = [];
        for ($i = 0; $i < 12; $i++) {
            $d = Carbon::now()->subMonths($i);
            $monthsOptions[] = [
                'value' => $d->format('Y-m'),
                'label' => ucfirst($d->locale('es')->translatedFormat('F Y')),
            ];
        }

        // ===== ROLES / TIPO DE USUARIO =====
        $user     = auth()->user();
        $roleSlug = strtolower(optional($user->role)->slug ?? '');
        $roleName = strtolower(optional($user->role)->nombre ?? '');

        // Consideramos técnico si el slug o el nombre se parecen a "tecnico"/"técnico"
        $isTecnico = in_array($roleSlug, ['tecnico', 'técnico'])
            || in_array($roleName, ['tecnico', 'técnico']);

        // Helper: si es técnico, filtra por él; si no, deja global
        $aplicarFiltroTecnico = function ($query) use ($isTecnico, $user) {
            if ($isTecnico && $user) {
                $query->where('registrado_por_user_id', $user->id);
            }
            return $query;
        };

        // ===== 2. KPIs =====
        $today = Carbon::today();

        // HOY
        $equiposHoy = $aplicarFiltroTecnico(
            Equipo::whereDate('created_at', $today)
        )->count();

        // SEMANA
        $semanaInicio = $today->copy()->startOfWeek();
        $semanaFin    = $today->copy()->endOfWeek();

        $equiposSemana = $aplicarFiltroTecnico(
            Equipo::whereBetween('created_at', [$semanaInicio, $semanaFin])
        )->count();

        // MES (mes seleccionado)
        $equiposMes = $aplicarFiltroTecnico(
            Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        )->count();

        $kpis = [
            'equiposHoy'    => $equiposHoy,
            'equiposSemana' => $equiposSemana,
            'equiposMes'    => $equiposMes,
            'hoy_change'    => '+5%',
            'semana_change' => '+12%',
            'mes_change'    => '+20%',
        ];

        // ===== 3. GRÁFICA LÍNEA (equipos por semana del MES SELECCIONADO) =====
        $lineDataLabels = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
        $lineDataCounts = [0, 0, 0, 0, 0];

        $equiposDelMes = $aplicarFiltroTecnico(
            Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        )->get(['created_at']);

        foreach ($equiposDelMes as $equipo) {
            $diaDelMes = $equipo->created_at->day;

            if ($diaDelMes <= 7) {
                $lineDataCounts[0]++;
            } elseif ($diaDelMes <= 14) {
                $lineDataCounts[1]++;
            } elseif ($diaDelMes <= 21) {
                $lineDataCounts[2]++;
            } elseif ($diaDelMes <= 28) {
                $lineDataCounts[3]++;
            } else {
                $lineDataCounts[4]++;
            }
        }

        // ===== 4. GRÁFICA BARRAS VS (4 meses · año actual vs año anterior) =====
        $labels           = [];
        $serieActualAno   = [];
        $serieAnoAnterior = [];

        // Recorremos desde 3 meses atrás hasta el mes seleccionado (orden cronológico)
        for ($i = 3; $i >= 0; $i--) {
            $monthDate = $selectedDate->copy()->subMonths($i);

            // Etiqueta tipo "Oct", "Nov", "Dic"
            $labels[] = ucfirst($monthDate->locale('es')->translatedFormat('M'));

            // Rango del mes en el año actual
            $currentYearStart = $monthDate->copy()->startOfMonth();
            $currentYearEnd   = $monthDate->copy()->endOfMonth();

            // Mismo mes pero del año anterior
            $prevYearStart = $monthDate->copy()->subYear()->startOfMonth();
            $prevYearEnd   = $monthDate->copy()->subYear()->endOfMonth();

            // Query base para año actual
            $queryCurrent = Equipo::whereBetween('created_at', [$currentYearStart, $currentYearEnd]);

            // Query base para año anterior
            $queryPrev = Equipo::whereBetween('created_at', [$prevYearStart, $prevYearEnd]);

            // Si es técnico, filtramos por ese usuario
            if ($isTecnico && $user) {
                $queryCurrent->where('registrado_por_user_id', $user->id);
                $queryPrev->where('registrado_por_user_id', $user->id);
            }

            $serieActualAno[]   = $queryCurrent->count();
            $serieAnoAnterior[] = $queryPrev->count();
        }

        $tecnicoChartData = [
            'labels' => $labels,
            'series' => [
                'actual'   => $serieActualAno,
                'anterior' => $serieAnoAnterior,
            ],
        ];

        // ===== 5. META MENSUAL (radial) =====
        $colaboradores      = 5;
        $metaPorColaborador = 120;
        $metaTotal          = $colaboradores * $metaPorColaborador; // 600

        $equiposRealizadosMes = $equiposMes;
        $equiposFaltantes     = max($metaTotal - $equiposRealizadosMes, 0);

        $percentMeta = $metaTotal > 0
            ? min(round(($equiposRealizadosMes / $metaTotal) * 100), 100)
            : 0;

        $radialPercent = $percentMeta;

        // ===== 6. Detalle Meta Mensual =====
        $breakdown = [
            ['label' => 'Meta mensual total',       'value' => $metaTotal],
            ['label' => 'Equipos realizados (mes)', 'value' => $equiposRealizadosMes],
            ['label' => 'Faltantes para la meta',   'value' => $equiposFaltantes],
            ['label' => 'Colaboradores',            'value' => $colaboradores],
        ];

        // ===== 7. Enviar a la vista =====
        return view('dashboard', [
            'kpis'               => $kpis,
            'lineChart'          => ['labels' => $lineDataLabels, 'data' => $lineDataCounts],
            'tecnicoChart'       => $tecnicoChartData,
            'radialPercent'      => $radialPercent,
            'currentMonthName'   => $currentMonthName,
            'breakdown'          => $breakdown,
            'monthsOptions'      => $monthsOptions,
            'selectedMonthValue' => $selectedMonthValue,
            'monthFinished'      => $monthFinished,
            'isTecnico'          => $isTecnico,
        ]);
    }
}
