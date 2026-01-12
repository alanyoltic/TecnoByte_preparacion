<?php

namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Models\{
    Equipo,
    Lote,
    Proveedor,
    LoteModeloRecibido,
    EquipoBateria,
    EquipoMonitor,
    EquipoGpu
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarEquipo extends Component
{
    // =======================
    // Catálogos / listas
    // =======================
    public $proveedores = [];
    public $lotes = [];
    public $modelosLote = [];
    public $lotesTerminadosIds = [];

    // =======================
    // Dependientes (Lote -> Modelo -> Marca/Modelo/Proveedor)
    // =======================
    public $lote_id;
    public $lote_modelo_id;
    public $proveedor_id;
    public $marca;
    public $modelo;

    // =======================
    // Base
    // =======================
    public $numero_serie;
    public $estatus_general = 'En Revisión';

    public $tipo_equipo;
    public $sistema_operativo;
    public $area_tienda;

    // =======================
    // CPU
    // =======================
    public $procesador_modelo;
    public $procesador_generacion;
    public $procesador_nucleos;
    public $procesador_frecuencia;

    // =======================
    // Pantalla integrada
    // =======================
    public $pantalla_pulgadas;
    public $pantalla_resolucion;
    public $pantalla_es_touch = false;
    public $pantalla_tipo;

    // =======================
    // Monitor externo
    // =======================
    public $monitor_incluido = null; // 'SI'/'NO'
    public $monitor_pulgadas;
    public $monitor_resolucion;
    public $monitor_tipo_panel;
    public $monitor_es_touch = false;

    // Entradas monitor: [{tipo:'HDMI', cantidad:1}]
    public array $monitor_entradas_rows = [];

    public array $monitorEntradasOptions = [
        'HDMI', 'Mini HDMI', 'VGA', 'DVI',
        'DisplayPort', 'Mini DisplayPort',
        'USB 2.0', 'USB 3.0', 'USB 3.1', 'USB 3.2', 'USB-C',
    ];

    // Detalles monitor (chips -> strings)
    public $monitor_detalles_esteticos_checks = '';
    public $monitor_detalles_esteticos_otro = '';
    public $monitor_detalles_funcionamiento_checks = '';
    public $monitor_detalles_funcionamiento_otro = '';

    // =======================
    // RAM
    // =======================
    public $ram_total;
    public $ram_tipo;
    public $ram_es_soldada = false;
    public $ram_slots_totales;
    public $ram_expansion_max;
    public $ram_cantidad_soldada;
    public $ram_sin_slots = false;

    // =======================
    // Almacenamiento
    // =======================
    public $almacenamiento_principal_capacidad;
    public $almacenamiento_principal_tipo;

    public $almacenamiento_secundario_capacidad = 'N/A';
    public $almacenamiento_secundario_tipo = 'N/A';

    // Slots almacenamiento (UI) + columnas DB
    public array $slots_almacenamiento = [];
    public $slots_alm_ssd;
    public $slots_alm_m2;
    public $slots_alm_m2_micro;
    public $slots_alm_hdd;
    public $slots_alm_msata;

    // =======================
    // GPU (NUEVO ESQUEMA)
    // =======================
    public bool $gpu_integrada_tiene = false;
    public ?string $gpu_integrada_marca = null;
    public ?string $gpu_integrada_modelo = null;
    public ?int $gpu_integrada_vram = null;

    public bool $gpu_dedicada_tiene = false;
    public ?string $gpu_dedicada_marca = null;
    public ?string $gpu_dedicada_modelo = null;
    public ?int $gpu_dedicada_vram = null;

    public string $gpu_integrada_marca_mode = 'LISTA';
    public string $gpu_dedicada_marca_mode  = 'LISTA';

    public ?string $gpu_integrada_vram_unidad = 'GB';
    public ?string $gpu_dedicada_vram_unidad  = 'GB';



    // =======================
    // Red
    // =======================
    public $ethernet_tiene = false;
    public $ethernet_es_gigabit = false;

    // =======================
    // Puertos / lectores (UI) + columnas DB
    // =======================
    public array $puertos_usb = [];
    public array $puertos_video = [];
    public array $lectores = [];

    public $puertos_conectividad; // requerido (texto final)
    public $dispositivos_entrada; // requerido (texto final)

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

    // =======================
    // Baterías
    // =======================
    public $bateria_tiene = true;
    public $bateria1_tipo = null;
    public $bateria1_salud = null;

    public $bateria2_tiene = false;
    public $bateria2_tipo = null;
    public $bateria2_salud = null;

    // =======================
    // Otros
    // =======================
    public $teclado_idioma = 'N/A';
    public $notas_generales;

    public array $detalles_esteticos_checks = [];
    public ?string $detalles_esteticos_otro = null;
    public array $detalles_funcionamiento_checks = [];
    public ?string $detalles_funcionamiento_otro = null;

    public $detalles_esteticos;
    public $detalles_funcionamiento;

    // =======================
    // MAPS (UI -> columnas DB)
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

    // Entradas monitor -> columnas equipo_monitores (ajusta si tu tabla no tiene alguna)
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
        'USB-C'            => 'in_usb_c',
    ];

    // =======================
    // Lifecycle
    // =======================
    public function mount(): void
    {
        $this->cargarCatalogos();
        $this->setDefaults();
    }

    private function setDefaults(): void
    {
        $this->estatus_general = 'En Revisión';

        $this->almacenamiento_secundario_capacidad = 'N/A';
        $this->almacenamiento_secundario_tipo = 'N/A';
        $this->teclado_idioma = 'N/A';

        $this->bateria_tiene = true;
        $this->bateria2_tiene = false;

        $this->ethernet_tiene = false;
        $this->ethernet_es_gigabit = false;

        $this->puertos_usb = [];
        $this->puertos_video = [];
        $this->lectores = [];
        $this->slots_almacenamiento = [];
        $this->monitor_entradas_rows = [];
        $this->modelosLote = [];

        $this->gpu_integrada_tiene = false;
        $this->gpu_dedicada_tiene = false;

        
    }

    // =======================
    // Catálogos
    // =======================
    private function cargarCatalogos(): void
    {
        $this->proveedores = Proveedor::orderBy('nombre_empresa')->get();

        $todosLotes = Lote::with([
                'proveedor',
                'modelosRecibidos' => fn ($q) => $q->withCount('equipos'),
            ])
            ->orderBy('fecha_llegada', 'desc')
            ->get();

        $lotesConPendientes = collect();
        $lotesTerminados = collect();

        foreach ($todosLotes as $lote) {
            $tienePendientes = $lote->modelosRecibidos->contains(function ($m) {
                $total = (int) $m->cantidad_recibida;
                $registrados = (int) ($m->equipos_count ?? 0);
                return $registrados < $total;
            });

            $tienePendientes ? $lotesConPendientes->push($lote) : $lotesTerminados->push($lote);
        }

        $terminadosTomados = $lotesTerminados->take(2);
        $this->lotesTerminadosIds = $terminadosTomados->pluck('id')->toArray();

        $this->lotes = $lotesConPendientes->concat($terminadosTomados)->values();
    }

    // =======================
    // Lote / Modelo dependiente
    // =======================
    public function actualizarLote($loteId): void
    {
        $this->lote_modelo_id = null;
        $this->marca = null;
        $this->modelo = null;
        $this->modelosLote = [];

        if (!$loteId) {
            $this->proveedor_id = null;
            return;
        }

        $lote = Lote::with(['proveedor', 'modelosRecibidos'])->find($loteId);
        if (!$lote) {
            $this->proveedor_id = null;
            return;
        }

        $this->proveedor_id = $lote->proveedor_id;

        $this->modelosLote = $lote->modelosRecibidos()
            ->withCount('equipos')
            ->get()
            ->filter(function ($m) {
                $total = (int) $m->cantidad_recibida;
                $registrados = (int) ($m->equipos_count ?? 0);
                return $registrados < $total;
            })
            ->map(fn ($m) => ['id' => $m->id, 'marca' => $m->marca, 'modelo' => $m->modelo])
            ->values()
            ->toArray();
    }

    public function actualizarModelo($modeloId): void
    {
        $this->marca = null;
        $this->modelo = null;

        if (!$modeloId) return;

        $lm = LoteModeloRecibido::find($modeloId);
        if (!$lm) return;

        $this->marca = $lm->marca;
        $this->modelo = $lm->modelo;
    }

    // =======================
    // Helpers UI dinámicos
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

    // =======================
    // Toggles
    // =======================
    public function updatedEthernetTiene($value): void
    {
        if (!$value) $this->ethernet_es_gigabit = false;
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
    // Pantalla: integrada vs externa
    // =======================
    private function tipoKey(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        $v = preg_replace('/\s+/', ' ', $v);
        return $v;
    }

    private function isLaptopLikeTipo(?string $tipo): bool
    {
        return in_array($this->tipoKey($tipo), ['laptop','2 en 1','all in one','tablet'], true)
            || in_array(trim((string)$tipo), ['LAPTOP','2 EN 1','ALL IN ONE','TABLET'], true);
    }

    private function isPcLikeTipo(?string $tipo): bool
    {
        return in_array($this->tipoKey($tipo), ['escritorio','micro pc','gamer'], true)
            || in_array(trim((string)$tipo), ['ESCRITORIO','MICRO PC','GAMER'], true);
    }

    public function getPantallaIntegradaProperty(): bool
    {
        return $this->isLaptopLikeTipo($this->tipo_equipo);
    }

    public function getPantallaExternaProperty(): bool
    {
        return $this->isPcLikeTipo($this->tipo_equipo);
    }

    public function updatedTipoEquipo($value = null): void
    {
        // 1) Monitor según tipo de equipo
        if ($this->pantallaIntegrada) {
            // Integrada => monitor externo no aplica
            $this->monitor_incluido = 'NO';
            $this->resetMonitorAllFields();
        } elseif ($this->pantallaExterna) {
            // Externa => si no es SI, limpiamos (pero dejamos elegir)
            if ($this->monitor_incluido !== 'SI') {
                $this->resetMonitorAllFields();
            }
        } else {
            // Tipo no definido / raro => no forzar
        }

        // 2) GPU defaults por tipo
        $this->syncGpuDefaultsByTipo();
    }

    public function updatedMonitorIncluido(): void
    {
        if ($this->pantallaExterna && $this->monitor_incluido !== 'SI') {
            $this->resetMonitorAllFields();
        }
    }

    private function resetMonitorAllFields(): void
    {
        $this->monitor_pulgadas = null;
        $this->monitor_resolucion = null;
        $this->monitor_tipo_panel = null;
        $this->monitor_es_touch = false;

        $this->monitor_entradas_rows = [];
        $this->monitor_detalles_esteticos_checks = '';
        $this->monitor_detalles_esteticos_otro = '';
        $this->monitor_detalles_funcionamiento_checks = '';
        $this->monitor_detalles_funcionamiento_otro = '';
    }

    // =======================
    // GPU: reglas
    // =======================
    private function syncGpuDefaultsByTipo(): void
    {
        if ($this->isLaptopLikeTipo($this->tipo_equipo)) {
            // Laptop-like: integrada SIEMPRE (no la borres)
            $this->gpu_integrada_tiene = true;
            return;
        }

        // PC-like o desconocido: no forzamos nada
    }

    public function updatedGpuIntegradaTiene(): void
    {
        // Si es laptop-like, no permitimos apagar
        if ($this->isLaptopLikeTipo($this->tipo_equipo)) {
            $this->gpu_integrada_tiene = true;
            return;
        }

        if (!$this->gpu_integrada_tiene) {
            $this->gpu_integrada_marca = null;
            $this->gpu_integrada_modelo = null;
            $this->gpu_integrada_vram = null;
        }
    }

    public function updatedGpuDedicadaTiene(): void
    {
        if (!$this->gpu_dedicada_tiene) {
            $this->gpu_dedicada_marca = null;
            $this->gpu_dedicada_modelo = null;
            $this->gpu_dedicada_vram = null;
            $this->gpu_dedicada_vram_unidad = 'GB';

        }
    }


    public function updatedGpuIntegradaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->gpu_integrada_marca, ['INTEL','AMD','NVIDIA'], true)) {
            $this->gpu_integrada_marca = '';
        }
    }

    public function updatedGpuDedicadaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->gpu_dedicada_marca, ['INTEL','AMD','NVIDIA'], true)) {
            $this->gpu_dedicada_marca = '';
        }
    }


    // =======================
    // Validación
    // =======================
    protected function rules(): array
    {
        return [
            'lote_id'        => 'required|exists:lotes,id',
            'lote_modelo_id' => 'required|exists:lote_modelos_recibidos,id',
            'proveedor_id'   => 'required|exists:proveedores,id',

            'numero_serie'   => 'required|string|max:255|unique:equipos,numero_serie',
            'modelo'         => 'required|string|max:255',

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

            'gpu_integrada_tiene' => ['boolean'],
            'gpu_integrada_marca' => ['nullable','string','max:120'],
            'gpu_integrada_modelo' => ['nullable','string','max:180'],
            'gpu_integrada_vram' => ['nullable','integer','min:0','max:64'],

            'gpu_dedicada_tiene' => ['boolean'],
            'gpu_dedicada_vram' => ['nullable','integer','min:0','max:64'],
            'gpu_dedicada_marca'  => [ $this->gpu_dedicada_tiene ? 'required' : 'nullable', 'string','max:120' ],
            'gpu_dedicada_modelo' => [ $this->gpu_dedicada_tiene ? 'required' : 'nullable', 'string','max:180' ],
        ];
    }

    protected function messages(): array
    {
        return [
            'lote_id.required' => 'Selecciona un lote.',
            'lote_modelo_id.required' => 'Selecciona un modelo del lote.',
            'proveedor_id.required' => 'Selecciona un proveedor.',
            'numero_serie.unique' => 'Este número de serie ya está registrado.',
            'puertos_conectividad.required' => 'Selecciona al menos un puerto de conectividad.',
            'dispositivos_entrada.required' => 'Selecciona al menos un dispositivo de entrada.',
        ];
    }

    // =======================
    // Guardar
    // =======================
    public function guardar(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: $e->validator->errors()->first());
            throw $e;
        }

        // Pre-procesamiento
        $this->almacenamiento_secundario_capacidad = $this->almacenamiento_secundario_capacidad ?: 'N/A';
        $this->almacenamiento_secundario_tipo      = $this->almacenamiento_secundario_tipo ?: 'N/A';
        $this->teclado_idioma                      = $this->teclado_idioma ?: 'N/A';

        $this->detalles_esteticos = $this->buildChecksText($this->detalles_esteticos_checks, $this->detalles_esteticos_otro);
        $this->detalles_funcionamiento = $this->buildChecksText($this->detalles_funcionamiento_checks, $this->detalles_funcionamiento_otro);

        $this->puertos_conectividad = $this->truncate($this->puertos_conectividad, 255);
        $this->dispositivos_entrada = $this->truncate($this->dispositivos_entrada, 255);

        // Mappers a columnas (antes del payload)
        $this->mapSlotsToDbColumns();
        $this->applyAggregatesToEquipoColumns();

        DB::transaction(function () {
            // Seguridad: el modelo debe pertenecer al lote
            $belongs = LoteModeloRecibido::query()
                ->whereKey($this->lote_modelo_id)
                ->where('lote_id', $this->lote_id)
                ->exists();

            if (!$belongs) {
                throw ValidationException::withMessages([
                    'lote_modelo_id' => 'El modelo seleccionado no pertenece al lote seleccionado.',
                ]);
            }

            // Cupo
            $lm = LoteModeloRecibido::query()
                ->whereKey($this->lote_modelo_id)
                ->lockForUpdate()
                ->firstOrFail();

            $registrados = (int) Equipo::query()
                ->where('lote_modelo_id', $this->lote_modelo_id)
                ->count();

            if ($registrados >= (int) $lm->cantidad_recibida) {
                throw ValidationException::withMessages([
                    'lote_modelo_id' => 'Ya se registraron todos los equipos disponibles de este modelo en este lote.',
                ]);
            }

            // Crear equipo
            $equipo = Equipo::create($this->equipoPayload());

            // Relacionadas
            $this->guardarBaterias($equipo->id);
            $this->guardarMonitor($equipo->id);
            $this->guardarGpus($equipo->id);
        });

        $this->reiniciarFormulario();
        $this->dispatch('toast', type: 'success', message: 'Equipo registrado correctamente.');
    }

    // =======================
    // Payload principal
    // =======================
    private function equipoPayload(): array
    {
        return [
            'lote_modelo_id'         => $this->lote_modelo_id,
            'numero_serie'           => $this->numero_serie,
            'registrado_por_user_id' => Auth::id(),
            'proveedor_id'           => $this->proveedor_id,

            'estatus_general' => $this->estatus_general,

            'marca'             => $this->marca,
            'modelo'            => $this->modelo,
            'tipo_equipo'       => $this->tipo_equipo,
            'sistema_operativo' => $this->sistema_operativo,
            'area_tienda'       => $this->area_tienda,

            'procesador_modelo'     => $this->procesador_modelo,
            'procesador_generacion' => $this->procesador_generacion,
            'procesador_nucleos'    => $this->procesador_nucleos,
            'procesador_frecuencia' => $this->procesador_frecuencia,

            'pantalla_pulgadas'   => $this->pantalla_pulgadas,
            'pantalla_resolucion' => $this->pantalla_resolucion,
            'pantalla_es_touch'   => (bool) $this->pantalla_es_touch,
            'pantalla_tipo'       => $this->pantalla_tipo,

            'ram_total'            => $this->ram_total,
            'ram_tipo'             => $this->ram_tipo,
            'ram_es_soldada'       => (bool) $this->ram_es_soldada,
            'ram_slots_totales'    => $this->ram_slots_totales,
            'ram_expansion_max'    => $this->ram_expansion_max,
            'ram_cantidad_soldada' => $this->ram_cantidad_soldada,

            'almacenamiento_principal_capacidad'  => $this->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo'       => $this->almacenamiento_principal_tipo,
            'almacenamiento_secundario_capacidad' => $this->almacenamiento_secundario_capacidad,
            'almacenamiento_secundario_tipo'      => $this->almacenamiento_secundario_tipo,

            'slots_alm_ssd'      => $this->slots_alm_ssd,
            'slots_alm_m2'       => $this->slots_alm_m2,
            'slots_alm_m2_micro' => $this->slots_alm_m2_micro,
            'slots_alm_hdd'      => $this->slots_alm_hdd,
            'slots_alm_msata'    => $this->slots_alm_msata,

            'ethernet_tiene'      => (bool) $this->ethernet_tiene,
            'ethernet_es_gigabit' => (bool) $this->ethernet_es_gigabit,

            'puertos_conectividad' => $this->puertos_conectividad,
            'dispositivos_entrada' => $this->dispositivos_entrada,

            'puertos_hdmi'        => $this->puertos_hdmi,
            'puertos_mini_hdmi'   => $this->puertos_mini_hdmi,
            'puertos_vga'         => $this->puertos_vga,
            'puertos_dvi'         => $this->puertos_dvi,
            'puertos_displayport' => $this->puertos_displayport,
            'puertos_mini_dp'     => $this->puertos_mini_dp,

            'puertos_usb_2'  => $this->puertos_usb_2,
            'puertos_usb_30' => $this->puertos_usb_30,
            'puertos_usb_31' => $this->puertos_usb_31,
            'puertos_usb_32' => $this->puertos_usb_32,
            'puertos_usb_c'  => $this->puertos_usb_c,

            'lectores_sd'    => $this->lectores_sd,
            'lectores_sc'    => $this->lectores_sc,
            'lectores_esata' => $this->lectores_esata,
            'lectores_sim'   => $this->lectores_sim,

            'bateria_tiene' => (bool) $this->bateria_tiene,

            'teclado_idioma'          => $this->teclado_idioma,
            'notas_generales'         => $this->notas_generales,
            'detalles_esteticos'      => $this->detalles_esteticos,
            'detalles_funcionamiento' => $this->detalles_funcionamiento,
        ];
    }

    // =======================
    // Relacionadas: Baterías
    // =======================
    private function guardarBaterias(int $equipoId): void
    {
        if (!$this->bateria_tiene) return;

        if ($this->bateria1_tipo) {
            EquipoBateria::create([
                'equipo_id'     => $equipoId,
                'tipo'          => $this->bateria1_tipo,
                'salud_percent' => $this->bateria1_salud ?: null,
                'notas'         => null,
            ]);
        }

        if ($this->bateria2_tiene && $this->bateria2_tipo) {
            EquipoBateria::create([
                'equipo_id'     => $equipoId,
                'tipo'          => $this->bateria2_tipo,
                'salud_percent' => $this->bateria2_salud ?: null,
                'notas'         => null,
            ]);
        }
    }

    // =======================
    // Relacionadas: Monitor/Pantalla
    // =======================
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
            // Tu versión actual borra el registro si NO incluye monitor
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

    /**
     * Construye payload de columnas in_* para EquipoMonitor.
     * - Si count es 0, manda NULL.
     */
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
    // Relacionadas: GPU (equipo_gpus)
    // =======================
    private function guardarGpus(int $equipoId): void
    {
        // En registrar: limpiamos por seguridad (evita duplicados si algo reintenta)
        EquipoGpu::where('equipo_id', $equipoId)->delete();

        $esLaptopLike = $this->isLaptopLikeTipo($this->tipo_equipo);

        // INTEGRADA: obligatoria en laptop-like
        if ($this->gpu_integrada_tiene || $esLaptopLike) {
            EquipoGpu::create([
                'equipo_id' => $equipoId,
                'tipo'      => 'integrada',
                'activo'    => true,
                'marca'     => $this->gpu_integrada_marca ?: 'Intel/AMD',
                'modelo'    => $this->gpu_integrada_modelo ?: 'Integrated Graphics',
                'vram'       => filled($this->gpu_integrada_vram) ? $this->gpu_integrada_vram : null,
                'vram_unidad'=> filled($this->gpu_integrada_vram) ? ($this->gpu_integrada_vram_unidad ?: 'GB') : null,

            ]);
        }

        // DEDICADA: opcional
        if ($this->gpu_dedicada_tiene) {
            EquipoGpu::create([
                'equipo_id' => $equipoId,
                'tipo'      => 'dedicada',
                'activo'    => true,
                'marca'     => $this->gpu_dedicada_marca,
                'modelo'    => $this->gpu_dedicada_modelo,
                'vram'       => filled($this->gpu_dedicada_vram) ? $this->gpu_dedicada_vram : null,
                'vram_unidad'=> filled($this->gpu_dedicada_vram) ? ($this->gpu_dedicada_vram_unidad ?: 'GB') : null,

            ]);
        }
    }

    // =======================
    // Agregadores / Mappers (UI -> columnas)
    // =======================
    private function applyAggregatesToEquipoColumns(): void
    {
        $usbCounts = $this->aggregateCounters($this->puertos_usb, 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($usbCounts, self::MAP_USB);

        $videoCounts = $this->aggregateCounters($this->puertos_video, 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($videoCounts, self::MAP_VIDEO);

        // Lectores sin cantidad: cuenta 1 cada uno
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

            // Solo set si está null (para no pisar si ya venía)
            if ($this->{$col} === null) {
                $this->{$col} = (string) $count;
            }
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
    // Text helpers
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

    private function truncate($value, int $max)
    {
        if ($value === null) return null;
        $value = (string) $value;
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }

    // =======================
    // Reset
    // =======================
    private function reiniciarFormulario(): void
    {
        $this->reset();

        $this->cargarCatalogos();
        $this->setDefaults();

        $this->resetErrorBag();
        $this->resetValidation();

        $this->dispatch('reiniciar-ui-selects');
    }

    public function render()
    {
        return view('livewire.equipos.registrar-equipo');
    }
}
