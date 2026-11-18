<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Cargador;
use App\Models\Lote;
use App\Models\CompraInventario;
use App\Services\CargadorTraceService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Gestión de Cargadores'])]
class GestionCargadores extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filtroEstatus = '';

    // Propiedades del Modal de Creación/Edición
    public $modalAbierto = false;
    public $cargadorId;
    public $serie;
    public $marca;
    public $voltaje;
    public $amperaje;
    public $punta;
    public $estatus = 'DISPONIBLE';

    protected $rules = [
        'serie' => 'nullable|string|max:255',
        'marca' => 'nullable|string|max:255',
        'voltaje' => 'nullable|string|max:255',
        'amperaje' => 'nullable|string|max:255',
        'punta' => 'nullable|string|max:255',
        'estatus' => 'required|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstatus()
    {
        $this->resetPage();
    }

    public function abrirModalNuevo()
    {
        $this->resetValidation();
        $this->reset(['cargadorId', 'serie', 'marca', 'voltaje', 'amperaje', 'punta', 'estatus']);
        $this->estatus = 'DISPONIBLE';
        $this->modalAbierto = true;
    }

    public function abrirModalEditar($id)
    {
        $this->resetValidation();
        $cargador = Cargador::findOrFail($id);
        $this->cargadorId = $cargador->id;
        $this->serie = $cargador->serie;
        $this->marca = $cargador->marca;
        $this->voltaje = $cargador->voltaje;
        $this->amperaje = $cargador->amperaje;
        $this->punta = $cargador->punta;
        $this->estatus = $cargador->estatus->value;

        $this->modalAbierto = true;
    }

    public function guardar()
    {
        // Validar unicidad si hay serie
        $rules = $this->rules;
        if ($this->serie) {
            $rules['serie'] = 'nullable|string|max:255|unique:cargadores,serie,' . $this->cargadorId;
        }

        $this->validate($rules);

        if ($this->cargadorId) {
            $cargador = Cargador::findOrFail($this->cargadorId);
            $cargador->update([
                'serie' => $this->serie,
                'marca' => $this->marca,
                'voltaje' => $this->voltaje,
                'amperaje' => $this->amperaje,
                'punta' => $this->punta,
                'estatus' => $this->estatus,
            ]);
            CargadorTraceService::log($cargador, 'ACTUALIZADO', 'Datos actualizados manualmente');
            $this->dispatch('toast', type: 'success', message: 'Cargador actualizado correctamente.');
        } else {
            $cargador = Cargador::create([
                'serie' => $this->serie,
                'marca' => $this->marca,
                'voltaje' => $this->voltaje,
                'amperaje' => $this->amperaje,
                'punta' => $this->punta,
                'estatus' => $this->estatus,
            ]);
            CargadorTraceService::log($cargador, 'CREADO', 'Cargador creado manualmente');
            $this->dispatch('toast', type: 'success', message: 'Cargador creado correctamente.');
        }

        $this->modalAbierto = false;
    }

    public function eliminar($id)
    {
        $cargador = Cargador::findOrFail($id);
        CargadorTraceService::log($cargador, 'SCRAP', 'Cargador enviado a Scrap / eliminado');
        $cargador->update(['estatus' => 'SCRAP']);
        $cargador->delete();

        $this->dispatch('toast', type: 'success', message: 'Cargador enviado a baja.');
    }

    public function render()
    {
        $cargadores = Cargador::query()
            ->with(['lote', 'compraInventario', 'equipo'])
            ->when($this->search, function ($query) {
                $query->where('serie', 'like', "%{$this->search}%")
                      ->orWhere('marca', 'like', "%{$this->search}%");
            })
            ->when($this->filtroEstatus, function ($query) {
                $query->where('estatus', $this->filtroEstatus);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.preparacion.inventario.gestion-cargadores', [
            'cargadores' => $cargadores,
        ]);
    }
}
