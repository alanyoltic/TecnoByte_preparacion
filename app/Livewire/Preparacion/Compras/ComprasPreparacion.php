<?php

namespace App\Livewire\Preparacion\Compras;

use App\Models\Almacen;
use App\Models\AlmacenEncargado;
use App\Models\CatalogoPieza;
use App\Models\Cargador;
use App\Models\CompraInventario;
use App\Models\CompraInventarioItem;
use App\Models\InventarioPieza;
use App\Models\Consumible;
use App\Models\InventarioConsumible;
use App\Models\LoteCompra;
use App\Models\Proveedor;
use App\Models\SolicitudPieza;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Compras — Preparación'])]
class ComprasPreparacion extends Component
{
    use WithPagination;

    // ── Área fija ─────────────────────────────────────────────────────
    protected string $area = 'PREPARACION';

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

    // ── Selector de tipo de compra ('piezas' | 'consumibles' | 'cargadores' | 'todos') ──
    public string $tipoCompra = 'piezas';

    // ── Ítems de la orden ─────────────────────────────────────────────
    public array $itemsPiezas      = [];
    public array $itemsConsumibles = [];
    public array $itemsCargadores  = [];

    // ── Modales ───────────────────────────────────────────────────────
    public bool $modalSeriesCargador   = false;
    public int  $serialesCargadorIndex = -1;

    public bool   $modalProveedor       = false;
    public string $proveedorNombre      = '';
    public string $proveedorAbreviacion = '';
    public string $proveedorEmail       = '';
    public string $proveedorTelefono    = '';

    public bool   $modalLoteCompra       = false;
    public string $loteCompraNombre      = '';
    public string $loteCompraDescripcion = '';

    public bool   $modalNuevaPieza          = false;
    public int    $nuevaPiezaItemIndex      = -1;
    public string $nuevaPiezaNombre         = '';
    public string $nuevaPiezaCategoria      = '';
    public string $nuevaPiezaEspecificacion = '';
    public string $nuevaPiezaDescripcion    = '';
    public bool   $nuevaPiezaRequiereSerie  = false;

    public ?int $verCompraId = null;
    public string $error = '';

    // ══════════════════════════════════════════════════════════════════
    // MOUNT
    // ══════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('prep.compras.ver'), 403);
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
            ->withCount('cargadores')
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
            'items.catalogoPieza', 'items.consumible', 'cargadores',
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
        // Lotes de compra de Preparación, más los cerrados/recientes si se requiere.
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
            return Almacen::whereIn('clave', ['PIEZAS_PEND', 'PREPARACION'])
                ->where('activo', true)
                ->orderByRaw("FIELD(clave, 'PIEZAS_PEND', 'PREPARACION')")
                ->get(['id', 'nombre', 'clave']);
        }

        $almacenIds = AlmacenEncargado::where('user_id', $user->id)
            ->where('activo', true)
            ->pluck('almacen_id');

        if ($almacenIds->isEmpty()) {
            return Almacen::whereIn('clave', ['PIEZAS_PEND', 'PREPARACION'])
                ->where('activo', true)
                ->orderByRaw("FIELD(clave, 'PIEZAS_PEND', 'PREPARACION')")
                ->get(['id', 'nombre', 'clave']);
        }

        return Almacen::whereIn('id', $almacenIds)->where('activo', true)->get(['id', 'nombre', 'clave']);
    }

    #[Computed]
    public function catalogoPiezas()
    {
        return CatalogoPieza::activos()
            ->withSum(['inventario as stock_disponible' => fn ($q) => $q->where('cantidad_disponible', '>', 0)], 'cantidad_disponible')
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'especificacion']);
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

    public function getCategoriasPiezas(): array
    {
        return [
            'RAM', 'SSD', 'HDD', 'Batería', 'Pantalla',
            'Teclado', 'Carcasa', 'Palmrest', 'Bisagra',
            'Cargador', 'Placa Base', 'Ventilador', 'Otro',
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    // NAVEGACIÓN
    // ══════════════════════════════════════════════════════════════════

    public function nuevaCompra(): void
    {
        abort_unless(auth()->user()?->can('prep.compras.gestionar'), 403);
        $this->resetFormCompra();
        $this->itemsPiezas[] = $this->itemPiezaVacio();
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
        if ($this->tipoCompra === 'piezas') {
            $this->itemsConsumibles = []; $this->itemsCargadores = [];
            if (empty($this->itemsPiezas)) $this->itemsPiezas[] = $this->itemPiezaVacio();
        } elseif ($this->tipoCompra === 'consumibles') {
            $this->itemsPiezas = []; $this->itemsCargadores = [];
            if (empty($this->itemsConsumibles)) $this->itemsConsumibles[] = $this->itemConsumibleVacio();
        } elseif ($this->tipoCompra === 'cargadores') {
            $this->itemsPiezas = []; $this->itemsConsumibles = [];
            if (empty($this->itemsCargadores)) $this->itemsCargadores[] = $this->itemCargadorVacio();
        } else {
            // Todos
            if (empty($this->itemsPiezas)) $this->itemsPiezas[] = $this->itemPiezaVacio();
            if (empty($this->itemsConsumibles)) $this->itemsConsumibles[] = $this->itemConsumibleVacio();
            if (empty($this->itemsCargadores)) $this->itemsCargadores[] = $this->itemCargadorVacio();
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN ÍTEMS
    // ══════════════════════════════════════════════════════════════════

    public function agregarItemPieza(): void { $this->itemsPiezas[] = $this->itemPiezaVacio(); }
    public function removerItemPieza(int $index): void { array_splice($this->itemsPiezas, $index, 1); $this->itemsPiezas = array_values($this->itemsPiezas); }
    private function itemPiezaVacio(): array { return ['catalogo_pieza_id' => '', 'cantidad' => 1, 'precio_unitario' => '', 'notas' => '']; }

    public function agregarItemConsumible(): void { $this->itemsConsumibles[] = $this->itemConsumibleVacio(); }
    public function removerItemConsumible(int $index): void { array_splice($this->itemsConsumibles, $index, 1); $this->itemsConsumibles = array_values($this->itemsConsumibles); }
    private function itemConsumibleVacio(): array { return ['consumible_id' => '', 'cantidad' => 1, 'precio_unitario' => '', 'notas' => '']; }

    public function agregarItemCargador(): void { $this->itemsCargadores[] = $this->itemCargadorVacio(); }
    public function removerItemCargador(int $index): void { array_splice($this->itemsCargadores, $index, 1); $this->itemsCargadores = array_values($this->itemsCargadores); }
    private function itemCargadorVacio(): array { return ['marca' => '', 'voltaje' => '', 'amperaje' => '', 'punta' => '', 'cantidad' => 1, 'costo' => '', 'numeros_serie' => []]; }

    // ══════════════════════════════════════════════════════════════════
    // MODALES (Lote, Proveedor, Pieza, Series)
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

    // ── NUEVA PIEZA ──
    public function abrirModalNuevaPieza(int $itemIndex): void
    {
        $this->nuevaPiezaItemIndex = $itemIndex; $this->nuevaPiezaNombre = ''; $this->nuevaPiezaCategoria = ''; $this->nuevaPiezaEspecificacion = ''; $this->nuevaPiezaDescripcion = ''; $this->nuevaPiezaRequiereSerie = false;
        $this->resetErrorBag(['nuevaPiezaNombre', 'nuevaPiezaCategoria']); $this->modalNuevaPieza = true;
    }
    public function cerrarModalNuevaPieza(): void { $this->modalNuevaPieza = false; $this->nuevaPiezaItemIndex = -1; }
    public function guardarNuevaPieza(): void
    {
        $this->validate(['nuevaPiezaNombre' => 'required|string|max:255', 'nuevaPiezaCategoria' => 'required|string'], ['nuevaPiezaNombre.required' => 'Requerido.', 'nuevaPiezaCategoria.required' => 'Requerido.']);
        if (CatalogoPieza::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($this->nuevaPiezaNombre))])->where('categoria', $this->nuevaPiezaCategoria)->exists()) {
            $this->addError('nuevaPiezaNombre', 'Ya existe.'); return;
        }

        $pieza = CatalogoPieza::create([
            'nombre' => trim($this->nuevaPiezaNombre), 'categoria' => $this->nuevaPiezaCategoria,
            'especificacion' => trim($this->nuevaPiezaEspecificacion) ?: null, 'descripcion' => trim($this->nuevaPiezaDescripcion) ?: null,
            'requiere_serie' => $this->nuevaPiezaRequiereSerie, 'activo' => true,
        ]);

        if (isset($this->itemsPiezas[$this->nuevaPiezaItemIndex])) $this->itemsPiezas[$this->nuevaPiezaItemIndex]['catalogo_pieza_id'] = $pieza->id;
        unset($this->catalogoPiezas); $this->cerrarModalNuevaPieza();
        $this->dispatch('toast', ['type' => 'success', 'message' => "Pieza creada."]);
    }

    // ── SERIES CARGADOR ──
    public function abrirModalSeriesCargador(int $index): void
    {
        $this->serialesCargadorIndex = $index;
        if (!isset($this->itemsCargadores[$index]['numeros_serie'])) $this->itemsCargadores[$index]['numeros_serie'] = [];
        $cant = (int)($this->itemsCargadores[$index]['cantidad'] ?? 1);
        $actual = count($this->itemsCargadores[$index]['numeros_serie']);
        if ($actual === 0 && $cant > 0) $this->itemsCargadores[$index]['numeros_serie'][] = '';
        if ($actual > $cant) $this->itemsCargadores[$index]['numeros_serie'] = array_slice($this->itemsCargadores[$index]['numeros_serie'], 0, $cant);
        $this->modalSeriesCargador = true;
    }
    public function cerrarModalSeriesCargador(): void { $this->modalSeriesCargador = false; $this->serialesCargadorIndex = -1; }
    public function agregarSerieModalCargador(): void {
        $idx = $this->serialesCargadorIndex; $max = (int)($this->itemsCargadores[$idx]['cantidad'] ?? 1); $curr = count($this->itemsCargadores[$idx]['numeros_serie'] ?? []);
        if ($curr < $max) $this->itemsCargadores[$idx]['numeros_serie'][] = '';
    }
    public function quitarSerieModalCargador(int $serieIndex): void {
        $idx = $this->serialesCargadorIndex;
        if (isset($this->itemsCargadores[$idx]['numeros_serie'][$serieIndex])) array_splice($this->itemsCargadores[$idx]['numeros_serie'], $serieIndex, 1);
    }

    // ══════════════════════════════════════════════════════════════════
    // GUARDAR COMPRA
    // ══════════════════════════════════════════════════════════════════

    public function guardarCompra(): void
    {
        abort_unless(auth()->user()?->can('prep.compras.gestionar'), 403);
        $this->error = '';

        if (!$this->almacenDestinoId) {
            $this->error = 'Selecciona el almacén destino para esta compra.'; return;
        }

        $piezasValidas = [];
        if (in_array($this->tipoCompra, ['piezas', 'todos'])) {
            $piezasValidas = array_values(array_filter($this->itemsPiezas, fn ($i) => !empty($i['catalogo_pieza_id'])));
        }

        $consumiblesValidos = [];
        if (in_array($this->tipoCompra, ['consumibles', 'todos'])) {
            $consumiblesValidos = array_values(array_filter($this->itemsConsumibles, fn ($i) => !empty($i['consumible_id'])));
        }

        $cargadoresValidos = [];
        if (in_array($this->tipoCompra, ['cargadores', 'todos'])) {
            $cargadoresValidos = array_values(array_filter($this->itemsCargadores, fn ($c) => !empty($c['marca']) || !empty($c['voltaje']) || !empty($c['amperaje'])));
        }

        if (empty($piezasValidas) && empty($consumiblesValidos) && empty($cargadoresValidos)) {
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
            $solicitudesMarcadas = 0;

            DB::transaction(function () use ($piezasValidas, $consumiblesValidos, $cargadoresValidos, &$solicitudesMarcadas) {
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

                // ── Piezas ──
                foreach ($piezasValidas as $item) {
                    $precio   = is_numeric($item['precio_unitario'] ?? '') ? (float) $item['precio_unitario'] : null;
                    $cantidad = (int) $item['cantidad'];

                    $compraItem = CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id'    => $item['catalogo_pieza_id'],
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precio,
                        'almacen_id'           => $this->almacenDestinoId,
                        'notas'                => trim($item['notas'] ?? '') ?: null,
                    ]);

                    InventarioPieza::create([
                        'catalogo_pieza_id'   => $item['catalogo_pieza_id'],
                        'origen'              => InventarioPieza::COMPRA,
                        'compra_item_id'      => $compraItem->id,
                        'almacen_id'          => $this->almacenDestinoId,
                        'costo'               => $precio,
                        'registrado_por_id'   => Auth::id(),
                        'estatus'             => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso'       => $this->fechaCompra,
                        'cantidad_inicial'    => $cantidad,
                        'cantidad_disponible' => $cantidad,
                        'cantidad_reservada'  => 0,
                        'cantidad_usada'      => 0,
                        'cantidad_baja'       => 0,
                        'notas'               => trim($item['notas'] ?? '') ?: null,
                    ]);

                    $solicitudesMarcadas += $this->marcarSolicitudesComoCompradas((int) $item['catalogo_pieza_id'], $cantidad, $compra->folio);
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

                // ── Cargadores ──
                if (!empty($cargadoresValidos)) {
                    $prov = Proveedor::find($this->proveedorId);
                    $abr = $prov ? strtoupper(trim($prov->abreviacion)) : 'PROV';
                    $fechaBase = date('dmY', strtotime($this->fechaCompra));
                    $prefijo = "{$abr}{$fechaBase}-";

                    foreach ($cargadoresValidos as $cargador) {
                        $cantidad = (int)($cargador['cantidad'] ?? 1);
                        $costo = ($cargador['costo'] !== '') ? (float) $cargador['costo'] : null;
                        $seriesManuales = array_values(array_filter(array_map('trim', $cargador['numeros_serie'] ?? []), fn ($s) => $s !== ''));

                        for ($i = 0; $i < $cantidad; $i++) {
                            if (isset($seriesManuales[$i])) {
                                $serieFinal = $seriesManuales[$i];
                            } else {
                                $correlativo = 1;
                                do {
                                    $candidato = $prefijo . $correlativo;
                                    $existe = Cargador::where('serie', $candidato)->exists();
                                    if ($existe) $correlativo++;
                                } while ($existe);
                                $serieFinal = $candidato;
                            }

                            $nuevoCarg = Cargador::create([
                                'serie'                => $serieFinal,
                                'marca'                => $cargador['marca'] ?: null,
                                'voltaje'              => $cargador['voltaje'] ?: null,
                                'amperaje'             => $cargador['amperaje'] ?: null,
                                'punta'                => $cargador['punta'] ?: null,
                                'costo'                => $costo,
                                'compra_inventario_id' => $compra->id,
                                'estatus'              => 'DISPONIBLE',
                                'area'                 => $this->area,
                            ]);

                            \App\Services\CargadorTraceService::log($nuevoCarg, 'CREADO', "Alta por compra — área {$this->area}");
                        }
                    }
                }
            });

            $msg = 'Compra registrada correctamente.';
            if ($solicitudesMarcadas > 0) $msg .= " Se actualizaron {$solicitudesMarcadas} solicitud(es) pendiente(s).";

            $this->dispatch('toast', ['type' => 'success', 'message' => $msg]);
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
        $this->tipoCompra       = 'piezas';
        $this->itemsPiezas      = [];
        $this->itemsConsumibles = [];
        $this->itemsCargadores  = [];
        $this->error            = '';

        $this->preseleccionarAlmacen();
        $this->resetErrorBag();
        unset($this->compraDetalle, $this->catalogoPiezas, $this->catalogoConsumibles, $this->lotesCompras);
    }

    public function getTotalEstimado(): float
    {
        $tot = 0;
        foreach ($this->itemsPiezas as $i) $tot += (is_numeric($i['precio_unitario'] ?? '') ? (float) $i['precio_unitario'] : 0) * (int)($i['cantidad'] ?? 0);
        foreach ($this->itemsConsumibles as $i) $tot += (is_numeric($i['precio_unitario'] ?? '') ? (float) $i['precio_unitario'] : 0) * (int)($i['cantidad'] ?? 0);
        foreach ($this->itemsCargadores as $i) $tot += (is_numeric($i['costo'] ?? '') ? (float) $i['costo'] : 0) * (int)($i['cantidad'] ?? 0);
        return $tot;
    }

    private function marcarSolicitudesComoCompradas(int $catalogoPiezaId, int $cantidad, ?string $folio): int
    {
        if ($cantidad < 1) return 0;
        $solicitudes = SolicitudPieza::where('catalogo_pieza_id', $catalogoPiezaId)->where('estatus', SolicitudPieza::PENDIENTE_COMPRA)
            ->orderBy('created_at')->lockForUpdate()->limit($cantidad)->get();
        foreach ($solicitudes as $sol) {
            $nota = collect([$sol->notas_respuesta, "Compra registrada" . ($folio ? " (folio {$folio})" : "") . "."])->filter()->implode("\n");
            $sol->marcarComoComprada(Auth::id(), $nota);
        }
        return $solicitudes->count();
    }

    public function updatedBusqueda(): void { $this->resetPage(); }
    public function updatedFiltroProveedor(): void { $this->resetPage(); }

    public function render()
    {
        return view('livewire.preparacion.compras.compras-preparacion');
    }
}
