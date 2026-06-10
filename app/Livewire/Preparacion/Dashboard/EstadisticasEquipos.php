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
    public $search = '';
    public $filtroMarca = '';
    public $filtroTipo = '';
    public $orden = 'total_desc'; // total_desc, marca_asc

    public $estadisticas = [];
    public $listaMarcas = [];
    public $listaTipos = [];
    
    public $totales = [
        'general'      => 0, 
        'sin_registrar'=> 0, 
        'sin_asignar'  => 0, 
        'asignado'     => 0,
        'proceso'      => 0,
        'pieza'        => 0,
        'garantia'     => 0,
        'desarme'      => 0,
        'calidad'      => 0,
        'finalizado'   => 0,
        'transferido'  => 0,
    ];

    public function mount()
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
            
        $this->cargarEstadisticas();
    }

    public function updated($propertyName)
    {
        // Al actualizar cualquier filtro, recargamos
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
            'general'      => 0,
            'sin_registrar'=> 0,
            'sin_asignar'  => 0,
            'asignado'     => 0,
            'proceso'      => 0,
            'pieza'        => 0,
            'garantia'     => 0,
            'desarme'      => 0,
            'calidad'      => 0,
            'finalizado'   => 0,
            'transferido'  => 0,
        ];

        foreach ($data as $row) {
            // "Disponibles" = equipos recibidos en lote que aún no tienen número de serie en el sistema.
            // NO son "aire" ni "prometidos": son equipos físicos en bodega pendientes de escanear.
            // Si hay más físicos que lote (alta manual sin lote), lo topamos a 0.
            $disponibles = max(0, $row->total_recibido - $row->total_equipos);
            $row->sin_registrar = $disponibles;

            if (!isset($agrupado[$row->marca])) {
                $agrupado[$row->marca] = [
                    'total_marca_fisicos' => 0,
                    'total_marca_sin_reg' => 0,
                    'modelos' => []
                ];
            }

            $agrupado[$row->marca]['modelos'][] = $row;
            $agrupado[$row->marca]['total_marca_fisicos'] += $row->total_equipos;
            $agrupado[$row->marca]['total_marca_sin_reg'] += $disponibles;

            // Totales generales:
            // general = total recibido en lote (base real); si hay más físicos (alta manual), usamos el mayor
            $totales['general']      += max($row->total_recibido, $row->total_equipos);
            $totales['sin_registrar']+= $disponibles;
            $totales['sin_asignar']  += $row->c_sin_asignar;
            $totales['asignado']     += $row->c_asignado;
            $totales['proceso']      += $row->c_proceso;
            $totales['pieza']        += $row->c_pieza;
            $totales['garantia']     += $row->c_garantia;
            $totales['desarme']      += $row->c_desarme;
            $totales['calidad']      += $row->c_calidad;
            $totales['finalizado']   += $row->c_finalizado;
            $totales['transferido']  += $row->c_transferido;
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
