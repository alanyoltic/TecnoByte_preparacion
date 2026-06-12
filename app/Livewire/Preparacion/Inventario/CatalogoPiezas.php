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
use App\Models\Almacen;
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
    public ?int   $compraProveedorId    = null;
    public string $compraFecha          = '';
    public string $compraFechaRecibido  = ''; // puede ser igual a compraFecha
    public string $compraFolio          = '';
    public ?int   $compraLoteId         = null;
    public string $compraNotas          = '';
    public array  $compraItems          = [];
    public ?int   $compraAlmacenId      = null;

    // ── Form deshueso ─────────────────────────────────────────────────────
    public ?int   $deshuesoEquipoId       = null;
    public string $deshuesoEquipoBusqueda = '';
    public array  $deshuesoItems          = []; // piezas extraídas (nombre libre + categoría)
    public array  $deshuesoRecuperar      = []; // IDs de InventarioPieza a recuperar del equipo
    public int    $deshuesoAlmacenId      = 7;  // almacén destino (default: Piezas Pendientes)

    // ── Modal eliminar ────────────────────────────────────────────────────
    public bool   $modalEliminar = false;
    public ?int   $eliminandoId  = null;
    public string $tipoEliminar  = '';
    public bool   $puedeBorrarFisicamente = false;

    // ── Modal proveedor ───────────────────────────────────────────────────
    public bool   $modalProveedor       = false;
    public string $proveedorNombre      = '';
    public string $proveedorAbreviacion = '';
    public string $proveedorEmail       = '';
    public string $proveedorTelefono    = '';

    // ── Modal lote ────────────────────────────────────────────────────────
    public bool   $modalLote        = false;
    public string $loteNombre       = '';
    public string $loteFechaLlegada = '';

    // ── Modal categoría ───────────────────────────────────────────────────
    public bool   $modalCategoria       = false;
    public string $nuevaCategoriaNombre = '';
    public int    $categoriaItemIndex   = 0;

    // ── Restock (agregar stock a pieza existente) ─────────────────────────
    public ?int   $restockPiezaId           = null;
    public string $restockOrigen            = ''; // '' | 'compra' | 'deshueso'
    public ?int   $restockCompraProveedorId = null;
    public string $restockCompraFecha       = '';
    public string $restockCompraFolio       = '';
    public ?int   $restockAlmacenId         = null;
    public int    $restockCantidad          = 1;
    public string $restockPrecio            = '';
    public string $restockNotas             = '';

    // ── Error ─────────────────────────────────────────────────────────────
    public string $error = '';

    const CATEGORIAS = [
        'RAM', 'SSD', 'HDD', 'Batería', 'Pantalla',
        'Teclado', 'Carcasa', 'Palmrest', 'Bisagra',
        'Cargador', 'Placa Base', 'Ventilador', 'Otro',
    ];

    // Sólo estos almacenes son válidos para recibir piezas
    const ALMACENES_PIEZAS = ['PREPARACION', 'PIEZAS_PEND'];

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
        $busqueda = trim($this->deshuesoEquipoBusqueda);
        return Equipo::where('estatus_ciclo', Equipo::CICLO_SCRAP)
            ->where(fn ($q) => $q
                ->where('numero_serie', 'like', "%{$busqueda}%")
                ->orWhere('modelo',      'like', "%{$busqueda}%")
            )
            ->limit(8)
            ->get(['id', 'numero_serie', 'marca', 'modelo']);
    }

    /** Piezas del inventario que están actualmente aplicadas al equipo seleccionado. */
    #[Computed]
    public function piezasEquipoOrigen()
    {
        if (!$this->deshuesoEquipoId) return collect();
        return InventarioPieza::with(['catalogoPieza', 'almacen'])
            ->where('equipo_destino_id', $this->deshuesoEquipoId)
            ->where('estatus', '!=', InventarioPieza::DADA_DE_BAJA)
            ->get();
    }

    #[Computed]
    public function almacenes()
    {
        return \App\Models\Almacen::whereIn('clave', self::ALMACENES_PIEZAS)
            ->where('activo', true)
            ->orderByRaw("FIELD(clave, 'PIEZAS_PEND', 'PREPARACION')")
            ->get(['id', 'nombre']);
    }

    #[Computed]
    public function restockPieza(): ?CatalogoPieza
    {
        if (!$this->restockPiezaId) return null;
        return CatalogoPieza::find($this->restockPiezaId);
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

    public function irACompra(): void
    {
        $this->resetFormCompra();
        $this->compraItems[] = $this->itemCompraVacio();
        $this->vista = 'compra';
    }

    public function irADeshueso(): void
    {
        $this->resetFormDeshueso();
        $this->deshuesoItems[] = ['nombre' => '', 'categoria' => '', 'catalogo_pieza_id_sel' => null, 'cantidad' => 1, 'notas' => ''];
        $this->vista = 'deshueso';
    }

    public function iniciarRestock(int $piezaId): void
    {
        $this->restockPiezaId           = $piezaId;
        $this->restockOrigen            = '';
        $this->restockCompraProveedorId = null;
        $this->restockCompraFecha       = now()->toDateString();
        $this->restockCompraFolio       = '';
        $this->restockAlmacenId         = 7; // Piezas Pendientes por defecto
        $this->restockCantidad          = 1;
        $this->restockPrecio            = '';
        $this->restockNotas             = '';
        $this->deshuesoEquipoId         = null;
        $this->deshuesoEquipoBusqueda   = '';
        $this->error                    = '';
        $this->resetErrorBag();
        $this->vista = 'restock';
        unset($this->restockPieza, $this->equiposBusqueda, $this->piezasEquipoOrigen);
    }

    public function seleccionarRestockOrigen(string $origen): void
    {
        $this->restockOrigen = in_array($origen, ['compra', 'deshueso']) ? $origen : '';
        $this->error         = '';
        $this->resetErrorBag();
    }

    public function volverALista(): void
    {
        $this->vista             = 'lista';
        $this->piezaInventarioId = null;
        $this->restockPiezaId    = null;
        $this->restockOrigen     = '';
        $this->error             = '';
        $this->resetFormCatalogo();
        $this->resetFormCompra();
        $this->resetFormDeshueso();
        unset($this->piezas, $this->restockPieza);
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

        // Verificar duplicado exacto (nombre + categoría, sin importar mayúsculas)
        $existe = CatalogoPieza::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($this->nombre))])
            ->where('categoria', $this->categoria)
            ->when($this->editandoId, fn($q) => $q->where('id', '!=', $this->editandoId))
            ->exists();

        if ($existe) {
            $this->addError('nombre', 'Ya existe una pieza con el mismo nombre en esa categoría.');
            return;
        }

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
        $pieza = CatalogoPieza::findOrFail($id);
        $this->puedeBorrarFisicamente = $pieza->sePuedeBorrarFisicamente();
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
            $pieza = CatalogoPieza::findOrFail($this->eliminandoId);
            
            if ($pieza->sePuedeBorrarFisicamente()) {
                // Borrado físico
                $inventarios = $pieza->inventario()->get();
                $compras = CompraInventarioItem::where('catalogo_pieza_id', $pieza->id)->get();
                
                $snapshot = [
                    'pieza' => $pieza->toArray(),
                    'inventario_piezas' => $inventarios->toArray(),
                    'compras_inventario_items' => $compras->toArray(),
                ];
                
                DB::table('catalogo_piezas_eliminaciones')->insert([
                    'catalogo_pieza_id' => $pieza->id,
                    'nombre' => $pieza->nombre,
                    'categoria' => $pieza->categoria,
                    'motivo' => 'Borrado Físico (sin uso)',
                    'user_id' => auth()->id(),
                    'snapshot' => json_encode($snapshot),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Borrar relaciones físicas
                InventarioPieza::where('catalogo_pieza_id', $pieza->id)->delete();
                CompraInventarioItem::where('catalogo_pieza_id', $pieza->id)->delete();
                $pieza->forceDelete();
                
                $this->dispatch('toast', ['type' => 'success', 'message' => 'Pieza y su historial eliminados físicamente del sistema.']);
            } else {
                // Borrado Lógico
                DB::table('catalogo_piezas_eliminaciones')->insert([
                    'catalogo_pieza_id' => $pieza->id,
                    'nombre' => $pieza->nombre,
                    'categoria' => $pieza->categoria,
                    'motivo' => 'Borrado Lógico (Soft Delete)',
                    'user_id' => auth()->id(),
                    'snapshot' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $pieza->delete();
                $this->dispatch('toast', ['type' => 'info', 'message' => 'Pieza ocultada del catálogo (se conservó su historial).']);
            }
            unset($this->piezas);
        } elseif ($this->tipoEliminar === 'inventario') {
            InventarioPieza::findOrFail($this->eliminandoId)->delete();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Entrada de inventario eliminada.']);
            unset($this->inventarioItems, $this->piezaActual);
        }
        $this->modalEliminar = false;
        $this->eliminandoId  = null;
        $this->tipoEliminar  = '';
        $this->puedeBorrarFisicamente = false;
    }

    public function cerrarModalEliminar(): void
    {
        $this->modalEliminar = false;
        $this->eliminandoId  = null;
        $this->tipoEliminar  = '';
        $this->puedeBorrarFisicamente = false;
    }

    // ══════════════════════════════════════════════════════════════════════
    // PROVEEDORES (modal inline)
    // ══════════════════════════════════════════════════════════════════════

    public function abrirModalProveedor(): void
    {
        $this->proveedorNombre      = '';
        $this->proveedorAbreviacion = '';
        $this->proveedorEmail       = '';
        $this->proveedorTelefono    = '';
        $this->resetErrorBag(['proveedorNombre', 'proveedorEmail', 'proveedorTelefono']);
        $this->modalProveedor = true;
    }

    public function cerrarModalProveedor(): void
    {
        $this->modalProveedor = false;
    }

    // ══════════════════════════════════════════════════════════════════════
    // MODAL LOTE (crear lote de compra inline)
    // ══════════════════════════════════════════════════════════════════════

    public function abrirModalLote(): void
    {
        if (!$this->compraProveedorId) {
            $this->addError('compraProveedorId', 'Selecciona primero un proveedor para crear el lote.');
            return;
        }
        $this->loteNombre       = '';
        $this->loteFechaLlegada = now()->toDateString();
        $this->resetErrorBag(['loteNombre', 'loteFechaLlegada']);
        $this->modalLote = true;
    }

    public function cerrarModalLote(): void
    {
        $this->modalLote = false;
    }

    public function guardarLote(): void
    {
        $this->validateOnly('loteNombre', [
            'loteNombre' => 'required|string|max:255',
        ], [
            'loteNombre.required' => 'El nombre del lote es obligatorio.',
        ]);

        $lote = Lote::create([
            'nombre_lote'   => trim($this->loteNombre),
            'proveedor_id'  => $this->compraProveedorId,
            'fecha_llegada' => $this->loteFechaLlegada ?: null,
        ]);

        $this->compraLoteId = $lote->id;
        unset($this->lotes);
        $this->modalLote = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => "Lote \"{$lote->nombre_lote}\" creado y seleccionado."]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // MODAL CATEGORÍA (categoría personalizada por ítem)
    // ══════════════════════════════════════════════════════════════════════

    public function abrirModalCategoria(int $index): void
    {
        $this->categoriaItemIndex   = $index;
        $this->nuevaCategoriaNombre = '';
        $this->resetErrorBag(['nuevaCategoriaNombre']);
        $this->modalCategoria = true;
    }

    public function cerrarModalCategoria(): void
    {
        $this->modalCategoria = false;
    }

    public function aplicarNuevaCategoria(): void
    {
        $this->validateOnly('nuevaCategoriaNombre', [
            'nuevaCategoriaNombre' => 'required|string|max:50',
        ], [
            'nuevaCategoriaNombre.required' => 'El nombre de la categoría es obligatorio.',
            'nuevaCategoriaNombre.max'      => 'Máximo 50 caracteres.',
        ]);

        $nombre = ucfirst(trim($this->nuevaCategoriaNombre));
        $this->compraItems[$this->categoriaItemIndex]['categoria'] = $nombre;
        $this->modalCategoria = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => "Categoría \"{$nombre}\" aplicada al ítem."]);
    }

    public function guardarProveedor(): void
    {
        $this->validate([
            'proveedorNombre'      => 'required|string|max:200',
            'proveedorAbreviacion' => 'required|string|max:20',
            'proveedorEmail'       => 'nullable|email|max:200',
        ], [
            'proveedorNombre.required'      => 'El nombre de la empresa es obligatorio.',
            'proveedorAbreviacion.required' => 'La abreviación es obligatoria.',
            'proveedorAbreviacion.max'      => 'Máximo 20 caracteres.',
            'proveedorEmail.email'          => 'El correo no tiene un formato válido.',
        ]);

        // Verificar duplicado de nombre
        if (Proveedor::whereRaw('LOWER(TRIM(nombre_empresa)) = ?', [strtolower(trim($this->proveedorNombre))])->exists()) {
            $this->addError('proveedorNombre', 'Ya existe un proveedor con ese nombre.');
            return;
        }

        // Verificar duplicado de abreviación
        if (Proveedor::whereRaw('LOWER(TRIM(abreviacion)) = ?', [strtolower(trim($this->proveedorAbreviacion))])->exists()) {
            $this->addError('proveedorAbreviacion', 'Ya existe un proveedor con esa abreviación.');
            return;
        }

        $proveedor = Proveedor::create([
            'nombre_empresa'    => trim($this->proveedorNombre),
            'abreviacion'       => trim($this->proveedorAbreviacion),
            'email_contacto'    => trim($this->proveedorEmail) ?: null,
            'telefono_contacto' => trim($this->proveedorTelefono) ?: null,
        ]);

        // Auto-seleccionar el nuevo proveedor en el form de compra
        $this->compraProveedorId = $proveedor->id;

        unset($this->proveedores);
        $this->modalProveedor = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => "Proveedor \"{$proveedor->nombre_empresa}\" creado y seleccionado."]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // RESTOCK
    // ══════════════════════════════════════════════════════════════════════

    public function guardarRestock(): void
    {
        $this->error = '';

        if (!$this->restockOrigen) {
            $this->error = 'Selecciona el origen del restock (compra o despiece).';
            return;
        }

        $pieza = CatalogoPieza::findOrFail($this->restockPiezaId);

        if ($this->restockOrigen === 'compra') {
            $this->validate([
                'restockCompraProveedorId' => 'required|exists:proveedores,id',
                'restockCompraFecha'       => 'required|date',
                'restockAlmacenId'         => 'required|exists:almacenes,id',
                'restockCantidad'          => 'required|integer|min:1',
                'restockPrecio'            => 'nullable|numeric|min:0',
            ], [
                'restockCompraProveedorId.required' => 'Selecciona un proveedor.',
                'restockCompraFecha.required'       => 'La fecha es obligatoria.',
                'restockAlmacenId.required'         => 'Selecciona el almacén destino.',
                'restockCantidad.min'               => 'La cantidad mínima es 1.',
            ]);

            try {
                DB::transaction(function () use ($pieza) {
                    $precio = is_numeric($this->restockPrecio) ? (float) $this->restockPrecio : null;

                    $compra = CompraInventario::create([
                        'proveedor_id'      => $this->restockCompraProveedorId,
                        'fecha_compra'      => $this->restockCompraFecha,
                        'folio'             => trim($this->restockCompraFolio) ?: null,
                        'notas'             => trim($this->restockNotas) ?: null,
                        'registrado_por_id' => Auth::id(),
                    ]);

                    $compraItem = CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id'    => $pieza->id,
                        'cantidad'             => $this->restockCantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => $this->restockAlmacenId,
                        'notas'                => trim($this->restockNotas) ?: null,
                    ]);

                    $this->consolidarOCrearInventario($pieza, $this->restockCantidad, [
                        'origen'             => InventarioPieza::COMPRA,
                        'compra_item_id'     => $compraItem->id,
                        'almacen_id'         => $this->restockAlmacenId,
                        'costo'              => $precio,
                        'registrado_por_id'  => Auth::id(),
                        'estatus'            => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'      => $this->restockCompraFecha,
                        'notas'              => trim($this->restockNotas) ?: null,
                    ]);
                });

                $this->dispatch('toast', ['type' => 'success', 'message' => "Stock agregado: {$this->restockCantidad} × {$pieza->nombre}."]);
                $this->volverALista();
            } catch (\Exception $e) {
                $this->error = 'Error al guardar: ' . $e->getMessage();
            }

        } elseif ($this->restockOrigen === 'deshueso') {
            $this->validate([
                'deshuesoEquipoId' => 'required|exists:equipos,id',
                'restockAlmacenId' => 'required|exists:almacenes,id',
                'restockCantidad'  => 'required|integer|min:1',
            ], [
                'deshuesoEquipoId.required' => 'Selecciona el equipo origen.',
                'restockAlmacenId.required' => 'Selecciona el almacén destino.',
                'restockCantidad.min'       => 'La cantidad mínima es 1.',
            ]);

            try {
                DB::transaction(function () use ($pieza) {
                    InventarioPieza::create([
                        'catalogo_pieza_id'   => $pieza->id,
                        'origen'              => InventarioPieza::DESHUESO,
                        'equipo_origen_id'    => $this->deshuesoEquipoId,
                        'almacen_id'          => $this->restockAlmacenId,
                        'registrado_por_id'   => Auth::id(),
                        'estatus'             => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'       => now()->toDateString(),
                        'notas'               => trim($this->restockNotas) ?: null,
                        'cantidad_inicial'    => $this->restockCantidad,
                        'cantidad_disponible' => $this->restockCantidad,
                        'cantidad_reservada'  => 0,
                        'cantidad_usada'      => 0,
                        'cantidad_baja'       => 0,
                    ]);
                });

                $this->dispatch('toast', ['type' => 'success', 'message' => "Stock agregado: {$this->restockCantidad} × {$pieza->nombre}."]);
                $this->volverALista();
            } catch (\Exception $e) {
                $this->error = 'Error al guardar: ' . $e->getMessage();
            }
        }
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
            'compraProveedorId'              => 'required|exists:proveedores,id',
            'compraFecha'                    => 'required|date',
            'compraFechaRecibido'            => 'nullable|date',
            'compraAlmacenId'                => 'required|exists:almacenes,id',
            'compraFolio'                    => 'nullable|string|max:100',
            'compraItems'                    => 'required|array|min:1',
            'compraItems.*.nombre'           => 'required|string|max:255',
            'compraItems.*.categoria'        => 'required|string',
            'compraItems.*.cantidad'         => 'required|integer|min:1',
            'compraItems.*.precio_unitario'  => 'nullable|numeric|min:0',
        ], [
            'compraProveedorId.required'         => 'Selecciona un proveedor.',
            'compraFecha.required'               => 'La fecha de compra es obligatoria.',
            'compraAlmacenId.required'           => 'Selecciona el almacén destino.',
            'compraItems.min'                    => 'Agrega al menos una pieza.',
            'compraItems.*.nombre.required'      => 'Escribe el nombre de la pieza.',
            'compraItems.*.categoria.required'   => 'Selecciona la categoría.',
            'compraItems.*.cantidad.min'         => 'La cantidad mínima es 1.',
        ]);

        try {
            DB::transaction(function () {
                $fechaRecibido = $this->compraFechaRecibido ?: $this->compraFecha;

                $compra = CompraInventario::create([
                    'proveedor_id'      => $this->compraProveedorId,
                    'lote_id'           => $this->compraLoteId ?: null,
                    'fecha_compra'      => $this->compraFecha,
                    'folio'             => trim($this->compraFolio) ?: null,
                    'notas'             => trim($this->compraNotas) ?: null,
                    'registrado_por_id' => Auth::id(),
                ]);

                foreach ($this->compraItems as $item) {
                    $precio    = is_numeric($item['precio_unitario'] ?? '') ? (float) $item['precio_unitario'] : null;
                    $cantidad  = (int) $item['cantidad'];
                    $catalogo  = $this->encontrarOCrearCatalogo($item['nombre'], $item['categoria'], $item['catalogo_pieza_id_sel'] ?? null);

                    $compraItem = CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id'    => $catalogo->id,
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => $this->compraAlmacenId,
                        'notas'                => trim($item['notas'] ?? '') ?: null,
                    ]);

                    $this->consolidarOCrearInventario($catalogo, $cantidad, [
                        'origen'             => InventarioPieza::COMPRA,
                        'compra_item_id'     => $compraItem->id,
                        'almacen_id'         => $this->compraAlmacenId,
                        'costo'              => $precio,
                        'registrado_por_id'  => Auth::id(),
                        'estatus'            => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'      => $fechaRecibido,
                        'notas'              => trim($item['notas'] ?? '') ?: null,
                    ]);
                }
            });

            $this->dispatch('toast', ['type' => 'success', 'message' => 'Compra registrada correctamente.']);
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
            $this->deshuesoEquipoId       = $id;
            $this->deshuesoEquipoBusqueda = "{$equipo->numero_serie} — {$equipo->marca} {$equipo->modelo}";
            $this->deshuesoRecuperar      = []; // reset selección de recuperación
        }
        unset($this->equiposBusqueda, $this->piezasEquipoOrigen);
    }

    public function agregarItemDeshueso(): void
    {
        $this->deshuesoItems[] = [
            'nombre'               => '',
            'categoria'            => '',
            'catalogo_pieza_id_sel'=> null,
            'cantidad'             => 1,
            'notas'                => '',
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

        $tieneRecuperacion = !empty($this->deshuesoRecuperar);
        $itemsConNombre    = array_values(array_filter(
            $this->deshuesoItems,
            fn($i) => !empty(trim($i['nombre'] ?? ''))
        ));

        if (!$tieneRecuperacion && empty($itemsConNombre)) {
            $this->error = 'Agrega al menos una pieza o selecciona piezas a recuperar.';
            return;
        }

        // Validar items con nombre
        foreach ($itemsConNombre as $idx => $item) {
            if (empty(trim($item['categoria'] ?? ''))) {
                $this->error = 'Selecciona la categoría de la pieza "' . $item['nombre'] . '".';
                return;
            }
            if ((int)($item['cantidad'] ?? 0) < 1) {
                $this->error = 'La cantidad de "' . $item['nombre'] . '" debe ser al menos 1.';
                return;
            }
        }

        try {
            DB::transaction(function () use ($itemsConNombre) {
                $recuperadas = 0;
                $nuevas      = 0;

                // ── Flujo A: recuperar piezas ya registradas en el equipo ─
                if (!empty($this->deshuesoRecuperar)) {
                    $piezas = InventarioPieza::lockForUpdate()
                        ->whereIn('id', $this->deshuesoRecuperar)
                        ->where('equipo_destino_id', $this->deshuesoEquipoId)
                        ->get();

                    foreach ($piezas as $p) {
                        $p->update([
                            'equipo_destino_id'   => null,
                            'estatus'             => InventarioPieza::DISPONIBLE,
                            'cantidad_disponible' => $p->cantidad_disponible + 1,
                            'cantidad_usada'      => max(0, $p->cantidad_usada - 1),
                            'almacen_id'          => $this->deshuesoAlmacenId,
                        ]);
                        $recuperadas++;
                    }
                }

                // ── Flujo B: registrar piezas físicas extraídas ───────────
                foreach ($itemsConNombre as $item) {
                    $cantidad = (int) $item['cantidad'];
                    $catalogo = $this->encontrarOCrearCatalogo($item['nombre'], $item['categoria'], $item['catalogo_pieza_id_sel'] ?? null);

                    $this->consolidarOCrearInventario($catalogo, $cantidad, [
                        'origen'             => InventarioPieza::DESHUESO,
                        'equipo_origen_id'   => $this->deshuesoEquipoId,
                        'almacen_id'         => $this->deshuesoAlmacenId,
                        'registrado_por_id'  => Auth::id(),
                        'estatus'            => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'      => now()->toDateString(),
                        'notas'              => trim($item['notas'] ?? '') ?: null,
                    ]);
                    $nuevas++;
                }

                $partes = [];
                if ($recuperadas > 0) $partes[] = "{$recuperadas} pieza(s) recuperada(s)";
                if ($nuevas > 0)      $partes[] = "{$nuevas} pieza(s) registrada(s) del deshueso";
                $this->_deshuesoMsg = implode(' y ', $partes) . '.';
            });

            $this->dispatch('toast', ['type' => 'success', 'message' => $this->_deshuesoMsg]);
            $this->volverALista();

        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
        }
    }

    private string $_deshuesoMsg = '';

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ══════════════════════════════════════════════════════════════════════

    private function itemCompraVacio(): array
    {
        // catalogo_pieza_id_sel: se rellena al elegir sugerencia; null = crear nuevo si no existe
        return ['nombre' => '', 'categoria' => '', 'catalogo_pieza_id_sel' => null, 'cantidad' => 1, 'precio_unitario' => '', 'notas' => ''];
    }

    /**
     * Para piezas NO serializadas: suma el stock al registro activo existente
     * del mismo catálogo + almacén. Si no existe ninguno, crea uno nuevo.
     * Para piezas serializadas: siempre crea un registro independiente.
     */
    private function consolidarOCrearInventario(CatalogoPieza $catalogo, int $cantidad, array $datos): void
    {
        if (!$catalogo->requiere_serie) {
            $existing = InventarioPieza::where('catalogo_pieza_id', $catalogo->id)
                ->where('almacen_id', $datos['almacen_id'])
                ->where('estatus', '!=', InventarioPieza::DADA_DE_BAJA)
                ->first();

            if ($existing) {
                $existing->increment('cantidad_inicial', $cantidad);
                $existing->increment('cantidad_disponible', $cantidad);
                $existing->actualizarEstatus();
                return;
            }
        }

        InventarioPieza::create(array_merge($datos, [
            'catalogo_pieza_id'   => $catalogo->id,
            'cantidad_inicial'    => $cantidad,
            'cantidad_disponible' => $cantidad,
            'cantidad_reservada'  => 0,
            'cantidad_usada'      => 0,
            'cantidad_baja'       => 0,
        ]));
    }

    /**
     * Busca una entrada en catálogo_piezas por nombre+categoría (sin importar mayúsculas).
     * Si no existe la crea automáticamente.
     */
    private function encontrarOCrearCatalogo(string $nombre, string $categoria, ?int $idSeleccionado = null): CatalogoPieza
    {
        // Si el usuario eligió una pieza existente explícitamente, usarla directo
        if ($idSeleccionado) {
            $found = CatalogoPieza::find($idSeleccionado);
            if ($found) return $found;
        }

        $nombre = trim($nombre);

        $existing = CatalogoPieza::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($nombre)])
            ->where('categoria', $categoria)
            ->first();

        if ($existing) {
            return $existing;
        }

        return CatalogoPieza::create([
            'nombre'    => $nombre,
            'categoria' => $categoria,
            'activo'    => true,
        ]);
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
        $this->compraProveedorId   = null;
        $this->compraFecha         = now()->toDateString();
        $this->compraFechaRecibido = '';
        $this->compraFolio         = '';
        $this->compraLoteId        = null;
        $this->compraNotas         = '';
        $this->compraItems         = [];
        $this->compraAlmacenId     = 7; // Piezas Pendientes por defecto
        $this->error               = '';
        $this->resetErrorBag();
    }

    private function resetFormDeshueso(): void
    {
        $this->deshuesoEquipoId       = null;
        $this->deshuesoEquipoBusqueda = '';
        $this->deshuesoItems          = [];
        $this->deshuesoRecuperar      = [];
        $this->deshuesoAlmacenId      = 7; // Piezas Pendientes por defecto
        $this->error                  = '';
        $this->resetErrorBag();
        unset($this->equiposBusqueda, $this->piezasEquipoOrigen);
    }

    public function updatedBusqueda(): void { $this->resetPage(); }
    public function updatedFiltroCategoria(): void { $this->resetPage(); }
    public function updatedFiltroStock(): void { $this->resetPage(); }
    public function updatedNombre(): void { unset($this->piezasSimilares); }
    public function updatedDeshuesoEquipoBusqueda(): void { unset($this->equiposBusqueda, $this->piezasEquipoOrigen); }

    public function render()
    {
        return view('livewire.preparacion.inventario.catalogo-piezas');
    }
}
