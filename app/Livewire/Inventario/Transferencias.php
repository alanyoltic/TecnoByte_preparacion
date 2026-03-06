<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transferencia;

class Transferencias extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = 'todos';

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
        $query = Transferencia::with([
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
                    ->orWhereHas('origen', fn($q2) => $q2->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('destino', fn($q2) => $q2->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('creador', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        return view('livewire.inventario.transferencias', [
            'transferencias' => $query->paginate(15),
            'stats' => [
                'total' => Transferencia::count(),
                'borrador' => Transferencia::where('estatus', 'BORRADOR')->count(),
                // La UI usa "Pendiente"/"Aprobada", en BD corresponden a ENVIADA/ACEPTADA.
                'pendiente' => Transferencia::where('estatus', 'ENVIADA')->count(),
                'aprobada' => Transferencia::where('estatus', 'ACEPTADA')->count(),
            ],
        ]);
    }
}