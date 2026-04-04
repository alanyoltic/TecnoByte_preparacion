<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\SolicitudPieza;

#[Layout('layouts.app', ['pageTitle' => 'Mis Solicitudes de Piezas'])]
class SolicitudesPiezas extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'PENDIENTE';
    public string $busqueda      = '';

    // Modal confirmar instalación
    public bool $modalConfirmar          = false;
    public ?SolicitudPieza $solicitudSeleccionada = null;
    public bool   $funciono              = true;
    public string $notasConfirmacion     = '';

    protected $queryString = ['filtroEstatus', 'busqueda'];

    public function render()
    {
        $solicitudes = SolicitudPieza::query()
            ->with([
                'catalogoPieza',
                'inventarioPieza.almacen',
                'respondidaPor',
                'asignacionEquipo.equipo',
                'equipo',
            ])
            ->where('solicitado_por_id', auth()->id())
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
                    );
                });
            })
            ->orderByRaw("FIELD(estatus, 'SURTIDA_INVENTARIO', 'PENDIENTE', 'PENDIENTE_COMPRA', 'COMPRADA', 'CONFIRMADA', 'CANCELADA')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $base = SolicitudPieza::where('solicitado_por_id', auth()->id());

        $contadores = [
            'pendientes'       => (clone $base)->where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'surtidas'         => (clone $base)->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'pendientes_compra'=> (clone $base)->where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'confirmadas'      => (clone $base)->where('estatus', SolicitudPieza::CONFIRMADA)->count(),
            'canceladas'       => (clone $base)->where('estatus', SolicitudPieza::CANCELADA)->count(),
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

    // ── Confirmar instalación ─────────────────────────────────────────────

    public function abrirConfirmar(int $id): void
    {
        $solicitud = SolicitudPieza::findOrFail($id);

        if ($solicitud->solicitado_por_id !== auth()->id()) {
            return;
        }

        if (!$solicitud->puedeSerConfirmada()) {
            session()->flash('error', 'Esta solicitud no puede confirmarse todavía. El inventario aún no ha surtido la pieza.');
            return;
        }

        $this->solicitudSeleccionada = $solicitud;
        $this->funciono              = true;
        $this->notasConfirmacion     = '';
        $this->modalConfirmar        = true;
    }

    public function confirmarInstalacion(): void
    {
        $this->validate([
            'notasConfirmacion' => 'nullable|string|max:500',
        ]);

        try {
            $this->solicitudSeleccionada->confirmarInstalacion(
                $this->funciono,
                $this->notasConfirmacion ?: null
            );

            session()->flash('success', $this->funciono
                ? 'Instalación confirmada. ¡La pieza funcionó correctamente!'
                : 'Registrado que la pieza no funcionó. Se marcará como defectuosa.'
            );

            $this->cerrarModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al confirmar: ' . $e->getMessage());
        }
    }

    private function cerrarModal(): void
    {
        $this->modalConfirmar        = false;
        $this->solicitudSeleccionada = null;
        $this->funciono              = true;
        $this->notasConfirmacion     = '';
    }
}
