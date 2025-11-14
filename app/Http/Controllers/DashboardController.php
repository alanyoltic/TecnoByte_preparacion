<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ===== 1. MES SELECCIONADO DESDE EL HEADER =====
        // Esperamos algo tipo ?month=2024-11
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

        // ===== 2. KPIs =====
        $today = Carbon::today();

        $equiposHoy    = Equipo::whereDate('created_at', $today)->count();
        $equiposSemana = Equipo::whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])->count();
        $equiposMes    = Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

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

        $equiposDelMes = Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                               ->get(['created_at']);

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

        // ===== 4. GRÁFICA BARRAS (producción por técnico del MES SELECCIONADO) =====
        $equiposPorTecnico = Equipo::query()
            ->join('users', 'equipos.registrado_por_user_id', '=', 'users.id')
            ->whereBetween('equipos.created_at', [$startOfMonth, $endOfMonth])
            ->select('users.nombre', DB::raw('count(*) as total'))
            ->groupBy('users.nombre')
            ->pluck('total', 'nombre');

        $tecnicoChartData = [
            'labels' => $equiposPorTecnico->keys(),
            'data'   => $equiposPorTecnico->values(),
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

        // ===== 6. Detalle Meta Mensual (tabla derecha) =====
        $breakdown = [
            ['label' => 'Meta mensual total',   'value' => $metaTotal],
            ['label' => 'Equipos realizados (mes)', 'value' => $equiposRealizadosMes],
            ['label' => 'Faltantes para la meta',   'value' => $equiposFaltantes],
            ['label' => 'Colaboradores',        'value' => $colaboradores],
        ];

        // ===== 7. Enviar a la vista =====
        return view('dashboard', [
            'kpis'               => $kpis,
            'lineChart'          => ['labels' => $lineDataLabels, 'data' => $lineDataCounts],
            'tecnicoChart'       => $tecnicoChartData,
            'radialPercent'      => $radialPercent,
            'currentMonthName'   => $currentMonthName,     // ahora es el mes SELECCIONADO
            'breakdown'          => $breakdown,
            'monthsOptions'      => $monthsOptions,
            'selectedMonthValue' => $selectedMonthValue,
            'monthFinished'      => $monthFinished,
        ]);
    }
}
