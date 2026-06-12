<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Exports\EquiposExport;
use App\Exports\ReportePreparacionPlaneacionExport;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Roles;
use App\Models\User;
use App\Services\EquipoTraceService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app', ['pageTitle' => 'Gestión de inventario'])]
class GestionInventario extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?int $tecnico_id = null;

    public array $tecnicos = [];

    // Filtros básicos
    public ?string $search = null;

    public string $filtroEstado = 'todos';

    public string $filtroLote = 'todos';

    public string $filtroProveedor = 'todos';

    // Filtros avanzados
    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    public string $filtroTipoEquipo = 'todos';

    public string $filtroArea = 'todos';

    public string $filtroGpu = 'todos';   // todos | dedicada | sin_dedicada

    public string $filtroBateria = 'todos';   // todos | baja | media | alta

    public string $filtroSO = 'todos';

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

    public array $estatusAreaOpciones = [];

    public function descargarExcel()
    {
        $this->autorizarGestion();

        $equipos = Equipo::with([
            'loteModelo.lote.proveedor',
            'registradoPor',
            'gpus',
            'monitor',
            'baterias',
            'clasificacionPuntos',
        ])->get();

        return Excel::download(new EquiposExport($equipos), 'equipos.xlsx');
    }

    public function mount(): void
    {
        $this->autorizarGestion();

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

        $this->estatusAreaOpciones = $this->cargarEstatusAreaOpciones();
        $rolTecnicoId = Roles::where('slug', 'tecnico')->value('id');

        $this->tecnicos = User::withoutGlobalScopes()
            ->select('id', 'nombre')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->where('role_id', $rolTecnicoId)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'nombre' => $u->nombre])
            ->toArray();

    }

    private function autorizarGestion(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.gestion'), 403);
    }

    private function cargarEstatusAreaOpciones(): array
    {
        $labels = Equipo::labelsArea();

        return [
            ['value' => Equipo::AREA_SIN_ASIGNAR,        'label' => $labels[Equipo::AREA_SIN_ASIGNAR] ?? Equipo::AREA_SIN_ASIGNAR],
            ['value' => Equipo::AREA_EN_ESPERA,          'label' => $labels[Equipo::AREA_EN_ESPERA] ?? Equipo::AREA_EN_ESPERA],
            ['value' => Equipo::AREA_ASIGNADO,           'label' => $labels[Equipo::AREA_ASIGNADO] ?? Equipo::AREA_ASIGNADO],
            ['value' => Equipo::AREA_EN_PROCESO,         'label' => $labels[Equipo::AREA_EN_PROCESO] ?? Equipo::AREA_EN_PROCESO],
            ['value' => Equipo::AREA_EN_CALIDAD,         'label' => $labels[Equipo::AREA_EN_CALIDAD] ?? Equipo::AREA_EN_CALIDAD],
            ['value' => Equipo::AREA_FINALIZADO,         'label' => $labels[Equipo::AREA_FINALIZADO] ?? Equipo::AREA_FINALIZADO],
            ['value' => Equipo::AREA_TRANSFERIDO,        'label' => $labels[Equipo::AREA_TRANSFERIDO] ?? Equipo::AREA_TRANSFERIDO],
            ['value' => Equipo::AREA_PENDIENTE_PIEZA,    'label' => $labels[Equipo::AREA_PENDIENTE_PIEZA] ?? Equipo::AREA_PENDIENTE_PIEZA],
            ['value' => Equipo::AREA_PENDIENTE_GARANTIA, 'label' => $labels[Equipo::AREA_PENDIENTE_GARANTIA] ?? Equipo::AREA_PENDIENTE_GARANTIA],
            ['value' => Equipo::AREA_PENDIENTE_DESARME,  'label' => $labels[Equipo::AREA_PENDIENTE_DESARME] ?? Equipo::AREA_PENDIENTE_DESARME],
            ['value' => Equipo::AREA_GARANTIA_INT,       'label' => $labels[Equipo::AREA_GARANTIA_INT] ?? Equipo::AREA_GARANTIA_INT],
            ['value' => Equipo::AREA_GARANTIA_EXT,       'label' => $labels[Equipo::AREA_GARANTIA_EXT] ?? Equipo::AREA_GARANTIA_EXT],
        ];
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

    public bool $modalCambiarEstatus = false;

    public string $nuevoEstatusSeleccionado = '';

    public function abrirEliminarSeleccion()
    {
        $this->autorizarGestion();

        if (count($this->selected) === 0) {
            return;
        }

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
        $this->autorizarGestion();

        if (empty(trim($this->motivo_eliminacion)) || strlen(trim($this->motivo_eliminacion)) < 8) {
            $this->addError('motivo_eliminacion', 'Debes proporcionar un motivo detallado (mínimo 8 caracteres).');

            return;
        }

        if (empty($this->selected)) {
            $this->cerrarEliminarSeleccion();

            return;
        }

        $traceService = app(EquipoTraceService::class);
        $equipos = Equipo::withTrashed()->whereIn('id', $this->selected)->get();
        $errores = [];
        $eliminados = 0;

        foreach ($equipos as $equipo) {
            $snapshot = null;

            if ($traceService->requiereSnapshotForense($equipo)) {
                $snapshot = $traceService->crearSnapshotEliminacion($equipo, $this->motivo_eliminacion);
            }

            try {
                DB::transaction(function () use ($equipo) {
                    $equipo->gpus()->delete();
                    $equipo->baterias()->delete();

                    if ($equipo->monitor) {
                        $equipo->monitor()->delete();
                    }

                    \App\Models\EquipoAuditoria::where('equipo_id', $equipo->id)->delete();
                    $equipo->forceDelete();
                });

                if ($snapshot) {
                    $traceService->marcarEliminacionConfirmada($snapshot);
                }

                $eliminados++;
            } catch (\Throwable $e) {
                if ($snapshot) {
                    $traceService->marcarEliminacionFallida($snapshot, $e);
                }

                $errores[] = $equipo->numero_serie ?: ('ID '.$equipo->id);
            }
        }

        $this->selected = [];
        $this->motivo_eliminacion = '';
        $this->modalEliminarSeleccion = false;

        if (! empty($errores)) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: "Se eliminaron {$eliminados} equipo(s). Fallaron: ".implode(', ', $errores)
            );

            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Equipo/s eliminado/s correctamente.');
    }

    public function cerrarEliminarSeleccion()
    {
        $this->modalEliminarSeleccion = false;
    }

    public function abrirModalCambiarEstatus(): void
    {
        $this->autorizarGestion();

        if (empty($this->selected) || $this->nuevoEstatusSeleccionado === '') {
            return;
        }

        $this->modalCambiarEstatus = true;
    }

    public function confirmarCambiarEstatus(): void
    {
        $this->autorizarGestion();

        $this->actualizarEstatusSeleccionado($this->nuevoEstatusSeleccionado);
        $this->nuevoEstatusSeleccionado = '';
        $this->modalCambiarEstatus = false;
    }

    public function cerrarModalCambiarEstatus(): void
    {
        $this->modalCambiarEstatus = false;
        $this->nuevoEstatusSeleccionado = '';
    }

    public function resetSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    public function resetFiltros(): void
    {
        $this->search = null;
        $this->filtroEstado = 'todos';
        $this->filtroLote = 'todos';
        $this->filtroProveedor = 'todos';
        $this->tecnico_id = null;
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->filtroTipoEquipo = 'todos';
        $this->filtroArea = 'todos';
        $this->filtroGpu = 'todos';
        $this->filtroBateria = 'todos';
        $this->filtroSO = 'todos';

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
            ->with([
                'loteModelo.lote.proveedor',
                'registradoPor' => fn ($q) => $q->withoutGlobalScopes(),
            ])
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($q) use ($s) {
                    $q->where('numero_serie', 'like', "%{$s}%")
                        ->orWhere('marca', 'like', "%{$s}%")
                        ->orWhere('modelo', 'like', "%{$s}%")
                        ->orWhere('tipo_equipo', 'like', "%{$s}%")
                        ->orWhere('id', is_numeric($s) ? (int) $s : -1);
                });
            })
            ->when($this->filtroEstado !== 'todos', function ($q) {
                $q->where('estatus_area', $this->filtroEstado);
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
        $this->autorizarGestion();

        if (empty($this->selected) || $nuevoEstatus === '') {
            return;
        }

        $permitidos = array_column($this->estatusAreaOpciones ?: $this->cargarEstatusAreaOpciones(), 'value');

        if (! in_array($nuevoEstatus, $permitidos, true)) {
            $this->dispatch('toast', type: 'error', message: 'Estatus no válido.');

            return;
        }

        Equipo::whereIn('id', $this->selected)->update([
            'estatus_area' => $nuevoEstatus,
            'estatus_ciclo' => Equipo::cicloParaArea($nuevoEstatus),
        ]);

        $this->resetSelection();

        $this->dispatch('toast', type: 'success', message: 'Se actualizó el estatus de los equipos seleccionados.');
    }

    public function exportarReportePlaneacion()
    {
        $this->autorizarGestion();

        return Excel::download(
            new ReportePreparacionPlaneacionExport,
            'reporte_planeacion_preparacion.xlsx'
        );
    }

    /**
     * Cambiar área/tienda de los equipos seleccionados
     */
    public function actualizarAreaSeleccionada(?string $nuevaArea): void
    {
        $this->autorizarGestion();

        if (empty($this->selected) || $nuevaArea === null || $nuevaArea === '') {
            return;
        }

        Equipo::whereIn('id', $this->selected)->update([
            'area_tienda' => $nuevaArea,
        ]);

        $this->resetSelection();

        $this->dispatch('toast', type: 'success', message: 'Se actualizó el área/tienda de los equipos seleccionados.');
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
        $this->autorizarGestion();

        $query = $this->equiposQuery();

        if (! empty($this->selected)) {
            $query->whereIn('id', $this->selected);
        }

        $equipos = $query
            ->with(['loteModelo.lote.proveedor', 'registradoPor', 'clasificacionPuntos'])
            ->get();

        $fileName = 'inventario_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new EquiposExport($equipos), $fileName);
    }

    public function render()
    {
        $equipos = $this->equiposQuery()->paginate($this->perPage);

        $equiposConTecnico = Equipo::query()
            ->whereHas('asignacionEquipos.asignacion', function ($q) {
                $q->whereNotNull('tecnico_id');
            });

        // Stats para las tarjetas (son counts, ligeros)
        $stats = [
            'total' => Equipo::count(),
            'sin_asignar' => Equipo::query()
                ->whereDoesntHave('asignacionEquipos.asignacion', function ($q) {
                    $q->whereNotNull('tecnico_id');
                })
                ->count(),
            'por_hacer' => (clone $equiposConTecnico)
                ->whereNotIn('estatus_area', [
                    Equipo::AREA_EN_CALIDAD,
                    Equipo::AREA_FINALIZADO,
                    Equipo::AREA_TRANSFERIDO,
                ])
                ->count(),
            'en_calidad' => Equipo::where('estatus_area', Equipo::AREA_EN_CALIDAD)->count(),
            'finalizado' => Equipo::where('estatus_area', Equipo::AREA_FINALIZADO)->count(),
        ];

        return view('livewire.preparacion.inventario.gestion-inventario', [
            'equipos' => $equipos,
            'stats' => $stats,
            // estas ya vienen de mount(), no se vuelven a consultar:
            'lotes' => $this->lotes,
            'proveedores' => $this->proveedores,
            'tiposEquipo' => $this->tiposEquipo,
            'areas' => $this->areas,
            'sistemasOperativos' => $this->sistemasOperativos,
        ]);
    }
}
