<?php

namespace App\Livewire\Ventas\Compras;

use App\Models\Almacen;
use App\Models\AlmacenEncargado;
use App\Models\CompraInventario;
use App\Models\CompraInventarioItem;
use App\Models\Consumible;
use App\Models\InventarioConsumible;
use App\Models\InventarioProducto;
use App\Models\LoteCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Compras — Ventas'])]
class ComprasVentas extends Component
{
    use WithPagination;

    // ── Área fija ─────────────────────────────────────────────────────
    protected string $area = 'VENTAS';

    // ── Vista activa ('lista' | 'nueva') ──────────────────────────────
    public string $vista = 'lista';

    // ── Filtros historial ─────────────────────────────────────────────
    public string $busqueda         = '';
    public string $filtroProveedor  = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    // ── Cabecera de la compra ─────────────────────────────────────────
    public ?int   $proveedorId      = null;
    public ?int   $loteCompraId     = null; // Selector formal de LoteCompra
    public string $fechaCompra      = '';
    public string $folio            = '';
    public string $notasCompra      = '';
    public ?int   $almacenDestinoId = null;

    // ── Selector de tipo de compra ('productos' | 'consumibles' | 'todos') ──
    public string $tipoCompra = 'productos';

    // ── Ítems de la orden ─────────────────────────────────────────────
    public array $itemsProductos   = [];
    public array $itemsConsumibles = [];

    // ── Modales ───────────────────────────────────────────────────────
    public bool   $modalProveedor       = false;
    public string $proveedorNombre      = '';
    public string $proveedorAbreviacion = '';
    public string $proveedorEmail       = '';
    public string $proveedorTelefono    = '';

    public bool   $modalLoteCompra       = false;
    public string $loteCompraNombre      = '';
    public string $loteCompraDescripcion = '';

    public ?int $verCompraId = null;
    public string $error = '';

    // ══════════════════════════════════════════════════════════════════
    // MOUNT
    // ══════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        // En base a los seeders de Ventas
        abort_unless(auth()->user()?->can('ventas.compras.ver'), 403);
        $this->fechaCompra = now()->toDateString();
        $this->preseleccionarAlmacen();
    }

    private function preseleccionarAlmacen(): void
    {
        $almacenes = $this->almacenes;
        if ($almacenes->isNotEmpty()) {
            $this->almacenDestinoId = $almacenes->first()->id;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // COMPUTED
    // ══════════════════════════════════════════════════════════════════

    #[Computed]
    public function compras()
    {
        return CompraInventario::with(['proveedor', 'registradoPor', 'loteDestino'])
            ->withCount('items')
            ->deArea($this->area)
            ->when($this->busqueda, fn ($q) =>
                $q->where(fn ($sub) =>
                    $sub->whereHas('proveedor', fn ($p) =>
                        $p->where('nombre_empresa', 'like', "%{$this->busqueda}%")
                          ->orWhere('abreviacion',  'like', "%{$this->busqueda}%")
                    )->orWhere('folio', 'like', "%{$this->busqueda}%")
                     ->orWhereHas('loteDestino', fn ($l) => $l->where('nombre', 'like', "%{$this->busqueda}%"))
                )
            )
            ->when($this->filtroProveedor, fn ($q) =>
                $q->where('proveedor_id', $this->filtroProveedor)
            )
            ->when($this->filtroFechaDesde, fn ($q) =>
                $q->where('fecha_compra', '>=', $this->filtroFechaDesde)
            )
            ->when($this->filtroFechaHasta, fn ($q) =>
                $q->where('fecha_compra', '<=', $this->filtroFechaHasta)
            )
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id')
            ->paginate(15);
    }

    #[Computed]
    public function compraDetalle(): ?CompraInventario
    {
        if (!$this->verCompraId) return null;
        return CompraInventario::with([
            'proveedor', 'registradoPor', 'almacenDestino', 'loteDestino',
            'items.producto', 'items.consumible',
        ])->deArea($this->area)->find($this->verCompraId);
    }

    #[Computed]
    public function proveedores()
    {
        return Proveedor::orderBy('nombre_empresa')->get(['id', 'nombre_empresa', 'abreviacion']);
    }

    #[Computed]
    public function lotesCompras()
    {
        return LoteCompra::where('area', $this->area)
            ->whereIn('estatus', ['PENDIENTE', 'RECIBIDO'])
            ->orderByDesc('id')
            ->get(['id', 'nombre', 'estatus']);
    }

    #[Computed]
    public function almacenes()
    {
        $user = auth()->user();

        if ($user->isAdminCeo()) {
            return Almacen::where('clave', 'like', 'VENTAS%')
                ->where('activo', true)
                ->get(['id', 'nombre', 'clave']);
        }

        $almacenIds = AlmacenEncargado::where('user_id', $user->id)
            ->where('activo', true)
            ->pluck('almacen_id');

        if ($almacenIds->isEmpty()) {
            return Almacen::where('clave', 'like', 'VENTAS%')
                ->where('activo', true)
                ->get(['id', 'nombre', 'clave']);
        }

        return Almacen::whereIn('id', $almacenIds)->where('activo', true)->get(['id', 'nombre', 'clave']);
    }

    #[Computed]
    public function catalogoProductos()
    {
        return Producto::activos()
            ->withSum('inventarios as stock_disponible', 'cantidad')
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'sku']);
    }

    #[Computed]
    public function catalogoConsumibles()
    {
        return Consumible::where('activo', true)
            ->withSum('inventarios as stock_disponible', 'cantidad')
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'unidad_medida']);
    }

    // ══════════════════════════════════════════════════════════════════
    // NAVEGACIÓN
    // ══════════════════════════════════════════════════════════════════

    public function nuevaCompra(): void
    {
        abort_unless(auth()->user()?->can('ventas.compras.gestionar'), 403);
        $this->resetFormCompra();
        $this->itemsProductos[] = $this->itemProductoVacio();
        $this->vista = 'nueva';
    }

    public function volver(): void
    {
        $this->vista       = 'lista';
        $this->verCompraId = null;
        $this->resetFormCompra();
        unset($this->compras);
    }

    public function verDetalle(int $id): void
    {
        $this->verCompraId = $id;
        unset($this->compraDetalle);
    }

    public function cerrarDetalle(): void { $this->verCompraId = null; }

    public function updatedTipoCompra(): void
    {
        if ($this->tipoCompra === 'productos') {
            $this->itemsConsumibles = [];
            if (empty($this->itemsProductos)) $this->itemsProductos[] = $this->itemProductoVacio();
        } elseif ($this->tipoCompra === 'consumibles') {
            $this->itemsProductos = [];
            if (empty($this->itemsConsumibles)) $this->itemsConsumibles[] = $this->itemConsumibleVacio();
        } else {
            // Todos
            if (empty($this->itemsProductos)) $this->itemsProductos[] = $this->itemProductoVacio();
            if (empty($this->itemsConsumibles)) $this->itemsConsumibles[] = $this->itemConsumibleVacio();
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN ÍTEMS
    // ══════════════════════════════════════════════════════════════════

    public function agregarItemProducto(): void { $this->itemsProductos[] = $this->itemProductoVacio(); }
    public function removerItemProducto(int $index): void { array_splice($this->itemsProductos, $index, 1); $this->itemsProductos = array_values($this->itemsProductos); }
    private function itemProductoVacio(): array { return ['producto_id' => '', 'cantidad' => 1, 'precio_unitario' => '', 'notas' => '']; }

    public function agregarItemConsumible(): void { $this->itemsConsumibles[] = $this->itemConsumibleVacio(); }
    public function removerItemConsumible(int $index): void { array_splice($this->itemsConsumibles, $index, 1); $this->itemsConsumibles = array_values($this->itemsConsumibles); }
    private function itemConsumibleVacio(): array { return ['consumible_id' => '', 'cantidad' => 1, 'precio_unitario' => '', 'notas' => '']; }


    // ══════════════════════════════════════════════════════════════════
    // MODALES (Lote, Proveedor)
    // ══════════════════════════════════════════════════════════════════

    // ── LOTE COMPRA ──
    public function abrirModalLoteCompra(): void
    {
        $this->loteCompraNombre = '';
        $this->loteCompraDescripcion = '';
        $this->resetErrorBag(['loteCompraNombre']);
        $this->modalLoteCompra = true;
    }
    public function cerrarModalLoteCompra(): void { $this->modalLoteCompra = false; }
    public function guardarLoteCompra(): void
    {
        $this->validate(['loteCompraNombre' => 'required|string|max:100'], ['loteCompraNombre.required' => 'El nombre del lote es obligatorio.']);
        
        $lote = LoteCompra::create([
            'nombre' => trim($this->loteCompraNombre),
            'area' => $this->area,
            'descripcion' => trim($this->loteCompraDescripcion) ?: null,
            'estatus' => 'PENDIENTE',
        ]);
        
        $this->loteCompraId = $lote->id;
        unset($this->lotesCompras);
        $this->cerrarModalLoteCompra();
        $this->dispatch('toast', ['type' => 'success', 'message' => "Lote \"{$lote->nombre}\" creado."]);
    }

    // ── PROVEEDOR ──
    public function abrirModalProveedor(): void
    {
        $this->proveedorNombre = ''; $this->proveedorAbreviacion = ''; $this->proveedorEmail = ''; $this->proveedorTelefono = '';
        $this->resetErrorBag(['proveedorNombre', 'proveedorAbreviacion']);
        $this->modalProveedor = true;
    }
    public function cerrarModalProveedor(): void { $this->modalProveedor = false; }
    public function guardarProveedor(): void
    {
        $this->validate([
            'proveedorNombre' => 'required|string|max:200', 'proveedorAbreviacion' => 'required|string|max:20'
        ], ['proveedorNombre.required' => 'Requerido.', 'proveedorAbreviacion.required' => 'Requerido.']);

        if (Proveedor::whereRaw('LOWER(TRIM(nombre_empresa)) = ?', [strtolower(trim($this->proveedorNombre))])->exists()) {
            $this->addError('proveedorNombre', 'Ya existe.'); return;
        }

        $prov = Proveedor::create([
            'nombre_empresa' => trim($this->proveedorNombre),
            'abreviacion' => strtoupper(trim($this->proveedorAbreviacion)),
            'email_contacto' => trim($this->proveedorEmail) ?: null,
            'telefono_contacto' => trim($this->proveedorTelefono) ?: null,
        ]);

        $this->proveedorId = $prov->id;
        unset($this->proveedores);
        $this->cerrarModalProveedor();
        $this->dispatch('toast', ['type' => 'success', 'message' => "Proveedor creado."]);
    }

    // ══════════════════════════════════════════════════════════════════
    // GUARDAR COMPRA
    // ══════════════════════════════════════════════════════════════════

    public function guardarCompra(): void
    {
        abort_unless(auth()->user()?->can('ventas.compras.gestionar'), 403);
        $this->error = '';

        if (!$this->almacenDestinoId) {
            $this->error = 'Selecciona el almacén destino para esta compra.'; return;
        }

        $productosValidos = [];
        if (in_array($this->tipoCompra, ['productos', 'todos'])) {
            $productosValidos = array_values(array_filter($this->itemsProductos, fn ($i) => !empty($i['producto_id'])));
        }

        $consumiblesValidos = [];
        if (in_array($this->tipoCompra, ['consumibles', 'todos'])) {
            $consumiblesValidos = array_values(array_filter($this->itemsConsumibles, fn ($i) => !empty($i['consumible_id'])));
        }

        if (empty($productosValidos) && empty($consumiblesValidos)) {
            $this->error = 'Agrega al menos un ítem válido para registrar la compra.'; return;
        }

        $this->validate([
            'proveedorId'      => 'required|exists:proveedores,id',
            'fechaCompra'      => 'required|date',
            'folio'            => 'nullable|string|max:100',
            'loteCompraId'     => 'nullable|exists:lotes_compras,id',
            'almacenDestinoId' => 'required|exists:almacenes,id',
        ], [
            'proveedorId.required'      => 'Selecciona un proveedor.',
            'fechaCompra.required'      => 'La fecha de compra es obligatoria.',
            'almacenDestinoId.required' => 'Selecciona el almacén destino.',
        ]);

        try {
            DB::transaction(function () use ($productosValidos, $consumiblesValidos) {
                $compra = CompraInventario::create([
                    'proveedor_id'       => $this->proveedorId,
                    'lote_compra_id'     => $this->loteCompraId ?: null,
                    'fecha_compra'       => $this->fechaCompra,
                    'folio'              => trim($this->folio) ?: null,
                    'total_estimado'     => $this->getTotalEstimado() ?: null,
                    'notas'              => trim($this->notasCompra) ?: null,
                    'registrado_por_id'  => Auth::id(),
                    'area'               => $this->area,
                    'almacen_destino_id' => $this->almacenDestinoId,
                ]);

                // ── Productos ──
                foreach ($productosValidos as $item) {
                    $precio   = is_numeric($item['precio_unitario'] ?? '') ? (float) $item['precio_unitario'] : null;
                    $cantidad = (int) $item['cantidad'];

                    CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'producto_id'          => $item['producto_id'],
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => $this->almacenDestinoId,
                        'notas'                => trim($item['notas'] ?? '') ?: null,
                    ]);

                    $inv = InventarioProducto::firstOrNew([
                        'producto_id' => $item['producto_id'],
                        'almacen_id'  => $this->almacenDestinoId,
                    ]);
                    $inv->cantidad = ($inv->cantidad ?? 0) + $cantidad;
                    $inv->save();
                }

                // ── Consumibles ──
                foreach ($consumiblesValidos as $item) {
                    $precio   = is_numeric($item['precio_unitario'] ?? '') ? (float) $item['precio_unitario'] : null;
                    $cantidad = (int) $item['cantidad'];

                    CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'consumible_id'        => $item['consumible_id'],
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => $this->almacenDestinoId,
                        'notas'                => trim($item['notas'] ?? '') ?: null,
                    ]);

                    $inv = InventarioConsumible::firstOrNew([
                        'consumible_id' => $item['consumible_id'],
                        'almacen_id'    => $this->almacenDestinoId,
                    ]);
                    $inv->cantidad = ($inv->cantidad ?? 0) + $cantidad;
                    $inv->save();
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

    private function resetFormCompra(): void
    {
        $this->proveedorId      = null;
        $this->loteCompraId     = null;
        $this->fechaCompra      = now()->toDateString();
        $this->folio            = '';
        $this->notasCompra      = '';
        $this->tipoCompra       = 'productos';
        $this->itemsProductos   = [];
        $this->itemsConsumibles = [];
        $this->error            = '';

        $this->preseleccionarAlmacen();
        $this->resetErrorBag();
        unset($this->compraDetalle, $this->catalogoProductos, $this->catalogoConsumibles, $this->lotesCompras);
    }

    public function getTotalEstimado(): float
    {
        $tot = 0;
        foreach ($this->itemsProductos as $i) $tot += (is_numeric($i['precio_unitario'] ?? '') ? (float) $i['precio_unitario'] : 0) * (int)($i['cantidad'] ?? 0);
        foreach ($this->itemsConsumibles as $i) $tot += (is_numeric($i['precio_unitario'] ?? '') ? (float) $i['precio_unitario'] : 0) * (int)($i['cantidad'] ?? 0);
        return $tot;
    }

    public function updatedBusqueda(): void { $this->resetPage(); }
    public function updatedFiltroProveedor(): void { $this->resetPage(); }

    public function render()
    {
        return view('livewire.ventas.compras.compras-ventas');
    }
}
