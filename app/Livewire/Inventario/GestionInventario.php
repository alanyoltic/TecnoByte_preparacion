<?php

namespace App\Livewire\Inventario;


use App\Exports\EquiposExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component; 
use App\Models\EquipoEliminacion;
use Livewire\WithPagination;

class GestionInventario extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';


    public ?int $tecnico_id = null;
    public array $tecnicos = [];

    // Filtros básicos
    public ?string $search = null;
    public string $filtroEstado = 'todos';
    public string $filtroLote   = 'todos';
    public string $filtroProveedor = 'todos';

    // Filtros avanzados
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $filtroTipoEquipo = 'todos';
    public string $filtroArea       = 'todos';
    public string $filtroGpu        = 'todos';   // todos | dedicada | sin_dedicada
    public string $filtroBateria    = 'todos';   // todos | baja | media | alta
    public string $filtroSO         = 'todos';

    // Paginación
    public int $perPage = 25;

    // Selección masiva
    public array $selected = [];
    public bool $selectPage = false;

    // Opciones precargadas (para no consultar en cada render)
    public $lotes = [];
    public $proveedores = [];
    public $tiposEquipo = [];
    public $areas = [];
    public $sistemasOperativos = [];


public function descargarExcel()
{
$equipos = Equipo::with([
    'loteModelo.lote.proveedor',
    'registradoPor',
    'gpus',
    'monitor',
    'baterias',
])->get();


    return Excel::download(new EquiposExport($equipos), 'equipos.xlsx');
}




    public function mount(): void
    {
        // Estas consultas se hacen SOLO una vez, al montar el componente

        // Lotes (usamos directamente la tabla lotes)
        $this->lotes = Lote::query()
            ->orderByDesc('fecha_llegada')
            ->get();

        // Proveedores
        $this->proveedores = Proveedor::query()
            ->orderBy('nombre_empresa')
            ->get();

        // Tipos de equipo
        $this->tiposEquipo = Equipo::query()
            ->select('tipo_equipo')
            ->whereNotNull('tipo_equipo')
            ->distinct()
            ->orderBy('tipo_equipo')
            ->pluck('tipo_equipo')
            ->toArray();

        // Áreas / tienda
        $this->areas = Equipo::query()
            ->select('area_tienda')
            ->whereNotNull('area_tienda')
            ->distinct()
            ->orderBy('area_tienda')
            ->pluck('area_tienda')
            ->toArray();

        // Sistemas operativos
        $this->sistemasOperativos = Equipo::query()
            ->select('sistema_operativo')
            ->whereNotNull('sistema_operativo')
            ->distinct()
            ->orderBy('sistema_operativo')
            ->pluck('sistema_operativo')
            ->toArray();
        $rolTecnicoId = Roles::where('slug', 'tecnico')->value('id');

        $this->tecnicos = User::query()
            ->select('id', 'nombre')
            ->whereNull('deleted_at')
            ->where('role_id', $rolTecnicoId)
            ->orderBy('nombre')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'nombre' => $u->nombre])
            ->toArray();







    }

    public function updating($name): void
    {
        if (in_array($name, [
            'search',
            'tecnico_id',
            'filtroEstado',
            'filtroLote',
            'filtroProveedor',
            'perPage',
            'fechaDesde',
            'fechaHasta',
            'filtroTipoEquipo',
            'filtroArea',
            'filtroGpu',
            'filtroBateria',
            'filtroSO',
        ])) {
            $this->resetPage();
            $this->resetSelection();
        }
    }


    public bool $modalEliminarSeleccion = false;
    public string $motivo_eliminacion = '';

public function abrirEliminarSeleccion()
{
    if (count($this->selected) === 0) return;

    $this->motivo_eliminacion = '';
    $this->modalEliminarSeleccion = true;
}

public ?Equipo $equipoSeleccionado = null;


public function verResumenEquipo($equipoId)
{
    $this->equipoSeleccionado = Equipo::with([
        'loteModelo.lote.proveedor',
        'registradoPor',
        'gpus',
        'monitor',
        'baterias',
    ])->findOrFail($equipoId);
}



public function confirmarEliminarSeleccion()
{
    // 1) Validación de motivo
    if (empty(trim($this->motivo_eliminacion)) || strlen(trim($this->motivo_eliminacion)) < 8) {
        $this->addError('motivo_eliminacion', 'Debes proporcionar un motivo detallado (mínimo 8 caracteres).');
        return;
    }

    if (empty($this->selected)) {
        $this->cerrarEliminarSeleccion();
        return;
    }

    try {
        DB::transaction(function () {
            // Traemos los equipos incluyendo soft-deleted por seguridad
            $equipos = Equipo::withTrashed()->whereIn('id', $this->selected)->get();

            foreach ($equipos as $equipo) {
                // 2) Auditoría (Snapshot)
                \App\Models\EquipoEliminacion::create([
                    'numero_serie' => $equipo->numero_serie, 
                    'equipo_id_original' => $equipo->id,
                    'codigo'       => $equipo->codigo,
                    'tipo_equipo'  => $equipo->tipo_equipo,
                    'marca'        => $equipo->marca,
                    'modelo'       => $equipo->modelo,
                    'user_id'      => auth()->id(),
                    'motivo'       => $this->motivo_eliminacion,
                    'snapshot'     => [
                        'equipo'   => $equipo->toArray(),
                        'gpus'     => $equipo->gpus->toArray(),
                        'baterias' => $equipo->baterias->toArray(),
                    ],
                    'ip'           => request()->ip(),
                    'user_agent'   => substr((string) request()->userAgent(), 0, 250),
                ]);

                // 3) Borrado de Relaciones
                // Si tus modelos EquipoGpu y EquipoBateria NO usan SoftDeletes, usa delete()
                // Si los borras así, se eliminan físicamente de la tabla.
                $equipo->gpus()->delete(); 
                $equipo->baterias()->delete();

                if ($equipo->monitor) {
                    $equipo->monitor()->delete();
                }

                // 4) Borrado DEFINITIVO del Equipo
                // Como Equipo SÍ tiene SoftDeletes, usamos forceDelete para que desaparezca
                $equipo->forceDelete();
            }
        });

        // 5) Limpieza de interfaz
        $this->selected = [];
        $this->motivo_eliminacion = '';
        $this->modalEliminarSeleccion = false;

        $this->dispatch('toast', type: 'success', message: 'Equipo/s eliminado/s correctamente.');

    } catch (\Exception $e) {
        $this->dispatch('toast', type: 'error', message: 'Error al eliminar: ' . $e->getMessage());
    }
}


public function cerrarEliminarSeleccion()
{
    $this->modalEliminarSeleccion = false;
}


    public function resetSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }




        public function resetFiltros(): void
    {
        $this->search          = null;
        $this->filtroEstado    = 'todos';
        $this->filtroLote      = 'todos';
        $this->filtroProveedor = 'todos';
        $this->tecnico_id      = null;
        $this->fechaDesde      = null;
        $this->fechaHasta      = null;
        $this->filtroTipoEquipo = 'todos';
        $this->filtroArea       = 'todos';
        $this->filtroGpu        = 'todos';
        $this->filtroBateria    = 'todos';
        $this->filtroSO         = 'todos';

        $this->perPage = 25;

        $this->resetSelection();
        $this->resetPage();
    }


    public function updatedSelectPage($value): void
    {
        if ($value) {
            // Solo cargamos los IDs de la página actual, no todos los equipos
            $idsPagina = $this->equiposQuery()
                ->clone()
                ->paginate($this->perPage)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            $this->selected = $idsPagina;
        } else {
            $this->selected = [];
        }
    }


    
    

    /**
     * Query base con TODOS los filtros (básicos + avanzados)
     */
    protected function equiposQuery()
    {
        $q = Equipo::query()
            ->with(['loteModelo.lote.proveedor', 'registradoPor'])
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($q) use ($s) {
                    $q->where('numero_serie', 'like', "%{$s}%")
                        ->orWhere('marca', 'like', "%{$s}%")
                        ->orWhere('modelo', 'like', "%{$s}%")
                        ->orWhere('tipo_equipo', 'like', "%{$s}%");
                        
                });
            })
            ->when($this->filtroEstado !== 'todos', function ($q) {
                $q->where('estatus_general', $this->filtroEstado);
            })
            ->when($this->filtroLote !== 'todos', function ($q) {
                $q->whereHas('loteModelo.lote', function ($q2) {
                    $q2->where('id', $this->filtroLote);
                });
            })
            ->when($this->filtroProveedor !== 'todos', function ($q) {
                $q->whereHas('loteModelo.lote.proveedor', function ($q2) {
                    $q2->where('id', $this->filtroProveedor);
                });
            })

            ->when($this->tecnico_id, function ($q) {
            $q->where('registrado_por_user_id', $this->tecnico_id);
            });

            

        // Filtros por fecha
        if ($this->fechaDesde) {
            $q->whereDate('created_at', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $q->whereDate('created_at', '<=', $this->fechaHasta);
        }

        // Tipo de equipo
        if ($this->filtroTipoEquipo !== 'todos') {
            $q->where('tipo_equipo', $this->filtroTipoEquipo);
        }

        // Área / tienda
        if ($this->filtroArea !== 'todos') {
            $q->where('area_tienda', $this->filtroArea);
        }

        // GPU
        if ($this->filtroGpu === 'dedicada') {
            $q->whereNotNull('grafica_dedicada_modelo');
        } elseif ($this->filtroGpu === 'sin_dedicada') {
            $q->whereNull('grafica_dedicada_modelo');
        }

        // Salud de batería
        if ($this->filtroBateria === 'baja') {
            $q->whereNotNull('bateria_salud_percent')
              ->where('bateria_salud_percent', '<', 70);
        } elseif ($this->filtroBateria === 'media') {
            $q->whereNotNull('bateria_salud_percent')
              ->whereBetween('bateria_salud_percent', [70, 89]);
        } elseif ($this->filtroBateria === 'alta') {
            $q->whereNotNull('bateria_salud_percent')
              ->where('bateria_salud_percent', '>=', 90);
        }

        // Sistema operativo
        if ($this->filtroSO !== 'todos') {
            $q->where('sistema_operativo', $this->filtroSO);
        }

        return $q->orderByDesc('created_at');
    }

    /**
     * Cambiar estatus masivo
     */
    public function actualizarEstatusSeleccionado(string $nuevoEstatus): void
    {
        if (empty($this->selected) || $nuevoEstatus === '') {
            return;
        }

        Equipo::whereIn('id', $this->selected)->update([
            'estatus_general' => $nuevoEstatus,
        ]);

        $this->resetSelection();

        session()->flash('success', 'Se actualizó el estatus de los equipos seleccionados.');
    }


        /**
     * Cambiar área/tienda de los equipos seleccionados
     */
    public function actualizarAreaSeleccionada(?string $nuevaArea): void
    {
        if (empty($this->selected) || $nuevaArea === null || $nuevaArea === '') {
            return;
        }

        Equipo::whereIn('id', $this->selected)->update([
            'area_tienda' => $nuevaArea,
        ]);

        $this->resetSelection();

        session()->flash('success', 'Se actualizó el área/tienda de los equipos seleccionados.');
    }

    /**
     * Eliminar selección
     */


    /**
     * Exportar a CSV (Excel lo abre sin problema) con prácticamente todos los campos.
     * - Si hay equipos seleccionados → solo esos.
     * - Si no hay selección → exporta todo lo filtrado.
     */
   public function exportarSeleccion()
{
    $query = $this->equiposQuery();

    if (!empty($this->selected)) {
        $query->whereIn('id', $this->selected);
    }

    $equipos = $query
        ->with(['loteModelo.lote.proveedor', 'registradoPor'])
        ->get();

    $fileName = 'inventario_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(new EquiposExport($equipos), $fileName);
}



    public function render()
    {
        $equipos = $this->equiposQuery()->paginate($this->perPage);

        // Stats para las tarjetas (son counts, ligeros)
        $stats = [
            'total'        => Equipo::count(),
            'en_revision'  => Equipo::where('estatus_general', 'En Revisión')->count(),
            'aprobados'    => Equipo::where('estatus_general', 'Aprobado')->count(),
            'finalizados'  => Equipo::where('estatus_general', 'Finalizado')->count(),
        ];

        return view('livewire.inventario.gestion-inventario', [
            'equipos'            => $equipos,
            'stats'              => $stats,
            // estas ya vienen de mount(), no se vuelven a consultar:
            'lotes'              => $this->lotes,
            'proveedores'        => $this->proveedores,
            'tiposEquipo'        => $this->tiposEquipo,
            'areas'              => $this->areas,
            'sistemasOperativos' => $this->sistemasOperativos,
        ]);
    }
}
