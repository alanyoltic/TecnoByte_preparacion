<?php

namespace App\Livewire\Inventario;

use App\Models\SolicitudPieza;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Mis Solicitudes de Piezas'])]
class SolicitudesPiezas extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'PENDIENTE';
    public string $busqueda = '';

    public bool $modalConfirmar = false;
    public ?SolicitudPieza $solicitudSeleccionada = null;
    public bool $funciono = true;
    public string $notasConfirmacion = '';

    protected $queryString = ['filtroEstatus', 'busqueda'];

    public function mount(): void
    {
        $this->autorizarSolicitudes();
    }

    public function render()
    {
        $this->autorizarSolicitudes();

        $solicitudes = SolicitudPieza::query()
            ->with([
                'catalogoPieza',
                'inventarioPieza.almacen',
                'respondidaPor',
                'reasignadoA',
                'asignacionEquipo.equipo',
                'equipo',
            ])
            ->where(function ($q) {
                $q->where('solicitado_por_id', auth()->id())
                    ->orWhere('reasignado_a_id', auth()->id());
            })
            ->when($this->filtroEstatus !== 'TODAS', fn ($q) =>
                $q->where('estatus', $this->filtroEstatus)
            )
            ->when($this->busqueda, function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('catalogoPieza', fn ($c) =>
                        $c->where('nombre', 'like', '%' . $this->busqueda . '%')
                    )
                    ->orWhere('descripcion_libre', 'like', '%' . $this->busqueda . '%')
                    ->orWhereHas('equipo', fn ($e) =>
                        $e->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                            ->orWhere('modelo', 'like', '%' . $this->busqueda . '%')
                    )
                    ->orWhereHas('asignacionEquipo.equipo', fn ($e) =>
                        $e->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                            ->orWhere('modelo', 'like', '%' . $this->busqueda . '%')
                    );
                });
            })
            ->orderByRaw("FIELD(estatus, 'SURTIDA_INVENTARIO', 'COMPRADA', 'PENDIENTE', 'PENDIENTE_COMPRA', 'CONFIRMADA', 'CANCELADA')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $base = SolicitudPieza::where(function ($q) {
            $q->where('solicitado_por_id', auth()->id())
                ->orWhere('reasignado_a_id', auth()->id());
        });

        $contadores = [
            'pendientes' => (clone $base)->where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'surtidas' => (clone $base)->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'pendientes_compra' => (clone $base)->where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'compradas' => (clone $base)->where('estatus', SolicitudPieza::COMPRADA)->count(),
            'confirmadas' => (clone $base)->where('estatus', SolicitudPieza::CONFIRMADA)->count(),
            'canceladas' => (clone $base)->where('estatus', SolicitudPieza::CANCELADA)->count(),
        ];

        return view('livewire.inventario.solicitudes-piezas', compact('solicitudes', 'contadores'));
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

    public function abrirConfirmar(int $id): void
    {
        $solicitud = SolicitudPieza::findOrFail($id);

        if (!$this->esResponsableDeSolicitud($solicitud)) {
            session()->flash('error', 'Solo el tecnico responsable puede confirmar esta instalacion.');
            return;
        }

        if (!$solicitud->puedeSerConfirmada()) {
            session()->flash('error', 'Esta solicitud no puede confirmarse todavia. Inventario aun no ha surtido la pieza.');
            return;
        }

        $this->solicitudSeleccionada = $solicitud;
        $this->funciono = true;
        $this->notasConfirmacion = '';
        $this->modalConfirmar = true;
    }

    public function confirmarInstalacion(): void
    {
        $this->validate([
            'notasConfirmacion' => 'nullable|string|max:500',
        ]);

        if (!$this->solicitudSeleccionada || !$this->esResponsableDeSolicitud($this->solicitudSeleccionada)) {
            session()->flash('error', 'No tienes permiso para cerrar esta solicitud.');
            $this->cerrarModal();
            return;
        }

        try {
            $this->solicitudSeleccionada->finalizarInstalacionPorTecnico(
                auth()->id(),
                $this->funciono,
                $this->notasConfirmacion ?: null
            );

            session()->flash('success', $this->funciono
                ? 'Instalacion confirmada. El equipo volvio al flujo de calidad.'
                : 'Se registro la falla de la pieza y se genero una nueva solicitud.'
            );

            $this->cerrarModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al confirmar: ' . $e->getMessage());
        }
    }

    public function cerrarModal(): void
    {
        $this->modalConfirmar = false;
        $this->solicitudSeleccionada = null;
        $this->funciono = true;
        $this->notasConfirmacion = '';
    }

    private function autorizarSolicitudes(): void
    {
        $user = auth()->user();
        $roleSlug = strtolower((string) ($user?->role?->slug ?? ''));

        abort_unless(
            $user?->tienePermiso('prep.equipos.ver') && in_array($roleSlug, ['lider', 'tecnico'], true),
            403
        );
    }

    private function esResponsableDeSolicitud(SolicitudPieza $solicitud): bool
    {
        $responsableId = $solicitud->reasignado_a_id ?: $solicitud->solicitado_por_id;

        return (int) $responsableId === (int) auth()->id();
    }
}
