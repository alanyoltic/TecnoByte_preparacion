<?php

namespace App\Livewire\Ventas\Entradas;

use App\Models\Almacen;
use App\Models\EntradaProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrearEntrada extends Component
{
    public $proveedor_id;
    public $folio_factura;
    public $fecha;
    public $notas;
    public $almacen_id; // Almacen destino por defecto (ej. Ventas)

    // Buscador
    public $search = '';
    public $searchResults = [];

    // Carrito de recepción
    public $cart = []; 
    // Estructura: [['id' => 1, 'nombre' => 'Mouse', 'sku' => '...', 'cantidad' => 1, 'precio_unitario' => 100, 'subtotal' => 100], ...]

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        // Preseleccionar el primer almacén disponible para ventas, si lo hay, o dejarlo nulo para que seleccione
        $this->almacen_id = Almacen::first()->id ?? null;
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = Producto::activos()
                ->buscar($this->search)
                ->take(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function agregarAlCarrito(int $productoId)
    {
        $producto = Producto::find($productoId);
        if (!$producto) return;

        // Verificar si ya está en el carrito
        $index = collect($this->cart)->search(fn($item) => $item['id'] === $productoId);

        if ($index !== false) {
            $this->cart[$index]['cantidad']++;
            $this->cart[$index]['subtotal'] = $this->cart[$index]['cantidad'] * $this->cart[$index]['precio_unitario'];
        } else {
            $this->cart[] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'sku' => $producto->sku,
                'cantidad' => 1,
                'precio_unitario' => $producto->precio_compra, // Sugerir el costo estimado actual
                'subtotal' => $producto->precio_compra
            ];
        }

        $this->search = '';
        $this->searchResults = [];
    }

    public function actualizarCantidad($index, $cantidad)
    {
        $cantidad = (int) $cantidad;
        if ($cantidad < 1) $cantidad = 1;

        $this->cart[$index]['cantidad'] = $cantidad;
        $this->cart[$index]['subtotal'] = $cantidad * $this->cart[$index]['precio_unitario'];
    }

    public function actualizarPrecio($index, $precio)
    {
        $precio = (float) $precio;
        if ($precio < 0) $precio = 0;

        $this->cart[$index]['precio_unitario'] = $precio;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['cantidad'] * $precio;
    }

    public function removerDelCarrito($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // re-index
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function guardar()
    {
        $this->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'fecha' => 'required|date',
            'folio_factura' => 'nullable|string|max:100',
            'cart' => 'required|array|min:1',
            'cart.*.cantidad' => 'required|integer|min:1',
            'cart.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear Entrada
            $entrada = EntradaProducto::create([
                'proveedor_id' => $this->proveedor_id ?: null,
                'fecha' => $this->fecha,
                'folio_factura' => $this->folio_factura,
                'total_estimado' => $this->total,
                'notas' => $this->notas,
                'registrado_por_id' => auth()->id(),
            ]);

            // 2. Procesar Ítems y Actualizar Stock
            foreach ($this->cart as $item) {
                $entrada->items()->create([
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'almacen_id' => $this->almacen_id,
                ]);

                // Actualizar inventario global usando el helper del Producto
                $producto = Producto::find($item['id']);
                $producto->incrementarStock($this->almacen_id, $item['cantidad']);
                
                // Opcional: Actualizar el precio_compra (costo) sugerido si el nuevo costo es diferente
                if ($producto->precio_compra != $item['precio_unitario']) {
                    $producto->update(['precio_compra' => $item['precio_unitario']]);
                }
            }

            DB::commit();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Entrada registrada y stock actualizado correctamente.'
            ]);

            return redirect()->route('ventas.entradas.historial');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar la entrada: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.ventas.entradas.crear-entrada', [
            'proveedores' => Proveedor::orderBy('nombre_empresa')->get(),
            'almacenes' => Almacen::orderBy('nombre')->get(),
        ]);
    }
}
