<?php

namespace App\Livewire\Preparacion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CatalogoEquipo;
use App\Models\Equipo;
use App\Models\LoteModeloRecibido;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Catálogo de Equipos'])]
class CatalogoEquipos extends Component
{
    use WithPagination;

    public $search = '';
    public $marca, $modelo, $tipo_equipo;
    public $editingId = null;
    public $confirmingDeletionId = null;

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit($id)
    {
        $item = CatalogoEquipo::findOrFail($id);
        $this->editingId = $id;
        $this->marca = $item->marca;
        $this->modelo = $item->modelo;
        $this->tipo_equipo = $item->tipo_equipo;
    }

    public function cancelEdit()
    {
        $this->reset(['editingId', 'marca', 'modelo', 'tipo_equipo']);
    }

    public function save()
    {
        $this->validate([
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:255',
            'tipo_equipo' => 'nullable|string|max:100',
        ]);

        if ($this->editingId) {
            $item = CatalogoEquipo::find($this->editingId);
            $item->update([
                'marca' => ucfirst(strtolower(trim($this->marca))),
                'modelo' => trim($this->modelo),
                'tipo_equipo' => $this->tipo_equipo,
            ]);
            $this->dispatch('toast', type: 'success', message: 'Modelo actualizado correctamente.');
        } else {
            CatalogoEquipo::create([
                'marca' => ucfirst(strtolower(trim($this->marca))),
                'modelo' => trim($this->modelo),
                'tipo_equipo' => $this->tipo_equipo,
            ]);
            $this->dispatch('toast', type: 'success', message: 'Modelo agregado al catálogo.');
        }

        $this->cancelEdit();
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeletionId = $id;
    }

    public function delete()
    {
        if (!$this->confirmingDeletionId) return;

        $item = CatalogoEquipo::findOrFail($this->confirmingDeletionId);
        
        // Verificar si está en uso
        $usoLotes = LoteModeloRecibido::where('catalogo_equipo_id', $item->id)->exists();
        $usoEquipos = Equipo::where('catalogo_equipo_id', $item->id)->exists();

        if ($usoLotes || $usoEquipos) {
            $this->dispatch('toast', type: 'error', message: 'No se puede eliminar: este modelo ya está vinculado a lotes o equipos.');
        } else {
            $item->delete();
            $this->dispatch('toast', type: 'success', message: 'Modelo eliminado del catálogo.');
        }

        $this->confirmingDeletionId = null;
    }

    public function render()
    {
        $items = CatalogoEquipo::query()
            ->where(function($q) {
                $q->where('marca', 'like', '%' . $this->search . '%')
                  ->orWhere('modelo', 'like', '%' . $this->search . '%');
            })
            ->withCount(['lotesVinculados', 'equiposVinculados'])
            ->orderBy('marca')
            ->orderBy('modelo')
            ->paginate(15);

        return view('livewire.preparacion.catalogo-equipos', [
            'items' => $items
        ]);
    }
}
