<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\LoteModeloRecibido;
use App\Models\Equipo;
use App\Models\EquipoBateria;
use App\Models\EquipoMonitor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EditarEquipo extends Component
{
    public Equipo $equipo;


    // =======================
    // LOTES
    // =======================
        public $lotes = [];
        public $proveedores = [];
        public $modelosLote = [];
        public $lotesTerminadosIds = [];

    // =======================
    // Campos base (equipos)
    // =======================
    public $lote_id;
    public $lote_modelo_id;
    public $proveedor_id;

    public $numero_serie;
    public $estatus_general = 'En Revisión';

    public $marca;
    public $modelo;
    public $tipo_equipo;
    public $sistema_operativo;
    public $area_tienda;

    public $procesador_modelo;
    public $procesador_generacion;
    public $procesador_nucleos;
    public $procesador_frecuencia;

    public $ram_capacidad;
    public $ram_tipo;
    public $ram_es_soldada = false;
    public $ram_cantidad_soldada;
    public $ram_sin_slots = false;
    public $ram_expansion_max;
    public $ram_slots_totales;

    public $almacenamiento_principal_capacidad;
    public $almacenamiento_principal_tipo;

    public $almacenamiento_secundario_capacidad = 'N/A';
    public $almacenamiento_secundario_tipo = 'N/A';

    // Slots de almacenamiento (UI -> columnas slots_alm_*)
    public array $slots_almacenamiento = [];
    public $slots_alm_ssd;
    public $slots_alm_m2;
    public $slots_alm_m2_micro;
    public $slots_alm_hdd;
    public $slots_alm_msata;

    // Red
    public $ethernet_tiene = false;
    public $ethernet_es_gigabit = false;

    // Chips (final string)
    public array $conectividad_checks = [];
    public string $conectividad_pick = '';
    public array $entrada_checks = [];
    public string $entrada_pick = '';

    public $puertos_conectividad; // requerido
    public $dispositivos_entrada; // requerido


    

    // Puertos/lectores columnas DB
    public $puertos_hdmi;
    public $puertos_mini_hdmi;
    public $puertos_vga;
    public $puertos_dvi;
    public $puertos_displayport;
    public $puertos_mini_dp;

    public $puertos_usb_2;
    public $puertos_usb_30;
    public $puertos_usb_31;
    public $puertos_usb_32;
    public $puertos_usb_c;

    public $lectores_sd;
    public $lectores_sc;
    public $lectores_esata;
    public $lectores_sim;

    // UI dinámicas
    public array $puertos_usb = [];
    public array $puertos_video = [];
    public array $lectores = [];

    // Baterías (equipo_baterias)
    public $bateria_tiene = true;
    public $bateria1_tipo = null;
    public $bateria1_salud = null;

    public $bateria2_tiene = false;
    public $bateria2_tipo = null;
    public $bateria2_salud = null;

    // Teclado / notas
    public $teclado_idioma = 'N/A';
    public $notas_generales;

    // Detalles (checklist -> texto final)
    public $detalles_esteticos;
    public $detalles_funcionamiento;
    public array $detalles_esteticos_checks = [];
    public ?string $detalles_esteticos_otro = null;
    public array $detalles_funcionamiento_checks = [];
    public ?string $detalles_funcionamiento_otro = null;

    // =======================
    // Pantalla/Monitores (equipo_monitores)
    // =======================
    public $pantalla_pulgadas;
    public $pantalla_resolucion;
    public $pantalla_tipo;      // tipo_panel para integrada
    public $pantalla_es_touch = false;

    // Externa
    public $monitor_incluido = 'NO'; // SI/NO
    public $monitor_pulgadas = null;
    public $monitor_resolucion = null;
    public $monitor_tipo_panel = null;
    public $monitor_es_touch = false;

    // Entradas del monitor (rows)
    public array $monitor_entradas_rows = [];

    // Detalles monitor (en tu BD son strings, no arrays)
    public $monitor_detalles_esteticos_checks = '';
    public $monitor_detalles_esteticos_otro = '';
    public $monitor_detalles_funcionamiento_checks = '';
    public $monitor_detalles_funcionamiento_otro = '';

    public array $monitorEntradasOptions = [
        'HDMI','Mini HDMI','VGA','DVI','DisplayPort','Mini DisplayPort',
        'USB 2.0','USB 3.0','USB 3.1','USB 3.2','USB-C',
    ];

    // =======================
    // Maps (USB/Video/Lectores/Slots/Monitor IN)
    // =======================
    private const MAP_USB = [
        'USB 2.0'    => 'puertos_usb_2',
        'USB 3.0'    => 'puertos_usb_30',
        'USB 3.1'    => 'puertos_usb_31',
        'USB 3.2'    => 'puertos_usb_32',
        'USB-C'      => 'puertos_usb_c',
        'USB tipo C' => 'puertos_usb_c',
    ];

    private const MAP_VIDEO = [
        'HDMI'             => 'puertos_hdmi',
        'Mini HDMI'        => 'puertos_mini_hdmi',
        'VGA'              => 'puertos_vga',
        'DVI'              => 'puertos_dvi',
        'DisplayPort'      => 'puertos_displayport',
        'Mini DisplayPort' => 'puertos_mini_dp',
    ];

    private const MAP_LECTORES = [
        'SD'        => 'lectores_sd',
        'microSD'   => 'lectores_sd',
        'SmartCard' => 'lectores_sc',
        'eSATA'     => 'lectores_esata',
        'SIM'       => 'lectores_sim',
    ];

    private const MAP_SLOTS = [
        'SSD'       => 'slots_alm_ssd',
        'M.2'       => 'slots_alm_m2',
        'M.2 MICRO' => 'slots_alm_m2_micro',
        'HDD'       => 'slots_alm_hdd',
        'MSATA'     => 'slots_alm_msata',
    ];

    private const MAP_MONITOR_IN = [
        'HDMI'             => 'in_hdmi',
        'Mini HDMI'        => 'in_mini_hdmi',
        'VGA'              => 'in_vga',
        'DVI'              => 'in_dvi',
        'DisplayPort'      => 'in_displayport',
        'Mini DisplayPort' => 'in_mini_displayport',
        'USB 2.0'          => 'in_usb_2',
        'USB 3.0'          => 'in_usb_3',
        'USB 3.1'          => 'in_usb_31',
        'USB 3.2'          => 'in_usb_32',
        'USB-C'            => 'in_usb_c', // si tu tabla NO tiene esta col, quítala
    ];

    // =======================
    // Mount
    // =======================
    public function mount(Equipo $equipo): void

    {
        $this->equipo = $equipo;

        // IDs / readonly (pero los mostramos)
        $this->lote_id        = $this->equipo->lote_id ?? null;
        $this->lote_modelo_id = $this->equipo->lote_modelo_id ?? null;
        $this->proveedor_id   = $this->equipo->proveedor_id ?? null;

        // Copia simple de campos (equipos)
        foreach ($this->equipo->getAttributes() as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        // Defaults seguros
        $this->almacenamiento_secundario_capacidad = $this->almacenamiento_secundario_capacidad ?: 'N/A';
        $this->almacenamiento_secundario_tipo      = $this->almacenamiento_secundario_tipo ?: 'N/A';
        $this->teclado_idioma                      = $this->teclado_idioma ?: 'N/A';

        // Hidrata arrays UI desde columnas (USB/Video/Lectores/Slots)
        $this->hydratePuertosFromColumns();
        $this->hydrateSlotsFromColumns();

        // Detalles (parse del texto guardado: "A, B | Otro: X")
        [$this->detalles_esteticos_checks, $this->detalles_esteticos_otro] =
            $this->parseChecksText((string) ($this->detalles_esteticos ?? ''));

        [$this->detalles_funcionamiento_checks, $this->detalles_funcionamiento_otro] =
            $this->parseChecksText((string) ($this->detalles_funcionamiento ?? ''));

        // Baterías (equipo_baterias)
        $this->hydrateBaterias();

        // Pantalla/Monitores (equipo_monitores)
        $this->hydrateMonitor();
    }

    // =======================
    // Validación
    // =======================
    protected function rules(): array
    {
        return [
            'numero_serie' => [
                'required','string','max:255',
                Rule::unique('equipos', 'numero_serie')->ignore($this->equipo->id),
            ],

            'puertos_conectividad' => 'required|string|max:255',
            'dispositivos_entrada' => 'required|string|max:255',

            'monitor_incluido' => 'nullable|in:SI,NO',

            'monitor_entradas_rows' => 'array',
            'monitor_entradas_rows.*.tipo' => 'nullable|in:' . implode(',', $this->monitorEntradasOptions),
            'monitor_entradas_rows.*.cantidad' => 'nullable|integer|min:1|max:10',

            'slots_almacenamiento' => 'array',
            'slots_almacenamiento.*.tipo' => 'nullable|in:SSD,M.2,M.2 MICRO,HDD,MSATA',
            'slots_almacenamiento.*.cantidad' => 'nullable|integer|min:1|max:10',

            'puertos_usb' => 'array',
            'puertos_usb.*.tipo' => 'nullable|string|max:50',
            'puertos_usb.*.cantidad' => 'nullable|integer|min:1|max:10',

            'puertos_video' => 'array',
            'puertos_video.*.tipo' => 'nullable|string|max:50',
            'puertos_video.*.cantidad' => 'nullable|integer|min:1|max:10',

            'lectores' => 'array',
            'lectores.*.tipo' => 'nullable|string|max:50',
            'lectores.*.detalle' => 'nullable|string|max:100',

            'detalles_esteticos_checks' => 'array|min:1',
            'detalles_funcionamiento_checks' => 'array|min:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'numero_serie.unique' => 'Este número de serie ya está registrado.',
            'puertos_conectividad.required' => 'Selecciona al menos un puerto de conectividad.',
            'dispositivos_entrada.required' => 'Selecciona al menos un dispositivo de entrada.',
            'detalles_esteticos_checks.min' => 'Selecciona al menos un detalle estético (o N/A).',
            'detalles_funcionamiento_checks.min' => 'Selecciona al menos un detalle de funcionamiento (o N/A).',
        ];
    }

    // =======================
    // Guardar (actualizar)
    // =======================
    public function actualizar(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: $e->validator->errors()->first());
            throw $e;
        }

        // Defaults
        $this->almacenamiento_secundario_capacidad = $this->almacenamiento_secundario_capacidad ?: 'N/A';
        $this->almacenamiento_secundario_tipo      = $this->almacenamiento_secundario_tipo ?: 'N/A';
        $this->teclado_idioma                      = $this->teclado_idioma ?: 'N/A';

        // Detalles texto
        $this->detalles_esteticos = $this->buildChecksText($this->detalles_esteticos_checks, $this->detalles_esteticos_otro);
        $this->detalles_funcionamiento = $this->buildChecksText($this->detalles_funcionamiento_checks, $this->detalles_funcionamiento_otro);

        // Blindaje
        $this->puertos_conectividad = $this->truncate($this->puertos_conectividad, 255);
        $this->dispositivos_entrada = $this->truncate($this->dispositivos_entrada, 255);

        // Mappers a columnas
        $this->mapSlotsToDbColumns();
        $this->applyAggregatesToEquipoColumns();

        DB::transaction(function () {
            // Payload de update (solo campos que existan en equipos)
            $payload = $this->equipoPayloadForUpdate();

            // Mantén quién editó (si tienes columna, ajusta)
            // $payload['actualizado_por_user_id'] = Auth::id();

            $this->equipo->update($payload);

            $this->guardarBaterias($this->equipo->id);
            $this->guardarMonitor($this->equipo->id);
        });

        $this->dispatch('toast', type: 'success', message: 'Equipo actualizado correctamente.');
        $this->dispatch('equipo-editado'); // por si quieres escuchar en la lista
    }

    private function equipoPayloadForUpdate(): array
    {
        // IMPORTANTE: NO tocamos lote/modelo/proveedor aquí (read-only)
        return [
            'numero_serie' => $this->numero_serie,
            'estatus_general' => $this->estatus_general,

            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'tipo_equipo' => $this->tipo_equipo,
            'sistema_operativo' => $this->sistema_operativo,
            'area_tienda' => $this->area_tienda,

            'procesador_modelo' => $this->procesador_modelo,
            'procesador_generacion' => $this->procesador_generacion,
            'procesador_nucleos' => $this->procesador_nucleos,
            'procesador_frecuencia' => $this->procesador_frecuencia,

            'ram_capacidad' => $this->ram_capacidad,
            'ram_tipo' => $this->ram_tipo,
            'ram_es_soldada' => (int) ((bool) $this->ram_es_soldada),
            'ram_cantidad_soldada' => $this->ram_cantidad_soldada,
            'ram_sin_slots' => (int) ((bool) $this->ram_sin_slots),
            'ram_expansion_max' => $this->ram_expansion_max,
            'ram_slots_totales' => $this->ram_slots_totales,

            'almacenamiento_principal_capacidad' => $this->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo' => $this->almacenamiento_principal_tipo,

            'almacenamiento_secundario_capacidad' => $this->almacenamiento_secundario_capacidad,
            'almacenamiento_secundario_tipo' => $this->almacenamiento_secundario_tipo,

            // slots_alm_* ya mapeados
            'slots_alm_ssd' => $this->slots_alm_ssd,
            'slots_alm_m2' => $this->slots_alm_m2,
            'slots_alm_m2_micro' => $this->slots_alm_m2_micro,
            'slots_alm_hdd' => $this->slots_alm_hdd,
            'slots_alm_msata' => $this->slots_alm_msata,

            // red
            'ethernet_tiene' => (int) ((bool) $this->ethernet_tiene),
            'ethernet_es_gigabit' => (int) ((bool) $this->ethernet_es_gigabit),

            // conectividad / entrada finales
            'puertos_conectividad' => $this->puertos_conectividad,
            'dispositivos_entrada' => $this->dispositivos_entrada,

            // puertos/lectores (ya agregados por map)
            'puertos_hdmi' => $this->puertos_hdmi,
            'puertos_mini_hdmi' => $this->puertos_mini_hdmi,
            'puertos_vga' => $this->puertos_vga,
            'puertos_dvi' => $this->puertos_dvi,
            'puertos_displayport' => $this->puertos_displayport,
            'puertos_mini_dp' => $this->puertos_mini_dp,

            'puertos_usb_2' => $this->puertos_usb_2,
            'puertos_usb_30' => $this->puertos_usb_30,
            'puertos_usb_31' => $this->puertos_usb_31,
            'puertos_usb_32' => $this->puertos_usb_32,
            'puertos_usb_c' => $this->puertos_usb_c,

            'lectores_sd' => $this->lectores_sd,
            'lectores_sc' => $this->lectores_sc,
            'lectores_esata' => $this->lectores_esata,
            'lectores_sim' => $this->lectores_sim,

            // detalles
            'detalles_esteticos' => $this->truncate($this->detalles_esteticos, 255),
            'detalles_funcionamiento' => $this->truncate($this->detalles_funcionamiento, 255),

            'teclado_idioma' => $this->teclado_idioma,
            'notas_generales' => $this->notas_generales,
        ];
    }

    // =======================
    // Baterías
    // =======================
    private function hydrateBaterias(): void
    {
        $bats = EquipoBateria::query()
            ->where('equipo_id', $this->equipo->id)
            ->orderBy('id')
            ->get();

        if ($bats->isEmpty()) {
            $this->bateria_tiene = false;
            $this->bateria2_tiene = false;
            return;
        }

        $this->bateria_tiene = true;

        $b1 = $bats->get(0);
        $b2 = $bats->get(1);

        if ($b1) {
            $this->bateria1_tipo = $b1->tipo;
            $this->bateria1_salud = $b1->salud;
        }

        if ($b2) {
            $this->bateria2_tiene = true;
            $this->bateria2_tipo = $b2->tipo;
            $this->bateria2_salud = $b2->salud;
        } else {
            $this->bateria2_tiene = false;
        }
    }

    private function guardarBaterias(int $equipoId): void
    {
        // estrategia simple y segura
        EquipoBateria::where('equipo_id', $equipoId)->delete();

        if (!$this->bateria_tiene) {
            return;
        }

        if ($this->bateria1_tipo || $this->bateria1_salud) {
            EquipoBateria::create([
                'equipo_id' => $equipoId,
                'tipo'      => $this->bateria1_tipo,
                'salud'     => $this->bateria1_salud,
            ]);
        }

        if ($this->bateria2_tiene && ($this->bateria2_tipo || $this->bateria2_salud)) {
            EquipoBateria::create([
                'equipo_id' => $equipoId,
                'tipo'      => $this->bateria2_tipo,
                'salud'     => $this->bateria2_salud,
            ]);
        }
    }

    // =======================
    // Monitor
    // =======================
    private function hydrateMonitor(): void
    {
        $m = EquipoMonitor::query()->where('equipo_id', $this->equipo->id)->first();

        // Si no hay registro, dejamos todo como “no incluido”
        if (!$m) {
            $this->monitor_incluido = 'NO';
            return;
        }

        if ($m->origen_pantalla === 'INTEGRADA') {
            $this->pantalla_pulgadas  = $m->pulgadas;
            $this->pantalla_resolucion = $m->resolucion;
            $this->pantalla_tipo = $m->tipo_panel;
            $this->pantalla_es_touch = (bool) $m->es_touch;

            $this->monitor_incluido = 'NO';
            return;
        }

        // EXTERNA
        $this->monitor_incluido = 'SI';
        $this->monitor_pulgadas = $m->pulgadas;
        $this->monitor_resolucion = $m->resolucion;
        $this->monitor_tipo_panel = $m->tipo_panel;
        $this->monitor_es_touch = (bool) $m->es_touch;

        $this->monitor_detalles_esteticos_checks = (string) ($m->detalles_esteticos_checks ?? '');
        $this->monitor_detalles_esteticos_otro = (string) ($m->detalles_esteticos_otro ?? '');
        $this->monitor_detalles_funcionamiento_checks = (string) ($m->detalles_funcionamiento_checks ?? '');
        $this->monitor_detalles_funcionamiento_otro = (string) ($m->detalles_funcionamiento_otro ?? '');

        // Entradas (in_*)
        $rows = [];
        foreach (self::MAP_MONITOR_IN as $label => $col) {
            $qty = (int) ($m->{$col} ?? 0);
            if ($qty > 0) {
                $rows[] = ['tipo' => $label, 'cantidad' => $qty];
            }
        }
        $this->monitor_entradas_rows = $rows;
    }

    private function guardarMonitor(int $equipoId): void
    {
        // INTEGRADA
        if ($this->pantallaIntegrada) {
            EquipoMonitor::updateOrCreate(
                ['equipo_id' => $equipoId],
                [
                    'origen_pantalla' => 'INTEGRADA',
                    'incluido'        => 1,
                    'pulgadas'        => $this->pantalla_pulgadas ?: null,
                    'resolucion'      => $this->pantalla_resolucion ?: null,
                    'tipo_panel'      => $this->pantalla_tipo ?: null,
                    'es_touch'        => (int) ((bool) $this->pantalla_es_touch),
                ] + $this->monitorInputsPayload([]) // limpia entradas
            );
            return;
        }

        // EXTERNA
        if (!$this->pantallaExterna) {
            return;
        }

        if ($this->monitor_incluido !== 'SI') {
            EquipoMonitor::where('equipo_id', $equipoId)->delete();
            return;
        }

        $counts = $this->aggregateCounters($this->monitor_entradas_rows, 'tipo', 'cantidad');
        $inputsPayload = $this->monitorInputsPayload($counts);

        EquipoMonitor::updateOrCreate(
            ['equipo_id' => $equipoId],
            [
                'origen_pantalla' => 'EXTERNA',
                'incluido'        => 1,
                'pulgadas'        => $this->monitor_pulgadas ?: null,
                'resolucion'      => $this->monitor_resolucion ?: null,
                'tipo_panel'      => $this->monitor_tipo_panel ?: null,
                'es_touch'        => (int) ((bool) $this->monitor_es_touch),

                'detalles_esteticos_checks'      => $this->truncate($this->monitor_detalles_esteticos_checks ?: null, 255),
                'detalles_esteticos_otro'        => $this->truncate($this->monitor_detalles_esteticos_otro ?: null, 255),
                'detalles_funcionamiento_checks' => $this->truncate($this->monitor_detalles_funcionamiento_checks ?: null, 255),
                'detalles_funcionamiento_otro'   => $this->truncate($this->monitor_detalles_funcionamiento_otro ?: null, 255),
            ] + $inputsPayload
        );
    }

    private function monitorInputsPayload(array $countsByLabel): array
    {
        $payload = [];
        foreach (self::MAP_MONITOR_IN as $label => $col) {
            $qty = (int) ($countsByLabel[$label] ?? 0);
            $payload[$col] = $qty > 0 ? $qty : null;
        }
        return $payload;
    }

    // =======================
    // Pantalla integrada vs externa (misma lógica que registrar)
    // =======================
    private function tipoKey(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        $v = preg_replace('/\s+/', ' ', $v);
        return $v;
    }

    public function getPantallaIntegradaProperty(): bool
    {
        return in_array($this->tipoKey($this->tipo_equipo), ['laptop','all in one','tablet','2 en 1'], true);
    }

    public function getPantallaExternaProperty(): bool
    {
        return in_array($this->tipoKey($this->tipo_equipo), ['escritorio','micro pc','gamer'], true);
    }

    public function updatedTipoEquipo(): void
    {
        if ($this->pantallaIntegrada) {
            $this->monitor_incluido = 'NO';
            $this->resetMonitorExternoFields();
            return;
        }

        if ($this->pantallaExterna && $this->monitor_incluido !== 'SI') {
            $this->resetMonitorAllFields();
        }
    }

    public function updatedMonitorIncluido(): void
    {
        if ($this->pantallaExterna && $this->monitor_incluido !== 'SI') {
            $this->resetMonitorAllFields();
        }
    }

    private function resetMonitorExternoFields(): void
    {
        $this->monitor_entradas_rows = [];
        $this->monitor_detalles_esteticos_checks = '';
        $this->monitor_detalles_esteticos_otro = '';
        $this->monitor_detalles_funcionamiento_checks = '';
        $this->monitor_detalles_funcionamiento_otro = '';
    }

    private function resetMonitorAllFields(): void
    {
        $this->monitor_pulgadas = null;
        $this->monitor_resolucion = null;
        $this->monitor_tipo_panel = null;
        $this->monitor_es_touch = false;
        $this->resetMonitorExternoFields();
    }

    // =======================
    // Acciones UI (igual que registrar)
    // =======================
    public function addPuertoUsb(): void { $this->puertos_usb[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoUsb($i): void { $this->unsetIndex($this->puertos_usb, $i); }

    public function addPuertoVideo(): void { $this->puertos_video[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoVideo($i): void { $this->unsetIndex($this->puertos_video, $i); }

    public function addLector(): void { $this->lectores[] = ['tipo' => '', 'detalle' => '']; }
    public function removeLector($i): void { $this->unsetIndex($this->lectores, $i); }

    public function addSlotAlmacenamiento(): void { $this->slots_almacenamiento[] = ['tipo' => '', 'cantidad' => null]; }
    public function removeSlotAlmacenamiento($i): void { $this->unsetIndex($this->slots_almacenamiento, $i); }

    public function addMonitorEntrada(): void { $this->monitor_entradas_rows[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeMonitorEntrada($i): void { $this->unsetIndex($this->monitor_entradas_rows, $i); }

    private function unsetIndex(array &$arr, $i): void
    {
        if (!isset($arr[$i])) return;
        unset($arr[$i]);
        $arr = array_values($arr);
    }

    public function toggleRamSoldada(): void
    {
        $this->ram_es_soldada = !$this->ram_es_soldada;

        if (!$this->ram_es_soldada) {
            $this->ram_sin_slots = false;
            $this->ram_cantidad_soldada = null;
            $this->ram_expansion_max = null;
            $this->ram_slots_totales = null;
        }
    }

    public function toggleRamSinSlots(): void
    {
        $this->ram_sin_slots = !$this->ram_sin_slots;

        if ($this->ram_sin_slots) {
            $this->ram_expansion_max = '0 GB';
            $this->ram_slots_totales = '0';
        } else {
            $this->ram_expansion_max = null;
            $this->ram_slots_totales = null;
        }
    }

    public function toggleBateriaTiene(): void
    {
        $this->bateria_tiene = !$this->bateria_tiene;

        if (!$this->bateria_tiene) {
            $this->bateria1_tipo = null;
            $this->bateria1_salud = null;

            $this->bateria2_tiene = false;
            $this->bateria2_tipo = null;
            $this->bateria2_salud = null;
        }
    }

    public function toggleBateria2Tiene(): void
    {
        $this->bateria2_tiene = !$this->bateria2_tiene;

        if (!$this->bateria2_tiene) {
            $this->bateria2_tipo = null;
            $this->bateria2_salud = null;
        }
    }

    // =======================
    // Hydrates (DB -> arrays UI)
    // =======================
    private function hydratePuertosFromColumns(): void
    {
        // USB
        $usb = [];
        foreach (self::MAP_USB as $label => $col) {
            $qty = (int) ($this->{$col} ?? 0);
            if ($qty > 0) $usb[] = ['tipo' => $label, 'cantidad' => $qty];
        }
        $this->puertos_usb = $usb;

        // Video
        $vid = [];
        foreach (self::MAP_VIDEO as $label => $col) {
            $qty = (int) ($this->{$col} ?? 0);
            if ($qty > 0) $vid[] = ['tipo' => $label, 'cantidad' => $qty];
        }
        $this->puertos_video = $vid;

        // Lectores (son counts en tu modelo actual)
        $lec = [];
        foreach (self::MAP_LECTORES as $label => $col) {
            $qty = (int) ($this->{$col} ?? 0);
            if ($qty > 0) $lec[] = ['tipo' => $label, 'detalle' => ''];
        }
        $this->lectores = $lec;

        // chips finales ya vienen como string en equipos
        $this->puertos_conectividad = $this->puertos_conectividad ?: '';
        $this->dispositivos_entrada = $this->dispositivos_entrada ?: '';
    }

    private function hydrateSlotsFromColumns(): void
    {
        $rows = [];
        foreach (self::MAP_SLOTS as $label => $col) {
            $qty = (int) ($this->{$col} ?? 0);
            if ($qty > 0) $rows[] = ['tipo' => $label, 'cantidad' => $qty];
        }
        $this->slots_almacenamiento = $rows;
    }

    // =======================
    // Agregadores / Mappers
    // =======================
    private function applyAggregatesToEquipoColumns(): void
    {
        // limpia primero (para que si quitan un puerto se refleje)
        foreach (self::MAP_USB as $col) $this->{$col} = null;
        foreach (self::MAP_VIDEO as $col) $this->{$col} = null;
        foreach (self::MAP_LECTORES as $col) $this->{$col} = null;

        $usbCounts = $this->aggregateCounters($this->puertos_usb, 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($usbCounts, self::MAP_USB);

        $videoCounts = $this->aggregateCounters($this->puertos_video, 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($videoCounts, self::MAP_VIDEO);

        $lectorCounts = $this->aggregateCounters($this->lectores, 'tipo', null);
        $this->applyMapCountsToEquipo($lectorCounts, self::MAP_LECTORES);
    }

    private function aggregateCounters(array $rows, string $keyField, ?string $qtyField): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $k = trim((string) ($row[$keyField] ?? ''));
            if ($k === '') continue;

            $qty = 1;
            if ($qtyField) {
                $qty = (int) ($row[$qtyField] ?? 1);
                $qty = $qty > 0 ? $qty : 1;
            }

            $counts[$k] = ($counts[$k] ?? 0) + $qty;
        }
        return $counts;
    }

    private function applyMapCountsToEquipo(array $countsByLabel, array $mapLabelToEquipoColumn): void
    {
        foreach ($countsByLabel as $label => $count) {
            if (!isset($mapLabelToEquipoColumn[$label])) continue;
            $col = $mapLabelToEquipoColumn[$label];
            $this->{$col} = (string) $count;
        }
    }

    private function mapSlotsToDbColumns(): void
    {
        $this->slots_alm_ssd = null;
        $this->slots_alm_m2 = null;
        $this->slots_alm_m2_micro = null;
        $this->slots_alm_hdd = null;
        $this->slots_alm_msata = null;

        foreach (($this->slots_almacenamiento ?? []) as $row) {
            $tipo = trim((string) ($row['tipo'] ?? ''));
            if ($tipo === '' || !isset(self::MAP_SLOTS[$tipo])) continue;

            $cant = $row['cantidad'] ?? null;
            $valor = (is_numeric($cant) && (int) $cant > 0) ? (string) ((int) $cant) : null;

            $col = self::MAP_SLOTS[$tipo];
            $this->{$col} = $valor;
        }
    }

    // =======================
    // Helpers texto checklist
    // =======================
    private function buildChecksText(array $checks, ?string $otro): string
    {
        $checks = $checks ?? [];

        if (in_array('N/A', $checks, true)) {
            $checks = ['N/A'];
            $otro = null;
        }

        $txt = implode(', ', $checks);

        if (!empty($otro)) {
            $txt .= ($txt ? ' | ' : '') . 'Otro: ' . $otro;
        }

        return $txt;
    }

    private function parseChecksText(string $txt): array
    {
        $txt = trim($txt);
        if ($txt === '') return [[], null];

        $otro = null;

        // separa " | Otro: "
        $parts = explode('|', $txt, 2);
        $checksPart = trim($parts[0] ?? '');
        $otroPart   = trim($parts[1] ?? '');

        if ($otroPart !== '') {
            $otroPart = preg_replace('/^Otro:\s*/i', '', $otroPart);
            $otro = trim($otroPart) !== '' ? trim($otroPart) : null;
        }

        $checks = [];
        if ($checksPart !== '') {
            $checks = array_values(array_filter(array_map('trim', explode(',', $checksPart))));
        }

        return [$checks, $otro];
    }

    private function truncate($value, int $max)
    {
        if ($value === null) return null;
        $value = (string) $value;
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }

    public function render()
    {
        return view('livewire.inventario.editar-equipo');
    }
}
