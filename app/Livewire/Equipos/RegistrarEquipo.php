<?php

namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Livewire\Forms\EquipoForm;
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
    /**
     * ✅ Form Object (nombre estable)
     * En el Blade usarás: wire:model="form.campo"
     */
    public EquipoForm $form;


    // =======================
    // Catálogos / listas (UI)
    // =======================
    public array $proveedores = [];
    public array $lotes = [];
    public array $modelosLote = [];
    public array $lotesTerminadosIds = [];

    public array $monitorEntradasOptions = [
        'HDMI', 'Mini HDMI', 'VGA', 'DVI',
        'DisplayPort', 'Mini DisplayPort',
        'USB 2.0', 'USB 3.0', 'USB 3.1', 'USB 3.2', 'USB-C',
    ];

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
        'microSD'   => 'lectores_microsd',
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

    // Entradas monitor -> columnas equipo_monitores (ajusta si tu tabla difiere)
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
        // ✅ Instancia estable del Form
        

        $this->cargarCatalogos();
        $this->setDefaultsEnForm();
    }

    private function setDefaultsEnForm(): void
    {
        $f = $this->form;

        $f->estatus_general = 'En Revisión';

        $f->almacenamiento_secundario_capacidad = $f->almacenamiento_secundario_capacidad ?: 'N/A';
        $f->almacenamiento_secundario_tipo      = $f->almacenamiento_secundario_tipo ?: 'N/A';
        $f->teclado_idioma                      = $f->teclado_idioma ?: 'N/A';

        $f->bateria_tiene  = $f->bateria_tiene ?? true;
        $f->bateria2_tiene = $f->bateria2_tiene ?? false;

        $f->ethernet_tiene      = (bool) ($f->ethernet_tiene ?? false);
        $f->ethernet_es_gigabit = (bool) ($f->ethernet_es_gigabit ?? false);

        $f->puertos_usb ??= [];
        $f->puertos_video ??= [];
        $f->lectores ??= [];
        $f->slots_almacenamiento ??= [];
        $f->monitor_entradas_rows ??= [];

        $f->gpu_integrada_tiene = (bool) ($f->gpu_integrada_tiene ?? false);
        $f->gpu_dedicada_tiene  = (bool) ($f->gpu_dedicada_tiene ?? false);
        $f->gpu_integrada_vram_unidad = $f->gpu_integrada_vram_unidad ?: 'GB';
        $f->gpu_dedicada_vram_unidad  = $f->gpu_dedicada_vram_unidad ?: 'GB';

        $f->monitor_incluido = $f->monitor_incluido ?: 'NO';
        $this->modelosLote = [];
    }

    // =======================
    // Catálogos
    // =======================
    private function cargarCatalogos(): void
    {
        $this->proveedores = Proveedor::orderBy('nombre_empresa')->get()->all();

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

        $this->lotes = $lotesConPendientes->concat($terminadosTomados)->values()->all();
    }


    public function updatedFormMonitorIncluido($value): void
{
    if ($value !== 'SI') {
        $this->form->monitor_pulgadas = null;
        $this->form->monitor_resolucion = null;

        $this->form->monitor_entradas_rows = [];

        $this->form->monitor_detalles_esteticos_checks = '';
        $this->form->monitor_detalles_esteticos_otro = null;

        $this->form->monitor_detalles_funcionamiento_checks = '';
        $this->form->monitor_detalles_funcionamiento_otro = null;
    }
}


    // =======================
    // Lote / Modelo dependiente
    // =======================
    public function actualizarLote($loteId): void
    {
        $f = $this->form;


        $f->lote_id = $loteId ?: null;
        $f->lote_modelo_id = null;
        $f->marca = null;
        $f->modelo = null;
        $this->modelosLote = [];

        if (!$loteId) {
            $f->proveedor_id = null;
            return;
        }

        $lote = Lote::with(['proveedor', 'modelosRecibidos'])->find($loteId);
        if (!$lote) {
            $f->proveedor_id = null;
            return;
        }

        $f->proveedor_id = $lote->proveedor_id;

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
        $f = $this->form;


        $f->lote_modelo_id = $modeloId ?: null;
        $f->marca = null;
        $f->modelo = null;

        if (!$modeloId) return;

        $lm = LoteModeloRecibido::find($modeloId);
        if (!$lm) return;

        $f->marca = $lm->marca;
        $f->modelo = $lm->modelo;
    }

    // =======================
    // Helpers UI dinámicos (AHORA en Form)
    // =======================
    public function addPuertoUsb(): void { $this->form->puertos_usb[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoUsb($i): void { $this->unsetIndex($this->form->puertos_usb, $i); }

    public function addPuertoVideo(): void { $this->form->puertos_video[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoVideo($i): void { $this->unsetIndex($this->form->puertos_video, $i); }

    public function addLector(): void { $this->form->lectores[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeLector($i): void { $this->unsetIndex($this->form->lectores, $i); }

    public function addSlotAlmacenamiento(): void { $this->form->slots_almacenamiento[] = ['tipo' => '', 'cantidad' => null]; }
    public function removeSlotAlmacenamiento($i): void { $this->unsetIndex($this->form->slots_almacenamiento, $i); }

    public function addMonitorEntrada(): void { $this->form->monitor_entradas_rows[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeMonitorEntrada($i): void { $this->unsetIndex($this->form->monitor_entradas_rows, $i); }

    private function unsetIndex(array &$arr, $i): void
    {
        if (!isset($arr[$i])) return;
        unset($arr[$i]);
        $arr = array_values($arr);
    }

    // =======================
    // Tipo equipo: integrada vs externa
    // =======================
    private function tipoKey(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        $v = preg_replace('/\s+/', ' ', $v);
        return $v;
    }

    private function isLaptopLikeTipo(?string $tipo): bool
        {
            return in_array($this->tipoKey($tipo), ['laptop','2 en 1','all in one','tablet','gamer'], true)
                || in_array(trim((string)$tipo), ['LAPTOP','2 EN 1','ALL IN ONE','TABLET','GAMER'], true);
        }


    private function isPcLikeTipo(?string $tipo): bool
        {
            return in_array($this->tipoKey($tipo), ['escritorio','micro pc','gamer'], true)
                || in_array(trim((string)$tipo), ['ESCRITORIO','MICRO PC','GAMER'], true);
        }


    public function getPantallaIntegradaProperty(): bool
    {
        return $this->isLaptopLikeTipo($this->form->tipo_equipo ?? null);
    }

    public function getPantallaExternaProperty(): bool
    {
        return $this->isPcLikeTipo($this->form->tipo_equipo ?? null);
    }

    // Livewire hooks para propiedades anidadas
    public function updatedFormEthernetTiene($value): void
    {
        if (!(bool)$value) $this->form->ethernet_es_gigabit = false;
    }


    public function updatedFormTipoEquipo($value): void
{
    $tipo = strtoupper(trim((string) $value));

    $pantallaIntegrada = in_array($tipo, ['LAPTOP','2 EN 1','ALL IN ONE','TABLET','GAMER'], true);
    $pantallaExterna   = in_array($tipo, ['ESCRITORIO','MICRO PC'], true);


    if ($pantallaIntegrada) {
        // Limpia monitor externo
        $this->form->monitor_incluido = null;
        $this->form->monitor_pulgadas = null;
        $this->form->monitor_resolucion = null;
        $this->form->monitor_entradas_rows = [];
        $this->form->monitor_detalles_esteticos_checks = '';
        $this->form->monitor_detalles_esteticos_otro = null;
        $this->form->monitor_detalles_funcionamiento_checks = '';
        $this->form->monitor_detalles_funcionamiento_otro = null;
    }

    if ($pantallaExterna) {
        // Limpia pantalla integrada
        $this->form->pantalla_pulgadas = null;
        $this->form->pantalla_resolucion = null;
        $this->form->pantalla_es_touch = false;
    }
}




    private function resetMonitorAllFields(): void
    {
        $f = $this->form;


        $f->monitor_pulgadas = null;
        $f->monitor_resolucion = null;
        $f->monitor_tipo_panel = null;
        $f->monitor_es_touch = false;

        $f->monitor_entradas_rows = [];
        $f->monitor_detalles_esteticos_checks = '';
        $f->monitor_detalles_esteticos_otro = '';
        $f->monitor_detalles_funcionamiento_checks = '';
        $f->monitor_detalles_funcionamiento_otro = '';
    }

    // =======================
    // GPU: reglas
    // =======================
    private function syncGpuDefaultsByTipo(): void
    {
        if ($this->isLaptopLikeTipo($this->form->tipo_equipo)) {
            // Laptop-like: integrada SIEMPRE activa
            $this->form->gpu_integrada_tiene = true;
        }
    }

    public function updatedFormGpuIntegradaTiene(): void
    {
        // Si es laptop-like, no permitimos apagar
        if ($this->isLaptopLikeTipo($this->form->tipo_equipo)) {
            $this->form->gpu_integrada_tiene = true;
            return;
        }

        if (!$this->form->gpu_integrada_tiene) {
            $this->form->gpu_integrada_marca = null;
            $this->form->gpu_integrada_modelo = null;
            $this->form->gpu_integrada_vram = null;
        }
    }

    public function updatedFormGpuDedicadaTiene(): void
    {
        if (!$this->form->gpu_dedicada_tiene) {
            $this->form->gpu_dedicada_marca = null;
            $this->form->gpu_dedicada_modelo = null;
            $this->form->gpu_dedicada_vram = null;
            $this->form->gpu_dedicada_vram_unidad = 'GB';
        }
    }

    public function updatedFormGpuIntegradaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->form->gpu_integrada_marca, ['INTEL','AMD','NVIDIA'], true)) {
            $this->form->gpu_integrada_marca = '';
        }
    }

    public function updatedFormGpuDedicadaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->form->gpu_dedicada_marca, ['INTEL','AMD','NVIDIA'], true)) {
            $this->form->gpu_dedicada_marca = '';
        }
    }

    // =======================
    // Validación (prefijada a form.)
    // =======================
    protected function rules(): array
    {
        $f = $this->form;


        $pref = $this->prefixRules($f->rules());

        // Reglas extra que tu registrar ya tenía (arrays UI)
        $extra = [
            'form.lote_id'        => 'required|exists:lotes,id',
            'form.lote_modelo_id' => 'required|exists:lote_modelos_recibidos,id',
            'form.proveedor_id'   => 'required|exists:proveedores,id',

            'form.modelo'         => 'required|string|max:255',

            'form.monitor_incluido' => 'nullable|in:SI,NO',

            'form.monitor_entradas_rows' => 'array',
            'form.monitor_entradas_rows.*.tipo' => 'nullable|in:' . implode(',', $this->monitorEntradasOptions),
            'form.monitor_entradas_rows.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.slots_almacenamiento' => 'array',
            'form.slots_almacenamiento.*.tipo' => 'nullable|in:SSD,M.2,M.2 MICRO,HDD,MSATA',
            'form.slots_almacenamiento.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.puertos_usb' => 'array',
            'form.puertos_usb.*.tipo' => 'nullable|string|max:50',
            'form.puertos_usb.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.puertos_video' => 'array',
            'form.puertos_video.*.tipo' => 'nullable|string|max:50',
            'form.puertos_video.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.lectores' => 'array',
            'form.lectores.*.tipo' => 'nullable|string|max:50',
            'form.lectores.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.procesador_frecuencia' => ['nullable', 'string', 'max:20'],

        ];

        return array_merge($pref, $extra);
    }

    protected function messages(): array
    {
        return [
            'form.lote_id.required' => 'Selecciona un lote.',
            'form.lote_modelo_id.required' => 'Selecciona un modelo del lote.',
            'form.proveedor_id.required' => 'Selecciona un proveedor.',
            'form.numero_serie.unique' => 'Este número de serie ya está registrado.',
            'form.puertos_conectividad.required' => 'Selecciona al menos un puerto de conectividad.',
            'form.dispositivos_entrada.required' => 'Selecciona al menos un dispositivo de entrada.',
            'form.procesador_frecuencia.max' => 'La frecuencia del procesador no debe exceder 20 caracteres (ej: 3.6 GHz).',

        ];
    }

    private function prefixRules(array $rules): array
    {
        $out = [];
        foreach ($rules as $k => $v) $out["form.$k"] = $v;
        return $out;
    }



public function updatedFormRamEsSoldada($value): void
{
    if (! $value) {
        // Limpia cantidad soldada
        $this->form->ram_cantidad_soldada = '';

        // ✅ Apaga "totalmente soldada"
        $this->form->ram_sin_slots = false;

        // Libera slots/expansion si estaban forzados
        if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) {
            $this->form->ram_slots_totales = '';
        }
        if ($this->form->ram_expansion_max === '0 GB') {
            $this->form->ram_expansion_max = '';
        }
    }
}


public function updatedFormRamSinSlots($value): void
{
    // Si intentan activar "totalmente soldada" sin RAM soldada, no lo permitas
    if ($value && ! $this->form->ram_es_soldada) {
        $this->form->ram_sin_slots = false;
        return;
    }

    if ($value) {
        // Activar "sin slots"
        $this->form->ram_slots_totales = '0';
        $this->form->ram_expansion_max = '0 GB';
        return;
    }

    // Desactivar "sin slots" (liberar)
    if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) {
        $this->form->ram_slots_totales = '';
    }
    if ($this->form->ram_expansion_max === '0 GB') {
        $this->form->ram_expansion_max = '';
    }
}


public function updatedFormRamSlotsTotales($value): void
{
    $isZero = ($value === '0' || $value === 0);


       if ($isZero && ! $this->form->ram_es_soldada) {
        $this->form->ram_slots_totales = '';
        $this->form->ram_sin_slots = false;
        $this->form->ram_expansion_max = '';
        return;
    }

    // sincroniza el checkbox visual
    $this->form->ram_sin_slots = $isZero;

    // y fuerza expansion si es 0
    if ($isZero) {
        $this->form->ram_expansion_max = '0 GB';
    } elseif ($this->form->ram_expansion_max === '0 GB') {
        $this->form->ram_expansion_max = '';
    }
}


public function updatedFormRamExpansionMax($value): void
{
    // No permitir 0 GB si RAM soldada esta OFF
    if ($value === '0 GB' && ! $this->form->ram_es_soldada) {
        $this->form->ram_expansion_max = '';
        return;
    }

    // Si eligen 0 GB => debe quedar como "sin expansion"
    if ($value === '0 GB') {
        $this->form->ram_slots_totales = '0';
        $this->form->ram_sin_slots = true; // tu boolean UI
        return;
    }

    // Si ya no es 0 GB, y estaba en modo sin-slots, libera slots
    if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) {
        // solo liberalo si NO esta marcado sin-slots (o sea, si venia por expansion)
        // (si prefieres siempre liberar, quita el if)
        if (! $this->form->ram_sin_slots) {
            $this->form->ram_slots_totales = '';
        }
    }
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

        $f = $this->form;


        // Pre-procesamiento
        $f->almacenamiento_secundario_capacidad = $f->almacenamiento_secundario_capacidad ?: 'N/A';
        $f->almacenamiento_secundario_tipo      = $f->almacenamiento_secundario_tipo ?: 'N/A';
        $f->teclado_idioma                      = $f->teclado_idioma ?: 'N/A';

        $f->detalles_esteticos = $this->buildChecksText($f->detalles_esteticos_checks ?? [], $f->detalles_esteticos_otro);
        $f->detalles_funcionamiento = $this->buildChecksText($f->detalles_funcionamiento_checks ?? [], $f->detalles_funcionamiento_otro);

        $f->puertos_conectividad = $this->truncate($f->puertos_conectividad, 255);
        $f->dispositivos_entrada = $this->truncate($f->dispositivos_entrada, 255);

        // Mappers a columnas (antes del payload)
        $this->mapSlotsToDbColumns();
        $this->applyAggregatesToEquipoColumns();

        DB::transaction(function () use ($f) {
            // Seguridad: el modelo debe pertenecer al lote
            $belongs = LoteModeloRecibido::query()
                ->whereKey($f->lote_modelo_id)
                ->where('lote_id', $f->lote_id)
                ->exists();

            if (!$belongs) {
                throw ValidationException::withMessages([
                    'form.lote_modelo_id' => 'El modelo seleccionado no pertenece al lote seleccionado.',
                ]);
            }

            // Cupo
            $lm = LoteModeloRecibido::query()
                ->whereKey($f->lote_modelo_id)
                ->lockForUpdate()
                ->firstOrFail();

            $registrados = (int) Equipo::query()
                ->where('lote_modelo_id', $f->lote_modelo_id)
                ->count();

            if ($registrados >= (int) $lm->cantidad_recibida) {
                throw ValidationException::withMessages([
                    'form.lote_modelo_id' => 'Ya se registraron todos los equipos disponibles de este modelo en este lote.',
                ]);
            }

            // Crear equipo
            $equipo = Equipo::create($this->equipoPayload());

            // Relacionadas
            $this->guardarBaterias((int) $equipo->id);
            $this->guardarMonitor((int) $equipo->id);
            $this->guardarGpus((int) $equipo->id);
        });

        $this->reiniciarFormulario();
        $this->dispatch('toast', type: 'success', message: 'Equipo registrado correctamente.');
        $this->form->clearAfterSave();
        $this->resetValidation();
    }

    // =======================
    // Payload principal
    // =======================
    private function equipoPayload(): array
    {
        $f = $this->form;

        


        return [
            
            'lote_modelo_id'         => $f->lote_modelo_id,
            'numero_serie'           => $f->numero_serie,
            'registrado_por_user_id' => Auth::id(),
            'proveedor_id'           => $f->proveedor_id,

            'estatus_general' => $f->estatus_general,

            'marca'             => $f->marca,
            'modelo'            => $f->modelo,
            'tipo_equipo'       => $f->tipo_equipo,
            'sistema_operativo' => $f->sistema_operativo,
            'area_tienda'       => $f->area_tienda,

            'procesador_modelo'     => $f->procesador_modelo,
            'procesador_generacion' => $f->procesador_generacion,
            'procesador_nucleos'    => $f->procesador_nucleos,
            'procesador_frecuencia' => $f->procesador_frecuencia,



            'ram_total'            => $f->ram_total,
            'ram_tipo'             => $f->ram_tipo,
            'ram_es_soldada'       => (bool) $f->ram_es_soldada,
            'ram_slots_totales'    => $f->ram_slots_totales,
            'ram_expansion_max'    => $f->ram_expansion_max,
            'ram_cantidad_soldada' => $f->ram_cantidad_soldada,
            

            'almacenamiento_principal_capacidad'  => $f->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo'       => $f->almacenamiento_principal_tipo,
            'almacenamiento_secundario_capacidad' => $f->almacenamiento_secundario_capacidad,
            'almacenamiento_secundario_tipo'      => $f->almacenamiento_secundario_tipo,

            'slots_alm_ssd'      => $f->slots_alm_ssd,
            'slots_alm_m2'       => $f->slots_alm_m2,
            'slots_alm_m2_micro' => $f->slots_alm_m2_micro,
            'slots_alm_hdd'      => $f->slots_alm_hdd,
            'slots_alm_msata'    => $f->slots_alm_msata,

            'ethernet_tiene'      => (bool) $f->ethernet_tiene,
            'ethernet_es_gigabit' => (bool) $f->ethernet_es_gigabit,

            'puertos_conectividad' => $f->puertos_conectividad,
            'dispositivos_entrada' => $f->dispositivos_entrada,

            'puertos_hdmi'        => $f->puertos_hdmi,
            'puertos_mini_hdmi'   => $f->puertos_mini_hdmi,
            'puertos_vga'         => $f->puertos_vga,
            'puertos_dvi'         => $f->puertos_dvi,
            'puertos_displayport' => $f->puertos_displayport,
            'puertos_mini_dp'     => $f->puertos_mini_dp,

            'puertos_usb_2'  => $f->puertos_usb_2,
            'puertos_usb_30' => $f->puertos_usb_30,
            'puertos_usb_31' => $f->puertos_usb_31,
            'puertos_usb_32' => $f->puertos_usb_32,
            'puertos_usb_c'  => $f->puertos_usb_c,

            'lectores_sd'      => $f->lectores_sd,
            'lectores_microsd' => $f->lectores_microsd,
            'lectores_sc'      => $f->lectores_sc,
            'lectores_esata'   => $f->lectores_esata,
            'lectores_sim'     => $f->lectores_sim,

            'bateria_tiene' => (bool) $f->bateria_tiene,

            'teclado_idioma'          => $f->teclado_idioma,
            'notas_generales'         => $f->notas_generales,
            'detalles_esteticos'      => $f->detalles_esteticos,
            'detalles_funcionamiento' => $f->detalles_funcionamiento,
        ];
    }

    // =======================
    // Relacionadas: Baterías
    // =======================
    private function guardarBaterias(int $equipoId): void
    {
        $f = $this->form;


        if (!$f->bateria_tiene) return;

        if ($f->bateria1_tipo) {
            EquipoBateria::create([
                'equipo_id'     => $equipoId,
                'tipo'          => $f->bateria1_tipo,
                'salud_percent' => $f->bateria1_salud ?: null,
                'notas'         => null,
            ]);
        }

        if ($f->bateria2_tiene && $f->bateria2_tipo) {
            EquipoBateria::create([
                'equipo_id'     => $equipoId,
                'tipo'          => $f->bateria2_tipo,
                'salud_percent' => $f->bateria2_salud ?: null,
                'notas'         => null,
            ]);
        }
    }

    // =======================
    // Relacionadas: Monitor/Pantalla
    // =======================
    private function guardarMonitor(int $equipoId): void
    {
        $f = $this->form;


        // INTEGRADA
        if ($this->pantallaIntegrada) {
            EquipoMonitor::updateOrCreate(
                ['equipo_id' => $equipoId],
                [
                    'origen_pantalla' => 'INTEGRADA',
                    'incluido'        => 1,
                    'pulgadas'        => $f->pantalla_pulgadas ?: null,
                    'resolucion'      => $f->pantalla_resolucion ?: null,
                    'tipo_panel'      => $f->pantalla_tipo ?: null,
                    'es_touch'        => (int) ((bool) $f->pantalla_es_touch),
                ] + $this->monitorInputsPayload([]) // limpia entradas
            );
            return;
        }

        // EXTERNA
        if (!$this->pantallaExterna) {
            return;
        }

        if ($f->monitor_incluido !== 'SI') {
            // ✅ Mantener registro con incluido=0 y todo lo demás NULL/0
            EquipoMonitor::updateOrCreate(
                ['equipo_id' => $equipoId],
                [
                    'origen_pantalla' => 'EXTERNA',
                    'incluido'        => 0,
                    'pulgadas'        => null,
                    'resolucion'      => null,
                    'tipo_panel'      => null,
                    'es_touch'        => 0,

                    'detalles_esteticos_checks'      => null,
                    'detalles_esteticos_otro'        => null,
                    'detalles_funcionamiento_checks' => null,
                    'detalles_funcionamiento_otro'   => null,
                ] + $this->monitorInputsPayload([])
            );
            return;
        }

        $counts = $this->aggregateCounters($f->monitor_entradas_rows ?? [], 'tipo', 'cantidad');
        $inputsPayload = $this->monitorInputsPayload($counts);

        EquipoMonitor::updateOrCreate(
            ['equipo_id' => $equipoId],
            [
                'origen_pantalla' => 'EXTERNA',
                'incluido'        => 1,
                'pulgadas'        => $f->monitor_pulgadas ?: null,
                'resolucion'      => $f->monitor_resolucion ?: null,
                'tipo_panel'      => $f->monitor_tipo_panel ?: null,
                'es_touch'        => (int) ((bool) $f->monitor_es_touch),

                'detalles_esteticos_checks'      => $this->truncate($f->monitor_detalles_esteticos_checks ?: null, 255),
                'detalles_esteticos_otro'        => $this->truncate($f->monitor_detalles_esteticos_otro ?: null, 255),
                'detalles_funcionamiento_checks' => $this->truncate($f->monitor_detalles_funcionamiento_checks ?: null, 255),
                'detalles_funcionamiento_otro'   => $this->truncate($f->monitor_detalles_funcionamiento_otro ?: null, 255),
            ] + $inputsPayload
        );
    }


    public function updatedFormBateriaTiene($value): void
{
    if (! $value) {
        $this->form->bateria1_tipo = null;
        $this->form->bateria1_salud = null;
    }
}

    public function updatedFormBateria2Tiene($value): void
{
    if (! $value) {
        $this->form->bateria2_tipo = null;
        $this->form->bateria2_salud = null;
    }
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
        $f = $this->form;


        // En registrar: limpiamos por seguridad
        EquipoGpu::where('equipo_id', $equipoId)->delete();

        $esLaptopLike = $this->isLaptopLikeTipo($f->tipo_equipo);

        // INTEGRADA: obligatoria en laptop-like
        if ($f->gpu_integrada_tiene || $esLaptopLike) {
            EquipoGpu::create([
                'equipo_id'    => $equipoId,
                'tipo'         => 'INTEGRADA',
                'activo'       => 1,
                'marca'        => $f->gpu_integrada_marca ?: null,
                'modelo'       => $f->gpu_integrada_modelo ?: null,
                'vram'         => filled($f->gpu_integrada_vram) ? (int)$f->gpu_integrada_vram : null,
                'vram_unidad'  => filled($f->gpu_integrada_vram) ? ($f->gpu_integrada_vram_unidad ?: 'GB') : null,
                'notas'        => null,
            ]);
        }

        // DEDICADA: opcional
        if ($f->gpu_dedicada_tiene) {
            EquipoGpu::create([
                'equipo_id'    => $equipoId,
                'tipo'         => 'DEDICADA',
                'activo'       => 1,
                'marca'        => $f->gpu_dedicada_marca,
                'modelo'       => $f->gpu_dedicada_modelo,
                'vram'         => filled($f->gpu_dedicada_vram) ? (int)$f->gpu_dedicada_vram : null,
                'vram_unidad'  => filled($f->gpu_dedicada_vram) ? ($f->gpu_dedicada_vram_unidad ?: 'GB') : null,
                'notas'        => null,
            ]);
        }
    }

    // =======================
    // Agregadores / Mappers (Form UI -> columnas Equipo)
    // =======================
    private function applyAggregatesToEquipoColumns(): void
    {
        $f = $this->form;


        $usbCounts = $this->aggregateCounters($f->puertos_usb ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($usbCounts, self::MAP_USB);

        $videoCounts = $this->aggregateCounters($f->puertos_video ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($videoCounts, self::MAP_VIDEO);

        $lectorCounts = $this->aggregateCounters($f->lectores ?? [], 'tipo', 'cantidad');
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
        $f = $this->form;


        foreach ($countsByLabel as $label => $count) {
            if (!isset($mapLabelToEquipoColumn[$label])) continue;

            $col = $mapLabelToEquipoColumn[$label];

            // Solo set si está null (para no pisar si ya venía)
            if ($f->{$col} === null) {
                $f->{$col} = (string) $count;
            }
        }
    }

    private function mapSlotsToDbColumns(): void
    {
        $f = $this->form;

        $f->slots_alm_ssd = null;
        $f->slots_alm_m2 = null;
        $f->slots_alm_m2_micro = null;
        $f->slots_alm_hdd = null;
        $f->slots_alm_msata = null;

        foreach (($f->slots_almacenamiento ?? []) as $row) {
            $tipo = trim((string) ($row['tipo'] ?? ''));
            if ($tipo === '' || !isset(self::MAP_SLOTS[$tipo])) continue;

            $cant = $row['cantidad'] ?? null;
            $valor = (is_numeric($cant) && (int) $cant > 0) ? (string) ((int) $cant) : null;

            $col = self::MAP_SLOTS[$tipo];
            $f->{$col} = $valor;
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
        // Reinicia el Form y catálogos sin tocar el componente entero
        $this->form = new EquipoForm($this, 'equipoForm');

        $this->cargarCatalogos();
        $this->setDefaultsEnForm();

        $this->resetErrorBag();
        $this->resetValidation();

        $this->dispatch('reiniciar-ui-selects');
    }

    public function render()
    {
        return view('livewire.equipos.registrar-equipo');
    }
}
