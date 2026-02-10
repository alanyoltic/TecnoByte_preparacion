<?php

namespace App\Livewire\Inventario;

use Livewire\Component;

class ResumenEquipo extends Component
{
    public $equipo;

    public function mount($equipo)
    {
        $this->equipo = $equipo->load([
            'gpus',
            'baterias',
            'monitor'
        ]);
    }

    public function render()
    {
        return view('livewire.inventario.resumen-equipo');
    }
}
