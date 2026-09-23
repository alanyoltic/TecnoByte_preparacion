<?php

namespace App\Livewire\Ventas\Inventario;

use App\Models\Almacen;
use App\Models\Equipo;
use App\Models\InventarioProducto;
use App\Models\Producto;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Inventario — Ventas'])]
class GestionInventario extends Component
{
    use WithPagination;

    public string $tab           = 'equipos';   // equipos | productos
    public string $busqueda      = '';
    public string $filtroAlmacen = '';

    public function updatedBusqueda(): void      { $this->resetPage(); }
    public function updatedFiltroAlmacen(): void { $this->resetPage(); }
    public function updatedTab(): void           { $this->resetPage(); $this->busqueda = ''; }

    public function render()
    {
        // Almacenes que pertenecen al flujo de Ventas (excluye los internos de Prep)
        $almacenesVentas = Almacen::whereNotIn('id', [
            Almacen::PREPARACION,
            Almacen::GARANTIAS_INTERNAS,
            Almacen::GARANTIAS_EXTERNAS,
            Almacen::CALIDAD,
            Almacen::SCRAP,
            Almacen::PIEZAS_PENDIENTES,
            Almacen::AREA_TRANSFERENCIA,
        ])->orderBy('nombre')->get();

        if ($this->tab === 'equipos') {

            $query = Equipo::with('almacen')
                ->where('estatus_ciclo', Equipo::CICLO_VENTAS)
                ->when($this->filtroAlmacen, fn ($q) => $q->where('almacen_id', $this->filtroAlmacen))
                ->when($this->busqueda, function ($q) {
                    $s = $this->busqueda;
                    $q->where(fn ($q2) => $q2
                        ->where('numero_serie', 'like', "%{$s}%")
                        ->orWhere('marca', 'like', "%{$s}%")
                        ->orWhere('modelo', 'like', "%{$s}%")
                    );
                })
                ->orderBy('marca')->orderBy('modelo');

            $items = $query->paginate(20);

            $stats = [
                'disponibles' => Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)
                                       ->where('estatus_area', Equipo::AREA_DISPONIBLE_VENTA)->count(),
                'en_piso'     => Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)
                                       ->where('estatus_area', Equipo::AREA_EN_PISO_VENTA)->count(),
                'apartados'   => Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)
                                       ->where('estatus_area', Equipo::AREA_APARTADO_CLIENTE)->count(),
                'total'       => Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)->count(),
            ];

        } else {

            $query = InventarioProducto::with(['producto', 'almacen'])
                ->when($this->filtroAlmacen, fn ($q) => $q->where('almacen_id', $this->filtroAlmacen))
                ->when($this->busqueda, function ($q) {
                    $s = $this->busqueda;
                    $q->whereHas('producto', fn ($q2) => $q2
                        ->where('nombre', 'like', "%{$s}%")
                        ->orWhere('sku', 'like', "%{$s}%")
                        ->orWhere('marca', 'like', "%{$s}%")
                    );
                })
                ->where('cantidad', '>', 0)
                ->orderByDesc('cantidad');

            $items = $query->paginate(20);

            $stats = [
                'con_stock'      => InventarioProducto::where('cantidad', '>', 0)->distinct('producto_id')->count('producto_id'),
                'total_unidades' => (int) InventarioProducto::sum('cantidad'),
                'sin_stock'      => Producto::whereDoesntHave('inventarios', fn ($q) => $q->where('cantidad', '>', 0))->count(),
                'total'          => Producto::count(),
            ];
        }

        return view('livewire.ventas.inventario.gestion-inventario', [
            'items'           => $items,
            'stats'           => $stats,
            'almacenesVentas' => $almacenesVentas,
        ]);
    }
}
