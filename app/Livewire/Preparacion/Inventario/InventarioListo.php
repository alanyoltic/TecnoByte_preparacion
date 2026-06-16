<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\AsignacionEquipo;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Inventario listo'])]
class InventarioListo extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filtros / búsqueda
    public $search = '';

    public $filtroEstado = 'todos';

    public $filtroLote = 'todos';

    public $filtroProveedor = 'todos';

    public $filtroRegistradoPor = 'todos';

    public $colaboradores = [];

    // Catálogos
    public $lotes = [];

    public $proveedores = [];

    public $stats = [
        'total' => 0,
        'disponibles' => 0,
        'en_proceso' => 0,
        'en_calidad' => 0,
        'finalizado' => 0,
    ];

    // (Opcional pero útil) mantener filtros en la URL
    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => 'todos'],
        'filtroLote' => ['except' => 'todos'],
        'filtroProveedor' => ['except' => 'todos'],

    ];

    public function mount()
    {
        $this->autorizarVisualizacion();

        $this->lotes = Lote::orderBy('fecha_llegada', 'desc')->get();
        $this->proveedores = Proveedor::orderBy('nombre_empresa', 'asc')->get();

        $this->proveedores = Proveedor::orderBy('nombre_empresa', 'asc')->get();

        $this->colaboradores = User::withoutGlobalScopes()
            ->select('id', 'nombre')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'nombre' => $u->nombre])
            ->toArray();
    }

    private function autorizarVisualizacion(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.ver'), 403);
    }

    public function validarCalidad(int $equipoId): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.ver'), 403);

        $equipo = Equipo::find($equipoId);
        if (! $equipo || $equipo->estatus_area !== Equipo::AREA_EN_CALIDAD) {
            $this->dispatch('toast', type: 'error', message: 'El equipo no está en calidad.');

            return;
        }

        DB::transaction(function () use ($equipo) {
            $ae = AsignacionEquipo::where('equipo_id', $equipo->id)
                ->where('camino', AsignacionEquipo::EN_CALIDAD)
                ->latest('fin_en')
                ->first();

            if ($ae) {
                $ae->update(['camino' => AsignacionEquipo::COMPLETADO]);
            }

            $equipo->update(['estatus_area' => Equipo::AREA_FINALIZADO]);
        });

        $this->dispatch('toast', type: 'success', message: 'Equipo validado. Listo para ventas.');
    }

    /** Cuando cambia cualquiera de estos campos, regresamos a la página 1 */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroLote()
    {
        $this->resetPage();
    }

    public function updatingFiltroProveedor()
    {
        $this->resetPage();
    }

    public function updatedFiltroRegistradoPor()
    {
        $this->resetPage();
    }



    public function render()
    {
        $query = Equipo::query()
            ->with([
                'loteModelo.lote.proveedor',
                'registradoPor' => fn ($q) => $q->withoutGlobalScopes(),
                'monitor',
                'gpus',
            ])
            ->orderByDesc('created_at');

        // 🔍 Búsqueda rápida
        if (trim($this->search) !== '') {
            $search = '%'.trim($this->search).'%';

            $rawId = trim($this->search);
            $query->where(function ($q) use ($search, $rawId) {
                $q->where('numero_serie', 'like', $search)
                    ->orWhere('marca', 'like', $search)
                    ->orWhere('modelo', 'like', $search)
                    ->orWhere('tipo_equipo', 'like', $search)
                    ->orWhere('id', is_numeric($rawId) ? (int) $rawId : -1);
            });
        }

        // 🎯 Filtro por estatus
        if ($this->filtroEstado !== 'todos') {
            $query->where('estatus_area', $this->filtroEstado);
        }

        // 🎯 Filtro por lote
        if ($this->filtroLote !== 'todos') {
            $loteId = (int) $this->filtroLote;

            $query->whereHas('loteModelo.lote', function ($q) use ($loteId) {
                $q->where('id', $loteId);
            });
        }

        // 🎯 Filtro por proveedor
        if ($this->filtroProveedor !== 'todos') {
            $proveedorId = (int) $this->filtroProveedor;
            $query->where('proveedor_id', $proveedorId);
        }

        $query->when($this->filtroRegistradoPor !== 'todos', function ($q) {
            $q->where('registrado_por_user_id', $this->filtroRegistradoPor);
        });

        // Paginación final
        $equipos = $query->paginate(15);

        // Reactividad: Las tarjetas reaccionan exactamente a la query filtrada
        $baseQuery = clone $query;

        $this->stats['total'] = (clone $baseQuery)->count();
        $this->stats['disponibles'] = (clone $baseQuery)->whereIn('estatus_area', [
            Equipo::AREA_SIN_ASIGNAR, 
            Equipo::AREA_EN_ESPERA
        ])->count();
        $this->stats['en_proceso'] = (clone $baseQuery)->where('estatus_area', Equipo::AREA_EN_PROCESO)->count();
        $this->stats['en_calidad'] = (clone $baseQuery)->where('estatus_area', Equipo::AREA_EN_CALIDAD)->count();
        $this->stats['finalizado'] = (clone $baseQuery)->whereIn('estatus_area', [
            Equipo::AREA_FINALIZADO, 
            Equipo::AREA_TRANSFERIDO
        ])->count();

        return view('livewire.preparacion.inventario.inventario-listo', [
            'equipos' => $equipos,
            'lotes' => $this->lotes,
            'proveedores' => $this->proveedores,
            'stats' => $this->stats,
        ]);
    }
}
