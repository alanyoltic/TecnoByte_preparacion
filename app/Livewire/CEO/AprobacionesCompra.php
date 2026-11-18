<?php

namespace App\Livewire\CEO;

use App\Models\CompraInventario;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Lote;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app', ['pageTitle' => 'Aprobaciones de CEO'])]
class AprobacionesCompra extends Component
{
    public function mount()
    {
        abort_unless(Auth::user()->tienePermiso('prep.compras.aprobar_ceo'), 403);
    }

    public function aprobarOrden($compraId)
    {
        $compra = CompraInventario::with('items')->findOrFail($compraId);
        if ($compra->estatus !== 'PENDIENTE_CEO') return;

        DB::transaction(function () use ($compra) {
            $compra->update([
                'estatus' => 'APROBADA',
                'aprobado_por_id' => Auth::id(), // CEO
            ]);

            // NOTA: Ya no generamos el Lote aqu.
            // El lote fsico se generar cuando el almacn haga la Recepcin Express.
        });

        $this->dispatch('toast', type: 'success', message: 'Orden de compra aprobada financieramente.');
    }

    public function rechazarOrden($compraId)
    {
        $compra = CompraInventario::findOrFail($compraId);
        
        $compra->update([
            'estatus' => 'RECHAZADA',
            'cancelado_por_id' => Auth::id(),
            'fecha_cancelacion' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Orden de compra rechazada.');
    }

    public function render()
    {
        $ordenes = CompraInventario::with('proveedor', 'registradoPor')
            ->where('estatus', 'PENDIENTE_CEO')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.c-e-o.aprobaciones-compra', [
            'ordenes' => $ordenes,
        ]);
    }
}
