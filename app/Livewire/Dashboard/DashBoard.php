<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Equipo;
use App\Models\User;
use App\Models\PreparacionMetaMensual;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Aviso;
use App\Models\EmpleadoDelMes;

class Dashboard extends Component
{

//fix

    public bool $esAdminCeo = false;

    // ===== Roles =====
    public bool $isTecnico = false;

    // ===== Filtros Livewire =====
    public string $selectedMonthValue = '';
    public ?string $selectedColaboradorId = null;

    // ===== UI =====
    public string $currentMonthName = '';
    public bool $monthFinished = false;

    public array $monthsOptions = [];
    public array $colaboradores = [];

    // ===== Data =====
    public array $kpis = [];
    public array $lineChart = ['labels' => [], 'data' => []];
    public array $tecnicoChart = ['labels' => [], 'series' => ['actual' => [], 'anterior' => []]];
    public int $radialPercent = 0;
    public array $breakdown = [];

    // ===== Data =====
    public $avisos =[];



    


    // Glows persistentes 
    public array $glows = [];

    public ?string $selectedDate = null; // formato Y-m-d


    //Empleado del mes
    public ?array $empleadoMes = null;
    public bool $showEmpleadoModal = false;
    public ?string $empleadoMesUserId = null;
    public ?string $empleadoMesMensaje = null;



    




    private function cargarAvisos(): void
    {
        $now = Carbon::now();

        $this->avisos = Aviso::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('pinned')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'titulo' => $a->titulo,
                'texto'  => $a->texto,
                'tag'    => $a->tag ?? 'INFO',
                'color'  => $a->color ?? 'slate',
                'icono'  => $a->icono ?? 'Ã°Å¸â€œÅ’',
            ])
            ->toArray();
}


    




    public function mount(): void
    {
        $user     = auth()->user();
        $roleSlug = strtolower(optional($user->role)->slug ?? '');
        $roleName = strtolower(optional($user->role)->nombre ?? '');
        $this->cargarAvisos();
        

        $this->isTecnico = in_array($roleSlug, ['tecnico', 'tÃƒÂ©cnico'])
            || in_array($roleName, ['tecnico', 'tÃƒÂ©cnico']);

        $this->selectedMonthValue = now()->format('Y-m');

        // Si es tÃƒÂ©cnico, el filtro de colaborador debe quedar vacÃƒÂ­o (porque siempre se filtra a ÃƒÂ©l)
        if ($this->isTecnico) {
            $this->selectedColaboradorId = null;
        }

        // Glows una sola vez
        $this->glows = [
            'glow1Top'  => rand(-420, -260),
            'glow1Left' => rand(-320, -120),
            'glow2Bottom' => rand(-420, -260),
            'glow2Right'  => rand(-320, -120),
            'glow3Bottom' => rand(-340, -220),
            'glow3LeftPercent' => rand(30, 70),
        ];

        $this->buildMonthsOptions();
        $this->loadData();

        $this->loadData();
        $this->cargarEmpleadoDelMes();
    }

    public function updatedSelectedMonthValue(): void
    {
        $this->loadData();
    }

    public function updatedSelectedColaboradorId(): void
    {
        // Si es tÃƒÂ©cnico, ignora cambios (por seguridad)
        if ($this->isTecnico) {
            $this->selectedColaboradorId = null;
        }

        $this->loadData();
    }

    public function refreshDashboard(): void
    {
        $this->loadData();
        $this->cargarAvisos();
    }

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


private function cargarEmpleadoDelMes(): void
{
    $record = EmpleadoDelMes::query()
        ->where('month', $this->selectedMonthValue)   // YYYY-MM
        ->where('is_active', true)
        ->with(['user:id,nombre,apellido_paterno,apellido_materno,foto_perfil'])
        ->first();

    if (!$record) {
        $this->empleadoMes = null;
        return;
    }

    $u = $record->user;

    // Ã¢Å“â€¦ Normalizar foto_perfil a "path" (ej: fotos_perfil/archivo.jpg)
    $foto = $u->foto_perfil ?? null;

    // Si por alguna razÃƒÂ³n viene como URL completa (http://.../storage/...), lo convertimos a path
    if ($foto && str_contains($foto, '/storage/')) {
        $foto = ltrim(explode('/storage/', $foto, 2)[1], '/');
    }

    $this->empleadoMes = [
        'id'          => $u->id,
        'nombre'      => trim(($u->nombre ?? '') . ' ' . ($u->apellido_paterno ?? '')),
        'mensaje'     => $record->mensaje,
        'month'       => $record->month,
        'foto_perfil' => $foto, // Ã¢Å“â€¦ MISMO NOMBRE que en users/sidebar
    ];
}


public function quitarEmpleadoDelMes(): void
{
    if (! $this->esAdminCeo) return; // o abort_unless($this->esAdminCeo, 403);

    EmpleadoDelMes::query()
        ->where('month', $this->selectedMonthValue)
        ->where('is_active', true)
        ->update([
            'is_active' => false,
        ]);

    $this->empleadoMes = null;

    $this->dispatch('notify',
        type: 'success',
        message: 'Empleado del mes retirado correctamente.'
    );
}




public function openEmpleadoModal(): void
{
    if (! $this->esAdminCeo) return;

    $this->showEmpleadoModal = true;

    if ($this->empleadoMes) {
        $this->empleadoMesUserId = (string) $this->empleadoMes['id'];
        $this->empleadoMesMensaje = $this->empleadoMes['mensaje'];
    } else {
        $this->empleadoMesUserId = null;
        $this->empleadoMesMensaje = null;
    }
}

    public function closeEmpleadoModal(): void
    {
        $this->showEmpleadoModal = false;
    }


    public function saveEmpleadoDelMes(): void
{
    if (! $this->esAdminCeo) return;

    $this->validate([
        'empleadoMesUserId' => 'required|exists:users,id',
        'empleadoMesMensaje' => 'nullable|string|max:400',
        // cuando agreguemos "hasta quÃƒÂ© fecha", aquÃƒÂ­ va su regla
    ]);

    EmpleadoDelMes::updateOrCreate(
        ['month' => $this->selectedMonthValue],
        [
            'user_id' => $this->empleadoMesUserId,
            'mensaje' => $this->empleadoMesMensaje,
            'is_active' => true,
        ]
    );

    $this->showEmpleadoModal = false;
    $this->cargarEmpleadoDelMes();
    $this->dispatch('notify', type:'success', message:'Empleado del mes guardado.');
}

private function calcularCambio($actual, $anterior)
{
    if ($anterior == 0) {
        if ($actual == 0) {
            return '0%';
        }
        return '+100%';
    }

    $porcentaje = (($actual - $anterior) / $anterior) * 100;

    $signo = $porcentaje > 0 ? '+' : '';
    return $signo . round($porcentaje) . '%';
}


    





    private function loadData(): void
    {
        // ===== 1. MES SELECCIONADO =====
        try {
            $selectedDate = Carbon::createFromFormat('Y-m', $this->selectedMonthValue)->startOfMonth();
        } catch (\Exception $e) {
            $selectedDate = Carbon::now()->startOfMonth();
            $this->selectedMonthValue = $selectedDate->format('Y-m');
        }

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth   = $selectedDate->copy()->endOfMonth();

        $this->currentMonthName = $selectedDate->locale('es')->translatedFormat('F Y');
        $this->monthFinished    = $endOfMonth->lt(Carbon::now()->endOfDay());

        // ===== ROLES / FILTRO =====
        $user = auth()->user();

        $selectedColaboradorId = $this->selectedColaboradorId;

        $aplicarFiltro = function ($query) use ($user, $selectedColaboradorId) {
            if ($this->isTecnico && $user) {
                $query->where('registrado_por_user_id', $user->id);
            } elseif (!$this->isTecnico && !empty($selectedColaboradorId)) {
                $query->where('registrado_por_user_id', $selectedColaboradorId);
            }
            return $query;
        };

        // ===== 2. KPIs =====
        $today = Carbon::today();

        $equiposHoy = $aplicarFiltro(
            Equipo::whereDate('created_at', $today)
        )->count();
        $hoy = $selectedDate->copy()->day(Carbon::now()->day);
        $ayer = $hoy->copy()->subDay();
        $equiposHoy = $aplicarFiltro(
            Equipo::whereDate('created_at', $hoy)
        )->count();
        $equiposAyer = $aplicarFiltro(
            Equipo::whereDate('created_at', $ayer)
        )->count();

        $hoyChange = $this->calcularCambio($equiposHoy, $equiposAyer);




        $semanaInicio = $today->copy()->startOfWeek();
        $semanaFin    = $today->copy()->endOfWeek();

        $equiposSemana = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$semanaInicio, $semanaFin])
        )->count();
        $inicioSemana = $selectedDate->copy()->startOfWeek();
        $finSemana = $selectedDate->copy()->endOfWeek();

        $inicioSemanaAnterior = $selectedDate->copy()->subWeek()->startOfWeek();
        $finSemanaAnterior = $selectedDate->copy()->subWeek()->endOfWeek();
        $equiposSemana = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$inicioSemana, $finSemana])
        )->count();
        $equiposSemanaAnterior = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$inicioSemanaAnterior, $finSemanaAnterior])
        )->count();

        $semanaChange = $this->calcularCambio($equiposSemana, $equiposSemanaAnterior);






        $equiposMes = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        )->count();
        $inicioMes = $selectedDate->copy()->startOfMonth();
        $finMes = $selectedDate->copy()->endOfMonth();

        $inicioMesAnterior = $selectedDate->copy()->subMonth()->startOfMonth();
        $finMesAnterior = $selectedDate->copy()->subMonth()->endOfMonth();
        $equiposMes = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$inicioMes, $finMes])
        )->count();
        $equiposMesAnterior = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
        )->count();

        $mesChange = $this->calcularCambio($equiposMes, $equiposMesAnterior);


        $this->kpis = [
            'equiposHoy' => $equiposHoy,
            'equiposSemana' => $equiposSemana,
            'equiposMes' => $equiposMes,

            'hoy_change' => $hoyChange,
            'semana_change' => $semanaChange,
            'mes_change' => $mesChange,
        ];


        // ===== 3. GRÃƒÂFICA LÃƒÂNEA =====
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

        $this->lineChart = [
            'labels' => $lineDataLabels,
            'data'   => $lineDataCounts,
        ];

        // ===== 4. GRÃƒÂFICA BARRAS =====
        $labels           = [];
        $serieActualAno   = [];
        $serieAnoAnterior = [];

        for ($i = 3; $i >= 0; $i--) {
            $monthDate = $selectedDate->copy()->subMonths($i);

            $labels[] = ucfirst($monthDate->locale('es')->translatedFormat('M'));

            $currentYearStart = $monthDate->copy()->startOfMonth();
            $currentYearEnd   = $monthDate->copy()->endOfMonth();

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

        $this->tecnicoChart = [
            'labels' => $labels,
            'series' => [
                'actual'   => $serieActualAno,
                'anterior' => $serieAnoAnterior,
            ],
        ];

        // ===== 5. COLABORADORES =====
        $tecnicosBaseQuery = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn(DB::raw('LOWER(roles.slug)'), ['tecnico', 'tÃƒÂ©cnico']);

        $colaboradoresCount = (clone $tecnicosBaseQuery)->count();

        $this->colaboradores = [];
        if (!$this->isTecnico) {
            $this->colaboradores = (clone $tecnicosBaseQuery)
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

        // ===== 6. META MENSUAL (CONGELADA) =====

$anio = $selectedDate->year;
$mes  = $selectedDate->month;

// Buscar si ya existe meta congelada
$metaRecord = PreparacionMetaMensual::where('anio', $anio)
    ->where('mes', $mes)
    ->first();

// Si NO existe y es el mes actual Ã¢â€ â€™ la creamos
if (!$metaRecord && $anio == now()->year && $mes == now()->month) {

    $tecnicosIniciales = $colaboradoresCount;

    $metaPorColaborador = 140; // regla actual
    $metaTotalCalculada = max($tecnicosIniciales, 1) * $metaPorColaborador;

    $metaRecord = PreparacionMetaMensual::create([
        'anio' => $anio,
        'mes' => $mes,
        'tecnicos_iniciales' => $tecnicosIniciales,
        'meta_total' => $metaTotalCalculada,
    ]);
}

// Si es mes pasado y no existe, NO lo creamos automÃƒÂ¡ticamente
// (opcionalmente despuÃƒÂ©s podemos hacer backfill)
        $metaPorColaborador = 140;
        $isPersonalView = $this->isTecnico || !empty($selectedColaboradorId);

        if ($isPersonalView) {
            // Vista personal: meta individual.
            $metaTotal = $metaPorColaborador;
        } else {
            // Vista global: meta congelada del mes.
            $metaTotal = $metaRecord->meta_total ?? 0;
        }

$equiposRealizadosMes = $equiposMes;
$equiposFaltantes     = max($metaTotal - $equiposRealizadosMes, 0);

$percentMeta = $metaTotal > 0
    ? min(round(($equiposRealizadosMes / $metaTotal) * 100), 100)
    : 0;

$this->radialPercent = (int) $percentMeta;
if ($metaRecord && $metaRecord->hubo_movimientos) {
    $this->breakdown[] = [
        'label' => 'Ã¢Å¡Â  Hubo movimientos de personal este mes',
        'value' => '',
    ];
}


$this->breakdown = [
    ['label' => 'Meta mensual total',       'value' => $metaTotal],
    ['label' => 'Equipos realizados (mes)', 'value' => $equiposRealizadosMes],
    ['label' => 'Faltantes para la meta',   'value' => $equiposFaltantes],
    ['label' => 'TÃƒÂ©cnicos iniciales',       'value' => $isPersonalView ? 1 : ($metaRecord->tecnicos_iniciales ?? 0)],
];


        // Ã¢Å“â€¦ Disparar evento para actualizar ApexCharts sin recargar
        $this->dispatch('dashboard-data-updated',
            lineChart: $this->lineChart,
            tecnicoChart: $this->tecnicoChart,
            radialPercent: $this->radialPercent,
            isTecnico: $this->isTecnico,
        );
       
        
        $this->cargarEmpleadoDelMes();
        $this->esAdminCeo = ! $this->isTecnico;



    }
    

    public function render()
    {
        return view('livewire.dashboard.dash-board');
    }

   

}




