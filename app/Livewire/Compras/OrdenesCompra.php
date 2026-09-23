<?php

namespace App\Livewire\Compras;

use App\Models\Almacen;
use App\Models\AlmacenEncargado;
use App\Models\CatalogoPieza;
use App\Models\Consumible;
use App\Models\Producto;
use App\Models\Cargador;
use App\Models\CompraInventario;
use App\Models\CompraInventarioItem;
use App\Models\InventarioPieza;
use App\Models\InventarioProducto;
use App\Models\InventarioConsumible;
use App\Models\LoteCompra;
use App\Models\Proveedor;
use App\Models\CatalogoEquipo;
use App\Models\Lote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Órdenes de Compra'])]
class OrdenesCompra extends Component
{
    use WithPagination;

    public string $vista = 'lista';
    public string $area = 'PREPARACION'; // Se ajusta en mount

    // Búsqueda en catálogo (Omnisearch)
    public string $searchTerm = '';
    public array $searchResults = [];
    public bool $showSearchDropdown = false;

    // Filtros
    public string $busqueda = '';
    public string $filtroProveedor = '';

    // Cabecera Compra
    public ?int $proveedorId = null;
    public string $fechaCompra = '';
    public string $folio = '';
    public string $notasCompra = '';
    public string $nombreLotePropuesto = '';
    public string $tipoIva = 'INCLUIDO';
    public string $moneda = 'MXN';
    public ?float $tipoCambio = null;
    
    // Almacenes por categoría: ['pieza' => id, 'consumible' => id, 'producto' => id]
    public array $almacenesSeleccionados = [];

    // Totales calculados
    public float $subtotal = 0;
    public float $iva = 0;
    public float $total = 0;

    // Carrito unificado
    // Estructura de item en carrito:
    // [ 'tipo' => 'pieza|producto|consumible|cargador', 'id' => ID_ORIGEN, 'nombre' => '...', 'cantidad' => 1, 'precio' => 0, 'notas' => '', ... ]
    public array $carrito = [];

    // Modales (Proveedor, Lote) - simplificado para este ejemplo
    public bool $modalProveedor = false;
    public string $proveedorNombre = '';
    public string $proveedorAbreviacion = '';

    public function mount()
    {
        // Detectar área según URL o permisos
        if (request()->routeIs('ventas.*') || !auth()->user()?->can('prep.compras.ver')) {
            $this->area = 'VENTAS';
        } else {
            $this->area = 'PREPARACION';
        }

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

    public function verNuevaOrden()
    {
        $this->vista = 'nueva';
    }

    public function verHistorial()
    {
        // Limpiar estado
        $this->reset(['carrito', 'proveedorId', 'folio', 'notasCompra', 'nombreLotePropuesto', 'subtotal', 'iva', 'total', 'busqueda', 'searchTerm']);
        $this->fechaCompra = now()->toDateString();
        $this->tipoIva = 'INCLUIDO';
        $this->moneda = 'MXN';
        $this->tipoCambio = null;
        
        $this->vista = 'lista';
        
        // Limpiar paginación
        $this->resetPage();
    }

    #[Computed]
    public function compras()
    {
        return CompraInventario::with(['proveedor', 'registradoPor'])
            ->withCount('items')
            ->deArea($this->area)
            ->when($this->busqueda, fn ($q) =>
                $q->where(fn ($sub) =>
                    $sub->whereHas('proveedor', fn ($p) =>
                        $p->where('nombre_empresa', 'like', "%{$this->busqueda}%")
                    )->orWhere('folio', 'like', "%{$this->busqueda}%")
                )
            )
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id')
            ->paginate(15);
    }

    #[Computed]
    public function proveedores()
    {
        return Proveedor::orderBy('nombre_empresa')->get(['id', 'nombre_empresa']);
    }

    #[Computed]
    public function almacenes()
    {
        $user = auth()->user();
        
        if ($this->area === 'VENTAS') {
            return Almacen::where('clave', 'LIKE', '%VENTAS%')->orWhere('nombre', 'LIKE', '%Ventas%')->get();
        }

        // Preparación
        return Almacen::whereIn('clave', ['PIEZAS_PEND', 'PREPARACION'])->get();
    }

    // ── OMNISEARCH ──────────────────────────────────────────────────
    
    public function updatedSearchTerm()
    {
        $term = trim($this->searchTerm);
        if (strlen($term) < 2) {
            $this->searchResults = [];
            $this->showSearchDropdown = false;
            return;
        }

        $results = [];

        // Separar el término en palabras para búsqueda más inteligente (ej. "dell 5530")
        $words = array_filter(explode(' ', $term));

        // Si es preparación, buscar piezas y equipos
        if ($this->area === 'PREPARACION') {
            $equiposQuery = CatalogoEquipo::query();
            foreach ($words as $word) {
                $equiposQuery->where(function ($q) use ($word) {
                    $q->where('marca', 'like', "%{$word}%")
                      ->orWhere('modelo', 'like', "%{$word}%")
                      ->orWhere('tipo_equipo', 'like', "%{$word}%");
                });
            }
            $equipos = $equiposQuery->take(8)->get();
                
            foreach ($equipos as $e) {
                $results[] = [
                    'tipo' => 'equipo', 'id' => $e->id, 'nombre' => $e->marca . ' ' . $e->modelo,
                    'badge' => '💻 EQUIPO', 'extra' => $e->tipo_equipo
                ];
            }

            $piezasQuery = CatalogoPieza::query();
            foreach ($words as $word) {
                $piezasQuery->where(function ($q) use ($word) {
                    $q->where('nombre', 'like', "%{$word}%")
                      ->orWhere('especificacion', 'like', "%{$word}%");
                });
            }
            $piezas = $piezasQuery->take(8)->get();
                
            foreach ($piezas as $p) {
                $results[] = [
                    'tipo' => 'pieza', 'id' => $p->id, 'nombre' => $p->nombre,
                    'badge' => '🔧 PIEZA', 'extra' => $p->especificacion
                ];
            }
        }

        // Si es ventas, buscar productos
        if ($this->area === 'VENTAS') {
            $productosQuery = Producto::query();
            foreach ($words as $word) {
                $productosQuery->where(function ($q) use ($word) {
                    $q->where('nombre', 'like', "%{$word}%")
                      ->orWhere('sku', 'like', "%{$word}%");
                });
            }
            $productos = $productosQuery->take(8)->get();
                
            foreach ($productos as $p) {
                $results[] = [
                    'tipo' => 'producto', 'id' => $p->id, 'nombre' => $p->nombre,
                    'badge' => '📦 PRODUCTO', 'extra' => $p->sku
                ];
            }
        }

        // Ambos pueden buscar consumibles
        $consumiblesQuery = Consumible::query();
        foreach ($words as $word) {
            $consumiblesQuery->where(function ($q) use ($word) {
                $q->where('nombre', 'like', "%{$word}%")
                  ->orWhere('sku', 'like', "%{$word}%");
            });
        }
        $consumibles = $consumiblesQuery->take(8)->get();
            
        foreach ($consumibles as $c) {
            $results[] = [
                'tipo' => 'consumible', 'id' => $c->id, 'nombre' => $c->nombre,
                'badge' => '💧 CONSUMIBLE', 'extra' => $c->categoria
            ];
        }

        // Opción de Cargador (Siempre visible si se escribe cargador)
        if (stripos('cargador', $term) !== false && $this->area === 'PREPARACION') {
            $results[] = [
                'tipo' => 'cargador', 'id' => 0, 'nombre' => 'Añadir Cargador Genérico',
                'badge' => '🔌 CARGADOR', 'extra' => 'Registro manual'
            ];
        }

        // Verificar si ya están en el carrito
        foreach ($results as &$r) {
            $r['agregado'] = false;
            foreach ($this->carrito as $item) {
                if ($item['tipo'] === $r['tipo'] && $item['id'] === $r['id'] && $r['tipo'] !== 'cargador') {
                    $r['agregado'] = true;
                    break;
                }
            }
        }
        
        // Mover los agregados al final
        usort($results, function ($a, $b) {
            return $a['agregado'] <=> $b['agregado'];
        });

        $this->searchResults = $results;
        $this->showSearchDropdown = true;
    }

    public function selectItem($index)
    {
        if (!isset($this->searchResults[$index])) return;
        $res = $this->searchResults[$index];

        // Si ya está agregado (excepto cargador), no hacer nada
        if (($res['agregado'] ?? false) && $res['tipo'] !== 'cargador') {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Este producto ya está en el carrito.']);
            return;
        }

        $this->carrito[] = [
            'uniqid' => uniqid(),
            'tipo' => $res['tipo'],
            'id' => $res['id'],
            'nombre' => $res['nombre'],
            'cantidad' => 1,
            'precio' => 0,
            'notas' => '',
            // Extra para cargadores
            'marca' => '', 'voltaje' => '', 'amperaje' => '', 'punta' => ''
        ];
        $this->dispatch('toast', ['type' => 'info', 'message' => 'Agregado al carrito.']);

        $this->searchTerm = '';
        $this->showSearchDropdown = false;
        $this->calcularTotales();
    }
    
    public function removerDelCarrito($index)
    {
        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito);
        $this->calcularTotales();
    }

    public function updated($property)
    {
        if (str_starts_with($property, 'carrito.') || $property === 'tipoIva') {
            $this->calcularTotales();
        }
    }

    public function calcularTotales()
    {
        $sub = 0;
        foreach ($this->carrito as $item) {
            $sub += (floatval($item['cantidad']) * floatval($item['precio']));
        }

        $this->subtotal = $sub;

        if ($this->tipoIva === 'MAS_IVA') {
            $this->iva = $sub * 0.16; // Asumiendo IVA 16%
            $this->total = $sub + $this->iva;
        } elseif ($this->tipoIva === 'INCLUIDO') {
            $this->total = $sub;
            $this->subtotal = $sub / 1.16;
            $this->iva = $this->total - $this->subtotal;
        } else { // EXENTO
            $this->iva = 0;
            $this->total = $sub;
        }
    }

    // ── GUARDADO DE ORDEN ──────────────────────────────────────────

    public function guardarOrden()
    {
        $rules = [
            'proveedorId' => 'required',
            'fechaCompra' => 'required|date',
            'carrito' => 'required|array|min:1',
        ];
        
        if ($this->moneda === 'USD') {
            $rules['tipoCambio'] = 'required|numeric|min:0.01';
        }

        // Validar almacenes dinámicamente si hay piezas, productos o consumibles en el carrito
        $tiposEnCarrito = collect($this->carrito)->pluck('tipo')->unique()->toArray();
        foreach (['pieza', 'producto', 'consumible', 'cargador'] as $tipo) {
            if (in_array($tipo, $tiposEnCarrito)) {
                $rules["almacenesSeleccionados.{$tipo}"] = 'required';
            }
        }

        $this->validate($rules, [
            'carrito.required' => 'El carrito está vacío.',
            'proveedorId.required' => 'Seleccione un proveedor.',
            'almacenesSeleccionados.*.required' => 'Seleccione el almacén receptor para esta categoría.',
            'tipoCambio.required' => 'Especifique el tipo de cambio actual para dólares.',
        ]);

        $this->calcularTotales();

        // Si el usuario es gerente, bypass a PENDIENTE_CEO. Si no, PENDIENTE_GERENTE.
        $esGerente = Auth::user()->tienePermiso('prep.compras.aprobar_gerente') || Auth::user()->tienePermiso('prep.compras.aprobar_ceo');
        $estatusInicial = $esGerente ? 'PENDIENTE_CEO' : 'PENDIENTE_GERENTE';

        DB::transaction(function () use ($estatusInicial) {
            $compra = CompraInventario::create([
                'proveedor_id' => $this->proveedorId,
                'fecha_compra' => $this->fechaCompra,
                'folio' => $this->folio ?: null,
                'notas' => $this->notasCompra ?: null,
                'nombre_lote_propuesto' => $this->nombreLotePropuesto ?: null,
                'subtotal' => $this->subtotal,
                'iva' => $this->iva,
                'total' => $this->total,
                'tipo_iva' => $this->tipoIva,
                'moneda' => $this->moneda,
                'tipo_cambio' => $this->moneda === 'USD' ? $this->tipoCambio : null,
                'registrado_por_id' => Auth::id(),
                'area' => $this->area,
                'estatus' => $estatusInicial,
            ]);

            $cedisId = Almacen::where('nombre', 'like', '%CEDIS%')->value('id');

            foreach ($this->carrito as $item) {
                // Determinar el almacén de la partida basado en su categoría
                $almacenPartidaId = $item['tipo'] === 'equipo' 
                    ? $cedisId 
                    : ($this->almacenesSeleccionados[$item['tipo']] ?? null);

                if ($item['tipo'] === 'cargador') {
                    CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'almacen_id' => $almacenPartidaId,
                        'notas' => json_encode(['tipo' => 'cargador', 'marca' => $item['marca'], 'voltaje' => $item['voltaje'], 'amperaje' => $item['amperaje'], 'punta' => $item['punta']])
                    ]);
                } else {
                    CompraInventarioItem::create([
                        'compra_inventario_id' => $compra->id,
                        'catalogo_pieza_id' => $item['tipo'] === 'pieza' ? $item['id'] : null,
                        'catalogo_equipo_id' => $item['tipo'] === 'equipo' ? $item['id'] : null,
                        'producto_id' => $item['tipo'] === 'producto' ? $item['id'] : null,
                        'consumible_id' => $item['tipo'] === 'consumible' ? $item['id'] : null,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'almacen_id' => $almacenPartidaId,
                        'notas' => $item['notas'],
                    ]);
                }
            }
        });

        $this->verHistorial();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Orden de compra creada exitosamente.']);
    }

    public function autorizarGerente($compraId)
    {
        $compra = CompraInventario::findOrFail($compraId);
        abort_unless(Auth::user()->tienePermiso('prep.compras.aprobar_gerente'), 403);
        if ($compra->estatus !== 'PENDIENTE_GERENTE') return;

        $compra->update([
            'estatus' => 'PENDIENTE_CEO',
            'aprobado_gerente_por_id' => Auth::id(),
            'fecha_aprobacion_gerente' => now(),
        ]);
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Orden autorizada. Pasa a revisión del CEO.']);
    }

    public function rechazarOrden($compraId)
    {
        $compra = CompraInventario::findOrFail($compraId);
        abort_unless(Auth::user()->tienePermiso('prep.compras.aprobar_gerente') || Auth::user()->tienePermiso('prep.compras.aprobar_ceo'), 403);
        
        $compra->update([
            'estatus' => 'RECHAZADA',
            'cancelado_por_id' => Auth::id(),
            'fecha_cancelacion' => now(),
        ]);
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Orden rechazada correctamente.']);
    }

    public function cancelarOrden($compraId)
    {
        $compra = CompraInventario::findOrFail($compraId);
        abort_unless(Auth::id() === $compra->registrado_por_id || Auth::user()->tienePermiso('prep.compras.aprobar_ceo'), 403);

        DB::transaction(function () use ($compra) {
            // Si la orden ya estaba aprobada y tenía lote en CEDIS, lo destruimos
            if ($compra->estatus === 'APROBADA') {
                Lote::where('compra_inventario_id', $compra->id)->where('estatus', 'PENDIENTE_ENTREGA')->delete();
            }

            $compra->update([
                'estatus' => 'CANCELADA',
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
            ]);
        });

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Orden cancelada correctamente.']);
    }
    
    public function aprobarOrden($compraId)
    {
        $compra = CompraInventario::with('items')->findOrFail($compraId);
        abort_unless(Auth::user()->tienePermiso('prep.compras.aprobar_ceo'), 403);
        if ($compra->estatus !== 'PENDIENTE_CEO') return;

        DB::transaction(function () use ($compra) {
            $compra->estatus = 'APROBADA';
            $compra->aprobado_por_id = Auth::id();
            $compra->save();

            // Verificar si hay equipos
            $tieneEquipos = $compra->items->whereNotNull('catalogo_equipo_id')->isNotEmpty();

            if ($tieneEquipos) {
                Lote::create([
                    'nombre_lote' => $compra->nombre_lote_propuesto ?: 'Lote Autogenerado OC-'.$compra->id,
                    'proveedor_id' => $compra->proveedor_id,
                    'fecha_llegada' => now(), // Será ajustado cuando realmente llegue
                    'estatus' => 'PENDIENTE_ENTREGA',
                    'compra_inventario_id' => $compra->id,
                ]);
            }
        });

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Orden APROBADA con éxito por Finanzas (CEO).']);
    }

    // ── AUTORIZAR (RECIBIR ORDEN) ──────────────────────────────────
    public function recibirOrden($compraId)
    {
        $compra = CompraInventario::with('items')->findOrFail($compraId);
        
        // Regla: Solo el creador puede recibir
        abort_unless(Auth::id() === $compra->registrado_por_id, 403, 'Solo el solicitante puede recibir la mercancía de esta orden.');
        
        if ($compra->estatus !== 'APROBADA') return;

        DB::transaction(function () use ($compra) {
            foreach ($compra->items as $item) {
                
                // Si es PIEZA
                if ($item->catalogo_pieza_id) {
                    InventarioPieza::create([
                        'catalogo_pieza_id' => $item->catalogo_pieza_id,
                        'origen' => InventarioPieza::COMPRA,
                        'compra_item_id' => $item->id,
                        'almacen_id' => $item->almacen_id,
                        'costo' => $item->precio_unitario,
                        'registrado_por_id' => Auth::id(),
                        'estatus' => InventarioPieza::DISPONIBLE,
                        'fecha_ingreso' => now(),
                        'cantidad_inicial' => $item->cantidad,
                        'cantidad_disponible' => $item->cantidad,
                    ]);
                }
                
                // Si es PRODUCTO
                if ($item->producto_id) {
                    $inv = InventarioProducto::firstOrNew([
                        'producto_id' => $item->producto_id,
                        'almacen_id' => $item->almacen_id,
                    ]);
                    $inv->cantidad = ($inv->cantidad ?? 0) + $item->cantidad;
                    $inv->save();
                }

                // Si es CONSUMIBLE
                if ($item->consumible_id) {
                    $inv = InventarioConsumible::firstOrNew([
                        'consumible_id' => $item->consumible_id,
                        'almacen_id' => $item->almacen_id,
                    ]);
                    $inv->cantidad = ($inv->cantidad ?? 0) + $item->cantidad;
                    $inv->save();
                }

                // Si es CARGADOR (detectado por JSON en notas)
                if (!$item->catalogo_pieza_id && !$item->producto_id && !$item->consumible_id && str_contains($item->notas, 'cargador')) {
                    $data = json_decode($item->notas, true);
                    $prov = $compra->proveedor;
                    $abr = $prov ? strtoupper(trim($prov->abreviacion)) : 'PROV';
                    $prefijo = "{$abr}" . date('dmY') . "-";

                    for ($i = 0; $i < $item->cantidad; $i++) {
                        $correlativo = 1;
                        do {
                            $candidato = $prefijo . $correlativo;
                            $existe = Cargador::where('serie', $candidato)->exists();
                            if ($existe) $correlativo++;
                        } while ($existe);

                        Cargador::create([
                            'serie' => $candidato,
                            'marca' => $data['marca'] ?? null,
                            'voltaje' => $data['voltaje'] ?? null,
                            'amperaje' => $data['amperaje'] ?? null,
                            'punta' => $data['punta'] ?? null,
                            'costo' => $item->precio_unitario,
                            'compra_inventario_id' => $compra->id,
                            'estatus' => 'DISPONIBLE',
                            'area' => $compra->area,
                        ]);
                    }
                }
            }

            // Activar el Lote si existe
            Lote::where('compra_inventario_id', $compra->id)
                ->where('estatus', 'PENDIENTE_ENTREGA')
                ->update([
                    'estatus' => 'CEDIS',
                    'fecha_llegada' => now(),
                ]);

            $compra->update(['estatus' => 'RECIBIDA']);
        });

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Mercancía recibida e ingresada al inventario.']);
    }

    public function render()
    {
        return view('livewire.compras.ordenes-compra');
    }
}

