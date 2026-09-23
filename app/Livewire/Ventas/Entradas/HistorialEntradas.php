<?php

namespace App\Livewire\Ventas\Entradas;

use App\Models\EntradaProducto;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class HistorialEntradas extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio;
    public $fecha_fin;

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = EntradaProducto::with(['proveedor', 'registradoPor', 'items.producto']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('folio_factura', 'like', '%' . $this->search . '%')
                  ->orWhereHas('proveedor', function($qProv) {
                      $qProv->where('nombre_empresa', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->fecha_inicio) {
            $query->whereDate('fecha', '>=', $this->fecha_inicio);
        }

        if ($this->fecha_fin) {
            $query->whereDate('fecha', '<=', $this->fecha_fin);
        }

        return view('livewire.ventas.entradas.historial-entradas', [
            'entradas' => $query->latest('fecha')->latest('id')->paginate(15)
        ]);
    }
}
