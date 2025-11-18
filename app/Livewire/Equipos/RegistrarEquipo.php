<?php

namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Models\Lote;
use App\Models\Modelo;
use App\Models\Equipo;

class RegistrarEquipo extends Component
{
    public $lotes = [];
    public $modelos = [];

    public $lote_id;
    public $modelo_id;
    public $numero_serie;
    public $procesador;
    public $ram;
    public $almacenamiento;
    public $observaciones;

    public function mount()
    {
        $this->lotes   = Lote::all();
        $this->modelos = [];
    }

    public function updatedLoteId($value)
    {
        $this->modelos   = Modelo::where('lote_id', $value)->get();
        $this->modelo_id = null;
    }

    public function guardar()
    {
        $data = $this->validate([
            'lote_id'        => 'required|exists:lotes,id',
            'modelo_id'      => 'required|exists:modelos,id',
            'numero_serie'   => 'required|string|max:255',
            'procesador'     => 'nullable|string|max:255',
            'ram'            => 'required|string|max:100',
            'almacenamiento' => 'required|string|max:100',
            'observaciones'  => 'nullable|string|max:1000',
        ]);

        Equipo::create($data);

        session()->flash('success', 'Equipo registrado correctamente.');

        $this->reset([
            'lote_id', 'modelo_id', 'numero_serie',
            'procesador', 'ram', 'almacenamiento', 'observaciones',
        ]);

        $this->modelos = [];
        $this->lotes   = Lote::all();
    }

    public function render()
    {
        // Ojo: solo devuelve la vista, sin layout
        return view('livewire.equipos.registrar-equipo');
    }
}
