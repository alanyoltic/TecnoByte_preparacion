<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\User;

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

    // Tarjetas de totales
    public $stats = [
        'total'        => 0,
        'en_revision'  => 0,
        'aprobados'    => 0,
        'finalizados'  => 0,
    ];

    // (Opcional pero útil) mantener filtros en la URL
    protected $queryString = [
        'search'         => ['except' => ''],
        'filtroEstado'   => ['except' => 'todos'],
        'filtroLote'     => ['except' => 'todos'],
        'filtroProveedor'=> ['except' => 'todos'],
        
    ];

    public function mount()
    {
        // Lotes y proveedores para los selects
        $this->lotes = Lote::orderBy('fecha_llegada', 'desc')->get();
        $this->proveedores = Proveedor::orderBy('nombre_empresa', 'asc')->get();

        $this->calcularStats();

        $this->colaboradores = User::query()
            ->select('id', 'nombre') // si usas "name" cambia aquí
            ->orderBy('nombre')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'nombre' => $u->nombre])
            ->toArray();




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


    protected function calcularStats(): void
    {
        // Totales globales (sin filtros) – solo para las tarjetas de arriba
        $this->stats['total']        = Equipo::count();
        $this->stats['en_revision']  = Equipo::where('estatus_general', 'En Revisión')->count();
        $this->stats['aprobados']    = Equipo::where('estatus_general', 'Aprobado')->count();
        $this->stats['finalizados']  = Equipo::where('estatus_general', 'Finalizado')->count();
    }

    public function render()
    {
        $query = Equipo::query()
            ->with([
                'loteModelo.lote.proveedor',
                'registradoPor',
            ])
            ->orderByDesc('created_at');

        // 🔍 Búsqueda rápida
        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($q) use ($search) {
                $q->where('numero_serie', 'like', $search)
                  ->orWhere('marca', 'like', $search)
                  ->orWhere('modelo', 'like', $search)
                  ->orWhere('tipo_equipo', 'like', $search);
            });
        }

        // 🎯 Filtro por estatus
        if ($this->filtroEstado !== 'todos') {
            $query->where('estatus_general', $this->filtroEstado);
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

        // Si quieres que las tarjetas sean siempre globales, deja esto;
        // si quieres que sigan filtros, aquí podríamos cambiar la lógica.
        $this->calcularStats();

        return view('livewire.inventario.inventario-listo', [
            'equipos'      => $equipos,
            'lotes'        => $this->lotes,
            'proveedores'  => $this->proveedores,
            'stats'        => $this->stats,
        ]);
    }
}
