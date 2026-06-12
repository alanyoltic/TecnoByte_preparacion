<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Transferencia;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Transferencias'])]
class Transferencias extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filtroEstado = 'todos';

    public function mount(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.transferencias.ver'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
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
                    ->orWhereHas('creador', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Stats calculados solo sobre lo que el usuario puede ver
        $statsBase = $baseQuery();

        return view('livewire.preparacion.inventario.transferencias', [
            'transferencias' => $query->paginate(15),
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'borrador' => (clone $statsBase)->where('estatus', 'BORRADOR')->count(),
                'pendiente' => (clone $statsBase)->where('estatus', 'ENVIADA')->count(),
                'aprobada' => (clone $statsBase)->where('estatus', 'ACEPTADA')->count(),
            ],
        ]);
    }
}
