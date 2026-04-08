<?php

namespace App\Livewire\Inventario;

use App\Models\InventarioPieza;
use App\Models\SolicitudPieza;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GestionSolicitudesPiezas extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'PENDIENTE';
    public string $busqueda = '';

    public bool $modalSurtir = false;
    public ?SolicitudPieza $solicitudSeleccionada = null;
    public $piezasDisponibles = [];
    public ?int $piezaSeleccionada = null;
    public string $notasRespuesta = '';
    public ?int $tecnicoReasignadoId = null;
    public array $tecnicos = [];

    public bool $modalCompra = false;

    public bool $modalCancelar = false;
    public string $motivoCancelacion = '';

    protected $queryString = ['filtroEstatus', 'busqueda'];

    public function mount(): void
    {
        $this->autorizarGestion();
    }

    public function render()
    {
        $this->autorizarGestion();

        $solicitudes = SolicitudPieza::query()
            ->with([
                'asignacionEquipo.equipo',
                'equipo',
                'catalogoPieza',
                'solicitadoPor',
                'reasignadoA',
                'inventarioPieza.almacen',
                'respondidaPor',
            ])
            ->when($this->filtroEstatus !== 'TODAS', fn ($query) =>
                $query->where('estatus', $this->filtroEstatus)
            )
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
                    ->orWhere('descripcion_libre', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->orderByRaw("FIELD(estatus, 'PENDIENTE', 'PENDIENTE_COMPRA', 'COMPRADA', 'SURTIDA_INVENTARIO', 'CONFIRMADA', 'CANCELADA')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $contadores = [
            'pendientes' => SolicitudPieza::where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'surtidas' => SolicitudPieza::where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'pendientes_compra' => SolicitudPieza::where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'compradas' => SolicitudPieza::where('estatus', SolicitudPieza::COMPRADA)->count(),
            'confirmadas' => SolicitudPieza::where('estatus', SolicitudPieza::CONFIRMADA)->count(),
            'canceladas' => SolicitudPieza::where('estatus', SolicitudPieza::CANCELADA)->count(),
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
            session()->flash('error', 'Esta solicitud ya no puede surtirse desde inventario.');
            return;
        }

        if ($this->solicitudSeleccionada->catalogo_pieza_id) {
            // Solicitud vinculada a catálogo: mostrar solo ese tipo de pieza
            $this->piezasDisponibles = InventarioPieza::where('catalogo_pieza_id', $this->solicitudSeleccionada->catalogo_pieza_id)
                ->where('cantidad_disponible', '>', 0)
                ->with(['almacen', 'catalogoPieza', 'compraItem.compra.proveedor', 'equipoOrigen'])
                ->get();
        } else {
            // Solicitud libre (categoría + descripción): mostrar todo el inventario disponible
            $this->piezasDisponibles = InventarioPieza::where('cantidad_disponible', '>', 0)
                ->with(['almacen', 'catalogoPieza', 'compraItem.compra.proveedor', 'equipoOrigen'])
                ->orderBy('catalogo_pieza_id')
                ->get();
        }

        if ($this->piezasDisponibles->isNotEmpty()) {
            $this->piezaSeleccionada = (int) $this->piezasDisponibles->first()->id;
        }

        $this->tecnicos = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['tecnico', 'lider']))
            ->where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno'])
            ->toArray();

        $this->tecnicoReasignadoId = $this->solicitudSeleccionada->reasignado_a_id
            ?: $this->solicitudSeleccionada->solicitado_por_id;

        $this->notasRespuesta = '';
        $this->modalSurtir = true;
    }

    public function surtirDeInventario(): void
    {
        $this->validate([
            'piezaSeleccionada' => 'required|exists:inventario_piezas,id',
            'notasRespuesta' => 'nullable|string|max:500',
            'tecnicoReasignadoId' => 'required|exists:users,id',
        ], [
            'tecnicoReasignadoId.required' => 'Debes seleccionar a quien se reasigna la instalacion.',
        ]);

        try {
            $this->solicitudSeleccionada?->surtirDeInventario(
                $this->piezaSeleccionada,
                auth()->id(),
                $this->notasRespuesta ?: null,
                $this->tecnicoReasignadoId
            );

            session()->flash('success', 'Pieza asignada correctamente. El tecnico ya puede instalarla.');
            $this->cerrarModales();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al surtir: ' . $e->getMessage());
        }
    }

    public function abrirModalCompra(int $solicitudId): void
    {
        $this->solicitudSeleccionada = SolicitudPieza::findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeSerGestionada()) {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
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

            session()->flash('success', 'Solicitud marcada como pendiente de compra.');
            $this->cerrarModales();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function abrirModalCancelar(int $solicitudId): void
    {
        $this->solicitudSeleccionada = SolicitudPieza::findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeCancelarse()) {
            session()->flash('error', 'Esta solicitud ya no puede cancelarse.');
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

            session()->flash('success', 'Solicitud cancelada correctamente.');
            $this->cerrarModales();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cambiarFiltro(string $estatus): void
    {
        $this->filtroEstatus = $estatus;
        $this->resetPage();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
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
            'motivoCancelacion',
            'piezasDisponibles',
            'tecnicoReasignadoId',
            'tecnicos',
        ]);
    }

    private function autorizarGestion(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.gestion'), 403);
    }
}
