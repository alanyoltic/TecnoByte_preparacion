<?php

namespace App\Livewire\Preparacion\Dashboard;

use Livewire\Component;
use App\Models\CatalogoEquipo;
use App\Models\Equipo;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Estadísticas Detalladas de Equipos'])]
class EstadisticasEquipos extends Component
{
    public $search        = '';
    public $filtroMarca   = '';
    public $filtroTipo    = '';
    public $filtroModelo  = '';
    public $filtroEstatus = '';
    public $orden         = 'total_desc'; // total_desc, marca_asc

    public $estadisticas = [];
    public $listaMarcas  = [];
    public $listaTipos   = [];
    public $listaModelos = [];

    public $totales = [
        'general'     => 0, // Total Preparación: todo lo recibido en lote
        'disponibles' => 0, // Sin serie aún + con serie pero sin asignar (en bodega)
        'asignado'    => 0,
        'proceso'     => 0,
        'pieza'       => 0,
        'garantia'    => 0,
        'desarme'     => 0,
        'calidad'     => 0,
        'finalizado'  => 0,
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
        if (!empty($this->filtroMarca)) {
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
        $this->filtroMarca   = '';
        $this->filtroTipo    = '';
        $this->filtroModelo  = '';
        $this->search        = '';
        $this->actualizarListaModelos();
        $this->cargarEstadisticas();
    }

    public function cargarEstadisticas()
    {
        // Subconsulta: total de equipos recibidos por modelo según lotes (son reales, no promesas)
        $lotesSub = DB::table('lote_modelos_recibidos')
            ->select('catalogo_equipo_id', DB::raw('SUM(cantidad_recibida) as total_recibido'))
            ->groupBy('catalogo_equipo_id');

        // Subconsulta: conteo de estatus por equipo físico (con número de serie en la tabla equipos)
        $equiposSub = DB::table('equipos')
            ->whereNull('deleted_at')
            ->select(
                'catalogo_equipo_id',
                DB::raw('COUNT(id) as total_fisicos'),
                // Sin asignar: incluye SIN_ASIGNAR y EN_ESPERA (ambos son “disponibles con serie”)
                DB::raw('SUM(CASE WHEN estatus_area IN ("' . Equipo::AREA_SIN_ASIGNAR . '", "' . Equipo::AREA_EN_ESPERA . '") THEN 1 ELSE 0 END) as c_sin_asignar'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_ASIGNADO . '" THEN 1 ELSE 0 END) as c_asignado'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_EN_PROCESO . '" THEN 1 ELSE 0 END) as c_proceso'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_PENDIENTE_PIEZA . '" THEN 1 ELSE 0 END) as c_pieza'),
                DB::raw('SUM(CASE WHEN estatus_area IN ("' . Equipo::AREA_PENDIENTE_GARANTIA . '", "' . Equipo::AREA_GARANTIA_INT . '", "' . Equipo::AREA_GARANTIA_EXT . '") THEN 1 ELSE 0 END) as c_garantia'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_PENDIENTE_DESARME . '" THEN 1 ELSE 0 END) as c_desarme'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_EN_CALIDAD . '" THEN 1 ELSE 0 END) as c_calidad'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_FINALIZADO . '" THEN 1 ELSE 0 END) as c_finalizado'),
                DB::raw('SUM(CASE WHEN estatus_area = "' . Equipo::AREA_TRANSFERIDO . '" THEN 1 ELSE 0 END) as c_transferido')
            )
            ->groupBy('catalogo_equipo_id');

        // Query principal partiendo del catálogo (para que salgan modelos con lote aunque aún no tengan serie)
        $query = DB::table('catalogo_equipos as c')
            ->leftJoinSub($lotesSub, 'l', 'c.id', '=', 'l.catalogo_equipo_id')
            ->leftJoinSub($equiposSub, 'e', 'c.id', '=', 'e.catalogo_equipo_id')
            ->select(
                'c.marca',
                'c.modelo',
                'c.tipo_equipo',
                // total_recibido = equipos reales llegados según el lote (con o sin serie aún)
                DB::raw('COALESCE(l.total_recibido, 0) as total_recibido'),
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
            ->where(function($q) {
                $q->where('l.total_recibido', '>', 0)
                  ->orWhere('e.total_fisicos', '>', 0);
            });

        // Aplicación de Filtros
        if (!empty($this->filtroMarca)) {
            $query->where('c.marca', $this->filtroMarca);
        }

        if (!empty($this->filtroTipo)) {
            $query->where('c.tipo_equipo', $this->filtroTipo);
        }

        if (!empty($this->filtroModelo)) {
            $query->where('c.modelo', $this->filtroModelo);
        }

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('c.modelo', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('c.marca', 'LIKE', '%' . $this->search . '%');
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
            'general'     => 0, // Total Preparación: todo lo recibido en lote
            'disponibles' => 0, // Sin serie aún + con serie pero sin asignar (en bodega)
            'asignado'    => 0,
            'proceso'     => 0,
            'pieza'       => 0,
            'garantia'    => 0,
            'desarme'     => 0,
            'calidad'     => 0,
            'finalizado'  => 0,
            'transferido' => 0,
        ];

        foreach ($data as $row) {
            // Equipos en bodega sin serie escaneada aún
            $sin_serie   = max(0, $row->total_recibido - $row->total_equipos);
            $disponibles = $sin_serie + $row->c_sin_asignar;
            $row->disponibles = $disponibles;

            // ✔ Totales se acumulan SIEMPRE — las tarjetas muestran conteo global
            //   (independientemente del filtro de estatus activo)
            $totales['general']    += max($row->total_recibido, $row->total_equipos);
            $totales['disponibles']+= $disponibles;
            $totales['asignado']   += $row->c_asignado;
            $totales['proceso']    += $row->c_proceso;
            $totales['pieza']      += $row->c_pieza;
            $totales['garantia']   += $row->c_garantia;
            $totales['desarme']    += $row->c_desarme;
            $totales['calidad']    += $row->c_calidad;
            $totales['finalizado'] += $row->c_finalizado;
            $totales['transferido']+= $row->c_transferido;

            // ▼ Filtro de estatus: solo afecta qué filas aparecen en la tabla
            if ($this->filtroEstatus !== '') {
                $pasaFiltro = match($this->filtroEstatus) {
                    'disponibles' => $disponibles > 0,
                    'asignado'    => $row->c_asignado > 0,
                    'proceso'     => $row->c_proceso > 0,
                    'pieza'       => $row->c_pieza > 0,
                    'garantia'    => $row->c_garantia > 0,
                    'desarme'     => $row->c_desarme > 0,
                    'calidad'     => $row->c_calidad > 0,
                    'finalizado'  => $row->c_finalizado > 0,
                    'transferido' => $row->c_transferido > 0,
                    default       => true,
                };
                if (!$pasaFiltro) continue;
            }

            if (!isset($agrupado[$row->marca])) {
                $agrupado[$row->marca] = [
                    'total_marca_fisicos'     => 0,
                    'total_marca_disponibles' => 0,
                    'modelos'                 => []
                ];
            }

            $agrupado[$row->marca]['modelos'][]               = $row;
            $agrupado[$row->marca]['total_marca_fisicos']     += $row->total_equipos;
            $agrupado[$row->marca]['total_marca_disponibles'] += $disponibles;
        }

        // Si ordenamos por marca_asc, aseguramos que el array asociativo esté en orden alfabético de llaves
        if ($this->orden === 'marca_asc') {
            ksort($agrupado);
        } else {
            // Si es total_desc, ordenamos las marcas por la suma total de sus equipos
            uasort($agrupado, function($a, $b) {
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
