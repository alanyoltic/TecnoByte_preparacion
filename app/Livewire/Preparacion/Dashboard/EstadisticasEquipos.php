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
        'espera'       => 0,
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
        $query = DB::table('equipos as e')
            ->join('catalogo_equipos as c', 'e.catalogo_equipo_id', '=', 'c.id')
            ->select(
                'c.marca',
                'c.modelo',
                'c.tipo_equipo',
                DB::raw('COUNT(e.id) as total_equipos'),
                // Recién registrados / Sin empezar
                DB::raw('SUM(CASE WHEN e.estatus_area IN ("' . Equipo::AREA_SIN_ASIGNAR . '", "' . Equipo::AREA_EN_ESPERA . '") THEN 1 ELSE 0 END) as c_espera'),
                // Ya asignados a un técnico
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_ASIGNADO . '" THEN 1 ELSE 0 END) as c_asignado'),
                // En proceso de preparación
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_EN_PROCESO . '" THEN 1 ELSE 0 END) as c_proceso'),
                // Falta pieza
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_PENDIENTE_PIEZA . '" THEN 1 ELSE 0 END) as c_pieza'),
                // Garantías (Interna, Externa, Pendiente)
                DB::raw('SUM(CASE WHEN e.estatus_area IN ("' . Equipo::AREA_PENDIENTE_GARANTIA . '", "' . Equipo::AREA_GARANTIA_INT . '", "' . Equipo::AREA_GARANTIA_EXT . '") THEN 1 ELSE 0 END) as c_garantia'),
                // Desarme
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_PENDIENTE_DESARME . '" THEN 1 ELSE 0 END) as c_desarme'),
                // En calidad
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_EN_CALIDAD . '" THEN 1 ELSE 0 END) as c_calidad'),
                // Finalizados OK
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_FINALIZADO . '" THEN 1 ELSE 0 END) as c_finalizado'),
                // Transferidos (Salieron a ventas/sucursal)
                DB::raw('SUM(CASE WHEN e.estatus_area = "' . Equipo::AREA_TRANSFERIDO . '" THEN 1 ELSE 0 END) as c_transferido')
            )
            ->whereNull('e.deleted_at');

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

        $query->groupBy('c.marca', 'c.modelo', 'c.tipo_equipo');

        // Ordenación de la DB (afecta el orden principal antes de agrupar en PHP)
        if ($this->orden === 'total_desc') {
            $query->orderByDesc('total_equipos');
        } else {
            $query->orderBy('c.marca')->orderBy('c.modelo');
        }

        $data = $query->get();

        // Estructuramos la data agrupada por Marca para la vista
        $agrupado = [];
        $totales = [
            'general'      => 0,
            'espera'       => 0,
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
            if (!isset($agrupado[$row->marca])) {
                $agrupado[$row->marca] = [
                    'total_marca' => 0,
                    'modelos' => []
                ];
            }

            $agrupado[$row->marca]['modelos'][] = $row;
            $agrupado[$row->marca]['total_marca'] += $row->total_equipos;

            // Sumar a totales generales
            $totales['general']      += $row->total_equipos;
            $totales['espera']       += $row->c_espera;
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
                return $b['total_marca'] <=> $a['total_marca'];
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
