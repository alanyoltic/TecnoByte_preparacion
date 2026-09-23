<?php

namespace App\Livewire\Ventas\Inventario;

use App\Models\Almacen;
use App\Models\Transferencia;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Transferencias — Ventas'])]
class GestionTransferencias extends Component
{
    use WithPagination;

    public string $busqueda     = '';
    public string $filtroEstado = 'todos';  // todos | BORRADOR | ENVIADA | ACEPTADA | RECHAZADA

    // Modal detalle
    public bool $modalDetalle      = false;
    public ?int $transferenciaId   = null;

    public function updatedBusqueda(): void     { $this->resetPage(); }
    public function updatedFiltroEstado(): void { $this->resetPage(); }

    public function verDetalle(int $id): void
    {
        $this->transferenciaId = $id;
        $this->modalDetalle    = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle    = false;
        $this->transferenciaId = null;
    }

    public function render()
    {
        // IDs de almacenes que pertenecen al área de Ventas
        $idsVentas = Almacen::whereNotIn('id', [
            Almacen::PREPARACION,
            Almacen::GARANTIAS_INTERNAS,
            Almacen::GARANTIAS_EXTERNAS,
            Almacen::CALIDAD,
            Almacen::SCRAP,
            Almacen::PIEZAS_PENDIENTES,
            Almacen::AREA_TRANSFERENCIA,
        ])->pluck('id');

        $query = Transferencia::with(['origen', 'destino', 'creador', 'detalles'])
            // Solo transferencias donde origen O destino sea un almacén de Ventas
            ->where(fn ($q) => $q
                ->whereIn('almacen_origen_id', $idsVentas)
                ->orWhereIn('almacen_destino_id', $idsVentas)
            )
            ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('estatus', $this->filtroEstado))
            ->when($this->busqueda, function ($q) {
                $s = $this->busqueda;
                $q->where(fn ($q2) => $q2
                    ->where('id', 'like', "%{$s}%")
                    ->orWhereHas('origen',  fn ($r) => $r->where('nombre', 'like', "%{$s}%"))
                    ->orWhereHas('destino', fn ($r) => $r->where('nombre', 'like', "%{$s}%"))
                );
            })
            ->latest();

        // Stats rápidos (sobre el mismo scope de Ventas)
        $base = Transferencia::where(fn ($q) => $q
            ->whereIn('almacen_origen_id', $idsVentas)
            ->orWhereIn('almacen_destino_id', $idsVentas)
        );

        $stats = [
            'total'     => (clone $base)->count(),
            'borrador'  => (clone $base)->where('estatus', 'BORRADOR')->count(),
            'enviadas'  => (clone $base)->where('estatus', 'ENVIADA')->count(),
            'aceptadas' => (clone $base)->where('estatus', 'ACEPTADA')->count(),
            'rechazadas'=> (clone $base)->where('estatus', 'RECHAZADA')->count(),
        ];

        // Detalle de la transferencia seleccionada (si hay modal abierto)
        $detalle = $this->transferenciaId
            ? Transferencia::with(['origen', 'destino', 'creador', 'aprobador', 'detalles.movible'])
                ->find($this->transferenciaId)
            : null;

        return view('livewire.ventas.inventario.gestion-transferencias', [
            'transferencias' => $query->paginate(15),
            'stats'          => $stats,
            'detalle'        => $detalle,
        ]);
    }
}
