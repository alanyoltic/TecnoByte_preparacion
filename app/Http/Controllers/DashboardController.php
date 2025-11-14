<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\User;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // --- 1. KPIs (Tus 3 Tarjetas Superiores) ---
        $equiposHoy = Equipo::whereDate('created_at', Carbon::today())->count();
        $equiposSemana = Equipo::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $equiposMes = Equipo::whereMonth('created_at', Carbon::now()->month)->count();

        $kpis = [
            'equiposHoy' => $equiposHoy,
            'equiposSemana' => $equiposSemana,
            'equiposMes' => $equiposMes,
            'hoy_change' => '+5%',
            'semana_change' => '+12%',
            'mes_change' => '+20%',
        ];

        // --- 1.1 META MENSUAL (120 x 5 colaboradores) ---
        $colaboradores = 5;              // si luego quieres, esto se puede sacar de la BD
        $metaPorColaborador = 120;
        $metaTotal = $colaboradores * $metaPorColaborador; // 600

        // Usamos los equipos del mes como avance actual
        $metaChart = [
            'metaTotal' => $metaTotal,
            'actual'    => $equiposMes,
        ];

        // --- 2. Gráfica de Líneas (Equipos por Semana DEL MES ACTUAL) ---
        $currentMonthName = Carbon::now()->locale('es')->getTranslatedMonthName('F');

        $lineDataLabels = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
        $lineDataCounts = [0, 0, 0, 0, 0];

        $equiposDelMes = Equipo::whereMonth('created_at', Carbon::now()->month)
                               ->whereYear('created_at', Carbon::now()->year)
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

        // --- 3. Gráfica de Barras (Equipos por Técnico) ---
        $equiposPorTecnico = Equipo::query()
            ->join('users', 'equipos.registrado_por_user_id', '=', 'users.id')
            ->select('users.nombre', DB::raw('count(*) as total'))
            ->groupBy('users.nombre')
            ->pluck('total', 'nombre');

        $tecnicoChartData = [
            'labels' => $equiposPorTecnico->keys(),
            'data' => $equiposPorTecnico->values(),
        ];

        // --- 4. Gráfica Radial (% de Aprobación Total) ---
        $totalEquipos = Equipo::count();
        $equiposAprobados = Equipo::where('estatus_general', 'Aprobado')->count();
        if ($totalEquipos == 0) {
            $percentAprobados = 0;
        } else {
            $percentAprobados = round(($equiposAprobados / $totalEquipos) * 100);
        }

        // --- 5. Tabla 'Breakdown' ---
        $breakdown = [
            ['label' => 'Total Equipos', 'value' => $totalEquipos],
            ['label' => 'Equipos Aprobados', 'value' => $equiposAprobados],
            ['label' => 'Pendientes de Pieza', 'value' => Equipo::where('estatus_general', 'Pendiente Pieza')->count()],
            ['label' => 'Proveedores Activos', 'value' => Proveedor::count()],
        ];

        // --- 6. Enviar datos a la vista ---
        return view('dashboard', [
            'kpis'            => $kpis,
            'lineChart'       => ['labels' => $lineDataLabels, 'data' => $lineDataCounts],
            'tecnicoChart'    => $tecnicoChartData,
            'radialPercent'   => $percentAprobados,
            'currentMonthName'=> $currentMonthName,
            'breakdown'       => $breakdown,
            'metaChart'       => $metaChart,   // 👈 importante para la barra de meta
        ]);
    }
}
