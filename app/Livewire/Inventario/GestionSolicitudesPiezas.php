<?php

namespace App\Livewire\Inventario;

use App\Models\CatalogoPieza;
use App\Models\InventarioPieza;
use App\Models\SolicitudPieza;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SolicitudesPiezasExport;

class GestionSolicitudesPiezas extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'TODOS';
    public string $filtroTecnico = 'TODOS';
    public string $busqueda = '';

    public bool $modalSurtir = false;
    public ?SolicitudPieza $solicitudSeleccionada = null;
    public $piezasDisponibles = [];
    public ?int $piezaSeleccionada = null;
    public string $notasRespuesta = '';
    public ?float $puntosOverride = null;
    public ?int $tecnicoReasignadoId = null;
    public array $tecnicos = [];

    // Selección masiva
    public array $selected = [];
    public bool $selectPage = false;

    public bool $modalCompra = false;

    public bool $modalCancelar = false;
    public string $motivoCancelacion = '';

    protected $queryString = [
        'filtroEstatus' => ['except' => 'TODOS'],
        'filtroTecnico' => ['except' => 'TODOS'],
        'busqueda' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->autorizarGestion();
        $this->tecnicos = $this->cargarTecnicos();
    }

    public function render()
    {
        $this->autorizarGestion();

        $solicitudes = $this->solicitudesQuery()->paginate(15);

        $contadores = [
            'pendientes'        => SolicitudPieza::where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'surtidas'          => SolicitudPieza::where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'pendientes_compra' => SolicitudPieza::where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'compradas'         => SolicitudPieza::where('estatus', SolicitudPieza::COMPRADA)->count(),
            'en_calidad'        => SolicitudPieza::where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', true)->count(),
            'terminados'        => SolicitudPieza::where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', true)->count(),
            'fallo_pieza'       => SolicitudPieza::where('estatus', SolicitudPieza::REQUIERE_REASIGNACION)->count(),
            'paso_calidad'      => 0, // futuro — cuando exista el área de calidad
            'canceladas'        => SolicitudPieza::where('estatus', SolicitudPieza::CANCELADA)->count(),
        ];

        return view('livewire.inventario.gestion-solicitudes-piezas', [
            'solicitudes' => $solicitudes,
            'contadores' => $contadores,
        ])->layout('layouts.app');
    }

    public function abrirModalSurtir(int $solicitudId): void
    {
        $this->solicitudSeleccionada = SolicitudPieza::with([
            'catalogoPieza',
            'asignacionEquipo.equipo',
            'equipo',
            'solicitadoPor',
        ])->findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeSerSurtidaDesdeInventario()) {
            $this->dispatch('toast', type: 'error', message: 'Esta solicitud ya no puede surtirse desde inventario.');
            return;
        }

        $cantidadRequerida = max(1, (int) $this->solicitudSeleccionada->cantidad);
        $categoriaInferida = null;

        if ($this->solicitudSeleccionada->catalogo_pieza_id) {
            // Solicitud vinculada a catálogo: mostrar solo ese tipo de pieza
            $this->piezasDisponibles = InventarioPieza::where('catalogo_pieza_id', $this->solicitudSeleccionada->catalogo_pieza_id)
                ->where('cantidad_disponible', '>=', $cantidadRequerida)
                ->with(['almacen', 'catalogoPieza', 'compraItem.compra.proveedor', 'equipoOrigen'])
                ->get();
        } else {
            // Solicitud libre: si la descripción tiene formato "Categoría — detalle",
            // filtrar el inventario por esa categoría para no mostrar piezas de otro tipo.
            $categoriaInferida = $this->solicitudSeleccionada->categoria_solicitada_texto !== 'Otro'
                ? $this->solicitudSeleccionada->categoria_solicitada_texto
                : null;
            if ($categoriaInferida && !CatalogoPieza::where('categoria', $categoriaInferida)->exists()) {
                $categoriaInferida = null;
            }

            $this->piezasDisponibles = InventarioPieza::where('cantidad_disponible', '>=', $cantidadRequerida)
                ->when($categoriaInferida, fn ($q) =>
                    $q->whereHas('catalogoPieza', fn ($q2) =>
                        $q2->where('categoria', $categoriaInferida)
                    )
                )
                ->with(['almacen', 'catalogoPieza', 'compraItem.compra.proveedor', 'equipoOrigen'])
                ->orderBy('catalogo_pieza_id')
                ->get();
        }

        if ($this->piezasDisponibles->isNotEmpty()) {
            $this->piezaSeleccionada = (int) $this->piezasDisponibles->first()->id;
        }

        $this->tecnicos = $this->cargarTecnicos();

        $this->tecnicoReasignadoId = $this->solicitudSeleccionada->reasignado_a_id
            ?: $this->solicitudSeleccionada->solicitado_por_id;

        $this->notasRespuesta = '';
        $this->modalSurtir = true;
    }

    public function surtirDeInventario(): void
    {
        $this->validate([
            'piezaSeleccionada'   => 'required|exists:inventario_piezas,id',
            'puntosOverride'      => 'required|numeric|min:0.01',
            'tecnicoReasignadoId' => 'required|exists:users,id',
            'notasRespuesta'      => 'nullable|string|max:500',
        ], [
            'puntosOverride.required' => 'Debes definir los puntos que recibirá el técnico por instalar la pieza.',
            'puntosOverride.min'      => 'Los puntos deben ser mayor a 0.',
            'tecnicoReasignadoId.required' => 'Debes seleccionar a quien se reasigna la instalación.',
        ]);

        try {
            $this->solicitudSeleccionada?->surtirDeInventario(
                $this->piezaSeleccionada,
                auth()->id(),
                $this->notasRespuesta ?: null,
                $this->tecnicoReasignadoId,
                $this->puntosOverride > 0 ? (float) $this->puntosOverride : null,
            );

            $this->dispatch('toast', type: 'success', message: 'Pieza asignada correctamente. El tecnico ya puede instalarla.');
            $this->cerrarModales();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error al surtir: ' . $e->getMessage());
        }
    }

    public function abrirModalCompra(int $solicitudId): void
    {
        $this->solicitudSeleccionada = SolicitudPieza::findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeSerGestionada()) {
            $this->dispatch('toast', type: 'error', message: 'Esta solicitud ya fue procesada.');
            return;
        }

        $this->notasRespuesta = '';
        $this->modalCompra = true;
    }

    public function marcarPendienteCompra(): void
    {
        $this->validate([
            'notasRespuesta' => 'nullable|string|max:500',
        ]);

        try {
            $this->solicitudSeleccionada?->marcarPendienteCompra(
                auth()->id(),
                $this->notasRespuesta ?: null
            );

            $this->dispatch('toast', type: 'success', message: 'Solicitud marcada como pendiente de compra.');
            $this->cerrarModales();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function abrirModalCancelar(int $solicitudId): void
    {
        $this->solicitudSeleccionada = SolicitudPieza::findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeCancelarse()) {
            $this->dispatch('toast', type: 'error', message: 'Esta solicitud ya no puede cancelarse.');
            return;
        }

        $this->motivoCancelacion = '';
        $this->modalCancelar = true;
    }

    public function cancelarSolicitud(): void
    {
        $this->validate([
            'motivoCancelacion' => 'required|string|max:500',
        ]);

        try {
            $this->solicitudSeleccionada?->cancelar(
                auth()->id(),
                $this->motivoCancelacion
            );

            $this->dispatch('toast', type: 'success', message: 'Solicitud cancelada correctamente.');
            $this->cerrarModales();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function cambiarFiltro(string $estatus): void
    {
        $this->filtroEstatus = $estatus;
        $this->resetPage();
    }

    public function updatedFiltroEstatus(): void
    {
        $this->resetPage();
            $this->resetSelection();
        }

        public function updatedFiltroTecnico(): void
        {
            $this->resetPage();
            $this->resetSelection();
        }

        public function updatingBusqueda(): void
        {
            $this->resetPage();
            $this->resetSelection();
        }

    public function cerrarModales(): void
    {
        $this->modalSurtir = false;
        $this->modalCompra = false;
        $this->modalCancelar = false;
        $this->reset([
            'solicitudSeleccionada',
            'piezaSeleccionada',
            'notasRespuesta',
            'puntosOverride',
            'motivoCancelacion',
            'piezasDisponibles',
            'tecnicoReasignadoId',
            'tecnicos',
        ]);
    }

    protected function solicitudesQuery()
    {
            $query = SolicitudPieza::query()
                ->with([
                    'asignacionEquipo.equipo',
                    'equipo',
                    'catalogoPieza',
                    'solicitadoPor',
                    'reasignadoA',
                    'inventarioPieza.almacen',
                    'respondidaPor',
                    'intentos.asignadoA',
                    'intentos.inventarioPieza.almacen',
                    'intentos.asignadoPor',
                ])
                ->when($this->filtroTecnico !== 'TODOS', function ($query) {
                    $query->where('solicitado_por_id', (int) $this->filtroTecnico);
                })
                ->when($this->filtroEstatus !== 'TODOS', function ($query) {
                    match ($this->filtroEstatus) {
                        'EN_CALIDAD'     => $query->where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', true),
                        'FALLO_PIEZA'    => $query->where('estatus', SolicitudPieza::REQUIERE_REASIGNACION),
                        'PASO_CALIDAD'   => $query->whereRaw('0 = 1'), // futuro
                        'TERMINADOS'     => $query->where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', true),
                        default          => $query->where('estatus', $this->filtroEstatus),
                    };
                })
                ->when($this->busqueda, function ($query) {
                    $query->where(function ($q) {
                        $bId = is_numeric(trim($this->busqueda)) ? (int)trim($this->busqueda) : -1;
                        $q->whereHas('asignacionEquipo.equipo', function ($eq) use ($bId) {
                            $eq->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                                ->orWhere('modelo', 'like', '%' . $this->busqueda . '%')
                                ->orWhere('id', $bId);
                        })
                        ->orWhereHas('equipo', function ($eq) use ($bId) {
                            $eq->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                                ->orWhere('modelo', 'like', '%' . $this->busqueda . '%')
                                ->orWhere('id', $bId);
                        })
                        ->orWhereHas('solicitadoPor', function ($tec) {
                            $tec->where('nombre', 'like', '%' . $this->busqueda . '%')
                                ->orWhere('apellido_paterno', 'like', '%' . $this->busqueda . '%');
                        })
                        ->orWhereHas('catalogoPieza', function ($cat) {
                            $cat->where('nombre', 'like', '%' . $this->busqueda . '%');
                        })
                        ->orWhere('descripcion_libre', 'like', '%' . $this->busqueda . '%')
                        ->orWhere('categoria_solicitada', 'like', '%' . $this->busqueda . '%')
                        ->orWhere('detalle_solicitado', 'like', '%' . $this->busqueda . '%');
                    });
                })
                ->orderByRaw("FIELD(estatus, 'PENDIENTE', 'REQUIERE_REASIGNACION', 'PENDIENTE_COMPRA', 'COMPRADA', 'SURTIDA_INVENTARIO', 'CONFIRMADA', 'CANCELADA')")
                ->orderBy('created_at', 'desc');

            return $query;
        }

        public function updatedSelectPage($value): void
        {
            if ($value) {
                // Solo cargamos los IDs de la página actual, no todas las solicitudes
                $idsPagina = $this->solicitudesQuery()
                    ->clone()
                    ->paginate(15)
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->toArray();

                $this->selected = $idsPagina;
            } else {
                $this->selected = [];
            }
        }

        public function resetSelection(): void
        {
            $this->selected = [];
            $this->selectPage = false;
        }

        public function exportarSeleccion()
        {
            $this->autorizarGestion();

            $query = $this->solicitudesQuery();

            if (!empty($this->selected)) {
                $query->whereIn('id', $this->selected);
            }

            $solicitudes = $query->get();

            $fileName = 'solicitudes_piezas_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new SolicitudesPiezasExport($solicitudes), $fileName);
        }

        private function autorizarGestion(): void
        {
            abort_unless(auth()->user()?->tienePermiso('prep.inventario.gestion'), 403);
        }

        private function cargarTecnicos(): array
        {
            return User::where(function ($q) {
                $q->whereHas('role', fn ($r) => $r->where('slug', 'tecnico'))
                    ->where('is_active', true);
            })
                ->orWhere(function ($q) {
                    $q->whereHas('role', fn ($r) => $r->where('slug', 'lider'))
                        ->where('is_active', true)
                        ->whereHas('liderModoTecnico', fn ($lmt) => $lmt->where('es_tecnico', true));
                })
                ->orWhere(function ($q) {
                    $q->whereHas('role', fn ($r) => $r->where('slug', 'lider'))
                        ->where('is_active', true)
                        ->whereHas('solicitudesPiezas');
                })
                ->distinct()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'apellido_paterno'])
                ->toArray();
        }
}
