<?php

namespace App\Livewire\Preparacion\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\CompraInventario;
use App\Models\CompraInventarioItem;
use App\Models\InventarioPieza;
use App\Models\CatalogoPieza;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\Almacen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Compras de Inventario'])]
class ComprasInventario extends Component
{
    use WithPagination;

    // ── Vista activa ──────────────────────────────────────────────────
    public string $vista = 'lista';

    // ── Filtros lista ─────────────────────────────────────────────────
    public string $busqueda      = '';
    public string $filtroProveedor = '';

    // ── Form cabecera compra ──────────────────────────────────────────
    public ?int   $proveedorId   = null;
    public ?int   $loteId        = null;
    public string $fechaCompra   = '';
    public string $folio         = '';
    public string $notasCompra   = '';

    // ── Items de la compra ────────────────────────────────────────────
    public array $items = [];

    // ── Búsqueda de pieza por ítem ────────────────────────────────────
    public array $busquedasPieza = []; // búsqueda por índice de ítem

    // ── Error ─────────────────────────────────────────────────────────
    public string $error = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.gestion'), 403);
        $this->fechaCompra = now()->toDateString();
    }

    // ══════════════════════════════════════════════════════════════════
    // COMPUTED
    // ══════════════════════════════════════════════════════════════════

    #[Computed]
    public function compras()
    {
        return CompraInventario::with(['proveedor', 'registradoPor'])
            ->withCount('items')
            ->when($this->busqueda, fn($q) =>
                $q->whereHas('proveedor', fn($p) =>
                    $p->where('nombre_empresa', 'like', "%{$this->busqueda}%")
                      ->orWhere('abreviacion', 'like', "%{$this->busqueda}%")
                )->orWhere('folio', 'like', "%{$this->busqueda}%")
            )
            ->when($this->filtroProveedor, fn($q) =>
                $q->where('proveedor_id', $this->filtroProveedor)
            )
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id')
            ->paginate(15);
    }

    #[Computed]
    public function proveedores()
    {
        return Proveedor::orderBy('nombre_empresa')->get(['id', 'nombre_empresa', 'abreviacion']);
    }

    #[Computed]
    public function lotes()
    {
        return Lote::orderByDesc('id')->limit(30)->get(['id', 'nombre']);
    }

    #[Computed]
    public function almacenes()
    {
        return Almacen::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    #[Computed]
    public function catalogo()
    {
        return CatalogoPieza::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'especificacion']);
    }

    // ══════════════════════════════════════════════════════════════════
    // NAVEGACIÓN
    // ══════════════════════════════════════════════════════════════════

    public function nuevaCompra(): void
    {
        $this->resetForm();
        $this->vista = 'nueva';
    }

    public function volver(): void
    {
        $this->vista = 'lista';
        $this->resetForm();
        unset($this->compras);
    }

    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN DE ÍTEMS
    // ══════════════════════════════════════════════════════════════════

    public function agregarItem(): void
    {
        $this->items[] = [
            'catalogo_pieza_id' => '',
            'cantidad'          => 1,
            'precio_unitario'   => '',
            'almacen_id'        => 7,
            'notas'             => '',
        ];
        $this->busquedasPieza[] = '';
    }

    public function removerItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        array_splice($this->busquedasPieza, $index, 1);
        $this->items          = array_values($this->items);
        $this->busquedasPieza = array_values($this->busquedasPieza);
    }

    // ══════════════════════════════════════════════════════════════════
    // GUARDAR
    // ══════════════════════════════════════════════════════════════════

    public function guardarCompra(): void
    {
        $this->error = '';

        $this->validate([
            'proveedorId'          => 'required|exists:proveedores,id',
            'fechaCompra'          => 'required|date',
            'folio'                => 'nullable|string|max:100',
            'items'                => 'required|array|min:1',
            'items.*.catalogo_pieza_id' => 'required|exists:catalogo_piezas,id',
            'items.*.cantidad'          => 'required|integer|min:1',
            'items.*.precio_unitario'   => 'nullable|numeric|min:0',
            'items.*.almacen_id'        => 'required|exists:almacenes,id',
        ], [
            'proveedorId.required'             => 'Selecciona un proveedor.',
            'fechaCompra.required'             => 'La fecha de compra es obligatoria.',
            'items.required'                   => 'Agrega al menos una pieza.',
            'items.min'                        => 'Agrega al menos una pieza.',
            'items.*.catalogo_pieza_id.required' => 'Selecciona el tipo de pieza.',
            'items.*.cantidad.required'        => 'La cantidad es obligatoria.',
            'items.*.cantidad.min'             => 'La cantidad mínima es 1.',
        ]);

        try {
            DB::transaction(function () {
                $compra = CompraInventario::create([
                    'proveedor_id'      => $this->proveedorId,
                    'lote_id'           => $this->loteId ?: null,
                    'fecha_compra'      => $this->fechaCompra,
                    'folio'             => trim($this->folio) ?: null,
                    'notas'             => trim($this->notasCompra) ?: null,
                    'registrado_por_id' => Auth::id(),
                ]);

                foreach ($this->items as $item) {
                    $precio   = $item['precio_unitario'] !== '' ? (float) $item['precio_unitario'] : null;
                    $cantidad = (int) $item['cantidad'];

                    $compraItem = CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id'    => $item['catalogo_pieza_id'],
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => $item['almacen_id'],
                        'notas'                => trim($item['notas']) ?: null,
                    ]);

                    InventarioPieza::create([
                        'catalogo_pieza_id'  => $item['catalogo_pieza_id'],
                        'origen'             => InventarioPieza::COMPRA,
                        'compra_item_id'     => $compraItem->id,
                        'almacen_id'         => $item['almacen_id'],
                        'costo'              => $precio,
                        'registrado_por_id'  => Auth::id(),
                        'estatus'            => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'      => $this->fechaCompra,
                        'cantidad_inicial'   => $cantidad,
                        'cantidad_disponible'=> $cantidad,
                        'cantidad_reservada' => 0,
                        'cantidad_usada'     => 0,
                        'cantidad_baja'      => 0,
                        'notas'              => trim($item['notas']) ?: null,
                    ]);
                }
            });

            $this->dispatch('toast', ['type' => 'success', 'message' => 'Compra registrada correctamente.']);
            $this->volver();

        } catch (\Exception $e) {
            $this->error = 'Error al guardar: ' . $e->getMessage();
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════

    private function resetForm(): void
    {
        $this->proveedorId  = null;
        $this->loteId       = null;
        $this->fechaCompra  = now()->toDateString();
        $this->folio        = '';
        $this->notasCompra  = '';
        $this->items        = [];
        $this->busquedasPieza = [];
        $this->error        = '';
        $this->resetErrorBag();
    }

    /** Total estimado calculado de los ítems actuales. */
    public function getTotalEstimado(): float
    {
        return collect($this->items)->sum(function ($item) {
            $precio   = is_numeric($item['precio_unitario']) ? (float) $item['precio_unitario'] : 0;
            $cantidad = (int) ($item['cantidad'] ?? 0);
            return $precio * $cantidad;
        });
    }

    public function updatedBusqueda(): void { $this->resetPage(); }
    public function updatedFiltroProveedor(): void { $this->resetPage(); }

    public function render()
    {
        return view('livewire.preparacion.inventario.compras-inventario');
    }
}
