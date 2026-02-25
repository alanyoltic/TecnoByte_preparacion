<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use App\Models\EquipoMovimiento;

class Transferencias extends Component
{
    public $busqueda = '';
    public $equipoSeleccionado = null;
    public $departamentoDestino = '';
    public $motivo = '';
    public $search = '';
    

    public function buscar()
    {
        // luego conectamos lógica real
    }

    public function transferir()
    {
        // luego conectamos motor de movimientos
    }

    public function render()
    {
        $transferencias = EquipoMovimiento::with([
            'equipo',
            'desde',
            'hacia',
            'usuario'
        ])
        ->latest()
        ->paginate(10);

        return view('livewire.inventario.transferencias', [
            'transferencias' => $transferencias,
        ]);
    }
}