<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Equipo;
use App\Models\User;
use App\Models\PreparacionMetaMensual;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Aviso;
use App\Models\EmpleadoDelMes;
use App\Models\MetaTecnico;
use App\Models\LiderModoTecnico;

#[Layout('layouts.app', ['pageTitle' => 'Dashboard'])]
class Dashboard extends Component
{

//fix

    private const DEFAULT_EMPLEADO_MES_MENSAJE = 'Tu esfuerzo, dedicación y compromiso hicieron la diferencia este mes. ¡Gracias por dar siempre lo mejor de ti!';

    public bool $esAdminCeo = false;
    public bool $esLiderGerente = false;

    // Modal edición de meta
    public bool  $showModalMeta       = false;
    public int   $editMetaTotal       = 0;
    public array $editMetasTecnicos   = [];

    // Modal edición de líderes como técnicos
    public bool  $showModalLideres    = false;

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

    public string $labelDia = '';
    public string $labelSemana = '';
    public string $labelMes = '';



    


    // Glows persistentes 
    public array $glows = [];

    public ?string $selectedDate = null; // formato Y-m-d


    //Empleado del mes
    public ?array $empleadoMes = null;
    public bool $showEmpleadoModal = false;
    public bool $viejoSistema = false;
    public ?string $empleadoMesUserId = null;
    public ?string $empleadoMesMensaje = null;
    public bool $puedeConfigurarEmpleadoMes = false;



    




    private function cargarAvisos(): void
    {
        $this->avisos = Aviso::query()
            ->activos()
            ->ordenDashboard()
            ->limit(Aviso::DASHBOARD_LIMIT)
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
        $this->esAdminCeo     = in_array($roleSlug, ['ceo', 'admin', 'admin_sistema'], true);
        $this->esLiderGerente = in_array($roleSlug, ['ceo', 'gerente', 'lider'], true);
        
        // Empleado del mes: Solo CEO o GERENTE de PREPARACION
        $isCeo = in_array($roleSlug, ['ceo', 'admin', 'admin_sistema'], true);
        $isGerentePreparacion = $roleSlug === 'gerente' && optional($user->departamento)->id === 1;
        $this->puedeConfigurarEmpleadoMes = $isCeo || $isGerentePreparacion;
        

        $this->isTecnico = in_array($roleSlug, ['tecnico'])
            || in_array($roleName, ['tecnico']);

        $this->selectedMonthValue = now()->format('Y-m');

        // Si es tecnico, el filtro de colaborador debe quedar vacio (porque siempre se filtra a el)
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
        // Si es tecnico, ignora cambios (por seguridad)
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

    #[On('lideresActualizados')]
    public function actualizarPorLideres(): void
    {
        $this->loadData();
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

    // Normalizar foto_perfil a "path" (ej: fotos_perfil/archivo.jpg)
    $foto = $u->foto_perfil ?? null;

    // Si por alguna razon viene como URL completa (http://.../storage/...), lo convertimos a path
    if ($foto && str_contains($foto, '/storage/')) {
        $foto = ltrim(explode('/storage/', $foto, 2)[1], '/');
    }

    $this->empleadoMes = [
        'id'          => $u->id,
        'nombre'      => trim(($u->nombre ?? '') . ' ' . ($u->apellido_paterno ?? '')),
        'mensaje'     => trim((string) ($record->mensaje ?? '')) !== '' ? $record->mensaje : self::DEFAULT_EMPLEADO_MES_MENSAJE,
        'month'       => $record->month,
        'foto_perfil' => $foto, // MISMO NOMBRE que en users/sidebar
    ];
}


public function quitarEmpleadoDelMes(): void
{
    abort_unless($this->puedeConfigurarEmpleadoMes, 403);

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
    abort_unless($this->puedeConfigurarEmpleadoMes, 403);

    $this->showEmpleadoModal = true;

    if ($this->empleadoMes) {
        $this->empleadoMesUserId = (string) $this->empleadoMes['id'];
        $this->empleadoMesMensaje = $this->empleadoMes['mensaje'];
    } else {
        $this->empleadoMesUserId = null;
        $this->empleadoMesMensaje = self::DEFAULT_EMPLEADO_MES_MENSAJE;
    }
}

    public function closeEmpleadoModal(): void
    {
        $this->showEmpleadoModal = false;
    }


    public function saveEmpleadoDelMes(): void
{
    abort_unless($this->puedeConfigurarEmpleadoMes, 403);

    $this->validate([
        'empleadoMesUserId' => 'required|exists:users,id',
        'empleadoMesMensaje' => 'nullable|string|max:400',
        // cuando agreguemos "hasta que fecha", aqui va su regla
    ]);

    $mensaje = trim((string) ($this->empleadoMesMensaje ?? ''));
    if ($mensaje === '') {
        $mensaje = self::DEFAULT_EMPLEADO_MES_MENSAJE;
    }
    $this->empleadoMesMensaje = $mensaje;

    EmpleadoDelMes::updateOrCreate(
        ['month' => $this->selectedMonthValue],
        [
            'user_id' => $this->empleadoMesUserId,
            'mensaje' => $mensaje,
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

        // Si el usuario es del rol "calidad" mostramos un dashboard visualmente vacío
        // (mismas vistas/estructuras, pero sin métricas ni gráficos pesados).
        $roleSlug = strtolower(optional(auth()->user()->role)->slug ?? '');
        if ($roleSlug === 'calidad') {
            $this->kpis = [
                'equiposHoy' => 0,
                'equiposSemana' => 0,
                'equiposMes' => 0,
                'hoy_change' => 0,
                'semana_change' => 0,
                'mes_change' => 0,
            ];

            $this->lineChart = ['labels' => [], 'data' => []];
            $this->tecnicoChart = ['labels' => [], 'series' => ['actual' => [], 'anterior' => []]];
            $this->radialPercent = 0;
            $this->breakdown = [];
            $this->avisos = [];
            $this->colaboradores = [];

            return;
        }

        // ===== ROLES / FILTRO =====
        $user = auth()->user();

        $selectedColaboradorId = $this->selectedColaboradorId;

        $isPersonalView    = $this->isTecnico || !empty($selectedColaboradorId);
        $tecnicoIdPersonal = $isPersonalView
            ? ($this->isTecnico ? ($user?->id) : (int)$selectedColaboradorId)
            : null;

        // Para vista personal: contar equipos COMPLETADOS por el técnico en un rango
        $completadosTecnico = function (Carbon $start, Carbon $end) use ($tecnicoIdPersonal): int {
            if (!$tecnicoIdPersonal) return 0;
            return (int) DB::table('asignacion_equipos')
                ->join('asignaciones', 'asignacion_equipos.asignacion_id', '=', 'asignaciones.id')
                ->where('asignaciones.tecnico_id', $tecnicoIdPersonal)
                ->whereIn('asignacion_equipos.camino', ['COMPLETADO', 'EN_CALIDAD'])
                ->whereBetween('asignacion_equipos.fin_en', [$start, $end])
                ->count();
        };

        // Para vista global: filtrar equipos registrados por usuario (gerente sin técnico seleccionado)
        $aplicarFiltro = function ($query) use ($user, $selectedColaboradorId) {
            if ($this->isTecnico && $user) {
                $query->where('registrado_por_user_id', $user->id);
            } elseif (!$this->isTecnico && !empty($selectedColaboradorId)) {
                $query->where('registrado_por_user_id', $selectedColaboradorId);
            }
            return $query;
        };

        // ===== 2. KPIs =====
        $hoy  = $selectedDate->copy()->day(Carbon::now()->day);
        $ayer = $hoy->copy()->subDay();

        if ($isPersonalView) {
            $equiposHoy  = $completadosTecnico($hoy->copy()->startOfDay(), $hoy->copy()->endOfDay());
            $equiposAyer = $completadosTecnico($ayer->copy()->startOfDay(), $ayer->copy()->endOfDay());
        } else {
            $equiposHoy  = $aplicarFiltro(Equipo::whereDate('created_at', $hoy))->count();
            $equiposAyer = $aplicarFiltro(Equipo::whereDate('created_at', $ayer))->count();
        }

        $hoyChange = $this->calcularCambio($equiposHoy, $equiposAyer);

        \Log::info('DEBUG SEMANA', [
    'weekStart' => $weekStart ?? null,
    'weekEnd'   => $weekEnd ?? null,
    'selectedMonth' => $this->selectedMonthValue,
]);



        // SEMANA

        $today = Carbon::now();
        $day   = $today->day;

        // Construir fecha base en el mes seleccionado
        $referenceDate = $selectedDate->copy()->day(
            min($day, $selectedDate->copy()->endOfMonth()->day)
        );

        // Calcular inicio y fin de semana
        $weekStart = $referenceDate->copy()->startOfWeek();
        $weekEnd   = $referenceDate->copy()->endOfWeek();

        // Limitar para que no se salga del mes seleccionado
        if ($weekStart->lt($startOfMonth)) {
            $weekStart = $startOfMonth->copy();
        }

        if ($weekEnd->gt($endOfMonth)) {
            $weekEnd = $endOfMonth->copy();
        }

        $prevWeekStart = $weekStart->copy()->subWeek();
        $prevWeekEnd   = $weekEnd->copy()->subWeek();

        if ($isPersonalView) {
            $equiposSemana         = $completadosTecnico($weekStart, $weekEnd);
            $equiposSemanaAnterior = $completadosTecnico($prevWeekStart, $prevWeekEnd);
        } else {
            $equiposSemana         = $aplicarFiltro(Equipo::whereBetween('created_at', [$weekStart, $weekEnd]))->count();
            $equiposSemanaAnterior = $aplicarFiltro(Equipo::whereBetween('created_at', [$prevWeekStart, $prevWeekEnd]))->count();
        }

        $semanaChange = $this->calcularCambio($equiposSemana, $equiposSemanaAnterior);

        // ===== LABELS DINÁMICOS =====

        $this->labelDia = $referenceDate->locale('es')->translatedFormat('d M Y');

        $this->labelSemana =
            $weekStart->locale('es')->translatedFormat('d M')
            . ' - ' .
            $weekEnd->locale('es')->translatedFormat('d M');

$this->labelMes = $selectedDate->locale('es')->translatedFormat('F Y');






        $inicioMes         = $selectedDate->copy()->startOfMonth();
        $finMes            = $selectedDate->copy()->endOfMonth();
        $inicioMesAnterior = $selectedDate->copy()->subMonth()->startOfMonth();
        $finMesAnterior    = $selectedDate->copy()->subMonth()->endOfMonth();

        if ($isPersonalView) {
            $equiposMes         = $completadosTecnico($inicioMes, $finMes);
            $equiposMesAnterior = $completadosTecnico($inicioMesAnterior, $finMesAnterior);
        } else {
            $equiposMes         = $aplicarFiltro(Equipo::whereBetween('created_at', [$inicioMes, $finMes]))->count();
            $equiposMesAnterior = $aplicarFiltro(Equipo::whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior]))->count();
        }

        $mesChange = $this->calcularCambio($equiposMes, $equiposMesAnterior);


        $this->kpis = [
            'equiposHoy' => $equiposHoy,
            'equiposSemana' => $equiposSemana,
            'equiposMes' => $equiposMes,

            'hoy_change' => $hoyChange,
            'semana_change' => $semanaChange,
            'mes_change' => $mesChange,
        ];

        \Log::info('DEBUG SEMANA COUNT', [
    'equiposSemana' => $equiposSemana,
]);


        // ===== 3. GRAFICA LINEA =====
        $lineDataLabels = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
        $lineDataCounts = [0, 0, 0, 0, 0];

        if ($isPersonalView) {
            // Vista personal: completaciones por semana del mes
            $semanas = [[1,7],[8,14],[15,21],[22,28],[29,$endOfMonth->day]];
            foreach ($semanas as $i => [$dIni, $dFin]) {
                $s = $startOfMonth->copy()->day($dIni)->startOfDay();
                $e = $startOfMonth->copy()->day(min($dFin, $endOfMonth->day))->endOfDay();
                $lineDataCounts[$i] = $completadosTecnico($s, $e);
            }
        } else {
            $equiposDelMes = $aplicarFiltro(
                Equipo::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            )->get(['created_at']);

            foreach ($equiposDelMes as $equipo) {
                $diaDelMes = $equipo->created_at->day;
                if ($diaDelMes <= 7)      $lineDataCounts[0]++;
                elseif ($diaDelMes <= 14) $lineDataCounts[1]++;
                elseif ($diaDelMes <= 21) $lineDataCounts[2]++;
                elseif ($diaDelMes <= 28) $lineDataCounts[3]++;
                else                      $lineDataCounts[4]++;
            }
        }

        $this->lineChart = [
            'labels' => $lineDataLabels,
            'data'   => $lineDataCounts,
        ];

        // ===== 4. GRAFICA BARRAS =====
        $labels           = [];
        $serieActualAno   = [];
        $serieAnoAnterior = [];

        for ($i = 3; $i >= 0; $i--) {
            $monthDate = $selectedDate->copy()->subMonths($i);

            $labels[] = ucfirst($monthDate->locale('es')->translatedFormat('M'));

            $currentYearStart = $monthDate->copy()->startOfMonth();
            $currentYearEnd   = $monthDate->copy()->endOfMonth();
            $prevYearStart    = $monthDate->copy()->subYear()->startOfMonth();
            $prevYearEnd      = $monthDate->copy()->subYear()->endOfMonth();
            $periodoBar       = $monthDate->format('Y-m');
            $esViejoBar       = ($periodoBar < '2026-04');

            if ($isPersonalView) {
                if ($esViejoBar) {
                    // Meses viejos: equipos completados
                    $serieActualAno[]   = $completadosTecnico($currentYearStart, $currentYearEnd);
                    $serieAnoAnterior[] = $completadosTecnico($prevYearStart, $prevYearEnd);
                } else {
                    // Meses nuevos (abril+): puntos acumulados
                    $serieActualAno[]   = (float) DB::table('puntos_tecnicos')
                        ->where('tecnico_id', $tecnicoIdPersonal)
                        ->where('periodo', $periodoBar)
                        ->sum(DB::raw('puntos_final + ajuste_manual'));
                    // Año anterior en puntos (probablemente 0, no existía el sistema)
                    $prevPeriodoBar = $monthDate->copy()->subYear()->format('Y-m');
                    $serieAnoAnterior[] = (float) DB::table('puntos_tecnicos')
                        ->where('tecnico_id', $tecnicoIdPersonal)
                        ->where('periodo', $prevPeriodoBar)
                        ->sum(DB::raw('puntos_final + ajuste_manual'));
                }
            } else {
                $serieActualAno[]   = $aplicarFiltro(Equipo::whereBetween('created_at', [$currentYearStart, $currentYearEnd]))->count();
                $serieAnoAnterior[] = $aplicarFiltro(Equipo::whereBetween('created_at', [$prevYearStart, $prevYearEnd]))->count();
            }
        }

        $this->tecnicoChart = [
            'labels' => $labels,
            'series' => [
                'actual'   => $serieActualAno,
                'anterior' => $serieAnoAnterior,
            ],
        ];

        // ===== 5. COLABORADORES =====
        // Técnicos base (siempre cuentan)
        $tecnicosCount = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn(DB::raw('LOWER(roles.slug)'), ['tecnico'])
            ->count();

        // Líderes que trabajan como técnicos (global, sin período)
        $lideresComoTecnicoIds = LiderModoTecnico::lideresActivos();
        $lideresCount = count($lideresComoTecnicoIds);

        // Total colaboradores
        $colaboradoresCount = $tecnicosCount + $lideresCount;

        // Query de todos los colaboradores (técnicos + líderes activos)
        $colaboradoresBaseQuery = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where(function ($q) use ($lideresComoTecnicoIds) {
                $q->whereIn(DB::raw('LOWER(roles.slug)'), ['tecnico'])
                  ->orWhereIn('users.id', $lideresComoTecnicoIds);
            });

        $this->colaboradores = [];
        if (!$this->isTecnico) {
            $this->colaboradores = (clone $colaboradoresBaseQuery)
                ->distinct('users.id')
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

// Si NO existe y es el mes actual, la creamos
if (!$metaRecord && $anio == now()->year && $mes == now()->month) {

    $metaPorColaborador = 140; // regla actual
    $metaTotalCalculada = max($colaboradoresCount, 1) * $metaPorColaborador;

    $metaRecord = PreparacionMetaMensual::create([
        'anio' => $anio,
        'mes' => $mes,
        'tecnicos_iniciales' => $tecnicosCount,
        'lideres_iniciales' => $lideresCount,
        'colaboradores_iniciales' => $colaboradoresCount,
        'meta_total' => $metaTotalCalculada,
    ]);
}

// Si es mes pasado y no existe, NO lo creamos automáticamente
        $periodoStr = $selectedDate->format('Y-m');

        if ($isPersonalView) {
            // Vista personal: meta individual del técnico (o default 140)
            $metaTotal = $tecnicoIdPersonal
                ? MetaTecnico::obtenerMeta($tecnicoIdPersonal, $periodoStr)
                : MetaTecnico::META_DEFAULT;
        } else {
            // Vista global: meta congelada del mes.
            // Si no existe registro para meses pasados, usar fallback: técnicos × META_DEFAULT
            $metaTotal = $metaRecord->meta_total
                ?? (max($colaboradoresCount, 1) * MetaTecnico::META_DEFAULT);
        }

// Corte: desde 2026-04 se usa el nuevo sistema de puntos
$periodo        = $selectedDate->format('Y-m');
$this->viejoSistema = ($periodo < '2026-04');

if ($this->viejoSistema) {
    // ── Sistema viejo: conteo de equipos completados (antes de abr-2026) ──
    // Para vista personal: usar equipos completados por el técnico (asignacion_equipos)
    // Para vista global: usar equipos registrados en el sistema
    $realizadosMes        = $isPersonalView
        ? $completadosTecnico($startOfMonth, $endOfMonth)
        : $equiposMes;
    $faltantesMes         = max($metaTotal - $realizadosMes, 0);
    $percentMeta          = $metaTotal > 0 ? min(round(($realizadosMes / $metaTotal) * 100), 100) : 0;
    $this->radialPercent  = (int) $percentMeta;
    $breakdown_meta_lbl   = 'Meta mensual (equipos)';
    $breakdown_real_lbl   = 'Equipos realizados (mes)';
} else {
    // ── Sistema nuevo: suma de puntos (desde abr-2026) ─────────────────
    $puntosQuery = \DB::table('puntos_tecnicos')->where('periodo', $periodo);
    if ($this->isTecnico && $user) {
        $puntosQuery->where('tecnico_id', $user->id);
    } elseif (!$this->isTecnico && !empty($selectedColaboradorId)) {
        $puntosQuery->where('tecnico_id', $selectedColaboradorId);
    }
    $realizadosMes        = round((float)(clone $puntosQuery)->sum(\DB::raw('puntos_final + ajuste_manual')), 2);
    $faltantesMes         = max($metaTotal - $realizadosMes, 0);
    $percentMeta          = $metaTotal > 0 ? min(round(($realizadosMes / $metaTotal) * 100), 100) : 0;
    $this->radialPercent  = (int) $percentMeta;
    $breakdown_meta_lbl   = 'Meta mensual (puntos)';
    $breakdown_real_lbl   = 'Puntos realizados (mes)';
}

if ($metaRecord && $metaRecord->hubo_movimientos) {
    $this->breakdown[] = [
        'label' => 'Hubo movimientos de personal este mes',
        'value' => '',
    ];
}

$this->breakdown = [
    ['label' => $breakdown_meta_lbl, 'value' => $metaTotal],
    ['label' => $breakdown_real_lbl, 'value' => $realizadosMes],
    ['label' => 'Faltantes para la meta', 'value' => $faltantesMes],
    ['label' => 'Tecnicos iniciales', 'value' => $isPersonalView ? 1 : ($metaRecord->tecnicos_iniciales ?? 0)],
];



        // Disparar evento para actualizar ApexCharts sin recargar
        $this->dispatch('dashboard-data-updated',
            lineChart: $this->lineChart,
            tecnicoChart: $this->tecnicoChart,
            radialPercent: $this->radialPercent,
            isTecnico: $this->isTecnico,
        );
       
        
        $this->cargarEmpleadoDelMes();



    }
    

    // ── Edición de meta mensual ────────────────────────────────────────────

    public function abrirModalMeta(): void
    {
        abort_unless($this->esLiderGerente, 403);

        $periodo = $this->selectedMonthValue; // Y-m
        [$anioStr, $mesStr] = explode('-', $periodo);
        $anio = (int) $anioStr;
        $mes = (int) $mesStr;

        // Bloquear edición de meses anteriores
        $hoy = Carbon::now();
        $fechaSeleccionada = Carbon::createFromDate($anio, $mes, 1);

        if ($fechaSeleccionada < $hoy->startOfMonth()) {
            $this->dispatch('toast', type: 'error', message: 'No se puede modificar metas de meses anteriores.');
            return;
        }

        $metaRecord = PreparacionMetaMensual::where('anio', $anio)->where('mes', $mes)->first();
        $this->editMetaTotal = $metaRecord ? (int)$metaRecord->meta_total : 0;

        // Cargar meta individual de cada técnico (o proporcional si es mes actual)
        $this->editMetasTecnicos = collect($this->colaboradores)
            ->map(function ($c) use ($periodo, $anio, $mes) {
                $meta = MetaTecnico::where('tecnico_id', $c['id'])
                    ->where('periodo', $periodo)
                    ->value('meta_puntos');
                return [
                    'tecnico_id'  => $c['id'],
                    'nombre'      => $c['nombre'],
                    'meta_puntos' => $meta !== null ? (float)$meta : (float)$this->calcularMetaParaMes($anio, $mes),
                ];
            })->toArray();

        $this->showModalMeta = true;
    }

    /**
     * Calcula meta proporcional si es mes actual (mitad de mes)
     * Si es mes pasado o futuro, devuelve META_DEFAULT completo
     */
    private function calcularMetaParaMes(int $anio, int $mes): float
    {
        $hoy = Carbon::now();

        // Si NO es el mes actual, devolver meta completa
        if ($hoy->year != $anio || $hoy->month != $mes) {
            return (float) MetaTecnico::META_DEFAULT;
        }

        // Si es el mes actual, calcular proporcional
        $diasDelMes = Carbon::createFromDate($anio, $mes, 1)->daysInMonth;
        $diaActual = $hoy->day;
        $diasRestantes = $diasDelMes - $diaActual + 1; // +1 para incluir hoy

        $metaProporcional = round((float) MetaTecnico::META_DEFAULT * ($diasRestantes / $diasDelMes), 2);

        return $metaProporcional;
    }

    public function recalcularMetaTotal(): void
    {
        abort_unless($this->esLiderGerente, 403);
        $this->editMetaTotal = (int) array_sum(array_column($this->editMetasTecnicos, 'meta_puntos'));
    }

    public function guardarMeta(): void
    {
        abort_unless($this->esLiderGerente, 403);

        $this->validate([
            'editMetaTotal'                    => ['required', 'numeric', 'min:1'],
            'editMetasTecnicos.*.meta_puntos'  => ['required', 'numeric', 'min:0'],
        ]);

        $periodo = $this->selectedMonthValue;
        [$anio, $mes] = explode('-', $periodo);

        DB::transaction(function () use ($anio, $mes, $periodo) {
            PreparacionMetaMensual::updateOrCreate(
                ['anio' => (int)$anio, 'mes' => (int)$mes],
                ['meta_total' => $this->editMetaTotal]
            );

            foreach ($this->editMetasTecnicos as $item) {
                MetaTecnico::updateOrCreate(
                    ['tecnico_id' => $item['tecnico_id'], 'periodo' => $periodo],
                    [
                        'meta_puntos'     => $item['meta_puntos'],
                        'asignada_por_id' => auth()->id(),
                    ]
                );
            }
        });

        $this->showModalMeta = false;
        $this->dispatch('toast', type: 'success', message: 'Meta mensual actualizada.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.dash-board');
    }

   

}
