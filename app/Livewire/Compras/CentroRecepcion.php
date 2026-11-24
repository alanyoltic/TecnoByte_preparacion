<?php

namespace App\Livewire\Compras;

use Livewire\Component;
use App\Models\CompraInventario;
use App\Models\Lote;
use App\Models\LoteModeloRecibido;
use App\Models\Equipo;
use App\Models\Almacen;
use App\Services\EquipoMovimientoService;
use App\Models\InventarioPieza;
use App\Models\InventarioProducto;
use App\Models\InventarioConsumible;
use App\Models\Cargador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['pageTitle' => 'Centro de Recepción de Mercancía'])]
class CentroRecepcion extends Component
{
    public $compraId;
    public $compra;
    public $tab = 'resumen';

    public $seriesEquipos = [];
    
    // Para clasificar componentes genéricos:
    // [itemId] => [ ['cantidad' => x, 'especificacion' => ''] ]
    public $clasificacionesComponentes = []; 

    // Para capturar series de fabricante de cargadores (opcionales):
    // [itemId] => [ '0' => 'SERIE_FAB', '1' => '' ]
    public $seriesFabricanteCargadores = [];

    public function mount($compraId)
    {
        $this->compraId = $compraId;
        $this->compra = CompraInventario::with(
            'items.catalogoEquipo',
            'items.catalogoPieza',
            'items.producto',
            'items.consumible',
            'items.almacen',
            'proveedor',
            'registradoPor'
        )->findOrFail($compraId);
        
        $esCEO = optional(Auth::user()->role)->slug === 'ceo';
        abort_if($esCEO, 403, 'La Dirección General no realiza recepciones operativas.');
        abort_unless(Auth::id() === $this->compra->registrado_por_id, 403, 'Solo el solicitante de la orden puede recibir esta mercancía.');

        if ($this->compra->estatus !== 'APROBADA') {
            return redirect()->route($this->compra->area === 'VENTAS' ? 'ventas.compras' : 'preparacion.compras');
        }

        // Inicializar arreglos
        foreach ($this->compra->items as $item) {
            if ($item->catalogo_equipo_id) {
                for ($i = 0; $i < $item->cantidad; $i++) {
                    $this->seriesEquipos[$item->id][] = '';
                }
            } elseif ($item->catalogo_pieza_id) {
                $this->clasificacionesComponentes[$item->id] = [
                    ['cantidad' => $item->cantidad, 'especificacion' => '']
                ];
            } elseif ($item->esCargador()) {
                $this->seriesFabricanteCargadores[$item->id] = [];
                for ($i = 0; $i < $item->cantidad; $i++) {
                    $this->seriesFabricanteCargadores[$item->id][] = '';
                }
            }
        }

        // Pestaña inicial por defecto
        if ($this->compra->items->whereNotNull('catalogo_equipo_id')->isNotEmpty()) {
            $this->tab = 'equipos';
        } elseif ($this->compra->items->filter(fn($i) => $i->esCargador())->isNotEmpty()) {
            $this->tab = 'cargadores';
        } elseif ($this->compra->items->whereNotNull('catalogo_pieza_id')->isNotEmpty()) {
            $this->tab = 'componentes';
        } else {
            $this->tab = 'resumen';
        }
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function agregarClasificacion($itemId)
    {
        $this->clasificacionesComponentes[$itemId][] = ['cantidad' => 1, 'especificacion' => ''];
    }

    public function removerClasificacion($itemId, $index)
    {
        if (count($this->clasificacionesComponentes[$itemId]) > 1) {
            unset($this->clasificacionesComponentes[$itemId][$index]);
            $this->clasificacionesComponentes[$itemId] = array_values($this->clasificacionesComponentes[$itemId]);
        }
    }

    public function getSeriesPendientesCountProperty(): int
    {
        $faltan = 0;
        foreach ($this->seriesEquipos as $arr) {
            foreach ($arr as $s) {
                if (trim($s) === '') {
                    $faltan++;
                }
            }
        }
        return $faltan;
    }

    public function procesarRecepcion()
    {
        // 1. Validación de series de Equipos (Opcionales para Lotes Ciegos)
        $seriesCapturadas = [];
        foreach ($this->compra->items as $item) {
            if ($item->catalogo_equipo_id) {
                foreach ($this->seriesEquipos[$item->id] ?? [] as $index => $serie) {
                    $serieLimpia = strtoupper(trim($serie));
                    if ($serieLimpia === '') {
                        // Serie vacía permitida: se registrará como NULL (lote ciego)
                        continue;
                    }
                    if (in_array($serieLimpia, $seriesCapturadas, true)) {
                        $this->dispatch('toast', type: 'error', message: "El número de serie '{$serieLimpia}' está repetido en la captura.");
                        $this->tab = 'equipos';
                        return;
                    }
                    $seriesCapturadas[] = $serieLimpia;

                    if (Equipo::where('numero_serie', $serieLimpia)->exists()) {
                        $this->dispatch('toast', type: 'error', message: "El número de serie '{$serieLimpia}' ya existe registrado en el sistema.");
                        $this->tab = 'equipos';
                        return;
                    }
                }
            }

            // 2. Validación de Componentes / Piezas
            if ($item->catalogo_pieza_id) {
                $suma = 0;
                foreach ($this->clasificacionesComponentes[$item->id] ?? [] as $c) {
                    $suma += (int) ($c['cantidad'] ?? 0);
                }
                if ($suma !== (int) $item->cantidad) {
                    $nombrePieza = optional($item->catalogoPieza)->nombre;
                    $this->dispatch('toast', type: 'error', message: "La suma clasificada ({$suma}) no coincide con el total comprado ({$item->cantidad}) en {$nombrePieza}.");
                    $this->tab = 'componentes';
                    return;
                }
            }
        }

        DB::transaction(function () {
            // Verificar si hay equipos para crear el Lote
            $tieneEquipos = $this->compra->items->whereNotNull('catalogo_equipo_id')->isNotEmpty();
            $lote = null;
            if ($tieneEquipos) {
                $lote = Lote::create([
                    'nombre_lote' => $this->compra->nombre_lote_propuesto ?: 'Lote Autogenerado OC-'.$this->compra->id,
                    'proveedor_id' => $this->compra->proveedor_id,
                    'fecha_llegada' => now(),
                    'estatus' => $this->compra->area === 'VENTAS' ? 'VENTAS_DIRECTO' : 'CEDIS',
                    'compra_inventario_id' => $this->compra->id,
                ]);
            }

            foreach ($this->compra->items as $item) {
                // 1. EQUIPOS
                if ($item->catalogo_equipo_id && $lote) {
                    $marca = optional($item->catalogoEquipo)->marca ?: 'GENÉRICA';
                    $modelo = optional($item->catalogoEquipo)->modelo ?: 'GENÉRICO';
                    $tipoEquipo = optional($item->catalogoEquipo)->tipo_equipo ?: 'Laptop';

                    $clasificacionPuntosId = $item->catalogoEquipo?->clasificacion_puntos_id;

                    $loteModelo = LoteModeloRecibido::create([
                        'lote_id' => $lote->id,
                        'catalogo_equipo_id' => $item->catalogo_equipo_id,
                        'marca' => $marca,
                        'modelo' => $modelo,
                        'cantidad_recibida' => $item->cantidad,
                        'valor_unitario' => $item->precio_unitario,
                        'clasificacion_puntos_id' => $clasificacionPuntosId,
                    ]);
                    
                    $almacenDestino = $item->almacen ?? ($item->almacen_id ? Almacen::find($item->almacen_id) : null);
                    $sucursalId = $almacenDestino?->sucursal_id ?? Auth::user()->sucursal_id ?? 1;

                    foreach ($this->seriesEquipos[$item->id] as $serie) {
                        $serieLimpia = strtoupper(trim($serie));
                        $equipo = Equipo::create([
                            'numero_serie' => $serieLimpia !== '' ? $serieLimpia : null,
                            'lote_modelo_id' => $loteModelo->id,
                            'catalogo_equipo_id' => $item->catalogo_equipo_id,
                            'clasificacion_puntos_id' => $clasificacionPuntosId,
                            'proveedor_id' => $this->compra->proveedor_id,
                            'marca' => $marca,
                            'modelo' => $modelo,
                            'tipo_equipo' => $tipoEquipo,
                            'sucursal_id' => $sucursalId,
                            'estatus_ciclo' => $this->compra->area === 'VENTAS' ? Equipo::CICLO_VENTAS : Equipo::CICLO_PREPARACION,
                            'estatus_area' => $this->compra->area === 'VENTAS' ? Equipo::AREA_DISPONIBLE_VENTA : Equipo::AREA_SIN_ASIGNAR,
                            'almacen_id' => $item->almacen_id,
                            'registrado_por_user_id' => Auth::id(),
                        ]);

                        if ($almacenDestino) {
                            app(EquipoMovimientoService::class)->abrirEstanciaInicial(
                                $equipo,
                                $almacenDestino,
                                'ALTA_LOTE',
                                'Ingreso inicial por recepción de orden ' . ($this->compra->folio ?: 'OC-'.$this->compra->id)
                            );
                        }
                    }
                }
                
                // 2. PIEZAS (Componentes)
                if ($item->catalogo_pieza_id) {
                    foreach ($this->clasificacionesComponentes[$item->id] as $clasif) {
                        if ((int)$clasif['cantidad'] > 0) {
                            $specs = [];
                            if (trim($clasif['especificacion']) !== '') {
                                $specs = ['detalle_clasificacion' => $clasif['especificacion']];
                            }
                            InventarioPieza::create([
                                'catalogo_pieza_id' => $item->catalogo_pieza_id,
                                'origen' => InventarioPieza::COMPRA,
                                'compra_item_id' => $item->id,
                                'almacen_id' => $item->almacen_id,
                                'costo' => $item->precio_unitario,
                                'registrado_por_id' => Auth::id(),
                                'estatus' => InventarioPieza::DISPONIBLE,
                                'fecha_ingreso' => now(),
                                'cantidad_inicial' => $clasif['cantidad'],
                                'cantidad_disponible' => $clasif['cantidad'],
                                'especificaciones_tecnicas' => json_encode($specs),
                            ]);
                        }
                    }
                }

                // 3. PRODUCTOS
                if ($item->producto_id) {
                    $inv = InventarioProducto::firstOrNew([
                        'producto_id' => $item->producto_id,
                        'almacen_id' => $item->almacen_id,
                    ]);
                    $inv->cantidad = ($inv->cantidad ?? 0) + $item->cantidad;
                    $inv->save();
                }

                // 4. CONSUMIBLES
                if ($item->consumible_id) {
                    $inv = InventarioConsumible::firstOrNew([
                        'consumible_id' => $item->consumible_id,
                        'almacen_id' => $item->almacen_id,
                    ]);
                    $inv->cantidad = ($inv->cantidad ?? 0) + $item->cantidad;
                    $inv->save();
                }

                // 5. CARGADORES
                if ($item->esCargador()) {
                    $prov = $this->compra->proveedor;
                    $abr = $prov ? strtoupper(trim($prov->abreviacion ?? 'TB')) : 'TB';
                    $datos = $item->datosCargador();

                    for ($i = 0; $i < $item->cantidad; $i++) {
                        $serieInterna = Cargador::generarSerieInterna($abr, $this->compra->fecha_compra);
                        $serieFab = !empty($this->seriesFabricanteCargadores[$item->id][$i])
                            ? strtoupper(trim($this->seriesFabricanteCargadores[$item->id][$i]))
                            : null;

                        $cargador = Cargador::create([
                            'serie'                => $serieInterna,
                            'serie_fabricante'     => $serieFab,
                            'marca'                => $datos['marca'] ?? null,
                            'voltaje'              => $datos['voltaje'] ?? null,
                            'amperaje'             => $datos['amperaje'] ?? null,
                            'punta'                => $datos['punta'] ?? null,
                            'costo'                => $item->precio_unitario,
                            'compra_inventario_id' => $this->compra->id,
                            'lote_id'              => $lote?->id,
                            'estatus'              => 'DISPONIBLE',
                            'area'                 => $this->compra->area,
                        ]);

                        \App\Services\CargadorTraceService::log(
                            $cargador,
                            'CREADO',
                            'Alta inicial por recepción de orden ' . ($this->compra->folio ?: 'OC-'.$this->compra->id)
                        );
                    }
                }
            }

            $this->compra->update([
                'estatus' => 'RECIBIDA',
                'lote_id' => $lote?->id,
            ]);
        });

        $this->dispatch('toast', type: 'success', message: '¡Recepción completada e ingresada al inventario correctamente!');
        return redirect()->route($this->compra->area === 'VENTAS' ? 'ventas.compras' : 'preparacion.compras');
    }

    public function render()
    {
        return view('livewire.compras.centro-recepcion', [
            'compra'                     => $this->compra,
            'tab'                        => $this->tab,
            'seriesEquipos'              => $this->seriesEquipos,
            'clasificacionesComponentes' => $this->clasificacionesComponentes,
            'seriesFabricanteCargadores' => $this->seriesFabricanteCargadores,
            'seriesPendientesCount'      => $this->seriesPendientesCount,
        ]);
    }
}
