<?php

namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\LoteModeloRecibido;
use Illuminate\Support\Facades\Auth;

class RegistrarEquipo extends Component
{
    // =======================
    //  Catálogos
    // =======================
    public $lotesModelos = [];
    public $proveedores  = [];

    // =======================
    //  Campos base de la DB
    // =======================
    public $lote_modelo_id;
    public $numero_serie;
    public $proveedor_id;

    public $estatus_general = 'En Revisión';

    public $marca;
    public $modelo;
    public $tipo_equipo;
    public $sistema_operativo;
    public $area_tienda;

    public $procesador_modelo;
    public $procesador_generacion;
    public $procesador_nucleos;

    public $pantalla_pulgadas;
    public $pantalla_resolucion;
    public $pantalla_es_touch = false;
    public $pantalla_tipo;

    public $ram_total;
    public $ram_tipo;
    public $ram_es_soldada = false;
    public $ram_slots_totales;
    public $ram_expansion_max;
    public $ram_cantidad_soldada;

    public $almacenamiento_principal_capacidad;
    public $almacenamiento_principal_tipo;
    public $almacenamiento_secundario_capacidad = 'N/A';
    public $almacenamiento_secundario_tipo      = 'N/A';

    // Slots almacenamiento
    public $slots_alm_ssd;
    public $slots_alm_m2;
    public $slots_alm_m2_micro;
    public $slots_alm_hdd;
    public $slots_alm_msata;

    // Gráfica
    public $grafica_integrada_modelo;
    public $grafica_dedicada_modelo;
    public $grafica_dedicada_vram;
    public $tiene_tarjeta_dedicada = false;

    // Red / entrada
    public $ethernet_tiene       = false;
    public $ethernet_es_gigabit  = false;
    public $puertos_conectividad;
    public $dispositivos_entrada;

    // Puertos video (BD)
    public $puertos_hdmi;
    public $puertos_mini_hdmi;
    public $puertos_vga;
    public $puertos_dvi;
    public $puertos_displayport;
    public $puertos_mini_dp;

    // Puertos USB (BD)
    public $puertos_usb_2;
    public $puertos_usb_30;
    public $puertos_usb_31;
    public $puertos_usb_32;
    public $puertos_usb_c;

    // Lectores (BD)
    public $lectores_sd;
    public $lectores_sc;
    public $lectores_esata;
    public $lectores_sim;

    // Batería
    public $bateria_tiene       = true;
    public $bateria_salud_percent;
    public $bateria_cantidad;
    public $bateria_condicion;

    // Teclado / notas / detalles
    public $teclado_idioma = 'N/A';
    public $notas_generales;
    public $detalles_esteticos;
    public $detalles_funcionamiento;

    // =======================
    //  Listas dinámicas (UI)
    // =======================
    public $puertos_usb   = []; // ['tipo' => 'USB 3.0', 'cantidad' => 2]
    public $puertos_video = []; // ['tipo' => 'HDMI', 'cantidad' => 1]
    public $lectores      = []; // ['tipo' => 'SD', 'detalle' => '1 lector frontal']


    //lote
    public $lotes = [];     // Lista de lotes
    public $lote_id;

    public $modelosLote = [];
   
  








public function actualizarLote($loteId)
{
    // Reset de campos ligados al modelo
    $this->lote_modelo_id = null;
    $this->marca          = null;
    $this->modelo         = null;
    $this->modelosLote    = [];

    if (!$loteId) {
        $this->proveedor_id = null;
        return;
    }

    // Traemos lote con proveedor + modelos
    $lote = Lote::with(['proveedor', 'modelosRecibidos'])->find($loteId);

    if (!$lote) {
        $this->proveedor_id = null;
        return;
    }

    // Proveedor automático según el lote
    $this->proveedor_id = $lote->proveedor_id;

    // SOLO modelos con equipos pendientes
    $this->modelosLote = $lote->modelosRecibidos()
        ->withCount('equipos')  // relación equipos() en LoteModeloRecibido
        ->get()
        ->filter(function ($modelo) {
            // total que llegaron en este lote (ajusta el nombre de columna si es distinto)
            $total       = $modelo->cantidad_recibida;
            // cuántos equipos ya se registraron
            $registrados = $modelo->equipos_count ?? 0;

            // solo dejamos pasar los que aún tienen disponibles
            return $registrados < $total;
        })
        ->map(function ($modelo) {
            // devolvemos SOLO lo que usa el Blade
            return [
                'id'     => $modelo->id,
                'marca'  => $modelo->marca,
                'modelo' => $modelo->modelo,
            ];
        })
        ->values()
        ->toArray();
}

// Método explícito cuando cambia el modelo (también desde la vista)
public function actualizarModelo($modeloId)
{
    // Limpiamos primero
    $this->marca  = null;
    $this->modelo = null;

    if (!$modeloId) {
        return;
    }

    // Buscamos el modelo en la tabla lote_modelos_recibidos
    $loteModelo = LoteModeloRecibido::find($modeloId);

    if (!$loteModelo) {
        return;
    }

    // Prellenar marca y modelo
    $this->marca  = $loteModelo->marca;
    $this->modelo = $loteModelo->modelo;
}








protected function onLoteChanged($value)
{
    $this->lote_modelo_id = null;
    $this->marca          = null;
    $this->modelo         = null;
    $this->modelosLote    = [];

    if (!$value) {
        $this->proveedor_id = null;
        return;
    }

    $lote = Lote::with(['proveedor', 'modelosRecibidos'])->find($value);

    if (!$lote) {
        $this->proveedor_id = null;
        return;
    }

    $this->proveedor_id = $lote->proveedor_id;

    $this->modelosLote = $lote->modelosRecibidos()
        ->orderBy('modelo')
        ->get(['id', 'marca', 'modelo'])
        ->toArray();
}

protected function onLoteModeloChanged($value)
{
    $this->marca  = null;
    $this->modelo = null;

    if (!$value) {
        return;
    }

    $loteModelo = LoteModeloRecibido::find($value);

    if (!$loteModelo) {
        return;
    }

    $this->marca  = $loteModelo->marca;
    $this->modelo = $loteModelo->modelo;
}



public function mount()
{
    $lotesConEquipos = Lote::with(['proveedor', 'modelosRecibidos'])
        ->whereHas('modelosRecibidos')        // solo lotes con modelos
        ->orderBy('fecha_llegada', 'desc')
        ->get();

    
        $todosLotes = Lote::with([
            'proveedor',
            'modelosRecibidos' => function ($q) {
                $q->withCount('equipos'); // relación equipos() en LoteModeloRecibido
            },
        ])
        ->orderBy('fecha_llegada', 'desc')
        ->get();

    $lotesConPendientes = collect();
    $lotesTerminados    = collect();

    foreach ($todosLotes as $lote) {
        // ¿Este lote tiene al menos UN modelo con equipos pendientes?
        $tienePendientes = $lote->modelosRecibidos->contains(function ($modelo) {
            $total       = $modelo->cantidad_recibida;      // ajusta nombre de columna si es distinto
            $registrados = $modelo->equipos_count ?? 0;

            return $registrados < $total;
        });

        if ($tienePendientes) {
            $lotesConPendientes->push($lote);
        } else {
            // Lote sin modelos o con todos sus modelos llenos -> terminado
            $lotesTerminados->push($lote);
        }
    }

    // Tomamos solo los últimos 2 lotes terminados
    $terminadosTomados = $lotesTerminados->take(2);

    // Guardamos sus IDs en una propiedad para usarlos en la vista
    $this->lotesTerminadosIds = $terminadosTomados->pluck('id')->toArray();

    // 1) Primero todos los lotes con equipos pendientes
    // 2) Luego los últimos 2 terminados
    $this->lotes = $lotesConPendientes
        ->concat($terminadosTomados)
        ->values();



    $this->modelosLote = [];

   
    $this->proveedores  = Proveedor::orderBy('nombre_empresa')->get();


    $this->estatus_general = 'En Revisión';
    $this->almacenamiento_secundario_capacidad = 'N/A';
    $this->almacenamiento_secundario_tipo      = 'N/A';
    $this->teclado_idioma                      = 'N/A';
    $this->bateria_tiene                       = true;
    $this->ethernet_tiene                      = false;
    $this->ethernet_es_gigabit                 = false;
    $this->tiene_tarjeta_dedicada              = false;

    if (empty($this->puertos_usb)) {
        $this->puertos_usb = [['tipo' => '', 'cantidad' => 1]];
    }
    if (empty($this->puertos_video)) {
        $this->puertos_video = [['tipo' => '', 'cantidad' => 1]];
    }
    if (empty($this->lectores)) {
        $this->lectores = [['tipo' => '', 'detalle' => '']];
    }
}



    // =======================
    //  Métodos dinámicos
    // =======================
    public function addPuertoUsb()
    {
        $this->puertos_usb[] = ['tipo' => '', 'cantidad' => 1];
    }

    public function removePuertoUsb($index)
    {
        unset($this->puertos_usb[$index]);
        $this->puertos_usb = array_values($this->puertos_usb);
    }

    public function addPuertoVideo()
    {
        $this->puertos_video[] = ['tipo' => '', 'cantidad' => 1];
    }

    public function removePuertoVideo($index)
    {
        unset($this->puertos_video[$index]);
        $this->puertos_video = array_values($this->puertos_video);
    }

    public function addLector()
    {
        $this->lectores[] = ['tipo' => '', 'detalle' => ''];
    }

    public function removeLector($index)
    {
        unset($this->lectores[$index]);
        $this->lectores = array_values($this->lectores);
    }

    public $lotesTerminadosIds = [];


    // =======================
    //  Guardar equipo
    // =======================
    public function guardar()
    {
        $data = $this->validate([
            // FKs / obligatorios
            'lote_modelo_id' => 'required|exists:lote_modelos_recibidos,id',
            'proveedor_id'   => 'required|exists:proveedores,id',
            'numero_serie'   => 'required|string|max:255|unique:equipos,numero_serie',

            'estatus_general' => 'required|in:En Revisión,Aprobado,Pendiente Pieza,Pendiente Garantía,Pendiente Deshueso,Finalizado',

            // Generales
            'marca'            => 'nullable|string|max:100',
            'modelo'           => 'required|string|max:255',
            'tipo_equipo'      => 'nullable|string|max:100',
            'sistema_operativo'=> 'nullable|string|max:100',
            'area_tienda'      => 'nullable|string|max:100',

            // CPU
            'procesador_modelo'      => 'nullable|string|max:255',
            'procesador_generacion'  => 'nullable|string|max:100',
            'procesador_nucleos'     => 'nullable|integer|min:1|max:64',

            // Pantalla
            'pantalla_pulgadas'   => 'nullable|string|max:20',
            'pantalla_resolucion' => 'nullable|string|max:50',
            'pantalla_es_touch'   => 'boolean',
            'pantalla_tipo'       => 'nullable|string|max:100',

            // RAM
            'ram_total'            => 'nullable|string|max:50',
            'ram_tipo'             => 'nullable|string|max:50',
            'ram_es_soldada'       => 'boolean',
            'ram_slots_totales'    => 'nullable|string|max:100',
            'ram_expansion_max'    => 'nullable|string|max:100',
            'ram_cantidad_soldada' => 'nullable|string|max:100',

            // Almacenamiento
            'almacenamiento_principal_capacidad' => 'nullable|string|max:50',
            'almacenamiento_principal_tipo'      => 'nullable|string|max:50',
            'almacenamiento_secundario_capacidad'=> 'nullable|string|max:50',
            'almacenamiento_secundario_tipo'     => 'nullable|string|max:50',

            'slots_alm_ssd'       => 'nullable|string|max:50',
            'slots_alm_m2'        => 'nullable|string|max:50',
            'slots_alm_m2_micro'  => 'nullable|string|max:50',
            'slots_alm_hdd'       => 'nullable|string|max:50',
            'slots_alm_msata'     => 'nullable|string|max:50',

            // Gráfica
            'grafica_integrada_modelo' => 'nullable|string|max:255',
            'grafica_dedicada_modelo'  => 'nullable|string|max:255',
            'grafica_dedicada_vram'    => 'nullable|string|max:50',
            'tiene_tarjeta_dedicada'   => 'boolean',

            // Red / entrada
            'ethernet_tiene'       => 'boolean',
            'ethernet_es_gigabit'  => 'boolean',
            'puertos_conectividad' => 'nullable|string|max:255',
            'dispositivos_entrada' => 'nullable|string|max:255',

            // Puertos (BD) - opcionales si los llenas manual
            'puertos_hdmi'        => 'nullable|string|max:50',
            'puertos_mini_hdmi'   => 'nullable|string|max:50',
            'puertos_vga'         => 'nullable|string|max:50',
            'puertos_dvi'         => 'nullable|string|max:50',
            'puertos_displayport' => 'nullable|string|max:50',
            'puertos_mini_dp'     => 'nullable|string|max:50',

            'puertos_usb_2'   => 'nullable|string|max:50',
            'puertos_usb_30'  => 'nullable|string|max:50',
            'puertos_usb_31'  => 'nullable|string|max:50',
            'puertos_usb_32'  => 'nullable|string|max:50',
            'puertos_usb_c'   => 'nullable|string|max:50',

            'lectores_sd'    => 'nullable|string|max:50',
            'lectores_sc'    => 'nullable|string|max:50',
            'lectores_esata' => 'nullable|string|max:50',
            'lectores_sim'   => 'nullable|string|max:50',

            // Batería
            'bateria_tiene'        => 'boolean',
            'bateria_salud_percent'=> 'nullable|integer|min:0|max:100',
            'bateria_cantidad'     => 'nullable|string|max:100',
            'bateria_condicion'    => 'nullable|string|max:100',

            // Teclado / notas
            'teclado_idioma'        => 'nullable|string|max:50',
            'notas_generales'       => 'nullable|string',
            'detalles_esteticos'    => 'nullable|string',
            'detalles_funcionamiento'=> 'nullable|string',

            // Listas dinámicas
            'puertos_usb'                => 'array',
            'puertos_usb.*.tipo'         => 'nullable|string|max:50',
            'puertos_usb.*.cantidad'     => 'nullable|integer|min:1|max:10',
            'puertos_video'              => 'array',
            'puertos_video.*.tipo'       => 'nullable|string|max:50',
            'puertos_video.*.cantidad'   => 'nullable|integer|min:1|max:10',
            'lectores'                   => 'array',
            'lectores.*.tipo'            => 'nullable|string|max:50',
            'lectores.*.detalle'         => 'nullable|string|max:100',
        ], [
            'lote_modelo_id.required'  => 'Selecciona un lote/modelo.',
            'proveedor_id.required'    => 'Selecciona un proveedor.',
            'numero_serie.required'    => 'El número de serie es obligatorio.',
            'numero_serie.unique'      => 'Este número de serie ya está registrado.',
            'modelo.required'          => 'El modelo es obligatorio.',

            'lote_id'        => 'required|exists:lotes,id',
            'lote_modelo_id' => 'required|exists:lote_modelos_recibidos,id',

        ]);

        // Defaults
        if (empty($this->almacenamiento_secundario_capacidad)) {
            $this->almacenamiento_secundario_capacidad = 'N/A';
        }
        if (empty($this->almacenamiento_secundario_tipo)) {
            $this->almacenamiento_secundario_tipo = 'N/A';
        }
        if (empty($this->teclado_idioma)) {
            $this->teclado_idioma = 'N/A';
        }

        // Normalizar booleans
        $this->pantalla_es_touch    = $this->pantalla_es_touch ? 1 : 0;
        $this->ram_es_soldada       = $this->ram_es_soldada ? 1 : 0;
        $this->ethernet_tiene       = $this->ethernet_tiene ? 1 : 0;
        $this->ethernet_es_gigabit  = $this->ethernet_es_gigabit ? 1 : 0;
        $this->bateria_tiene        = $this->bateria_tiene ? 1 : 0;
        $this->tiene_tarjeta_dedicada = $this->tiene_tarjeta_dedicada ? 1 : 0;

        // ====================================
        //  Procesar listas dinámicas → columnas
        // ====================================

        // RESET contadores de BD (si no los llenas manual)
        $usb2 = $usb30 = $usb31 = $usb32 = $usbc = null;

        foreach ($this->puertos_usb as $p) {
            if (empty($p['tipo'])) continue;
            $cant = isset($p['cantidad']) ? (int)$p['cantidad'] : 1;

            switch ($p['tipo']) {
                case 'USB 2.0':
                    $usb2 = ($usb2 ?? 0) + $cant;
                    break;
                case 'USB 3.0':
                    $usb30 = ($usb30 ?? 0) + $cant;
                    break;
                case 'USB 3.1':
                    $usb31 = ($usb31 ?? 0) + $cant;
                    break;
                case 'USB 3.2':
                    $usb32 = ($usb32 ?? 0) + $cant;
                    break;
                case 'USB-C':
                case 'USB tipo C':
                    $usbc = ($usbc ?? 0) + $cant;
                    break;
            }
        }

        // Solo sobreescribimos si no capturaste algo manual en los inputs
        if ($usb2 !== null && $this->puertos_usb_2 === null)  $this->puertos_usb_2  = (string)$usb2;
        if ($usb30 !== null && $this->puertos_usb_30 === null)$this->puertos_usb_30 = (string)$usb30;
        if ($usb31 !== null && $this->puertos_usb_31 === null)$this->puertos_usb_31 = (string)$usb31;
        if ($usb32 !== null && $this->puertos_usb_32 === null)$this->puertos_usb_32 = (string)$usb32;
        if ($usbc !== null && $this->puertos_usb_c === null)  $this->puertos_usb_c  = (string)$usbc;

        // Video
        $hdmi = $miniHdmi = $vga = $dvi = $dp = $miniDp = null;

        foreach ($this->puertos_video as $p) {
            if (empty($p['tipo'])) continue;
            $cant = isset($p['cantidad']) ? (int)$p['cantidad'] : 1;

            switch ($p['tipo']) {
                case 'HDMI':
                    $hdmi = ($hdmi ?? 0) + $cant;
                    break;
                case 'Mini HDMI':
                    $miniHdmi = ($miniHdmi ?? 0) + $cant;
                    break;
                case 'VGA':
                    $vga = ($vga ?? 0) + $cant;
                    break;
                case 'DVI':
                    $dvi = ($dvi ?? 0) + $cant;
                    break;
                case 'DisplayPort':
                    $dp = ($dp ?? 0) + $cant;
                    break;
                case 'Mini DisplayPort':
                    $miniDp = ($miniDp ?? 0) + $cant;
                    break;
            }
        }

        if ($hdmi !== null && $this->puertos_hdmi === null)            $this->puertos_hdmi        = (string)$hdmi;
        if ($miniHdmi !== null && $this->puertos_mini_hdmi === null)   $this->puertos_mini_hdmi   = (string)$miniHdmi;
        if ($vga !== null && $this->puertos_vga === null)              $this->puertos_vga         = (string)$vga;
        if ($dvi !== null && $this->puertos_dvi === null)              $this->puertos_dvi         = (string)$dvi;
        if ($dp !== null && $this->puertos_displayport === null)       $this->puertos_displayport = (string)$dp;
        if ($miniDp !== null && $this->puertos_mini_dp === null)       $this->puertos_mini_dp     = (string)$miniDp;

        // Lectores
        $sd = $sc = $esata = $sim = null;

        foreach ($this->lectores as $l) {
            $tipo = $l['tipo'] ?? '';
            if ($tipo === '') continue;

            switch ($tipo) {
                case 'SD':
                case 'microSD':
                    $sd = ($sd ?? 0) + 1;
                    break;
                case 'SmartCard':
                    $sc = ($sc ?? 0) + 1;
                    break;
                case 'eSATA':
                    $esata = ($esata ?? 0) + 1;
                    break;
                case 'SIM':
                    $sim = ($sim ?? 0) + 1;
                    break;
            }
        }

        if ($sd !== null && $this->lectores_sd === null)      $this->lectores_sd    = (string)$sd;
        if ($sc !== null && $this->lectores_sc === null)      $this->lectores_sc    = (string)$sc;
        if ($esata !== null && $this->lectores_esata === null)$this->lectores_esata = (string)$esata;
        if ($sim !== null && $this->lectores_sim === null)    $this->lectores_sim   = (string)$sim;


        $loteModelo = LoteModeloRecibido::withCount('equipos')->findOrFail($this->lote_modelo_id);

            if ($loteModelo->equipos_count >= $loteModelo->cantidad_recibida) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'lote_modelo_id' => 'Ya se registraron todos los equipos disponibles de este modelo en este lote.',
                ]);
            }



        // =========================
        // CREAR REGISTRO EN DB
        // =========================
        Equipo::create([
            'lote_modelo_id'          => $this->lote_modelo_id,
            'numero_serie'            => $this->numero_serie,
            'registrado_por_user_id'  => Auth::id(),
            'proveedor_id'            => $this->proveedor_id,

            'estatus_general' => $this->estatus_general,

            'marca'            => $this->marca,
            'modelo'           => $this->modelo,
            'tipo_equipo'      => $this->tipo_equipo,
            'sistema_operativo'=> $this->sistema_operativo,
            'area_tienda'      => $this->area_tienda,

            'procesador_modelo'      => $this->procesador_modelo,
            'procesador_generacion'  => $this->procesador_generacion,
            'procesador_nucleos'     => $this->procesador_nucleos,

            'pantalla_pulgadas'   => $this->pantalla_pulgadas,
            'pantalla_resolucion' => $this->pantalla_resolucion,
            'pantalla_es_touch'   => $this->pantalla_es_touch,
            'pantalla_tipo'       => $this->pantalla_tipo,

            'ram_total'            => $this->ram_total,
            'ram_tipo'             => $this->ram_tipo,
            'ram_es_soldada'       => $this->ram_es_soldada,
            'ram_slots_totales'    => $this->ram_slots_totales,
            'ram_expansion_max'    => $this->ram_expansion_max,
            'ram_cantidad_soldada' => $this->ram_cantidad_soldada,

            'almacenamiento_principal_capacidad' => $this->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo'      => $this->almacenamiento_principal_tipo,
            'almacenamiento_secundario_capacidad'=> $this->almacenamiento_secundario_capacidad,
            'almacenamiento_secundario_tipo'     => $this->almacenamiento_secundario_tipo,

            'slots_alm_ssd'       => $this->slots_alm_ssd,
            'slots_alm_m2'        => $this->slots_alm_m2,
            'slots_alm_m2_micro'  => $this->slots_alm_m2_micro,
            'slots_alm_hdd'       => $this->slots_alm_hdd,
            'slots_alm_msata'     => $this->slots_alm_msata,

            'grafica_integrada_modelo' => $this->grafica_integrada_modelo,
            'grafica_dedicada_modelo'  => $this->grafica_dedicada_modelo,
            'grafica_dedicada_vram'    => $this->grafica_dedicada_vram,
            'tiene_tarjeta_dedicada'   => $this->tiene_tarjeta_dedicada,

            'ethernet_tiene'       => $this->ethernet_tiene,
            'ethernet_es_gigabit'  => $this->ethernet_es_gigabit,
            'puertos_conectividad' => $this->puertos_conectividad,
            'dispositivos_entrada' => $this->dispositivos_entrada,

            'puertos_hdmi'        => $this->puertos_hdmi,
            'puertos_mini_hdmi'   => $this->puertos_mini_hdmi,
            'puertos_vga'         => $this->puertos_vga,
            'puertos_dvi'         => $this->puertos_dvi,
            'puertos_displayport' => $this->puertos_displayport,
            'puertos_mini_dp'     => $this->puertos_mini_dp,

            'puertos_usb_2'   => $this->puertos_usb_2,
            'puertos_usb_30'  => $this->puertos_usb_30,
            'puertos_usb_31'  => $this->puertos_usb_31,
            'puertos_usb_32'  => $this->puertos_usb_32,
            'puertos_usb_c'   => $this->puertos_usb_c,

            'lectores_sd'    => $this->lectores_sd,
            'lectores_sc'    => $this->lectores_sc,
            'lectores_esata' => $this->lectores_esata,
            'lectores_sim'   => $this->lectores_sim,

            'bateria_tiene'        => $this->bateria_tiene,
            'bateria_salud_percent'=> $this->bateria_salud_percent,
            'bateria_cantidad'     => $this->bateria_cantidad,
            'bateria_condicion'    => $this->bateria_condicion,

            'teclado_idioma'        => $this->teclado_idioma,
            'notas_generales'       => $this->notas_generales,
            'detalles_esteticos'    => $this->detalles_esteticos,
            'detalles_funcionamiento'=> $this->detalles_funcionamiento,
        ]);

        // RESET
        $this->reset([
            'lote_modelo_id',
            'numero_serie',
            'proveedor_id',
            'estatus_general',
            'marca',
            'modelo',
            'tipo_equipo',
            'sistema_operativo',
            'area_tienda',
            'procesador_modelo',
            'procesador_generacion',
            'procesador_nucleos',
            'pantalla_pulgadas',
            'pantalla_resolucion',
            'pantalla_es_touch',
            'pantalla_tipo',
            'ram_total',
            'ram_tipo',
            'ram_es_soldada',
            'ram_slots_totales',
            'ram_expansion_max',
            'ram_cantidad_soldada',
            'almacenamiento_principal_capacidad',
            'almacenamiento_principal_tipo',
            'almacenamiento_secundario_capacidad',
            'almacenamiento_secundario_tipo',
            'slots_alm_ssd',
            'slots_alm_m2',
            'slots_alm_m2_micro',
            'slots_alm_hdd',
            'slots_alm_msata',
            'grafica_integrada_modelo',
            'grafica_dedicada_modelo',
            'grafica_dedicada_vram',
            'tiene_tarjeta_dedicada',
            'ethernet_tiene',
            'ethernet_es_gigabit',
            'puertos_conectividad',
            'dispositivos_entrada',
            'puertos_hdmi',
            'puertos_mini_hdmi',
            'puertos_vga',
            'puertos_dvi',
            'puertos_displayport',
            'puertos_mini_dp',
            'puertos_usb_2',
            'puertos_usb_30',
            'puertos_usb_31',
            'puertos_usb_32',
            'puertos_usb_c',
            'lectores_sd',
            'lectores_sc',
            'lectores_esata',
            'lectores_sim',
            'bateria_tiene',
            'bateria_salud_percent',
            'bateria_cantidad',
            'bateria_condicion',
            'teclado_idioma',
            'notas_generales',
            'detalles_esteticos',
            'detalles_funcionamiento',
            'puertos_usb',
            'puertos_video',
            'lectores',
        ]);

        // Restaurar defaults
        $this->estatus_general                  = 'En Revisión';
        $this->almacenamiento_secundario_capacidad = 'N/A';
        $this->almacenamiento_secundario_tipo      = 'N/A';
        $this->teclado_idioma                      = 'N/A';
        $this->bateria_tiene                       = true;
        $this->ethernet_tiene                      = false;
        $this->ethernet_es_gigabit                 = false;
        $this->tiene_tarjeta_dedicada              = false;

        $this->lotesModelos = LoteModeloRecibido::all();
        $this->proveedores  = Proveedor::all();

        session()->flash('success', 'Equipo registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.equipos.registrar-equipo');
    }
}
