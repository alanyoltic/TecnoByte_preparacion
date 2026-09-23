<?php

namespace App\Livewire\Ventas;

use App\Models\DespachoVentas;
use App\Services\DespachoVentasService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Despachos Entrantes — Ventas'])]
class DespachosEntrada extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'ENVIADO'; // Por default muestra los pendientes de aprobar
    public ?int $despachoSeleccionadoId = null;
    public string $motivoRechazo = '';
    public bool $modalRechazo = false;
    public bool $modalAprobacion = false;
    public string $mensajeError = '';

    // =========================================================
    // RENDER
    // =========================================================

    public function render()
    {
        $despachos = DespachoVentas::query()
            ->with(['sucursalDestino', 'almacenDestino', 'creadoPor'])
            ->withCount('equipos')
            ->when($this->filtroEstatus, fn ($q) => $q->where('estatus', $this->filtroEstatus))
            ->orderByDesc(
                fn ($q) => $q->selectRaw("FIELD(estatus, 'ENVIADO', 'BORRADOR', 'APROBADO', 'RECHAZADO', 'CANCELADO')")
            )
            ->orderByDesc('enviado_at')
            ->paginate(15);

        // Contadores para las pestañas
        $contadores = DespachoVentas::selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');

        return view('livewire.ventas.despachos-entrada', [
            'despachos'  => $despachos,
            'contadores' => $contadores,
            'estatuses'  => DespachoVentas::labelsEstatus(),
        ]);
    }

    // =========================================================
    // ACCIONES
    // =========================================================

    public function verDetalle(int $id): void
    {
        $this->despachoSeleccionadoId = $id;
    }

    public function iniciarAprobacion(int $id): void
    {
        $this->despachoSeleccionadoId = $id;
        $this->mensajeError = '';
        $this->modalAprobacion = true;
    }

    public function confirmarAprobacion(DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        $despacho = DespachoVentas::with('equipos.equipo')->find($this->despachoSeleccionadoId);

        if (! $despacho) {
            $this->mensajeError = 'Despacho no encontrado.';
            return;
        }

        try {
            $service->aprobar($despacho);
            $this->modalAprobacion = false;
            $this->despachoSeleccionadoId = null;
            session()->flash('success', "Despacho {$despacho->folio} aprobado. Los equipos ya están en Ventas.");
            $this->resetPage();
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function iniciarRechazo(int $id): void
    {
        $this->despachoSeleccionadoId = $id;
        $this->motivoRechazo = '';
        $this->mensajeError = '';
        $this->modalRechazo = true;
    }

    public function confirmarRechazo(DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (empty(trim($this->motivoRechazo))) {
            $this->mensajeError = 'Debes indicar el motivo del rechazo.';
            return;
        }

        $despacho = DespachoVentas::find($this->despachoSeleccionadoId);

        if (! $despacho) {
            $this->mensajeError = 'Despacho no encontrado.';
            return;
        }

        try {
            $service->rechazar($despacho, $this->motivoRechazo);
            $this->modalRechazo = false;
            $this->despachoSeleccionadoId = null;
            $this->motivoRechazo = '';
            session()->flash('success', "Despacho {$despacho->folio} rechazado.");
            $this->resetPage();
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function cerrarModales(): void
    {
        $this->modalAprobacion = false;
        $this->modalRechazo = false;
        $this->despachoSeleccionadoId = null;
        $this->mensajeError = '';
    }

    public function updatedFiltroEstatus(): void
    {
        $this->resetPage();
    }
}
