<?php

namespace App\Livewire\Lotes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lote;
use App\Models\Proveedor;

class ListaLotes extends Component
{
    use WithPagination;

    // =======================
    //  Filtros / UI
    // =======================
    public string $search = '';
    public int $perPage = 10;
    public string $filtroProveedor = 'todos';

    // =======================
    //  Catálogos / Stats
    // =======================
    public $proveedores = [];
    public array $stats = [
        'total' => 0,
        'con_fecha' => 0,
        'sin_fecha' => 0,
        'proveedores' => 0,
    ];

    public function mount(): void
    {
        $this->proveedores = Proveedor::orderBy('nombre_empresa')->get();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroProveedor(): void
    {
        $this->resetPage();
    }

    private function cargarStats(): void
    {
        // Stats globales (no dependen de filtros)
        $this->stats = [
            'total'       => Lote::count(),
            'con_fecha'   => Lote::whereNotNull('fecha_llegada')->count(),
            'sin_fecha'   => Lote::whereNull('fecha_llegada')->count(),
            'proveedores' => Proveedor::count(),
        ];
    }

    public function render()
    {
        $this->cargarStats();

        $q = Lote::query()
            ->with('proveedor');

        // Filtro proveedor
        if ($this->filtroProveedor !== 'todos' && $this->filtroProveedor !== '') {
            $q->where('proveedor_id', (int) $this->filtroProveedor);
        }

        // Búsqueda
        $s = trim($this->search);
        if ($s !== '') {
            $q->where(function ($qq) use ($s) {
                $qq->where('nombre_lote', 'like', "%{$s}%")
                   ->orWhereHas('proveedor', function ($p) use ($s) {
                       $p->where('nombre_empresa', 'like', "%{$s}%")
                         ->orWhere('abreviacion', 'like', "%{$s}%");
                   });
            });
        }

        $lotes = $q->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.lotes.lista-lotes', compact('lotes'));
    }
}
