<?php

namespace App\Livewire\Preparacion\Dashboard;

use App\Models\CatalogoEquipo;
use App\Models\Equipo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Estadísticas Detalladas de Equipos'])]
class EstadisticasEquipos extends Component
{
    public $search = '';

    public $filtroMarca = '';

    public $filtroTipo = '';

    public $filtroModelo = '';

    public $filtroEstatus = '';

    public $orden = 'total_desc'; // total_desc, marca_asc

    public $vistaActiva = 'tabla';      // tabla, donut, barras, apiladas

    public $estadisticas = [];

    public $listaMarcas = [];

    public $listaTipos = [];

    public $listaModelos = [];

    public $totales = [
        'general' => 0, // Total Preparación: todo lo recibido en lote
        'disponibles' => 0, // Sin serie aún + con serie pero sin asignar (en bodega)
        'asignado' => 0,
        'proceso' => 0,
        'pieza' => 0,
        'garantia' => 0,
        'desarme' => 0,
        'calidad' => 0,
        'finalizado' => 0,
        'transferido' => 0,
    ];

    public function mount(): void
    {
        $this->listaMarcas = CatalogoEquipo::where('activo', true)
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca')
            ->toArray();

        $this->listaTipos = CatalogoEquipo::where('activo', true)
            ->whereNotNull('tipo_equipo')
            ->where('tipo_equipo', '!=', '')
            ->distinct()
            ->orderBy('tipo_equipo')
            ->pluck('tipo_equipo')
            ->toArray();

        $this->actualizarListaModelos();
        $this->cargarEstadisticas();
    }

    public function updated(string $propertyName): void
    {
        // Si cambia la marca, resetear modelo y recargar lista de modelos
        if ($propertyName === 'filtroMarca') {
            $this->filtroModelo = '';
            $this->actualizarListaModelos();
        }
        $this->cargarEstadisticas();
    }

    /** Lista de modelos filtrada por la marca activa (para el select de modelo) */
    public function actualizarListaModelos(): void
    {
        $q = CatalogoEquipo::where('activo', true)->distinct()->orderBy('modelo');
        if (! empty($this->filtroMarca)) {
            $q->where('marca', $this->filtroMarca);
        }
        $this->listaModelos = $q->pluck('modelo')->toArray();
    }

    /** Activa/desactiva el filtro de estatus al hacer clic en una tarjeta */
    public function filtrarPorEstatus(string $estatus): void
    {
        $this->filtroEstatus = ($this->filtroEstatus === $estatus) ? '' : $estatus;
        $this->cargarEstadisticas();
    }

    /** Limpia todos los filtros de una vez */
    public function limpiarFiltros(): void
    {
        $this->filtroEstatus = '';
        $this->filtroMarca = '';
        $this->filtroTipo = '';
        $this->filtroModelo = '';
        $this->search = '';
        $this->actualizarListaModelos();
        $this->cargarEstadisticas();
    }

    /** Cambia la vista activa (tabla, donut, barras, apiladas) */
    public function cambiarVista(string $vista): void
    {
        $this->vistaActiva = $vista;
    }

    /** Prepara los datos en formato JSON para las gráficas de ApexCharts */
    public function getDatosGraficasProperty(): array
    {
        $fe = $this->filtroEstatus;

        // Función auxiliar para aplicar el filtro de estatus a las gráficas:
        // Si hay un filtro activo (ej. 'calidad'), ponemos en 0 los demás estatus
        // para que las gráficas reflejen exactamente lo que el usuario seleccionó.
        $val = function ($estatus, $valor) use ($fe) {
            return ($fe === '' || $fe === $estatus) ? $valor : 0;
        };

        // ── Datos para Donut: distribución de estatus ──
        $d_disp = $d_asig = $d_proc = $d_piez = $d_gara = $d_desa = $d_cali = $d_apro = $d_tran = 0;
        foreach ($this->estadisticas as $datosMarca) {
            foreach ($datosMarca['modelos'] as $m) {
                $d_disp += $val('disponibles', $m->disponibles);
                $d_asig += $val('asignado', $m->c_asignado);
                $d_proc += $val('proceso', $m->c_proceso);
                $d_piez += $val('pieza', $m->c_pieza);
                $d_gara += $val('garantia', $m->c_garantia);
                $d_desa += $val('desarme', $m->c_desarme ?? 0);
                $d_cali += $val('calidad', $m->c_calidad);
                $d_apro += $val('finalizado', $m->c_finalizado);
                $d_tran += $val('transferido', $m->c_transferido);
            }
        }
        $donut = [
            'labels' => ['Disponibles', 'Asignados', 'En Proceso', 'Piezas', 'Garantía', 'Desarme', 'Calidad', 'Aprobados', 'Transferidos'],
            'series' => [$d_disp, $d_asig, $d_proc, $d_piez, $d_gara, $d_desa, $d_cali, $d_apro, $d_tran],
            'colors' => ['#64748b', '#3b82f6', '#FF9521', '#f59e0b', '#ef4444', '#f43f5e', '#a855f7', '#10b981', '#14b8a6'],
        ];

        // ── Datos para Barras: top modelos por cantidad ──
        $modelos = [];
        foreach ($this->estadisticas as $marca => $datosMarca) {
            foreach ($datosMarca['modelos'] as $m) {
                $modelos[] = $m;
            }
        }

        // El sort original era por total_recibido. Si hay filtro, ordenamos por la métrica relevante.
        usort($modelos, function ($a, $b) use ($fe, $val) {
            $totalA = $val('disponibles', $a->disponibles) + $val('asignado', $a->c_asignado) + $val('proceso', $a->c_proceso) + $val('pieza', $a->c_pieza) + $val('garantia', $a->c_garantia) + $val('desarme', $a->c_desarme ?? 0) + $val('calidad', $a->c_calidad) + $val('finalizado', $a->c_finalizado) + $val('transferido', $a->c_transferido);
            $totalB = $val('disponibles', $b->disponibles) + $val('asignado', $b->c_asignado) + $val('proceso', $b->c_proceso) + $val('pieza', $b->c_pieza) + $val('garantia', $b->c_garantia) + $val('desarme', $b->c_desarme ?? 0) + $val('calidad', $b->c_calidad) + $val('finalizado', $b->c_finalizado) + $val('transferido', $b->c_transferido);

            return ($fe === '') ? ($b->total_recibido <=> $a->total_recibido) : ($totalB <=> $totalA);
        });

        $topModelos = array_slice($modelos, 0, 15);

        $barras = [
            'categorias' => array_map(fn ($m) => mb_substr($m->modelo, 0, 20), $topModelos),
            'series' => [
                ['name' => 'Disponibles',  'data' => array_map(fn ($m) => $val('disponibles', $m->disponibles), $topModelos), 'color' => '#64748b'],
                ['name' => 'Asignados',    'data' => array_map(fn ($m) => $val('asignado', $m->c_asignado), $topModelos), 'color' => '#3b82f6'],
                ['name' => 'En Proceso',   'data' => array_map(fn ($m) => $val('proceso', $m->c_proceso), $topModelos), 'color' => '#FF9521'],
                ['name' => 'Piezas',       'data' => array_map(fn ($m) => $val('pieza', $m->c_pieza), $topModelos), 'color' => '#f59e0b'],
                ['name' => 'Garantía',     'data' => array_map(fn ($m) => $val('garantia', $m->c_garantia), $topModelos), 'color' => '#ef4444'],
                ['name' => 'Calidad',      'data' => array_map(fn ($m) => $val('calidad', $m->c_calidad), $topModelos), 'color' => '#a855f7'],
                ['name' => 'Aprobados',    'data' => array_map(fn ($m) => $val('finalizado', $m->c_finalizado), $topModelos), 'color' => '#10b981'],
            ],
        ];

        // ── Datos para Apiladas: por marca ──
        $marcasKeys = array_keys($this->estadisticas);
        $apiladasSeries = [
            ['name' => 'Disponibles',  'data' => [], 'color' => '#64748b'],
            ['name' => 'Asignados',    'data' => [], 'color' => '#3b82f6'],
            ['name' => 'En Proceso',   'data' => [], 'color' => '#FF9521'],
            ['name' => 'Piezas',       'data' => [], 'color' => '#f59e0b'],
            ['name' => 'Garantía',     'data' => [], 'color' => '#ef4444'],
            ['name' => 'Calidad',      'data' => [], 'color' => '#a855f7'],
            ['name' => 'Aprobados',    'data' => [], 'color' => '#10b981'],
            ['name' => 'Transferidos', 'data' => [], 'color' => '#14b8a6'],
        ];

        foreach ($marcasKeys as $marca) {
            $dm = $this->estadisticas[$marca];
            $disp = $asig = $proc = $piez = $gara = $cali = $apro = $tran = 0;
            foreach ($dm['modelos'] as $m) {
                $disp += $val('disponibles', $m->disponibles);
                $asig += $val('asignado', $m->c_asignado);
                $proc += $val('proceso', $m->c_proceso);
                $piez += $val('pieza', $m->c_pieza);
                $gara += $val('garantia', $m->c_garantia);
                $cali += $val('calidad', $m->c_calidad);
                $apro += $val('finalizado', $m->c_finalizado);
                $tran += $val('transferido', $m->c_transferido);
            }
            $apiladasSeries[0]['data'][] = $disp;
            $apiladasSeries[1]['data'][] = $asig;
            $apiladasSeries[2]['data'][] = $proc;
            $apiladasSeries[3]['data'][] = $piez;
            $apiladasSeries[4]['data'][] = $gara;
            $apiladasSeries[5]['data'][] = $cali;
            $apiladasSeries[6]['data'][] = $apro;
            $apiladasSeries[7]['data'][] = $tran;
        }

        $apiladas = [
            'categorias' => array_values($marcasKeys),
            'series' => $apiladasSeries,
        ];

        return compact('donut', 'barras', 'apiladas');
    }

    public function cargarEstadisticas()
    {
        // Subconsulta: total de equipos recibidos por modelo según lotes (son reales, no promesas)
        // También calcula cupos ya asignados a técnicos pero aún no iniciados/escaneados
        $lotesSub = DB::table('lote_modelos_recibidos as lmr')
            ->leftJoin('catalogo_equipos as cat', 'lmr.catalogo_equipo_id', '=', 'cat.id')
            ->select(
                'lmr.marca',
                'lmr.modelo',
                DB::raw('MAX(cat.tipo_equipo) as tipo_equipo'),
                DB::raw('SUM(lmr.cantidad_recibida) as total_recibido'),
                DB::raw("SUM(
                    COALESCE((
                        SELECT SUM(GREATEST(a.cantidad - (
                            SELECT COUNT(*) FROM asignacion_equipos ae WHERE ae.asignacion_id = a.id
                        ), 0))
                        FROM asignaciones a
                        WHERE a.lote_modelo_id = lmr.id
                        AND a.estatus IN ('".\App\Models\Asignacion::PENDIENTE."', '".\App\Models\Asignacion::EN_PROCESO."')
                        AND a.deleted_at IS NULL
                    ), 0)
                ) as cupos_asignados_sin_serie")
            )
            ->groupBy('lmr.marca', 'lmr.modelo');

        // Subconsulta: conteo de estatus por equipo físico.
        // IMPORTANTE: usamos lote_modelos_recibidos para resolver catalogo_equipo_id,
        // porque muchos equipos creados desde MiTrabajo solo tienen lote_modelo_id
        // y NO tienen catalogo_equipo_id directamente en la tabla equipos.
        $equiposSub = DB::table('equipos as eq')
            ->whereNull('eq.deleted_at')
            ->select(
                'eq.marca',
                'eq.modelo',
                DB::raw('MAX(eq.tipo_equipo) as tipo_equipo'),
                DB::raw('COUNT(eq.id) as total_fisicos'),
                // Sin asignar: SIN_ASIGNAR y EN_ESPERA (disponibles con serie, en bodega)
                DB::raw('SUM(CASE WHEN eq.estatus_area IN ("'.Equipo::AREA_SIN_ASIGNAR.'", "'.Equipo::AREA_EN_ESPERA.'") THEN 1 ELSE 0 END) as c_sin_asignar'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_ASIGNADO.'" THEN 1 ELSE 0 END) as c_asignado'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_EN_PROCESO.'" THEN 1 ELSE 0 END) as c_proceso'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_PENDIENTE_PIEZA.'" THEN 1 ELSE 0 END) as c_pieza'),
                DB::raw('SUM(CASE WHEN eq.estatus_area IN ("'.Equipo::AREA_PENDIENTE_GARANTIA.'", "'.Equipo::AREA_GARANTIA_INT.'", "'.Equipo::AREA_GARANTIA_EXT.'") THEN 1 ELSE 0 END) as c_garantia'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_PENDIENTE_DESARME.'" THEN 1 ELSE 0 END) as c_desarme'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_EN_CALIDAD.'" THEN 1 ELSE 0 END) as c_calidad'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_FINALIZADO.'" THEN 1 ELSE 0 END) as c_finalizado'),
                DB::raw('SUM(CASE WHEN eq.estatus_area = "'.Equipo::AREA_TRANSFERIDO.'" THEN 1 ELSE 0 END) as c_transferido')
            )
            ->groupBy('eq.marca', 'eq.modelo');

        // Query principal partiendo del catálogo (para que salgan modelos con lote aunque aún no tengan serie)
        $baseLotes = DB::table('lote_modelos_recibidos')->select('marca', 'modelo');
        $baseEquipos = DB::table('equipos')->whereNull('deleted_at')->select('marca', 'modelo');
        $baseModelos = $baseLotes->union($baseEquipos);

        $query = DB::table(DB::raw("({$baseModelos->toSql()}) as c"))
            ->mergeBindings($baseModelos)
            ->leftJoinSub($lotesSub, 'l', function($join) {
                $join->on('c.marca', '=', 'l.marca')
                     ->on('c.modelo', '=', 'l.modelo');
            })
            ->leftJoinSub($equiposSub, 'e', function($join) {
                $join->on('c.marca', '=', 'e.marca')
                     ->on('c.modelo', '=', 'e.modelo');
            })
            ->select(
                'c.marca',
                'c.modelo',
                DB::raw('COALESCE(e.tipo_equipo, l.tipo_equipo, "") as tipo_equipo'),
                // total_recibido = equipos reales llegados según el lote (con o sin serie aún)
                DB::raw('COALESCE(l.total_recibido, 0) as total_recibido'),
                DB::raw('COALESCE(l.cupos_asignados_sin_serie, 0) as cupos_asignados_sin_serie'),
                DB::raw('COALESCE(e.total_fisicos, 0) as total_equipos'),
                DB::raw('COALESCE(e.c_sin_asignar, 0) as c_sin_asignar'),
                DB::raw('COALESCE(e.c_asignado, 0) as c_asignado'),
                DB::raw('COALESCE(e.c_proceso, 0) as c_proceso'),
                DB::raw('COALESCE(e.c_pieza, 0) as c_pieza'),
                DB::raw('COALESCE(e.c_garantia, 0) as c_garantia'),
                DB::raw('COALESCE(e.c_desarme, 0) as c_desarme'),
                DB::raw('COALESCE(e.c_calidad, 0) as c_calidad'),
                DB::raw('COALESCE(e.c_finalizado, 0) as c_finalizado'),
                DB::raw('COALESCE(e.c_transferido, 0) as c_transferido')
            )
            // Solo modelos que tengan equipos recibidos en lote O equipos con serie en el sistema
            ->where(function ($q) {
                $q->where('l.total_recibido', '>', 0)
                    ->orWhere('e.total_fisicos', '>', 0);
            });

        // Aplicación de Filtros
        if (! empty($this->filtroMarca)) {
            $query->where('c.marca', $this->filtroMarca);
        }

        if (! empty($this->filtroTipo)) {
            $query->having('tipo_equipo', $this->filtroTipo);
        }

        if (! empty($this->filtroModelo)) {
            $query->where('c.modelo', $this->filtroModelo);
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('c.modelo', 'LIKE', '%'.$this->search.'%')
                    ->orWhere('c.marca', 'LIKE', '%'.$this->search.'%');
            });
        }

        // Ordenación
        if ($this->orden === 'total_desc') {
            $query->orderByDesc('total_recibido');
        } else {
            $query->orderBy('c.marca')->orderBy('c.modelo');
        }

        $data = $query->get();

        // Estructuramos la data
        $agrupado = [];
        $totales = [
            'general' => 0, // Total Preparación: todo lo recibido en lote
            'disponibles' => 0, // Sin serie aún + con serie pero sin asignar (en bodega)
            'asignado' => 0,
            'proceso' => 0,
            'pieza' => 0,
            'garantia' => 0,
            'desarme' => 0,
            'calidad' => 0,
            'finalizado' => 0,
            'transferido' => 0,
        ];

        foreach ($data as $row) {
            // Equipos en bodega sin serie escaneada aún
            $sin_serie = max(0, $row->total_recibido - $row->total_equipos);

            // Descontar los que el gerente ya asignó (promesas/cupos sin serie)
            $asignados_sin_serie = min($sin_serie, $row->cupos_asignados_sin_serie);
            $disponibles_sin_serie = max(0, $sin_serie - $asignados_sin_serie);

            // Disponibles reales para ser asignados de cero
            $disponibles = $disponibles_sin_serie + $row->c_sin_asignar;
            $row->disponibles = $disponibles;

            // Asignados totales (físicos pre-asignados + cupos en promesa)
            $row->c_asignado = $row->c_asignado + $asignados_sin_serie;

            // ✔ Totales se acumulan SIEMPRE — las tarjetas muestran conteo global
            //   (independientemente del filtro de estatus activo)
            $totales['general'] += max($row->total_recibido, $row->total_equipos);
            $totales['disponibles'] += $disponibles;
            $totales['asignado'] += $row->c_asignado;
            $totales['proceso'] += $row->c_proceso;
            $totales['pieza'] += $row->c_pieza;
            $totales['garantia'] += $row->c_garantia;
            $totales['desarme'] += $row->c_desarme;
            $totales['calidad'] += $row->c_calidad;
            $totales['finalizado'] += $row->c_finalizado;
            $totales['transferido'] += $row->c_transferido;

            // ▼ Filtro de estatus: solo afecta qué filas aparecen en la tabla
            if ($this->filtroEstatus !== '') {
                $pasaFiltro = match ($this->filtroEstatus) {
                    'disponibles' => $disponibles > 0,
                    'asignado' => $row->c_asignado > 0,
                    'proceso' => $row->c_proceso > 0,
                    'pieza' => $row->c_pieza > 0,
                    'garantia' => $row->c_garantia > 0,
                    'desarme' => $row->c_desarme > 0,
                    'calidad' => $row->c_calidad > 0,
                    'finalizado' => $row->c_finalizado > 0,
                    'transferido' => $row->c_transferido > 0,
                    default => true,
                };
                if (! $pasaFiltro) {
                    continue;
                }
            }

            if (! isset($agrupado[$row->marca])) {
                $agrupado[$row->marca] = [
                    'total_marca_fisicos' => 0,
                    'total_marca_disponibles' => 0,
                    'modelos' => [],
                ];
            }

            $agrupado[$row->marca]['modelos'][] = $row;
            $agrupado[$row->marca]['total_marca_fisicos'] += $row->total_equipos;
            $agrupado[$row->marca]['total_marca_disponibles'] += $disponibles;
        }

        // Si ordenamos por marca_asc, aseguramos que el array asociativo esté en orden alfabético de llaves
        if ($this->orden === 'marca_asc') {
            ksort($agrupado);
        } else {
            // Si es total_desc, ordenamos las marcas por la suma total de sus equipos
            uasort($agrupado, function ($a, $b) {
                return $b['total_marca_fisicos'] <=> $a['total_marca_fisicos'];
            });
        }

        $this->estadisticas = $agrupado;
        $this->totales = $totales;
    }

    public function render()
    {
        return view('livewire.preparacion.dashboard.estadisticas-equipos');
    }
}
