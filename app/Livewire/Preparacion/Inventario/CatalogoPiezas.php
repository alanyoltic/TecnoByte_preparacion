<?php

namespace App\Livewire\Preparacion\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\CatalogoPieza;
use App\Models\InventarioPieza;
use App\Models\CompraInventario;
use App\Models\CompraInventarioItem;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\Equipo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Catálogo de Piezas'])]
class CatalogoPiezas extends Component
{
    use WithPagination;

    // ── Vista activa ──────────────────────────────────────────────────────
    // 'lista' | 'form' | 'inventario' | 'compra' | 'deshueso'
    public string $vista = 'lista';

    // ── Filtros lista ─────────────────────────────────────────────────────
    public string $busqueda       = '';
    public string $filtroCategoria = '';
    public string $filtroStock    = '';

    // ── Form catálogo ─────────────────────────────────────────────────────
    public ?int   $editandoId          = null;
    public string $nombre              = '';
    public string $categoria           = '';
    public string $descripcion         = '';
    public string $especificacion      = '';
    public string $notasCompatibilidad = '';
    public bool   $requiereSerie       = false;
    public bool   $activo              = true;

    // ── Vista inventario (por pieza) ──────────────────────────────────────
    public ?int   $piezaInventarioId = null;
    public string $filtroEstatus     = '';

    // ── Form compra ───────────────────────────────────────────────────────
    public ?int   $compraProveedorId = null;
    public string $compraFecha       = '';
    public string $compraFolio       = '';
    public ?int   $compraLoteId      = null;
    public string $compraNotas       = '';
    public array  $compraItems       = [];

    // ── Form deshueso ─────────────────────────────────────────────────────
    public ?int   $deshuesoEquipoId      = null;
    public string $deshuesoEquipoBusqueda = '';
    public array  $deshuesoItems         = [];

    // ── Modal eliminar ────────────────────────────────────────────────────
    public bool   $modalEliminar = false;
    public ?int   $eliminandoId  = null;
    public string $tipoEliminar  = '';

    // ── Error ─────────────────────────────────────────────────────────────
    public string $error = '';

    const CATEGORIAS = [
        'RAM', 'SSD', 'HDD', 'Batería', 'Pantalla',
        'Teclado', 'Carcasa', 'Palmrest', 'Bisagra',
        'Cargador', 'Placa Base', 'Ventilador', 'Otro',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.gestion'), 403);
        $this->compraFecha = now()->toDateString();
    }

    // ══════════════════════════════════════════════════════════════════════
    // COMPUTED
    // ══════════════════════════════════════════════════════════════════════

    #[Computed]
    public function piezas()
    {
        return CatalogoPieza::query()
            ->when($this->busqueda, fn($q) =>
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$this->busqueda}%")
                  ->orWhere('especificacion', 'like', "%{$this->busqueda}%")
            )
            ->when($this->filtroCategoria, fn($q) =>
                $q->where('categoria', $this->filtroCategoria)
            )
            ->when($this->filtroStock === 'con_stock', fn($q) =>
                $q->whereHas('inventarioDisponible')
            )
            ->when($this->filtroStock === 'sin_stock', fn($q) =>
                $q->whereDoesntHave('inventarioDisponible')
            )
            ->withSum(['inventario as stock_disponible' => fn($q) =>
                $q->where('cantidad_disponible', '>', 0)
            ], 'cantidad_disponible')
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->paginate(15);
    }

    #[Computed]
    public function piezaActual(): ?CatalogoPieza
    {
        if (!$this->piezaInventarioId) return null;
        return CatalogoPieza::withSum(['inventario as stock_disponible' => fn($q) =>
            $q->where('cantidad_disponible', '>', 0)
        ], 'cantidad_disponible')->find($this->piezaInventarioId);
    }

    #[Computed]
    public function inventarioItems()
    {
        if (!$this->piezaInventarioId) return collect();
        return InventarioPieza::with(['registradoPor', 'equipoOrigen', 'compraItem.compra.proveedor'])
            ->where('catalogo_pieza_id', $this->piezaInventarioId)
            ->when($this->filtroEstatus, fn($q) => $q->where('estatus', $this->filtroEstatus))
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function piezasSimilares()
    {
        if (strlen(trim($this->nombre)) < 3) return collect();
        return CatalogoPieza::where('nombre', 'like', '%' . trim($this->nombre) . '%')
            ->when($this->editandoId, fn($q) => $q->where('id', '!=', $this->editandoId))
            ->limit(5)
            ->get(['id', 'nombre', 'categoria', 'especificacion']);
    }

    #[Computed]
    public function equiposBusqueda()
    {
        if (strlen(trim($this->deshuesoEquipoBusqueda)) < 2) return collect();
        return Equipo::where('numero_serie', 'like', '%' . trim($this->deshuesoEquipoBusqueda) . '%')
            ->orWhere('modelo', 'like', '%' . trim($this->deshuesoEquipoBusqueda) . '%')
            ->limit(8)
            ->get(['id', 'numero_serie', 'marca', 'modelo']);
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
    public function catalogo()
    {
        return CatalogoPieza::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'especificacion', 'requiere_serie']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // NAVEGACIÓN
    // ══════════════════════════════════════════════════════════════════════

    public function nuevaPieza(): void
    {
        $this->resetFormCatalogo();
        $this->vista = 'form';
    }

    public function editarPieza(int $id): void
    {
        $pieza = CatalogoPieza::findOrFail($id);
        $this->editandoId          = $pieza->id;
        $this->nombre              = $pieza->nombre;
        $this->categoria           = $pieza->categoria ?? '';
        $this->descripcion         = $pieza->descripcion ?? '';
        $this->especificacion      = $pieza->especificacion ?? '';
        $this->notasCompatibilidad = $pieza->notas_compatibilidad ?? '';
        $this->requiereSerie       = (bool) $pieza->requiere_serie;
        $this->activo              = (bool) $pieza->activo;
        $this->error               = '';
        $this->vista               = 'form';
        unset($this->piezasSimilares);
    }

    public function verInventario(int $id): void
    {
        $this->piezaInventarioId = $id;
        $this->filtroEstatus     = '';
        $this->error             = '';
        $this->vista             = 'inventario';
        unset($this->piezaActual, $this->inventarioItems);
    }

    public function irACompra(?int $piezaPreId = null): void
    {
        $this->resetFormCompra();
        if ($piezaPreId) {
            $this->compraItems[] = [
                'catalogo_pieza_id' => $piezaPreId,
                'cantidad'          => 1,
                'precio_unitario'   => '',
                'notas'             => '',
            ];
        } else {
            $this->compraItems[] = $this->itemCompraVacio();
        }
        $this->vista = 'compra';
    }

    public function irADeshueso(?int $piezaPreId = null): void
    {
        $this->resetFormDeshueso();
        $this->deshuesoItems[] = [
            'catalogo_pieza_id' => $piezaPreId ?? '',
            'cantidad'          => 1,
            'numero_serie'      => '',
            'notas'             => '',
        ];
        $this->vista = 'deshueso';
    }

    public function volverALista(): void
    {
        $this->vista             = 'lista';
        $this->piezaInventarioId = null;
        $this->error             = '';
        $this->resetFormCatalogo();
        $this->resetFormCompra();
        $this->resetFormDeshueso();
        unset($this->piezas);
    }

    // ══════════════════════════════════════════════════════════════════════
    // CRUD CATÁLOGO
    // ══════════════════════════════════════════════════════════════════════

    public function guardarPieza(): void
    {
        $this->error = '';
        $this->validate([
            'nombre'    => 'required|string|max:255',
            'categoria' => 'required|string',
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'categoria.required' => 'Selecciona una categoría.',
        ]);

        $data = [
            'nombre'               => trim($this->nombre),
            'categoria'            => $this->categoria,
            'descripcion'          => trim($this->descripcion) ?: null,
            'especificacion'       => trim($this->especificacion) ?: null,
            'notas_compatibilidad' => trim($this->notasCompatibilidad) ?: null,
            'requiere_serie'       => $this->requiereSerie,
            'activo'               => $this->activo,
        ];

        if ($this->editandoId) {
            CatalogoPieza::findOrFail($this->editandoId)->update($data);
            $msg = 'Pieza actualizada correctamente.';
        } else {
            CatalogoPieza::create($data);
            $msg = 'Pieza agregada al catálogo.';
        }

        $this->dispatch('toast', ['type' => 'success', 'message' => $msg]);
        $this->volverALista();
    }

    public function toggleActivo(int $id): void
    {
        $pieza = CatalogoPieza::findOrFail($id);
        $pieza->update(['activo' => !$pieza->activo]);
        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => $pieza->activo ? 'Pieza activada.' : 'Pieza desactivada.',
        ]);
        unset($this->piezas);
    }

    public function abrirEliminarCatalogo(int $id): void
    {
        $this->eliminandoId  = $id;
        $this->tipoEliminar  = 'catalogo';
        $this->modalEliminar = true;
    }

    public function abrirEliminarInventario(int $id): void
    {
        $this->eliminandoId  = $id;
        $this->tipoEliminar  = 'inventario';
        $this->modalEliminar = true;
    }

    public function confirmarEliminar(): void
    {
        if ($this->tipoEliminar === 'catalogo') {
            CatalogoPieza::findOrFail($this->eliminandoId)->delete();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pieza eliminada del catálogo.']);
            unset($this->piezas);
        } elseif ($this->tipoEliminar === 'inventario') {
            InventarioPieza::findOrFail($this->eliminandoId)->delete();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Entrada de inventario eliminada.']);
            unset($this->inventarioItems, $this->piezaActual);
        }
        $this->modalEliminar = false;
        $this->eliminandoId  = null;
        $this->tipoEliminar  = '';
    }

    public function cerrarModalEliminar(): void
    {
        $this->modalEliminar = false;
        $this->eliminandoId  = null;
        $this->tipoEliminar  = '';
    }

    public function cambiarEstatusInventario(int $id, string $estatus): void
    {
        $item = InventarioPieza::findOrFail($id);
        if ($estatus === InventarioPieza::DADA_DE_BAJA) {
            $item->darDeBaja();
        } else {
            $item->update(['estatus' => $estatus]);
        }
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Estatus actualizado.']);
        unset($this->inventarioItems, $this->piezaActual);
    }

    // ══════════════════════════════════════════════════════════════════════
    // FORM COMPRA
    // ══════════════════════════════════════════════════════════════════════

    public function agregarItemCompra(): void
    {
        $this->compraItems[] = $this->itemCompraVacio();
    }

    public function removerItemCompra(int $index): void
    {
        array_splice($this->compraItems, $index, 1);
        $this->compraItems = array_values($this->compraItems);
    }

    public function getTotalCompra(): float
    {
        return collect($this->compraItems)->sum(function ($item) {
            $precio   = is_numeric($item['precio_unitario'] ?? '') ? (float) $item['precio_unitario'] : 0;
            $cantidad = (int) ($item['cantidad'] ?? 0);
            return $precio * $cantidad;
        });
    }

    public function guardarCompra(): void
    {
        $this->error = '';
        $this->validate([
            'compraProveedorId'                 => 'required|exists:proveedores,id',
            'compraFecha'                       => 'required|date',
            'compraFolio'                       => 'nullable|string|max:100',
            'compraItems'                       => 'required|array|min:1',
            'compraItems.*.catalogo_pieza_id'   => 'required|exists:catalogo_piezas,id',
            'compraItems.*.cantidad'            => 'required|integer|min:1',
            'compraItems.*.precio_unitario'     => 'nullable|numeric|min:0',
        ], [
            'compraProveedorId.required'              => 'Selecciona un proveedor.',
            'compraFecha.required'                    => 'La fecha es obligatoria.',
            'compraItems.min'                         => 'Agrega al menos una pieza.',
            'compraItems.*.catalogo_pieza_id.required'=> 'Selecciona el tipo de pieza.',
            'compraItems.*.cantidad.min'              => 'La cantidad mínima es 1.',
        ]);

        try {
            DB::transaction(function () {
                $compra = CompraInventario::create([
                    'proveedor_id'      => $this->compraProveedorId,
                    'lote_id'           => $this->compraLoteId ?: null,
                    'fecha_compra'      => $this->compraFecha,
                    'folio'             => trim($this->compraFolio) ?: null,
                    'notas'             => trim($this->compraNotas) ?: null,
                    'registrado_por_id' => Auth::id(),
                ]);

                foreach ($this->compraItems as $item) {
                    $precio   = is_numeric($item['precio_unitario'] ?? '') ? (float) $item['precio_unitario'] : null;
                    $cantidad = (int) $item['cantidad'];

                    $compraItem = CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id'    => $item['catalogo_pieza_id'],
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => 7,
                        'notas'                => trim($item['notas'] ?? '') ?: null,
                    ]);

                    InventarioPieza::create([
                        'catalogo_pieza_id'   => $item['catalogo_pieza_id'],
                        'origen'              => InventarioPieza::COMPRA,
                        'compra_item_id'      => $compraItem->id,
                        'almacen_id'          => 7,
                        'costo'               => $precio,
                        'registrado_por_id'   => Auth::id(),
                        'estatus'             => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'       => $this->compraFecha,
                        'notas'               => trim($item['notas'] ?? '') ?: null,
                        'cantidad_inicial'    => $cantidad,
                        'cantidad_disponible' => $cantidad,
                        'cantidad_reservada'  => 0,
                        'cantidad_usada'      => 0,
                        'cantidad_baja'       => 0,
                    ]);
                }
            });

            $total = count($this->compraItems);
            $this->dispatch('toast', ['type' => 'success', 'message' => "Compra registrada ({$total} tipo(s) de pieza)."]);
            $this->volverALista();

        } catch (\Exception $e) {
            $this->error = 'Error al guardar: ' . $e->getMessage();
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // FORM DESHUESO
    // ══════════════════════════════════════════════════════════════════════

    public function seleccionarEquipoDeshueso(int $id): void
    {
        $equipo = Equipo::find($id);
        if ($equipo) {
            $this->deshuesoEquipoId      = $id;
            $this->deshuesoEquipoBusqueda = "{$equipo->numero_serie} — {$equipo->marca} {$equipo->modelo}";
        }
        unset($this->equiposBusqueda);
    }

    public function agregarItemDeshueso(): void
    {
        $this->deshuesoItems[] = [
            'catalogo_pieza_id' => '',
            'cantidad'          => 1,
            'numero_serie'      => '',
            'notas'             => '',
        ];
    }

    public function removerItemDeshueso(int $index): void
    {
        array_splice($this->deshuesoItems, $index, 1);
        $this->deshuesoItems = array_values($this->deshuesoItems);
    }

    public function guardarDeshueso(): void
    {
        $this->error = '';

        if (!$this->deshuesoEquipoId) {
            $this->error = 'Selecciona el equipo del que se extraen las piezas.';
            return;
        }

        $this->validate([
            'deshuesoItems'                       => 'required|array|min:1',
            'deshuesoItems.*.catalogo_pieza_id'   => 'required|exists:catalogo_piezas,id',
            'deshuesoItems.*.cantidad'            => 'required|integer|min:1',
        ], [
            'deshuesoItems.min'                         => 'Agrega al menos una pieza.',
            'deshuesoItems.*.catalogo_pieza_id.required'=> 'Selecciona el tipo de pieza.',
            'deshuesoItems.*.cantidad.min'              => 'La cantidad mínima es 1.',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->deshuesoItems as $item) {
                    $pieza    = CatalogoPieza::findOrFail($item['catalogo_pieza_id']);
                    $cantidad = (int) $item['cantidad'];
                    $serie    = trim($item['numero_serie'] ?? '') ?: null;

                    if ($pieza->requiere_serie && $cantidad > 1) {
                        throw new \Exception("La pieza \"{$pieza->nombre}\" requiere serie — agrega 1 a la vez.");
                    }

                    InventarioPieza::create([
                        'catalogo_pieza_id'   => $item['catalogo_pieza_id'],
                        'origen'              => InventarioPieza::DESHUESO,
                        'equipo_origen_id'    => $this->deshuesoEquipoId,
                        'almacen_id'          => 7,
                        'numero_serie'        => $pieza->requiere_serie ? $serie : null,
                        'registrado_por_id'   => Auth::id(),
                        'estatus'             => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'       => now()->toDateString(),
                        'notas'               => trim($item['notas'] ?? '') ?: null,
                        'cantidad_inicial'    => $cantidad,
                        'cantidad_disponible' => $cantidad,
                        'cantidad_reservada'  => 0,
                        'cantidad_usada'      => 0,
                        'cantidad_baja'       => 0,
                    ]);
                }
            });

            $total = count($this->deshuesoItems);
            $this->dispatch('toast', ['type' => 'success', 'message' => "{$total} pieza(s) de deshueso registrada(s)."]);
            $this->volverALista();

        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ══════════════════════════════════════════════════════════════════════

    private function itemCompraVacio(): array
    {
        return ['catalogo_pieza_id' => '', 'cantidad' => 1, 'precio_unitario' => '', 'notas' => ''];
    }

    private function resetFormCatalogo(): void
    {
        $this->editandoId          = null;
        $this->nombre              = '';
        $this->categoria           = '';
        $this->descripcion         = '';
        $this->especificacion      = '';
        $this->notasCompatibilidad = '';
        $this->requiereSerie       = false;
        $this->activo              = true;
        $this->error               = '';
        $this->resetErrorBag();
        unset($this->piezasSimilares);
    }

    private function resetFormCompra(): void
    {
        $this->compraProveedorId = null;
        $this->compraFecha       = now()->toDateString();
        $this->compraFolio       = '';
        $this->compraLoteId      = null;
        $this->compraNotas       = '';
        $this->compraItems       = [];
        $this->error             = '';
        $this->resetErrorBag();
    }

    private function resetFormDeshueso(): void
    {
        $this->deshuesoEquipoId       = null;
        $this->deshuesoEquipoBusqueda = '';
        $this->deshuesoItems          = [];
        $this->error                  = '';
        $this->resetErrorBag();
        unset($this->equiposBusqueda);
    }

    public function updatedBusqueda(): void { $this->resetPage(); }
    public function updatedFiltroCategoria(): void { $this->resetPage(); }
    public function updatedFiltroStock(): void { $this->resetPage(); }
    public function updatedNombre(): void { unset($this->piezasSimilares); }
    public function updatedDeshuesoEquipoBusqueda(): void { unset($this->equiposBusqueda); }

    public function render()
    {
        return view('livewire.preparacion.inventario.catalogo-piezas');
    }
}
