<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Equipo;
use App\Models\EquipoPiezaFaltante;
use App\Models\Proveedor;

class PendientesPiezas extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filtroEstatus = 'todos';
    public $filtroProveedor = 'todos';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstatus()
    {
        $this->resetPage();
    }

    public function updatingFiltroProveedor()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Proveedores para el filtro
        $proveedores = Proveedor::orderBy('nombre_empresa')->get();

        // Query base: solo equipos con piezas faltantes
        $query = EquipoPiezaFaltante::query()
            ->with([
                'equipo.loteModelo.lote.proveedor',
                'equipo.registradoPor',
                'pieza',
            ])
            ->whereHas('equipo', function ($q) {
                $q->where('estatus_general', 'Pendiente Pieza');
            });

        // Filtro búsqueda (serie, marca, modelo, tipo, pieza)
        if ($this->search) {
            $search = '%' . $this->search . '%';

            $query->where(function ($q) use ($search) {
                $q->whereHas('equipo', function ($qe) use ($search) {
                    $qe->where('numero_serie', 'like', $search)
                        ->orWhere('marca', 'like', $search)
                        ->orWhere('modelo', 'like', $search)
                        ->orWhere('tipo_equipo', 'like', $search);
                })->orWhereHas('pieza', function ($qp) use ($search) {
                    $qp->where('nombre', 'like', $search);
                });
            });
        }

        // Filtro estatus pieza
        if ($this->filtroEstatus !== 'todos') {
            $query->where('estatus_pieza', $this->filtroEstatus);
        }

        // Filtro proveedor
        if ($this->filtroProveedor !== 'todos') {
            $proveedorId = $this->filtroProveedor;
            $query->whereHas('equipo.loteModelo.lote', function ($q) use ($proveedorId) {
                $q->where('proveedor_id', $proveedorId);
            });
        }

        $piezasPendientes = $query
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Stats sencillos
        $stats = [
            'total_equipos' => (clone $query)->distinct('equipo_id')->count('equipo_id'),
            'pendiente_compra' => (clone $query)->where('estatus_pieza', 'Pendiente Compra')->count(),
            'compradas' => (clone $query)->where('estatus_pieza', 'Comprada')->count(),
            'instaladas' => (clone $query)->where('estatus_pieza', 'Instalada')->count(),
        ];

        return view('livewire.inventario.pendientes-piezas', [
            'piezasPendientes' => $piezasPendientes,
            'stats' => $stats,
            'proveedores' => $proveedores,
        ]);
    }
}
