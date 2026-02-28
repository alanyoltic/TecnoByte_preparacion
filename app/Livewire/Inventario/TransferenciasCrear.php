<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use App\Models\Almacen;
use App\Models\Transferencia;
use App\Models\Equipo;
use Illuminate\Support\Carbon;

class TransferenciasCrear extends Component
{
    public $almacenesOrigen = [];
    public $almacen_origen_id;
    public $almacen_destino_id;

    
    public $items = [];

    public function mount()
    {
        $this->cargarAlmacenesOrigen();
        $this->agregarItem(); // Fila inicial
    }

    /*
    |--------------------------------------------------------------------------
    | CARGA ALMACENES DONDE EL USUARIO ES ENCARGADO
    |--------------------------------------------------------------------------
    */
    private function cargarAlmacenesOrigen()
    {
        $user = auth()->user();

        $this->almacenesOrigen = Almacen::whereHas('encargados', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('activo', 1)
              ->where('desde', '<=', Carbon::now())
              ->where(function ($q2) {
                  $q2->whereNull('hasta')
                     ->orWhere('hasta', '>=', Carbon::now());
              });
        })->get();
    }

    public function getEquiposDisponiblesProperty()
    {
        if (!$this->almacen_origen_id) {
            return collect();
        }

        return \App\Models\Equipo::where(
            'almacen_id',
            $this->almacen_origen_id
        )->get();
    }

    /*
    |--------------------------------------------------------------------------
    | AGREGAR / ELIMINAR ITEMS
    |--------------------------------------------------------------------------
    */
    public function agregarItem()
    {
        $this->items[] = [
            'tipo' => 'equipo',
            'item_id' => '',
            'cantidad' => 1,
        ];
    }

    public function eliminarItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR TRANSFERENCIA (BORRADOR)
    |--------------------------------------------------------------------------
    */
    public function guardar()
    {
        if (!auth()->user()->tienePermiso('transferencias.crear')) {
            abort(403);
        }

        $this->validate([
            'almacen_origen_id' => 'required',
            'almacen_destino_id' => 'required|different:almacen_origen_id',
        ]);

        Transferencia::create([
            'almacen_origen_id' => $this->almacen_origen_id,
            'almacen_destino_id' => $this->almacen_destino_id,
            'created_by' => auth()->id(),
            'estatus' => 'BORRADOR',
        ]);

        return redirect()->route('inventario.transferencias');
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */
public function render()
{
    if ($this->almacen_origen_id) {
        $this->equiposDisponibles = \App\Models\Equipo::where(
            'almacen_id',
            $this->almacen_origen_id
        )->get();
    }

    return view('livewire.inventario.transferencias-crear', [
        'almacenesDestino' => Almacen::all()
    ]);
}
}