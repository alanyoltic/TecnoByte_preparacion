<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\User;
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

        $selectedMonthValue = $selectedDate->format('Y-m');
        $currentMonthName   = $selectedDate->locale('es')->translatedFormat('F Y');
        $monthFinished      = $endOfMonth->lt(Carbon::now()->endOfDay());

        // Últimos 12 meses para el selector
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

        // Técnico si slug o nombre son "tecnico"/"técnico"
        $isTecnico = in_array($roleSlug, ['tecnico', 'técnico'])
            || in_array($roleName, ['tecnico', 'técnico']);

        // Filtro de colaborador SOLO para admin/ceo
        $selectedColaboradorId = $request->query('colaborador');

        // Helper de filtro global para TODOS los queries
        $aplicarFiltro = function ($query) use ($isTecnico, $user, $selectedColaboradorId) {
            if ($isTecnico && $user) {
                // Técnico: siempre filtra por él
                $query->where('registrado_por_user_id', $user->id);
            } elseif (!$isTecnico && !empty($selectedColaboradorId)) {
                // Admin/Ceo con filtro: filtra por colaborador elegido
                $query->where('registrado_por_user_id', $selectedColaboradorId);
            }

            return $query;
        };

        // ===== 2. KPIs =====
        $today = Carbon::today();

        // HOY
        $equiposHoy = $aplicarFiltro(
            Equipo::whereDate('created_at', $today)
        )->count();

        // SEMANA
        $semanaInicio = $today->copy()->startOfWeek();
        $semanaFin    = $today->copy()->endOfWeek();

        $equiposSemana = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$semanaInicio, $semanaFin])
        )->count();

        // MES (mes seleccionado)
        $equiposMes = $aplicarFiltro(
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

        $equiposDelMes = $aplicarFiltro(
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

        // De 3 meses atrás hasta el mes seleccionado (orden cronológico)
        for ($i = 3; $i >= 0; $i--) {
            $monthDate = $selectedDate->copy()->subMonths($i);

            // "Oct", "Nov", "Dic"
            $labels[] = ucfirst($monthDate->locale('es')->translatedFormat('M'));

            // Año actual
            $currentYearStart = $monthDate->copy()->startOfMonth();
            $currentYearEnd   = $monthDate->copy()->endOfMonth();

            // Mismo mes año anterior
            $prevYearStart = $monthDate->copy()->subYear()->startOfMonth();
            $prevYearEnd   = $monthDate->copy()->subYear()->endOfMonth();

            $queryCurrent = $aplicarFiltro(
                Equipo::whereBetween('created_at', [$currentYearStart, $currentYearEnd])
            );

            $queryPrev = $aplicarFiltro(
                Equipo::whereBetween('created_at', [$prevYearStart, $prevYearEnd])
            );

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

        // ===== 5. LISTA DE COLABORADORES (técnicos) =====
        $tecnicosBaseQuery = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn(DB::raw('LOWER(roles.slug)'), ['tecnico', 'técnico']);

        $colaboradoresCount = (clone $tecnicosBaseQuery)->count();

        $colaboradores = [];
        if (!$isTecnico) {
            $colaboradores = (clone $tecnicosBaseQuery)
                ->orderBy('users.nombre')
                ->get([
                    'users.id',
                    'users.nombre',
                    'users.apellido_paterno',
                ])
                ->map(function ($u) {
                    return [
                        'id'     => $u->id,
                        'nombre' => trim($u->nombre . ' ' . $u->apellido_paterno),
                    ];
                })
                ->toArray();
        }

        // ===== 6. META MENSUAL (radial) =====
        $metaPorColaborador = 140;

        // ¿Para cuántos colaboradores calculamos la meta?
        if ($isTecnico) {
            // Técnico logueado: su propia meta
            $colaboradoresMetaCount = 1;
        } elseif (!$isTecnico && !empty($selectedColaboradorId)) {
            // Admin/CEO filtrando a un solo colaborador
            $colaboradoresMetaCount = 1;
        } else {
            // Vista global: todos los técnicos del sistema
            $colaboradoresMetaCount = max($colaboradoresCount, 1);
        }

        $metaTotal = $colaboradoresMetaCount * $metaPorColaborador;

        $equiposRealizadosMes = $equiposMes;
        $equiposFaltantes     = max($metaTotal - $equiposRealizadosMes, 0);

        $percentMeta = $metaTotal > 0
            ? min(round(($equiposRealizadosMes / $metaTotal) * 100), 100)
            : 0;

        $radialPercent = $percentMeta;

        $breakdown = [
            ['label' => 'Meta mensual total',       'value' => $metaTotal],
            ['label' => 'Equipos realizados (mes)', 'value' => $equiposRealizadosMes],
            ['label' => 'Faltantes para la meta',   'value' => $equiposFaltantes],
            ['label' => 'Colaboradores',            'value' => $colaboradoresMetaCount],
        ];

        // ===== 7. Enviar a la vista =====
        return view('dashboard', [
            'kpis'                 => $kpis,
            'lineChart'            => ['labels' => $lineDataLabels, 'data' => $lineDataCounts],
            'tecnicoChart'         => $tecnicoChartData,
            'radialPercent'        => $radialPercent,
            'currentMonthName'     => $currentMonthName,
            'breakdown'            => $breakdown,
            'monthsOptions'        => $monthsOptions,
            'selectedMonthValue'   => $selectedMonthValue,
            'monthFinished'        => $monthFinished,
            'isTecnico'            => $isTecnico,
            'colaboradores'        => $colaboradores,
            'selectedColaboradorId'=> $selectedColaboradorId,
        ]);

    }
}
