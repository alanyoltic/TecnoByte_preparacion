<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use App\Models\Equipo;

class EditarEquipo extends Component
{
    public Equipo $equipo;

    // Campos editables (puedes ir agregando más según tu tabla)
    public $marca;
    public $modelo;
    public $tipo_equipo;
    public $numero_serie;

    public $procesador_modelo;
    public $procesador_frecuencia;
    public $procesador_generacion;

    public $ram_total;
    public $ram_tipo;

    public $almacenamiento_principal_capacidad;
    public $almacenamiento_principal_tipo;

    public $sistema_operativo;
    public $pantalla_es_touch;

    public $grafica_dedicada_modelo;
    public $grafica_dedicada_vram;

    public $estatus_general;
    public $grado;

    public function mount(Equipo $equipo)
    {
        $this->equipo = $equipo;

        // Llenamos el formulario con lo que ya tiene el equipo
        $this->marca      = $equipo->marca;
        $this->modelo     = $equipo->modelo;
        $this->tipo_equipo = $equipo->tipo_equipo;
        $this->numero_serie = $equipo->numero_serie;

        $this->procesador_modelo      = $equipo->procesador_modelo;
        $this->procesador_frecuencia  = $equipo->procesador_frecuencia;
        $this->procesador_generacion  = $equipo->procesador_generacion;

        $this->ram_total = $equipo->ram_total;
        $this->ram_tipo  = $equipo->ram_tipo;

        $this->almacenamiento_principal_capacidad = $equipo->almacenamiento_principal_capacidad;
        $this->almacenamiento_principal_tipo      = $equipo->almacenamiento_principal_tipo;

        $this->sistema_operativo   = $equipo->sistema_operativo;
        $this->pantalla_es_touch   = (bool) $equipo->pantalla_es_touch;

        $this->grafica_dedicada_modelo = $equipo->grafica_dedicada_modelo;
        $this->grafica_dedicada_vram   = $equipo->grafica_dedicada_vram;

        $this->estatus_general = $equipo->estatus_general;
        $this->grado           = $equipo->grado;
    }

    protected function rules()
    {
        return [
            'marca'       => 'nullable|string|max:100',
            'modelo'      => 'nullable|string|max:150',
            'tipo_equipo' => 'nullable|string|max:100',
            'numero_serie'=> 'nullable|string|max:100',

            'procesador_modelo'     => 'nullable|string|max:150',
            'procesador_frecuencia' => 'nullable|string|max:50',
            'procesador_generacion' => 'nullable|string|max:50',

            'ram_total' => 'nullable|string|max:50',
            'ram_tipo'  => 'nullable|string|max:50',

            'almacenamiento_principal_capacidad' => 'nullable|string|max:50',
            'almacenamiento_principal_tipo'      => 'nullable|string|max:50',

            'sistema_operativo' => 'nullable|string|max:100',
            'pantalla_es_touch' => 'nullable|boolean',

            'grafica_dedicada_modelo' => 'nullable|string|max:150',
            'grafica_dedicada_vram'   => 'nullable|string|max:50',

            'estatus_general' => 'nullable|string|max:100',
            'grado'           => 'nullable|string|max:50',
        ];
    }

    public function actualizarEquipo()
    {
        $this->validate();

        $this->equipo->update([
            'marca'       => $this->marca,
            'modelo'      => $this->modelo,
            'tipo_equipo' => $this->tipo_equipo,
            'numero_serie'=> $this->numero_serie,

            'procesador_modelo'     => $this->procesador_modelo,
            'procesador_frecuencia' => $this->procesador_frecuencia,
            'procesador_generacion' => $this->procesador_generacion,

            'ram_total' => $this->ram_total,
            'ram_tipo'  => $this->ram_tipo,

            'almacenamiento_principal_capacidad' => $this->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo'      => $this->almacenamiento_principal_tipo,

            'sistema_operativo' => $this->sistema_operativo,
            'pantalla_es_touch' => $this->pantalla_es_touch ? 1 : 0,

            'grafica_dedicada_modelo' => $this->grafica_dedicada_modelo,
            'grafica_dedicada_vram'   => $this->grafica_dedicada_vram,

            'estatus_general' => $this->estatus_general,
            'grado'           => $this->grado,
        ]);

        session()->flash('status', 'Equipo actualizado correctamente.');
    }

    public function render()
    {
        return view('livewire.inventario.editar-equipo');
    }
}
