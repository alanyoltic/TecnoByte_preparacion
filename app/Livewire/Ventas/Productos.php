<?php

namespace App\Livewire\Ventas;

use App\Models\Producto;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Productos'])]
class Productos extends Component
{
    use WithPagination;

    // ── Filtros ─────────────────────────────────────────────────────────────
    public string $busqueda    = '';
    public string $filtroCategoria = '';

    // ── Modal crear/editar ───────────────────────────────────────────────────
    public bool   $modalAbierto = false;
    public ?int   $productoId  = null;

    public string $sku            = '';
    public string $codigo_barras  = '';
    public string $nombre         = '';
    public string $categoria      = '';
    public string $marca          = '';
    public string $descripcion    = '';
    public string $precio_compra  = '';
    public string $precio_venta   = '';
    public bool   $activo         = true;

    // ── Modal eliminar ───────────────────────────────────────────────────────
    public bool  $modalEliminar = false;
    public ?int  $eliminarId    = null;

    // ── Listeners ────────────────────────────────────────────────────────────
    protected function resetFiltros(): void
    {
        $this->resetPage();
    }

    public function updatedBusqueda(): void    { $this->resetPage(); }
    public function updatedFiltroCategoria(): void { $this->resetPage(); }

    // ── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        $productos = Producto::query()
            ->when($this->busqueda, fn ($q) => $q->buscar($this->busqueda))
            ->when($this->filtroCategoria, fn ($q) => $q->where('categoria', $this->filtroCategoria))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.ventas.productos', [
            'productos'   => $productos,
            'categorias'  => Producto::$categorias,
        ]);
    }

    // ── Acciones Modal ───────────────────────────────────────────────────────
    public function abrirCrear(): void
    {
        $this->checkPermiso('ventas.productos.crear');
        $this->resetCampos();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        $this->checkPermiso('ventas.productos.editar');
        $producto = Producto::findOrFail($id);

        $this->productoId     = $id;
        $this->sku            = $producto->sku ?? '';
        $this->codigo_barras  = $producto->codigo_barras ?? '';
        $this->nombre         = $producto->nombre;
        $this->categoria      = $producto->categoria ?? '';
        $this->marca          = $producto->marca ?? '';
        $this->descripcion    = $producto->descripcion ?? '';
        $this->precio_compra  = $producto->precio_compra ?? '';
        $this->precio_venta   = $producto->precio_venta ?? '';
        $this->activo         = $producto->activo;

        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetCampos();
    }

    public function guardar(): void
    {
        $this->productoId
            ? $this->checkPermiso('ventas.productos.editar')
            : $this->checkPermiso('ventas.productos.crear');

        $datos = $this->validate([
            'sku'           => 'nullable|string|max:100|unique:productos,sku,' . ($this->productoId ?? 'NULL'),
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras,' . ($this->productoId ?? 'NULL'),
            'nombre'        => 'required|string|max:200',
            'categoria'     => 'nullable|string|max:50',
            'marca'         => 'nullable|string|max:100',
            'descripcion'   => 'nullable|string|max:500',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta'  => 'nullable|numeric|min:0',
            'activo'        => 'boolean',
        ]);

        if ($this->productoId) {
            Producto::findOrFail($this->productoId)->update($datos);
            $this->dispatch('notify', type: 'success', message: 'Producto actualizado correctamente.');
        } else {
            Producto::create($datos);
            $this->dispatch('notify', type: 'success', message: 'Producto registrado correctamente.');
        }

        $this->cerrarModal();
    }

    public function confirmarEliminar(int $id): void
    {
        $this->checkPermiso('ventas.productos.eliminar');
        $this->eliminarId   = $id;
        $this->modalEliminar = true;
    }

    public function eliminar(): void
    {
        $this->checkPermiso('ventas.productos.eliminar');
        Producto::findOrFail($this->eliminarId)->delete();
        $this->modalEliminar = false;
        $this->eliminarId    = null;
        $this->dispatch('notify', type: 'success', message: 'Producto eliminado.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    private function resetCampos(): void
    {
        $this->productoId    = null;
        $this->sku           = '';
        $this->codigo_barras = '';
        $this->nombre        = '';
        $this->categoria     = '';
        $this->marca         = '';
        $this->descripcion   = '';
        $this->precio_compra = '';
        $this->precio_venta  = '';
        $this->activo        = true;
        $this->resetValidation();
    }

    private function checkPermiso(string $permiso): void
    {
        abort_unless(auth()->user()?->tienePermiso($permiso), 403);
    }
}
