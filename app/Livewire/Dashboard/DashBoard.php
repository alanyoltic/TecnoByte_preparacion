<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Equipo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Aviso;
use App\Models\EmpleadoDelMes;

class Dashboard extends Component
{

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
                'icono'  => $a->icono ?? '📌',
            ])
            ->toArray();
}




    public function mount(): void
    {
        $user     = auth()->user();
        $roleSlug = strtolower(optional($user->role)->slug ?? '');
        $roleName = strtolower(optional($user->role)->nombre ?? '');
        $this->cargarAvisos();

        $this->isTecnico = in_array($roleSlug, ['tecnico', 'técnico'])
            || in_array($roleName, ['tecnico', 'técnico']);

        $this->selectedMonthValue = now()->format('Y-m');

        // Si es técnico, el filtro de colaborador debe quedar vacío (porque siempre se filtra a él)
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
        // Si es técnico, ignora cambios (por seguridad)
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

    // ✅ Normalizar foto_perfil a "path" (ej: fotos_perfil/archivo.jpg)
    $foto = $u->foto_perfil ?? null;

    // Si por alguna razón viene como URL completa (http://.../storage/...), lo convertimos a path
    if ($foto && str_contains($foto, '/storage/')) {
        $foto = ltrim(explode('/storage/', $foto, 2)[1], '/');
    }

    $this->empleadoMes = [
        'id'          => $u->id,
        'nombre'      => trim(($u->nombre ?? '') . ' ' . ($u->apellido_paterno ?? '')),
        'mensaje'     => $record->mensaje,
        'month'       => $record->month,
        'foto_perfil' => $foto, // ✅ MISMO NOMBRE que en users/sidebar
    ];
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
        // cuando agreguemos "hasta qué fecha", aquí va su regla
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

        $semanaInicio = $today->copy()->startOfWeek();
        $semanaFin    = $today->copy()->endOfWeek();

        $equiposSemana = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$semanaInicio, $semanaFin])
        )->count();

        $equiposMes = $aplicarFiltro(
            Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        )->count();

        $this->kpis = [
            'equiposHoy'    => $equiposHoy,
            'equiposSemana' => $equiposSemana,
            'equiposMes'    => $equiposMes,
            // (Tus cambios “+5%” eran dummy, los dejo igual por ahora)
            'hoy_change'    => '+5%',
            'semana_change' => '+12%',
            'mes_change'    => '+20%',
        ];

        // ===== 3. GRÁFICA LÍNEA =====
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

        // ===== 4. GRÁFICA BARRAS =====
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
            ->whereIn(DB::raw('LOWER(roles.slug)'), ['tecnico', 'técnico']);

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

        // ===== 6. META MENSUAL =====
        $metaPorColaborador = 120;

        if ($this->isTecnico) {
            $colaboradoresMetaCount = 1;
        } elseif (!$this->isTecnico && !empty($selectedColaboradorId)) {
            $colaboradoresMetaCount = 1;
        } else {
            $colaboradoresMetaCount = max($colaboradoresCount, 1);
        }

        $metaTotal = $colaboradoresMetaCount * $metaPorColaborador;

        $equiposRealizadosMes = $equiposMes;
        $equiposFaltantes     = max($metaTotal - $equiposRealizadosMes, 0);

        $percentMeta = $metaTotal > 0
            ? min(round(($equiposRealizadosMes / $metaTotal) * 100), 100)
            : 0;

        $this->radialPercent = (int) $percentMeta;

        $this->breakdown = [
            ['label' => 'Meta mensual total',       'value' => $metaTotal],
            ['label' => 'Equipos realizados (mes)', 'value' => $equiposRealizadosMes],
            ['label' => 'Faltantes para la meta',   'value' => $equiposFaltantes],
            ['label' => 'Colaboradores',            'value' => $colaboradoresMetaCount],
        ];

        // ✅ Disparar evento para actualizar ApexCharts sin recargar
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
