<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Almacen;
use App\Models\CatalogoPieza;
use App\Models\CompraInventario;
use App\Models\CompraInventarioItem;
use App\Models\InventarioPieza;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\SolicitudPieza;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Compras de Inventario'])]
class ComprasInventario extends Component
{
    use WithPagination;

    // ── Vista activa ──────────────────────────────────────────────────
    public string $vista = 'lista';

    // ── Filtros lista ─────────────────────────────────────────────────
    public string $busqueda = '';

    public string $filtroProveedor = '';

    // ── Form cabecera compra ──────────────────────────────────────────
    public ?int $proveedorId = null;

    public ?int $loteId = null;

    public string $fechaCompra = '';

    public string $folio = '';

    public string $notasCompra = '';

    // ── Items de la compra ────────────────────────────────────────────
    public array $items = [];

    // ── Búsqueda de pieza por ítem ────────────────────────────────────
    public array $busquedasPieza = []; // búsqueda por índice de ítem

    // ── Modal proveedor ───────────────────────────────────────────────
    public bool $modalProveedor = false;

    public string $proveedorNombre = '';

    public string $proveedorAbreviacion = '';

    public string $proveedorEmail = '';

    public string $proveedorTelefono = '';

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
            ->when($this->busqueda, fn ($q) => $q->whereHas('proveedor', fn ($p) => $p->where('nombre_empresa', 'like', "%{$this->busqueda}%")
                ->orWhere('abreviacion', 'like', "%{$this->busqueda}%")
            )->orWhere('folio', 'like', "%{$this->busqueda}%")
            )
            ->when($this->filtroProveedor, fn ($q) => $q->where('proveedor_id', $this->filtroProveedor)
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
        return Lote::orderByDesc('id')->limit(30)->get(['id', 'nombre_lote']);
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
            'cantidad' => 1,
            'precio_unitario' => '',
            'almacen_id' => Almacen::PIEZAS_PENDIENTES,
            'notas' => '',
        ];
        $this->busquedasPieza[] = '';
    }

    public function removerItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        array_splice($this->busquedasPieza, $index, 1);
        $this->items = array_values($this->items);
        $this->busquedasPieza = array_values($this->busquedasPieza);
    }

    // ══════════════════════════════════════════════════════════════════
    // GUARDAR
    // ══════════════════════════════════════════════════════════════════

    public function guardarCompra(): void
    {
        $this->error = '';
        $solicitudesMarcadas = 0;

        $this->validate([
            'proveedorId' => 'required|exists:proveedores,id',
            'fechaCompra' => 'required|date',
            'folio' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.catalogo_pieza_id' => 'required|exists:catalogo_piezas,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'nullable|numeric|min:0',
            'items.*.almacen_id' => 'required|exists:almacenes,id',
        ], [
            'proveedorId.required' => 'Selecciona un proveedor.',
            'fechaCompra.required' => 'La fecha de compra es obligatoria.',
            'items.required' => 'Agrega al menos una pieza.',
            'items.min' => 'Agrega al menos una pieza.',
            'items.*.catalogo_pieza_id.required' => 'Selecciona el tipo de pieza.',
            'items.*.cantidad.required' => 'La cantidad es obligatoria.',
            'items.*.cantidad.min' => 'La cantidad mínima es 1.',
        ]);

        try {
            DB::transaction(function () use (&$solicitudesMarcadas) {
                $compra = CompraInventario::create([
                    'proveedor_id' => $this->proveedorId,
                    'lote_id' => $this->loteId ?: null,
                    'fecha_compra' => $this->fechaCompra,
                    'folio' => trim($this->folio) ?: null,
                    'total_estimado' => $this->getTotalEstimado() ?: null,
                    'notas' => trim($this->notasCompra) ?: null,
                    'registrado_por_id' => Auth::id(),
                ]);

                foreach ($this->items as $item) {
                    $precio = $item['precio_unitario'] !== '' ? (float) $item['precio_unitario'] : null;
                    $cantidad = (int) $item['cantidad'];

                    $compraItem = CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id' => $item['catalogo_pieza_id'],
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio,
                        'almacen_id' => $item['almacen_id'],
                        'notas' => trim($item['notas']) ?: null,
                    ]);

                    InventarioPieza::create([
                        'catalogo_pieza_id' => $item['catalogo_pieza_id'],
                        'origen' => InventarioPieza::COMPRA,
                        'compra_item_id' => $compraItem->id,
                        'almacen_id' => $item['almacen_id'],
                        'costo' => $precio,
                        'registrado_por_id' => Auth::id(),
                        'estatus' => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso' => $this->fechaCompra,
                        'cantidad_inicial' => $cantidad,
                        'cantidad_disponible' => $cantidad,
                        'cantidad_reservada' => 0,
                        'cantidad_usada' => 0,
                        'cantidad_baja' => 0,
                        'notas' => trim($item['notas']) ?: null,
                    ]);

                    $solicitudesMarcadas += $this->marcarSolicitudesComoCompradas(
                        (int) $item['catalogo_pieza_id'],
                        $cantidad,
                        $compra->folio
                    );
                }
            });

            $mensaje = 'Compra registrada correctamente.';
            if ($solicitudesMarcadas > 0) {
                $mensaje .= ' Se actualizaron '.$solicitudesMarcadas.' solicitud(es) pendientes de compra.';
            }

            $this->dispatch('toast', ['type' => 'success', 'message' => $mensaje]);
            $this->volver();

        } catch (\Exception $e) {
            $this->error = 'Error al guardar: '.$e->getMessage();
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════

    private function resetForm(): void
    {
        $this->proveedorId = null;
        $this->loteId = null;
        $this->fechaCompra = now()->toDateString();
        $this->folio = '';
        $this->notasCompra = '';
        $this->items = [];
        $this->busquedasPieza = [];
        $this->error = '';
        $this->resetErrorBag();
    }

    private function marcarSolicitudesComoCompradas(int $catalogoPiezaId, int $cantidad, ?string $folio): int
    {
        if ($cantidad < 1) {
            return 0;
        }

        $solicitudes = SolicitudPieza::where('catalogo_pieza_id', $catalogoPiezaId)
            ->where('estatus', SolicitudPieza::PENDIENTE_COMPRA)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->limit($cantidad)
            ->get();

        foreach ($solicitudes as $solicitud) {
            $mensajeCompra = 'Compra registrada'.($folio ? ' (folio '.$folio.')' : '').'. Ya puedes surtirla desde inventario.';
            $nota = collect([$solicitud->notas_respuesta, $mensajeCompra])
                ->filter()
                ->implode("\n");

            $solicitud->marcarComoComprada(Auth::id(), $nota);
        }

        return $solicitudes->count();
    }

    /** Total estimado calculado de los ítems actuales. */
    public function getTotalEstimado(): float
    {
        return collect($this->items)->sum(function ($item) {
            $precio = is_numeric($item['precio_unitario']) ? (float) $item['precio_unitario'] : 0;
            $cantidad = (int) ($item['cantidad'] ?? 0);

            return $precio * $cantidad;
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // PROVEEDORES (modal inline)
    // ══════════════════════════════════════════════════════════════════

    public function abrirModalProveedor(): void
    {
        $this->proveedorNombre = '';
        $this->proveedorAbreviacion = '';
        $this->proveedorEmail = '';
        $this->proveedorTelefono = '';
        $this->resetErrorBag(['proveedorNombre', 'proveedorEmail']);
        $this->modalProveedor = true;
    }

    public function cerrarModalProveedor(): void
    {
        $this->modalProveedor = false;
    }

    public function guardarProveedor(): void
    {
        $this->validateOnly('proveedorNombre', [
            'proveedorNombre' => 'required|string|max:200',
        ], [
            'proveedorNombre.required' => 'El nombre de la empresa es obligatorio.',
        ]);

        if (! empty($this->proveedorEmail)) {
            $this->validateOnly('proveedorEmail', [
                'proveedorEmail' => 'email|max:200',
            ], [
                'proveedorEmail.email' => 'El correo no tiene un formato válido.',
            ]);
        }

        if (Proveedor::whereRaw('LOWER(TRIM(nombre_empresa)) = ?', [strtolower(trim($this->proveedorNombre))])->exists()) {
            $this->addError('proveedorNombre', 'Ya existe un proveedor con ese nombre.');

            return;
        }

        $proveedor = Proveedor::create([
            'nombre_empresa' => trim($this->proveedorNombre),
            'abreviacion' => trim($this->proveedorAbreviacion) ?: null,
            'email_contacto' => trim($this->proveedorEmail) ?: null,
            'telefono_contacto' => trim($this->proveedorTelefono) ?: null,
        ]);

        // Auto-seleccionar el nuevo proveedor en el form
        $this->proveedorId = $proveedor->id;

        unset($this->proveedores);
        $this->modalProveedor = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => "Proveedor \"{$proveedor->nombre_empresa}\" creado y seleccionado."]);
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroProveedor(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.preparacion.inventario.compras-inventario');
    }
}
