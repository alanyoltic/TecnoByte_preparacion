<?php
namespace App\Livewire\Preparacion\Lotes;

use App\Models\Lote;
use App\Models\Equipo;
use App\Models\LoteModeloRecibido;
use App\Models\InventarioPieza;
use App\Models\Cargador;
use App\Models\ClasificacionPuntos;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['pageTitle' => 'Ver Lote'])]
class VerLote extends Component
{
    
    public Lote $lote;
    
    public $nombre_lote;
    public $fecha_llegada;
    public $proveedor_nombre;

    public $modelos = [];
    public $piezas = [];
    public $cargadores = [];

    public $clasificaciones = [];

    public $modalSeries = false;
    public $serialesIndex = -1;
    public $tipoModal = 'equipo';

    public function mount(Lote $lote)
    {
        abort_unless(Auth::user()?->tienePermiso('prep.lotes.ver') || Auth::user()?->tienePermiso('prep.lotes.gestion'), 403);

        $this->lote = $lote->load('compraInventario.items.catalogoEquipo', 'modelosRecibidos.equipos.asignacionEquipos.asignacion.tecnico', 'proveedor');
        
        $this->nombre_lote = $lote->nombre_lote;
        $this->fecha_llegada = $lote->fecha_llegada ? date('Y-m-d', strtotime($lote->fecha_llegada)) : date('Y-m-d');
        $this->proveedor_nombre = $lote->proveedor->nombre_empresa ?? $lote->proveedor->nombre ?? 'Proveedor Desconocido';
        
        $this->clasificaciones = ClasificacionPuntos::where('activo', true)->orderBy('clave')->get();

        $this->cargarItemsDeCompra();
    }

    private function cargarItemsDeCompra()
    {
        $this->modelos = [];
        $this->piezas = [];
        $this->cargadores = [];

        // 1. Cargar equipos desde Compra Inventario si existe
        if ($this->lote->compraInventario && $this->lote->compraInventario->items->isNotEmpty()) {
            foreach ($this->lote->compraInventario->items as $item) {
                if ($item->catalogo_equipo_id) {
                    $loteModeloIds = LoteModeloRecibido::where('lote_id', $this->lote->id)
                        ->where('catalogo_equipo_id', $item->catalogo_equipo_id)
                        ->pluck('id');
                    $equiposDB = Equipo::whereIn('lote_modelo_id', $loteModeloIds)->orderBy('id')->get();
                    
                    $registrados = $equiposDB->count();
                    $usados = $equiposDB->where('estatus_area', '!=', 'SIN_ASIGNAR')->count();
                    $faltantes = max(0, $item->cantidad - $registrados);
                    
                    $registrosExistentes = [];
                    foreach ($equiposDB as $eq) {
                        $registrosExistentes[] = [
                            'id'            => $eq->id,
                            'numero_serie'  => $eq->numero_serie,
                            'bloqueado'     => ($eq->estatus_area !== 'SIN_ASIGNAR'),
                            'tecnico'       => $eq->tecnico_asignado?->name ?? 'Sin asignar',
                            'estatus_area'  => $eq->estatus_area,
                            'estatus_ciclo' => $eq->estatus_ciclo,
                            'area_label'    => $eq->estatus_area_label,
                            'ciclo_label'   => $eq->estatus_ciclo_label,
                        ];
                    }

                    $this->modelos[] = [
                        'item_id' => $item->id,
                        'catalogo_equipo_id' => $item->catalogo_equipo_id,
                        'marca' => optional($item->catalogoEquipo)->marca ?: 'Genérica',
                        'modelo' => optional($item->catalogoEquipo)->modelo ?: 'Genérico',
                        'cantidad' => $item->cantidad,
                        'registrados' => $registrados,
                        'usados' => $usados,
                        'pendientes' => $faltantes > 0 ? $faltantes : 0,
                        'valor_unitario' => $item->precio_unitario,
                        'clasificacion_puntos_id' => '',
                        'registros_existentes' => $registrosExistentes,
                        'numeros_serie_nuevos' => [],
                        'almacen_id' => $item->almacen_id
                    ];
                } elseif ($item->catalogo_pieza_id) {
                    $piezasDB = InventarioPieza::where('compra_item_id', $item->id)->orderBy('id')->get();
                    $registrados = $piezasDB->count();
                    $usados = $piezasDB->where('estatus', '!=', InventarioPieza::DISPONIBLE)->count();
                    $faltantes = $item->cantidad - $registrados;

                    $registrosExistentes = [];
                    foreach ($piezasDB as $pz) {
                        $registrosExistentes[] = [
                            'id' => $pz->id,
                            'numero_serie' => $pz->numero_serie,
                            'codigo_interno' => $pz->codigo_interno,
                            'tiene_externa' => !empty($pz->numero_serie),
                            'bloqueado' => ($pz->estatus !== InventarioPieza::DISPONIBLE)
                        ];
                    }
                    
                    $this->piezas[] = [
                        'item_id' => $item->id,
                        'catalogo_pieza_id' => $item->catalogo_pieza_id,
                        'nombre' => $item->catalogoPieza->nombre,
                        'cantidad' => $item->cantidad,
                        'registrados' => $registrados,
                        'usados' => $usados,
                        'pendientes' => $faltantes > 0 ? $faltantes : 0,
                        'costo' => $item->precio_unitario,
                        'registros_existentes' => $registrosExistentes,
                        'numeros_serie_nuevos' => [],
                        'nuevos_tiene_externa' => [],
                        'almacen_id' => $item->almacen_id
                    ];
                    
                } elseif (!$item->producto_id && !$item->consumible_id && str_contains(strtolower($item->notas), 'cargador')) {
                    $cargadoresDB = Cargador::where('compra_inventario_id', $this->lote->compra_inventario_id)->orderBy('id')->get();
                    $registrados = $cargadoresDB->count();
                    $usados = $cargadoresDB->where('estatus', '!=', 'DISPONIBLE')->count();
                    $faltantes = $item->cantidad - $registrados;

                    $registrosExistentes = [];
                    foreach ($cargadoresDB as $cr) {
                        $registrosExistentes[] = [
                            'id' => $cr->id,
                            'numero_serie' => $cr->serie_fabricante,
                            'codigo_interno' => $cr->serie,
                            'tiene_externa' => !empty($cr->serie_fabricante),
                            'bloqueado' => ($cr->estatus !== 'DISPONIBLE')
                        ];
                    }

                    $this->cargadores[] = [
                        'item_id' => $item->id,
                        'notas' => $item->notas,
                        'cantidad' => $item->cantidad,
                        'registrados' => $registrados,
                        'usados' => $usados,
                        'pendientes' => $faltantes > 0 ? $faltantes : 0,
                        'costo' => $item->precio_unitario,
                        'registros_existentes' => $registrosExistentes,
                        'numeros_serie_nuevos' => [],
                        'nuevos_tiene_externa' => []
                    ];
                }
            }
        }

        // Si no se cargaron modelos por compra_inventario, cargar directamente desde LoteModeloRecibido
        if (empty($this->modelos) && $this->lote->modelosRecibidos) {
            foreach ($this->lote->modelosRecibidos as $lm) {
                $equiposDB = Equipo::where('lote_modelo_id', $lm->id)->orderBy('id')->get();
                $registrados = $equiposDB->count();
                $usados = $equiposDB->where('estatus_area', '!=', 'SIN_ASIGNAR')->count();
                $faltantes = max(0, $lm->cantidad_recibida - $registrados);

                $registrosExistentes = [];
                foreach ($equiposDB as $eq) {
                    $registrosExistentes[] = [
                        'id'            => $eq->id,
                        'numero_serie'  => $eq->numero_serie,
                        'bloqueado'     => ($eq->estatus_area !== 'SIN_ASIGNAR'),
                        'tecnico'       => $eq->tecnico_asignado?->name ?? 'Sin asignar',
                        'estatus_area'  => $eq->estatus_area,
                        'estatus_ciclo' => $eq->estatus_ciclo,
                        'area_label'    => $eq->estatus_area_label,
                        'ciclo_label'   => $eq->estatus_ciclo_label,
                    ];
                }

                $this->modelos[] = [
                    'item_id' => $lm->id,
                    'catalogo_equipo_id' => $lm->catalogo_equipo_id,
                    'marca' => $lm->marca ?: 'Genérica',
                    'modelo' => $lm->modelo ?: 'Genérico',
                    'cantidad' => $lm->cantidad_recibida,
                    'registrados' => $registrados,
                    'usados' => $usados,
                    'pendientes' => $faltantes > 0 ? $faltantes : 0,
                    'valor_unitario' => $lm->valor_unitario,
                    'clasificacion_puntos_id' => $lm->clasificacion_puntos_id,
                    'registros_existentes' => $registrosExistentes,
                    'numeros_serie_nuevos' => [],
                    'almacen_id' => $lm->equipos->first()?->almacen_id
                ];
            }
        }
    }

    public function abrirModalSeries($index, $tipo)
    {
        $this->serialesIndex = $index;
        $this->tipoModal = $tipo;

        if ($tipo === 'equipo') {
            $pendientes = $this->modelos[$index]['pendientes'];
            $current = $this->modelos[$index]['numeros_serie_nuevos'] ?? [];
            $this->modelos[$index]['numeros_serie_nuevos'] = array_replace(array_fill(0, $pendientes, ''), $current);
        } elseif ($tipo === 'pieza') {
            $pendientes = $this->piezas[$index]['pendientes'];
            $current = $this->piezas[$index]['numeros_serie_nuevos'] ?? [];
            $this->piezas[$index]['numeros_serie_nuevos'] = array_replace(array_fill(0, $pendientes, ''), $current);
            $currentChecks = $this->piezas[$index]['nuevos_tiene_externa'] ?? [];
            $this->piezas[$index]['nuevos_tiene_externa'] = array_replace(array_fill(0, $pendientes, false), $currentChecks);
        } elseif ($tipo === 'cargador') {
            $pendientes = $this->cargadores[$index]['pendientes'];
            $current = $this->cargadores[$index]['numeros_serie_nuevos'] ?? [];
            $this->cargadores[$index]['numeros_serie_nuevos'] = array_replace(array_fill(0, $pendientes, ''), $current);
            $currentChecks = $this->cargadores[$index]['nuevos_tiene_externa'] ?? [];
            $this->cargadores[$index]['nuevos_tiene_externa'] = array_replace(array_fill(0, $pendientes, false), $currentChecks);
        }

        $this->modalSeries = true;
    }

    public function toggleExternaFormExistente($i)
    {
        if ($this->tipoModal === 'pieza') {
            if ($this->piezas[$this->serialesIndex]['registros_existentes'][$i]['bloqueado']) return;
            $val = !$this->piezas[$this->serialesIndex]['registros_existentes'][$i]['tiene_externa'];
            $this->piezas[$this->serialesIndex]['registros_existentes'][$i]['tiene_externa'] = $val;
            if (!$val) $this->piezas[$this->serialesIndex]['registros_existentes'][$i]['numero_serie'] = '';
        } else {
            if ($this->cargadores[$this->serialesIndex]['registros_existentes'][$i]['bloqueado']) return;
            $val = !$this->cargadores[$this->serialesIndex]['registros_existentes'][$i]['tiene_externa'];
            $this->cargadores[$this->serialesIndex]['registros_existentes'][$i]['tiene_externa'] = $val;
            if (!$val) $this->cargadores[$this->serialesIndex]['registros_existentes'][$i]['numero_serie'] = '';
        }
    }

    public function toggleExternaFormNuevo($i)
    {
        if ($this->tipoModal === 'pieza') {
            $val = !$this->piezas[$this->serialesIndex]['nuevos_tiene_externa'][$i];
            $this->piezas[$this->serialesIndex]['nuevos_tiene_externa'][$i] = $val;
            if (!$val) $this->piezas[$this->serialesIndex]['numeros_serie_nuevos'][$i] = '';
        } else {
            $val = !$this->cargadores[$this->serialesIndex]['nuevos_tiene_externa'][$i];
            $this->cargadores[$this->serialesIndex]['nuevos_tiene_externa'][$i] = $val;
            if (!$val) $this->cargadores[$this->serialesIndex]['numeros_serie_nuevos'][$i] = '';
        }
    }

    public function cerrarModalSeries()
    {
        $this->modalSeries = false;
        $this->serialesIndex = -1;
    }

    public function guardarLote()
    {
        $this->lote->update(['nombre_lote' => $this->nombre_lote]);

        DB::transaction(function () {
            
            $almacenPreparacion = \App\Models\Almacen::find(\App\Models\Almacen::PREPARACION);

            // 1. EQUIPOS
            foreach ($this->modelos as $m) {
                // Actualizar existentes
                foreach ($m['registros_existentes'] as $reg) {
                    if (!$reg['bloqueado']) {
                        $eq = Equipo::find($reg['id']);
                        if ($eq) {
                            $eq->numero_serie = (trim((string)$reg['numero_serie']) !== '') ? strtoupper(trim($reg['numero_serie'])) : null;
                            if (!empty($m['clasificacion_puntos_id'])) {
                                $eq->clasificacion_puntos_id = $m['clasificacion_puntos_id'];
                            }
                            $eq->save();
                        }
                    }
                }

                // Crear nuevos si hay pendientes
                if ($m['pendientes'] > 0) {
                    $loteModelo = LoteModeloRecibido::firstOrCreate([
                        'lote_id' => $this->lote->id,
                        'catalogo_equipo_id' => $m['catalogo_equipo_id'],
                    ], [
                        'cantidad_recibida' => $m['cantidad'], // Historical ref
                        'valor_unitario' => $m['valor_unitario'],
                        'marca' => $m['marca'],
                        'modelo' => $m['modelo']
                    ]);

                    for ($i = 0; $i < $m['pendientes']; $i++) {
                        $serieAsignar = $m['numeros_serie_nuevos'][$i] ?? null;

                        $equipo = Equipo::create([
                            'numero_serie' => (trim((string)$serieAsignar) !== '') ? strtoupper(trim($serieAsignar)) : null,
                            'lote_modelo_id' => $loteModelo->id,
                            'catalogo_equipo_id' => $m['catalogo_equipo_id'],
                            'marca' => $m['marca'],
                            'modelo' => $m['modelo'],
                            'clasificacion_puntos_id' => !empty($m['clasificacion_puntos_id']) ? $m['clasificacion_puntos_id'] : null,
                            'estatus_ciclo' => 'PREPARACION',
                            'estatus_area' => 'SIN_ASIGNAR',
                            'registrado_por_user_id' => Auth::id(),
                            'proveedor_id' => $this->lote->proveedor_id,
                            'almacen_id' => $m['almacen_id'] ?? \App\Models\Almacen::PREPARACION,
                            'sucursal_id' => Auth::user()->sucursal_id ?? 1,
                        ]);

                        if ($almacenPreparacion) {
                            app(\App\Services\EquipoMovimientoService::class)->abrirEstanciaInicial(
                                $equipo,
                                $almacenPreparacion,
                                'ALTA_LOTE',
                                'Alta inicial desde lote'
                            );
                        }
                    }
                }
            }

            // 2. PIEZAS
            $prefijoPieza = "TB-PIZ-";
            foreach ($this->piezas as $p) {
                // Actualizar existentes
                foreach ($p['registros_existentes'] as $reg) {
                    if (!$reg['bloqueado']) {
                        $pz = InventarioPieza::find($reg['id']);
                        if ($pz) {
                            $pz->numero_serie = (trim((string)$reg['numero_serie']) !== '') ? strtoupper(trim($reg['numero_serie'])) : null;
                            $pz->save();
                        }
                    }
                }

                // Crear nuevos
                if ($p['pendientes'] > 0) {
                    for ($i = 0; $i < $p['pendientes']; $i++) {
                        $serieFabricante = $p['numeros_serie_nuevos'][$i] ?? null;
                        
                        $correlativo = 1;
                        do {
                            $candidato = $prefijoPieza . str_pad($correlativo, 5, '0', STR_PAD_LEFT);
                            $existe = InventarioPieza::where('codigo_interno', $candidato)->exists();
                            if ($existe) $correlativo++;
                        } while ($existe);

                        InventarioPieza::create([
                            'catalogo_pieza_id' => $p['catalogo_pieza_id'],
                            'origen' => InventarioPieza::COMPRA,
                            'compra_item_id' => $p['item_id'],
                            'almacen_id' => $p['almacen_id'] ?? \App\Models\Almacen::PREPARACION,
                            'costo' => $p['costo'],
                            'registrado_por_id' => Auth::id(),
                            'estatus' => InventarioPieza::DISPONIBLE,
                            'fecha_ingreso' => now(),
                            'cantidad_inicial' => 1,
                            'cantidad_disponible' => 1,
                            'codigo_interno' => $candidato,
                            'numero_serie' => (trim((string)$serieFabricante) !== '') ? strtoupper(trim($serieFabricante)) : null,
                        ]);
                    }
                }
            }

            // 3. CARGADORES
            $prefijoCrg = "TB-CRG-";
            foreach ($this->cargadores as $c) {
                // Actualizar existentes
                foreach ($c['registros_existentes'] as $reg) {
                    if (!$reg['bloqueado']) {
                        $cr = Cargador::find($reg['id']);
                        if ($cr) {
                            $cr->serie_fabricante = (trim((string)$reg['numero_serie']) !== '') ? strtoupper(trim($reg['numero_serie'])) : null;
                            $cr->save();
                        }
                    }
                }

                // Crear nuevos
                if ($c['pendientes'] > 0) {
                    for ($i = 0; $i < $c['pendientes']; $i++) {
                        $serieFabricante = $c['numeros_serie_nuevos'][$i] ?? null;

                        $correlativo = 1;
                        do {
                            $candidato = $prefijoCrg . str_pad($correlativo, 5, '0', STR_PAD_LEFT);
                            $existe = Cargador::where('serie', $candidato)->exists();
                            if ($existe) $correlativo++;
                        } while ($existe);

                        Cargador::create([
                            'serie' => $candidato,
                            'serie_fabricante' => (trim((string)$serieFabricante) !== '') ? strtoupper(trim($serieFabricante)) : null,
                            'costo' => $c['costo'],
                            'compra_inventario_id' => $this->lote->compra_inventario_id,
                            'lote_id' => $this->lote->id,
                            'estatus' => 'DISPONIBLE',
                            'area' => 'PREPARACION',
                        ]);
                    }
                }
            }

            $this->lote->update(['estatus' => 'CEDIS']);
        });

        $this->dispatch('toast', type: 'success', message: 'Lote actualizado y asignado correctamente.');
        return redirect()->route('preparacion.lotes');
    }


    public function render()
    {
        return view('livewire.preparacion.lotes.ver-lote', [
            'lote'             => $this->lote,
            'nombre_lote'      => $this->nombre_lote,
            'fecha_llegada'    => $this->fecha_llegada,
            'proveedor_nombre' => $this->proveedor_nombre,
            'modelos'          => $this->modelos,
            'clasificaciones'  => $this->clasificaciones,
            'modalSeries'      => $this->modalSeries,
            'serialesIndex'    => $this->serialesIndex,
        ]);
    }
}
