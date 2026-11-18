<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Transferencia;
use App\Services\TransferenciaService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Transferencias de Inventario'])]
class Transferencias extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filtroEstado = 'todos';

    // Modal de Detalle y Acciones
    public bool $modalDetalle = false;

    public ?int $transferenciaId = null;

    public string $motivoRechazo = '';

    public bool $mostrarConfirmarRechazo = false;

    public string $mensajeFeedback = '';

    public string $tipoFeedback = 'success';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless(
            $user && (
                $user->esCeoOAdmin()
                || $user->esGerente()
                || $user->tienePermiso('prep.transferencias.ver')
                || $user->tienePermiso('ventas.transferencias.ver')
                || $user->puedeCrearTransferencias()
            ),
            403,
            'No tienes permisos para ver el listado de transferencias.'
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function verDetalle(int $id): void
    {
        $this->transferenciaId = $id;
        $this->modalDetalle = true;
        $this->mostrarConfirmarRechazo = false;
        $this->motivoRechazo = '';
        $this->mensajeFeedback = '';
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->transferenciaId = null;
        $this->mostrarConfirmarRechazo = false;
        $this->motivoRechazo = '';
    }

    public function enviar(TransferenciaService $service): void
    {
        if (! $this->transferenciaId) {
            return;
        }

        $tr = Transferencia::find($this->transferenciaId);
        if (! $tr) {
            return;
        }

        try {
            $service->enviar($tr);
            $this->tipoFeedback = 'success';
            $this->mensajeFeedback = "Transferencia #{$tr->id} enviada correctamente. Artículos en tránsito.";
            session()->flash('success', $this->mensajeFeedback);
        } catch (\Exception $e) {
            $this->tipoFeedback = 'error';
            $this->mensajeFeedback = $e->getMessage();
        }
    }

    public function aceptar(TransferenciaService $service): void
    {
        if (! $this->transferenciaId) {
            return;
        }

        $tr = Transferencia::find($this->transferenciaId);
        if (! $tr) {
            return;
        }

        try {
            $service->aceptar($tr);
            $this->tipoFeedback = 'success';
            $this->mensajeFeedback = "Transferencia #{$tr->id} aceptada con éxito. Inventarios y estancias actualizadas.";
            session()->flash('success', $this->mensajeFeedback);
        } catch (\Exception $e) {
            $this->tipoFeedback = 'error';
            $this->mensajeFeedback = $e->getMessage();
        }
    }

    public function activarRechazo(): void
    {
        $this->mostrarConfirmarRechazo = true;
    }

    public function cancelarRechazo(): void
    {
        $this->mostrarConfirmarRechazo = false;
        $this->motivoRechazo = '';
    }

    public function confirmarRechazo(TransferenciaService $service): void
    {
        if (! $this->transferenciaId) {
            return;
        }

        $tr = Transferencia::find($this->transferenciaId);
        if (! $tr) {
            return;
        }

        $this->validate([
            'motivoRechazo' => 'required|min:4|max:255',
        ], [
            'motivoRechazo.required' => 'Debes ingresar el motivo de rechazo.',
            'motivoRechazo.min' => 'El motivo debe tener al menos 4 caracteres.',
        ]);

        try {
            $service->rechazar($tr, $this->motivoRechazo);
            $this->tipoFeedback = 'success';
            $this->mensajeFeedback = "Transferencia #{$tr->id} rechazada. Artículos devueltos al almacén origen.";
            $this->mostrarConfirmarRechazo = false;
            $this->motivoRechazo = '';
            session()->flash('success', $this->mensajeFeedback);
        } catch (\Exception $e) {
            $this->tipoFeedback = 'error';
            $this->mensajeFeedback = $e->getMessage();
        }
    }

    public function cancelarTransferencia(TransferenciaService $service): void
    {
        if (! $this->transferenciaId) {
            return;
        }

        $tr = Transferencia::find($this->transferenciaId);
        if (! $tr) {
            return;
        }

        try {
            $service->cancelar($tr, 'Cancelada por el usuario');
            $this->tipoFeedback = 'success';
            $this->mensajeFeedback = "Transferencia #{$tr->id} cancelada.";
            session()->flash('success', $this->mensajeFeedback);
        } catch (\Exception $e) {
            $this->tipoFeedback = 'error';
            $this->mensajeFeedback = $e->getMessage();
        }
    }

    public function render()
    {
        $user = auth()->user();

        // Query base con filtro de visibilidad por rol
        $baseQuery = fn () => $user->aplicarFiltroVisibilidadTransferencias(
            Transferencia::query()
        );

        $query = $baseQuery()->with([
            'origen',
            'destino',
            'creador',
            'detalles',
        ])->latest();

        if ($this->filtroEstado !== 'todos') {
            $estadoDb = match ($this->filtroEstado) {
                'PENDIENTE' => 'ENVIADA',
                'APROBADA' => 'ACEPTADA',
                default => $this->filtroEstado,
            };

            $query->where('estatus', $estadoDb);
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('origen', fn ($q2) => $q2->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('destino', fn ($q2) => $q2->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('creador', fn ($q2) => $q2->where('nombre', 'like', "%{$search}%"));
            });
        }

        // Stats calculados solo sobre lo que el usuario puede ver
        $statsBase = $baseQuery();

        $detalle = null;
        $puedeGestionarOrigen = false;
        $puedeGestionarDestino = false;

        if ($this->modalDetalle && $this->transferenciaId) {
            $detalle = Transferencia::with([
                'origen',
                'destino',
                'creador',
                'aprobador',
                'detalles.movible',
            ])->find($this->transferenciaId);

            if ($detalle) {
                $service = app(TransferenciaService::class);
                $puedeGestionarOrigen = $service->esEncargadoActivo($detalle->almacen_origen_id);
                $puedeGestionarDestino = $service->esEncargadoActivo($detalle->almacen_destino_id);
            }
        }

        return view('livewire.preparacion.inventario.transferencias', [
            'transferencias' => $query->paginate(15),
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'borrador' => (clone $statsBase)->where('estatus', 'BORRADOR')->count(),
                'enviadas' => (clone $statsBase)->where('estatus', 'ENVIADA')->count(),
                'aceptadas' => (clone $statsBase)->where('estatus', 'ACEPTADA')->count(),
                'rechazadas' => (clone $statsBase)->where('estatus', 'RECHAZADA')->count(),
                'pendiente' => (clone $statsBase)->where('estatus', 'ENVIADA')->count(),
                'aprobada' => (clone $statsBase)->where('estatus', 'ACEPTADA')->count(),
            ],
            'detalle' => $detalle,
            'puedeGestionarOrigen' => $puedeGestionarOrigen,
            'puedeGestionarDestino' => $puedeGestionarDestino,
        ]);
    }
}
