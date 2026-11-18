<?php

namespace App\Livewire\Ventas;

use App\Models\Venta;
use App\Services\VentaService;
use Livewire\Component;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['pageTitle' => 'Historial de Ventas'])]
class HistorialVentas extends Component
{
    use WithPagination;

    public $search = '';
    public $estatus = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function cancelarVenta(int $ventaId, VentaService $ventaService)
    {
        try {
            $ventaService->cancelarCompletada($ventaId, auth()->id());
            $this->dispatch('toast', type: 'success', message: 'Venta cancelada y stock retornado correctamente.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $query = Venta::with(['cliente', 'vendedor'])
            ->orderBy('created_at', 'desc');

        // Limitar la vista para Asesores (solo ven sus propias ventas)
        $user = auth()->user();
        if ($user && !in_array(strtolower(optional($user->role)->slug), ['ceo', 'admin', 'gerente', 'gerente_area', 'supervisor'])) {
            $query->where('vendedor_id', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('folio', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function ($q) {
                      $q->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('telefono', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->estatus) {
            $query->where('estatus', $this->estatus);
        }

        if ($this->fecha_inicio) {
            $query->whereDate('created_at', '>=', $this->fecha_inicio);
        }

        if ($this->fecha_fin) {
            $query->whereDate('created_at', '<=', $this->fecha_fin);
        }

        $ventas = $query->paginate(15);

        return view('livewire.ventas.historial-ventas', [
            'ventas' => $ventas
        ]);
    }
}
