<?php

namespace App\Livewire\Compras;

use App\Models\CatalogoPieza;
use App\Models\Producto;
use App\Models\Consumible;
use App\Models\CatalogoEquipo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Catálogo Global'])]
class CatalogoGlobal extends Component
{
    use WithPagination;

    public string $area = 'PREPARACION';
    public string $tab = 'piezas'; // 'piezas', 'equipos', 'accesorios', 'insumos'
    public string $search = '';
    
    // Filtros
    public string $categoria_filter = '';

    // Variables comunes del modal
    public bool $showModal = false;
    public bool $isEditing = false;
    public $itemId = null;

    // ----- CAMPOS -----
    // Piezas / Insumos / Accesorios
    public $nombre = '';
    public $categoria = '';
    public $descripcion = '';
    public $codigo_barras = '';
    public $sku = '';
    public $activo = true;
    public $requiere_serie = false;
    
    // Accesorios
    public $precio_venta = 0;
    public $precio_compra = 0;
    
    // Equipos / Accesorios
    public $marca = '';
    
    // Equipos
    public $modelo = '';
    public $tipo_equipo = 'LAPTOP';

    public $categoriasPiezas = [
        'RAM' => 'RAM',
        'SSD' => 'SSD',
        'HDD' => 'HDD',
        'BATERIA' => 'Batería',
        'PANTALLA' => 'Pantalla',
        'TECLADO' => 'Teclado',
        'CARCASA' => 'Carcasa',
        'PALMREST' => 'Palmrest',
        'BISAGRA' => 'Bisagra',
        'CARGADOR' => 'Cargador',
        'PLACA_BASE' => 'Placa Base',
        'VENTILADOR' => 'Ventilador',
        'OTRO' => 'Otro'
    ];

    public $categoriasProductos = [
        'CABLE' => 'Cables y Adaptadores',
        'PERIFERICO' => 'Periféricos (Mouse, Teclado)',
        'AUDIO' => 'Audio (Audífonos, Bocinas)',
        'ALMACENAMIENTO' => 'Almacenamiento Externo (USB, HDD)',
        'ACCESORIO' => 'Accesorios Generales',
        'MOCHILA' => 'Mochilas y Fundas'
    ];

    public $categoriasConsumibles = [
        'OFICINA' => 'Papelería y Oficina',
        'LIMPIEZA' => 'Limpieza y Químicos',
        'HERRAMIENTA' => 'Herramientas',
        'EMPAQUE' => 'Empaque y Embalaje'
    ];

    public $tiposEquipo = [
        'LAPTOP' => 'Laptop',
        'DESKTOP' => 'PC de Escritorio',
        'ALL_IN_ONE' => 'All In One',
        'MINI_PC' => 'Mini PC',
        'OTRO' => 'Otro'
    ];

    public function mount()
    {
        if (request()->routeIs('ventas.*') || !auth()->user()?->can('prep.compras.ver')) {
            $this->area = 'VENTAS';
            $this->tab = 'accesorios';
        } else {
            $this->area = 'PREPARACION';
            $this->tab = 'equipos'; // Mostramos equipos como default para preparacion
        }
    }

    public function changeTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
        $this->search = '';
        $this->categoria_filter = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoriaFilter()
    {
        $this->resetPage();
    }

    // -- QUERIES --
    #[Computed]
    public function equipos()
    {
        if ($this->tab !== 'equipos') return [];
        return CatalogoEquipo::query()
            ->when($this->search, function($q) {
                $q->where('marca', 'like', "%{$this->search}%")
                  ->orWhere('modelo', 'like', "%{$this->search}%");
            })
            ->when($this->categoria_filter, function($q) {
                $q->where('tipo_equipo', $this->categoria_filter);
            })
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function piezas()
    {
        if ($this->tab !== 'piezas') return [];
        return CatalogoPieza::query()
            ->when($this->search, function($q) {
                $q->where('nombre', 'like', "%{$this->search}%");
            })
            ->when($this->categoria_filter, function($q) {
                $q->where('categoria', $this->categoria_filter);
            })
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function accesorios()
    {
        if ($this->tab !== 'accesorios') return [];
        return Producto::query()
            ->when($this->search, function($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%")
                  ->orWhere('codigo_barras', 'like', "%{$this->search}%");
            })
            ->when($this->categoria_filter, function($q) {
                $q->where('categoria', $this->categoria_filter);
            })
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function insumos()
    {
        if ($this->tab !== 'insumos') return [];
        return Consumible::query()
            ->when($this->area !== 'AMBAS', function($q) {
                $q->whereIn('area', [$this->area, 'AMBAS']);
            })
            ->when($this->search, function($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%")
                  ->orWhere('codigo_barras', 'like', "%{$this->search}%");
            })
            ->when($this->categoria_filter, function($q) {
                $q->where('categoria', $this->categoria_filter);
            })
            ->latest()
            ->paginate(12);
    }

    // -- CRUD --
    public function create()
    {
        $this->resetValidation();
        $this->reset([
            'itemId', 'sku', 'codigo_barras', 'nombre', 
            'categoria', 'descripcion', 'marca', 'modelo', 'tipo_equipo',
            'precio_venta', 'precio_compra', 'requiere_serie'
        ]);
        
        $this->activo = true;
        
        // Defaults
        if ($this->tab === 'piezas') $this->categoria = 'RAM';
        elseif ($this->tab === 'accesorios') $this->categoria = 'ACCESORIO';
        elseif ($this->tab === 'insumos') $this->categoria = 'OFICINA';
        elseif ($this->tab === 'equipos') $this->tipo_equipo = 'LAPTOP';

        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editEquipo(CatalogoEquipo $equipo)
    {
        $this->resetValidation();
        $this->itemId = $equipo->id;
        $this->marca = $equipo->marca;
        $this->modelo = $equipo->modelo;
        $this->tipo_equipo = $equipo->tipo_equipo;
        $this->activo = $equipo->activo;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function editPieza(CatalogoPieza $pieza)
    {
        $this->resetValidation();
        $this->itemId = $pieza->id;
        $this->nombre = $pieza->nombre;
        $this->categoria = $pieza->categoria;
        $this->requiere_serie = $pieza->requiere_serie;
        $this->activo = $pieza->activo;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function editProducto(Producto $producto)
    {
        $this->resetValidation();
        $this->itemId = $producto->id;
        $this->nombre = $producto->nombre;
        $this->categoria = $producto->categoria;
        $this->marca = $producto->marca;
        $this->codigo_barras = $producto->codigo_barras;
        $this->sku = $producto->sku;
        $this->precio_venta = $producto->precio_venta;
        $this->precio_compra = $producto->precio_compra;
        $this->descripcion = $producto->descripcion;
        $this->activo = $producto->activo;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function editConsumible(Consumible $consumible)
    {
        $this->resetValidation();
        $this->itemId = $consumible->id;
        $this->nombre = $consumible->nombre;
        $this->categoria = $consumible->categoria;
        $this->codigo_barras = $consumible->codigo_barras;
        $this->sku = $consumible->sku;
        $this->descripcion = $consumible->descripcion;
        $this->activo = $consumible->activo;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->tab === 'equipos') {
            $this->validate([
                'marca' => 'required|string|max:100',
                'modelo' => 'required|string|max:100',
                'tipo_equipo' => 'required|string|max:50',
            ]);
            CatalogoEquipo::updateOrCreate(
                ['id' => $this->itemId],
                ['marca' => $this->marca, 'modelo' => $this->modelo, 'tipo_equipo' => $this->tipo_equipo, 'activo' => $this->activo]
            );
        } elseif ($this->tab === 'piezas') {
            $this->validate([
                'nombre' => 'required|string|max:255',
                'categoria' => 'required|string|max:50',
                'requiere_serie' => 'boolean',
            ]);
            CatalogoPieza::updateOrCreate(
                ['id' => $this->itemId],
                ['nombre' => $this->nombre, 'categoria' => $this->categoria, 'requiere_serie' => $this->requiere_serie, 'activo' => $this->activo]
            );
        } elseif ($this->tab === 'accesorios') {
            $this->validate([
                'nombre' => 'required|string|max:255',
                'categoria' => 'required|string|max:50',
                'precio_venta' => 'required|numeric|min:0',
                'precio_compra' => 'nullable|numeric|min:0',
            ]);
            if (empty($this->sku) && !$this->itemId) $this->sku = 'ACC-' . strtoupper(substr(uniqid(), -6));
            Producto::updateOrCreate(
                ['id' => $this->itemId],
                [
                    'nombre' => $this->nombre, 'categoria' => $this->categoria, 'marca' => $this->marca,
                    'descripcion' => $this->descripcion, 'precio_venta' => $this->precio_venta,
                    'precio_compra' => $this->precio_compra, 'sku' => $this->sku, 
                    'codigo_barras' => empty($this->codigo_barras) ? null : $this->codigo_barras, 
                    'activo' => $this->activo
                ]
            );
        } elseif ($this->tab === 'insumos') {
            $this->validate([
                'nombre' => 'required|string|max:255',
                'categoria' => 'required|string|max:50',
            ]);
            if (empty($this->sku) && !$this->itemId) $this->sku = 'INS-' . strtoupper(substr(uniqid(), -6));
            Consumible::updateOrCreate(
                ['id' => $this->itemId],
                [
                    'nombre' => $this->nombre, 'categoria' => $this->categoria, 'descripcion' => $this->descripcion,
                    'sku' => $this->sku, 
                    'codigo_barras' => empty($this->codigo_barras) ? null : $this->codigo_barras, 
                    'area' => $this->area, 'activo' => $this->activo
                ]
            );
        }

        $this->showModal = false;
    }

    public function toggleActivo($id, $type)
    {
        if ($type === 'equipos') $m = CatalogoEquipo::find($id);
        elseif ($type === 'piezas') $m = CatalogoPieza::find($id);
        elseif ($type === 'accesorios') $m = Producto::find($id);
        else $m = Consumible::find($id);
        
        if ($m) {
            $m->activo = !$m->activo;
            $m->save();
        }
    }

    public function render()
    {
        return view('livewire.compras.catalogo-global');
    }
}
