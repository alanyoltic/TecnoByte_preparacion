<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\DespachoVentas;
use App\Services\DespachoVentasService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Despachos a Ventas'])]
class DespachosVentas extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $filtroEstatus = '';

    protected $queryString = [
        'busqueda'      => ['except' => ''],
        'filtroEstatus' => ['except' => ''],
    ];

    public function render()
    {
        $despachos = DespachoVentas::query()
            ->with(['sucursalDestino', 'almacenDestino', 'creadoPor'])
            ->withCount(['equipos', 'productos'])
            ->when($this->filtroEstatus, fn ($q) => $q->where('estatus', $this->filtroEstatus))
            ->when($this->busqueda, fn ($q) => $q->where('folio', 'like', "%{$this->busqueda}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.preparacion.inventario.despachos-ventas', [
            'despachos' => $despachos,
            'estatuses' => DespachoVentas::labelsEstatus(),
        ]);
    }

    public function cancelar(int $id, DespachoVentasService $service): void
    {
        $despacho = DespachoVentas::findOrFail($id);

        $user = auth()->user();
        $puedeCancelar = $user->esCeoOAdmin()
            || $user->tienePermiso('prep.despachos.crear')
            || $user->tienePermiso('prep.inventario.gestion')
            || $despacho->creado_por_user_id === $user->id;

        if (! $puedeCancelar) {
            abort(403, 'No tienes permiso para cancelar este despacho.');
        }

        try {
            $service->cancelar($despacho);
            session()->flash('success', "Despacho {$despacho->folio} cancelado.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstatus(): void
    {
        $this->resetPage();
    }
}
