<?php

namespace App\Livewire\CEO;

use App\Models\CompraInventario;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app', ['pageTitle' => 'Revisión de Orden de Compra'])]
class RevisionCompra extends Component
{
    public int $compraId;
    public CompraInventario $compra;

    public function mount(int $compraId): void
    {
        abort_unless(
            optional(Auth::user()->role)->slug === 'ceo' || Auth::user()->tienePermiso('prep.compras.aprobar_ceo'),
            403,
            'Solo la Dirección General puede acceder a esta revisión.'
        );

        $this->compraId = $compraId;
        $this->compra = CompraInventario::with([
            'proveedor',
            'registradoPor',
            'items.catalogoEquipo',
            'items.catalogoPieza',
            'items.producto',
            'items.consumible',
            'items.almacen'
        ])->findOrFail($compraId);
    }

    public function aprobar()
    {
        if ($this->compra->estatus !== 'PENDIENTE_CEO') {
            $this->dispatch('toast', type: 'warning', message: 'Esta orden no está pendiente de autorización del CEO.');
            return redirect()->route('preparacion.compras');
        }

        DB::transaction(function () {
            $this->compra->update([
                'estatus' => 'APROBADA',
                'aprobado_por_id' => Auth::id(),
            ]);
        });

        $this->dispatch('toast', type: 'success', message: 'Orden de compra autorizada financieramente.');
        return redirect()->route('preparacion.compras');
    }

    public function rechazar()
    {
        if ($this->compra->estatus !== 'PENDIENTE_CEO') {
            $this->dispatch('toast', type: 'warning', message: 'Esta orden no está pendiente de autorización.');
            return redirect()->route('preparacion.compras');
        }

        DB::transaction(function () {
            $this->compra->update([
                'estatus' => 'RECHAZADA',
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
            ]);
        });

        $this->dispatch('toast', type: 'success', message: 'Orden de compra rechazada.');
        return redirect()->route('preparacion.compras');
    }

    public function render()
    {
        return view('livewire.c-e-o.revision-compra', [
            'compra' => $this->compra
        ]);
    }
}
