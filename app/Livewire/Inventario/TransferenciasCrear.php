<?php

namespace App\Livewire\Inventario;

use Livewire\Component;

class TransferenciasCrear extends Component
{
    public function render()
    {
        return view('livewire.inventario.transferencias-crear');
    }

    public $almacen_origen_id;
    public $almacen_destino_id;

    public function guardar()
    {
        if (!auth()->user()->tienePermiso('transferencias.crear')) {
            abort(403);
        }

        $transferencia = Transferencia::create([
            'almacen_origen_id' => $this->almacen_origen_id,
            'almacen_destino_id' => $this->almacen_destino_id,
            'created_by' => auth()->id(),
            'estatus' => 'BORRADOR'
        ]);

        return redirect()->route('inventario.transferencias');
    }




}
