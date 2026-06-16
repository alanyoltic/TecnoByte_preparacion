<?php

namespace App\Livewire\Preparacion\Lotes;

use App\Models\Lote;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Editar lotes'])]
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
    //  Eliminar lote
    // =======================
    public bool $modalEliminarLote = false;

    public ?int $loteEliminarId = null;

    public string $loteEliminarNombre = '';

    public int $loteEliminarEquiposPapelera = 0;

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
        abort_unless(auth()->user()?->tienePermiso('prep.lotes.ver'), 403);

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
            'total' => Lote::count(),
            'con_fecha' => Lote::whereNotNull('fecha_llegada')->count(),
            'sin_fecha' => Lote::whereNull('fecha_llegada')->count(),
            'proveedores' => Proveedor::count(),
        ];
    }

    public function abrirModalEliminarLote(int $id): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.lotes.ver'), 403);

        $lote = Lote::with('modelosRecibidos')->findOrFail($id);

        $this->loteEliminarEquiposPapelera = 0;
        foreach ($lote->modelosRecibidos as $m) {
            $this->loteEliminarEquiposPapelera += $m->equipos()->onlyTrashed()->count();
        }

        $this->loteEliminarId = $id;
        $this->loteEliminarNombre = $lote->nombre_lote ?? ('Lote #'.$id);
        $this->modalEliminarLote = true;
    }

    public function confirmarEliminarLote(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.lotes.ver'), 403);

        if (! $this->loteEliminarId) {
            return;
        }

        $lote = Lote::with('modelosRecibidos')->findOrFail($this->loteEliminarId);

        // Contar equipos registrados en este lote
        $totalEquipos = 0;
        foreach ($lote->modelosRecibidos as $m) {
            $totalEquipos += $m->equipos()->count();
        }

        if ($totalEquipos > 0) {
            $this->dispatch('toast', type: 'error',
                message: "No se puede eliminar: el lote tiene {$totalEquipos} equipo(s) activo(s) registrado(s). Elimínalos primero."
            );
            $this->cerrarModalEliminarLote();

            return;
        }

        try {
            $traceService = app(\App\Services\EquipoTraceService::class);

            DB::transaction(function () use ($lote, $traceService) {
                // Eliminar equipos en papelera definitivamente
                foreach ($lote->modelosRecibidos as $m) {
                    $equiposBorrados = $m->equipos()->onlyTrashed()->get();
                    foreach ($equiposBorrados as $equipo) {
                        $traceService->crearSnapshotEliminacion($equipo, 'Eliminación forzada por borrado de lote');
                        $equipo->gpus()->delete();
                        $equipo->baterias()->delete();
                        if ($equipo->monitor) {
                            $equipo->monitor()->delete();
                        }
                        \App\Models\EquipoAuditoria::where('equipo_id', $equipo->id)->delete();
                        $equipo->forceDelete();
                    }
                }

                $lote->modelosRecibidos()->delete();
                $lote->delete();
            });

            $this->dispatch('toast', type: 'success',
                message: "Lote \"{$this->loteEliminarNombre}\" eliminado correctamente."
            );
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Error al eliminar el lote: '.$e->getMessage());
        }

        $this->cerrarModalEliminarLote();
    }

    public function cerrarModalEliminarLote(): void
    {
        $this->modalEliminarLote = false;
        $this->loteEliminarId = null;
        $this->loteEliminarNombre = '';
        $this->loteEliminarEquiposPapelera = 0;
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

        return view('livewire.preparacion.lotes.lista-lotes', compact('lotes'));
    }
}
