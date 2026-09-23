<?php

namespace App\Livewire\Ventas;

use App\Models\Cliente;
use App\Models\DespachoVentas;
use App\Models\Equipo;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Dashboard Ventas'])]
class Dashboard extends Component
{
    // ===== Roles =====
    public bool $isTecnico = false;
    public bool $esAdminCeo = false;
    public bool $esGerenteVentas = false;
    public bool $esLiderGerente = false;
    public bool $puedeConfigurarEmpleadoMes = false;
    public bool $showModalLideres = false;
    public bool $showModalMeta = false;
    public bool $showEmpleadoModal = false;
    public bool $viejoSistema = false;

    // ===== Filtros Livewire =====
    public string $selectedMonthValue = '';
    public ?string $selectedColaboradorId = null;

    // ===== UI =====
    public string $currentMonthName = '';
    public bool $monthFinished = false;
    public array $monthsOptions = [];
    public array $colaboradores = [];

    // ===== Data =====
    public array $kpis = [
        'equiposHoy' => 0,
        'equiposSemana' => 0,
        'equiposMes' => 0,
        'hoy_change' => '0%',
        'semana_change' => '0%',
        'mes_change' => '0%',
        'disponibles' => 0,
        'despachosPendientes' => 0,
        'apartados' => 0,
        'vendidosMes' => 0,
    ];

    public array $lineChart = ['labels' => [], 'data' => []];
    public array $tecnicoChart = ['labels' => [], 'series' => ['actual' => [], 'anterior' => []]];
    public int $radialPercent = 0;
    public array $breakdown = [];
    public array $despachosPendientesList = [];

    public string $labelDia = '';
    public string $labelSemana = '';
    public string $labelMes = '';

    // Glows persistentes
    public array $glows = [
        'glow1Top' => -300,
        'glow1Left' => -200,
        'glow2Bottom' => -300,
        'glow2Right' => -200,
        'glow3Bottom' => -250,
        'glow3LeftPercent' => 50,
    ];

    // Empleado del mes (opcional / null para Ventas)
    public ?array $empleadoMes = null;
    public ?string $empleadoMesUserId = null;
    public ?string $empleadoMesMensaje = null;
    public array $editMetasTecnicos = [];
    public array $avisos = [];

    public function mount(): void
    {
        $user = auth()->user();
        $roleSlug = strtolower(optional($user->role)->slug ?? '');

        $this->esAdminCeo = in_array($roleSlug, ['ceo', 'admin_sistema', 'sistemas'], true);
        $this->esGerenteVentas = in_array($roleSlug, ['ceo', 'gerente', 'admin_sistema'], true);
        $this->esLiderGerente = $this->esGerenteVentas;

        $this->selectedMonthValue = now()->format('Y-m');

        $this->buildMonthsOptions();
        $this->loadData();
    }

    public function updatedSelectedMonthValue(): void
    {
        $this->loadData();
    }

    public function updatedSelectedColaboradorId(): void
    {
        $this->loadData();
    }

    public function refreshDashboard(): void
    {
        $this->loadData();
    }

    public function abrirModalMeta(): void {}
    public function quitarEmpleadoDelMes(): void {}
    public function openEmpleadoModal(): void {}
    public function closeEmpleadoModal(): void { $this->showEmpleadoModal = false; }
    public function saveEmpleadoDelMes(): void {}
    public function descargarReportePuntos(): void {}
    public function recalcularMetaTotal(): void {}
    public function guardarMeta(): void {}

    private function buildMonthsOptions(): void
    {
        $monthsOptions = [];
        for ($i = 0; $i < 12; $i++) {
            $d = Carbon::now()->subMonths($i);
            $monthsOptions[] = [
                'value' => $d->format('Y-m'),
                'label' => ucfirst($d->locale('es')->translatedFormat('F Y')),
            ];
        }
        $this->monthsOptions = $monthsOptions;
    }

    private function calcularCambio($actual, $anterior): string
    {
        if ($anterior == 0) {
            return $actual == 0 ? '0%' : '+100%';
        }
        $porcentaje = (($actual - $anterior) / $anterior) * 100;
        $signo = $porcentaje > 0 ? '+' : '';
        return $signo . round($porcentaje) . '%';
    }

    public function loadData(): void
    {
        try {
            $selectedDate = Carbon::createFromFormat('Y-m', $this->selectedMonthValue)->startOfMonth();
        } catch (\Exception $e) {
            $selectedDate = Carbon::now()->startOfMonth();
            $this->selectedMonthValue = $selectedDate->format('Y-m');
        }

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $this->currentMonthName = ucfirst($selectedDate->locale('es')->translatedFormat('F Y'));
        $this->monthFinished = $endOfMonth->lt(Carbon::now()->endOfDay());

        $this->labelDia = $selectedDate->locale('es')->translatedFormat('d M Y');
        $this->labelSemana = $startOfMonth->locale('es')->translatedFormat('d M') . ' - ' . $endOfMonth->locale('es')->translatedFormat('d M');
        $this->labelMes = $this->currentMonthName;

        // Colaboradores de Ventas
        $this->colaboradores = User::whereHas('departamento', function ($q) {
            $q->where('clave', 'VENTAS');
        })->get(['id', 'nombre', 'apellido_paterno'])
          ->map(fn ($u) => ['id' => $u->id, 'nombre' => trim("{$u->nombre} {$u->apellido_paterno}")])
          ->toArray();

        // 1. Conteo de KPI de Equipos e Ingresos a Ventas
        $disponiblesCount = Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)->count();
        $apartadosCount = Equipo::where('estatus_ciclo', Equipo::CICLO_APARTADO)->count();
        $vendidosMesCount = Equipo::where('estatus_ciclo', Equipo::CICLO_VENDIDO)
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->count();

        $despachosPendientesCount = DespachoVentas::where('estatus', DespachoVentas::ENVIADO)->count();

        // Despachos recientes por recibir
        $this->despachosPendientesList = DespachoVentas::with(['creadoPor', 'equipos'])
            ->where('estatus', DespachoVentas::ENVIADO)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->toArray();

        // Equipos agregados/recibidos en Ventas (KPIs hoy/semana/mes)
        $equiposQuery = function (Carbon $start, Carbon $end) {
            return Equipo::whereIn('estatus_ciclo', [Equipo::CICLO_VENTAS, Equipo::CICLO_APARTADO, Equipo::CICLO_VENDIDO])
                ->whereBetween('updated_at', [$start, $end])
                ->count();
        };

        $hoy = $selectedDate->copy()->day(min(Carbon::now()->day, $selectedDate->daysInMonth));
        $ayer = $hoy->copy()->subDay();

        $equiposHoy = $equiposQuery($hoy->copy()->startOfDay(), $hoy->copy()->endOfDay());
        $equiposAyer = $equiposQuery($ayer->copy()->startOfDay(), $ayer->copy()->endOfDay());
        $hoyChange = $this->calcularCambio($equiposHoy, $equiposAyer);

        $weekStart = $hoy->copy()->startOfWeek();
        $weekEnd = $hoy->copy()->endOfWeek();
        $prevWeekStart = $weekStart->copy()->subWeek();
        $prevWeekEnd = $weekEnd->copy()->subWeek();

        $equiposSemana = $equiposQuery($weekStart, $weekEnd);
        $equiposSemanaAnterior = $equiposQuery($prevWeekStart, $prevWeekEnd);
        $semanaChange = $this->calcularCambio($equiposSemana, $equiposSemanaAnterior);

        $equiposMes = $equiposQuery($startOfMonth, $endOfMonth);
        $equiposMesAnterior = $equiposQuery($startOfMonth->copy()->subMonth(), $endOfMonth->copy()->subMonth());
        $mesChange = $this->calcularCambio($equiposMes, $equiposMesAnterior);

        $this->kpis = [
            'equiposHoy' => $equiposHoy,
            'equiposSemana' => $equiposSemana,
            'equiposMes' => $equiposMes,
            'hoy_change' => $hoyChange,
            'semana_change' => $semanaChange,
            'mes_change' => $mesChange,
            'disponibles' => $disponiblesCount,
            'despachosPendientes' => $despachosPendientesCount,
            'apartados' => $apartadosCount,
            'vendidosMes' => $vendidosMesCount,
        ];

        // 2. Gráfica de Líneas (Ventas / Ingresos por semana)
        $lineDataLabels = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
        $lineDataCounts = [0, 0, 0, 0, 0];

        $semanas = [[1, 7], [8, 14], [15, 21], [22, 28], [29, $endOfMonth->day]];
        foreach ($semanas as $i => [$dIni, $dFin]) {
            $s = $startOfMonth->copy()->day($dIni)->startOfDay();
            $e = $startOfMonth->copy()->day(min($dFin, $endOfMonth->day))->endOfDay();
            $lineDataCounts[$i] = $equiposQuery($s, $e);
        }

        $this->lineChart = [
            'labels' => $lineDataLabels,
            'data' => $lineDataCounts,
        ];

        // 3. Gráfica de Comparativa (Últimos 4 Meses)
        $labels = [];
        $serieActualAno = [];
        $serieAnoAnterior = [];

        for ($i = 3; $i >= 0; $i--) {
            $monthDate = $selectedDate->copy()->subMonths($i);
            $labels[] = ucfirst($monthDate->locale('es')->translatedFormat('M'));

            $currentStart = $monthDate->copy()->startOfMonth();
            $currentEnd = $monthDate->copy()->endOfMonth();
            $prevStart = $monthDate->copy()->subYear()->startOfMonth();
            $prevEnd = $monthDate->copy()->subYear()->endOfMonth();

            $serieActualAno[] = $equiposQuery($currentStart, $currentEnd);
            $serieAnoAnterior[] = $equiposQuery($prevStart, $prevEnd);
        }

        $this->tecnicoChart = [
            'labels' => $labels,
            'series' => [
                'actual' => $serieActualAno,
                'anterior' => $serieAnoAnterior,
            ],
        ];

        // 4. Meta Mensual de Ventas
        $metaMensual = 100;
        $faltantes = max($metaMensual - $equiposMes, 0);
        $percentMeta = $metaMensual > 0 ? min(round(($equiposMes / $metaMensual) * 100), 100) : 0;

        $this->radialPercent = (int) $percentMeta;
        $this->breakdown = [
            ['label' => 'Meta mensual de ventas', 'value' => $metaMensual],
            ['label' => 'Equipos recepcionados / vendidos', 'value' => $equiposMes],
            ['label' => 'Faltantes para la meta', 'value' => $faltantes],
            ['label' => 'Personal de Ventas', 'value' => count($this->colaboradores)],
        ];

        $this->dispatch('dashboard-data-updated',
            lineChart: $this->lineChart,
            tecnicoChart: $this->tecnicoChart,
            radialPercent: $this->radialPercent,
            isTecnico: false,
        );
    }

    public function render()
    {
        return view('livewire.ventas.dashboard', get_object_vars($this));
    }
}
