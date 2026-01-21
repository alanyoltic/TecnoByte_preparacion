<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Equipo;
use Illuminate\Validation\Rule;

class EquipoForm extends Form
{
    public ?Equipo $equipo = null;

    // =========================
    // Propiedades del Modelo Equipo
    // =========================
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

    // CPU
    public $procesador_modelo;
    public $procesador_generacion;
    public $procesador_nucleos;
    public $procesador_frecuencia;

    // RAM
    public $ram_total;
    public $ram_capacidad; // (si aún lo usas en alguna parte legacy)
    public $ram_tipo;
    public bool $ram_es_soldada = false;
    public $ram_cantidad_soldada;
    public bool $ram_sin_slots = false;
    public $ram_expansion_max;
    public $ram_slots_totales;

    // Almacenamiento
    public $almacenamiento_principal_capacidad;
    public $almacenamiento_principal_tipo;
    public $almacenamiento_secundario_capacidad = 'N/A';
    public $almacenamiento_secundario_tipo = 'N/A';

    // Slots almacenamiento (columnas)
    public $slots_alm_ssd;
    public $slots_alm_m2;
    public $slots_alm_m2_micro;
    public $slots_alm_hdd;
    public $slots_alm_msata;

    // Slots almacenamiento (UI)
    public array $slots_almacenamiento = [];

    // Red
    public bool $ethernet_tiene = false;
    public bool $ethernet_es_gigabit = false;

    // Conectividad (texto final)
    public $puertos_conectividad;
    public $dispositivos_entrada;

    // Puertos video (columnas)
    public $puertos_hdmi;
    public $puertos_mini_hdmi;
    public $puertos_vga;
    public $puertos_dvi;
    public $puertos_displayport;
    public $puertos_mini_dp;

    // Puertos USB (columnas)
    public $puertos_usb_2;
    public $puertos_usb_30;
    public $puertos_usb_31;
    public $puertos_usb_32;
    public $puertos_usb_c;

    // Lectores (columnas)
    public $lectores_sd;
    public $lectores_sc;
    public $lectores_esata;
    public $lectores_sim;
    public $lectores_microsd;

    // Puertos/lectores (UI dinámico)
    public array $puertos_usb = [];
    public array $puertos_video = [];
    public array $lectores = [];
    

    // Teclado / notas
    public $teclado_idioma = 'N/A';
    public $notas_generales;

    // Detalles (texto final)
    public $detalles_esteticos;
    public $detalles_funcionamiento;

    // Detalles (chips UI)
    public array $detalles_esteticos_checks = [];
    public ?string $detalles_esteticos_otro = null;
    public array $detalles_funcionamiento_checks = [];
    public ?string $detalles_funcionamiento_otro = null;

    // =========================
    // Pantalla / Monitor
    // =========================
    // Pantalla integrada (legacy en equipos + ref a equipo_monitores en tu sistema)
    public $pantalla_pulgadas = null;
    public $pantalla_resolucion = null;
    public bool $pantalla_es_touch = false;
    public $pantalla_tipo = null; // ✅ faltaba (en registrar lo usas)

    // Monitor externo (tabla equipo_monitores)
    public $monitor_incluido = 'NO';
    public $monitor_pulgadas = null;
    public $monitor_resolucion = null;
    public bool $monitor_es_touch = false;
    public $monitor_tipo_panel = null; // ✅ faltaba (en registrar lo usas)

    public array $monitor_entradas_rows = [];

    public $monitor_detalles_esteticos_checks = '';
    public $monitor_detalles_esteticos_otro = '';
    public $monitor_detalles_funcionamiento_checks = '';
    public $monitor_detalles_funcionamiento_otro = '';

    // =========================
    // GPU (LEGACY - mantener por compatibilidad si Editar aún lo usa)
    // =========================
    public $grafica_integrada_modelo;
    public $grafica_dedicada_modelo;
    public $tiene_tarjeta_dedicada = 'NO';
    public $grafica_dedicada_vram;
    public $gpu_driver;
    public $gpu_notas;

    // =========================
    // GPU (NUEVO ESQUEMA) ✅
    // =========================
    public bool $gpu_integrada_tiene = false;
    public ?string $gpu_integrada_marca = null;
    public ?string $gpu_integrada_modelo = null;
    public ?int $gpu_integrada_vram = null;
    public ?string $gpu_integrada_vram_unidad = 'GB';

    public bool $gpu_dedicada_tiene = false;
    public ?string $gpu_dedicada_marca = null;
    public ?string $gpu_dedicada_modelo = null;
    public ?int $gpu_dedicada_vram = null;
    public ?string $gpu_dedicada_vram_unidad = 'GB';

    public string $gpu_integrada_marca_mode = 'LISTA';
    public string $gpu_dedicada_marca_mode  = 'LISTA';










    // =========================
    // Baterías
    // =========================
    public bool $bateria_tiene = true;
    public ?string $bateria1_tipo = null;
    public ?string $bateria1_salud = null;

    public bool $bateria2_tiene = false;
    public ?string $bateria2_tipo = null;
    public ?string $bateria2_salud = null;

    // =========================
    // Fill / set equipo
    // =========================
    public function setEquipo(Equipo $equipo): void
    {
        $this->equipo = $equipo;
        $this->fillFromModel($equipo);
    }

    

    public function fillFromModel(Equipo $equipo): void
    {
        $this->fill($equipo->toArray());

        // Casts boolean (DB -> PHP)
        $this->ram_es_soldada      = (bool) $equipo->ram_es_soldada;
        $this->ram_sin_slots       = (bool) $equipo->ram_sin_slots;
        $this->ethernet_tiene      = (bool) $equipo->ethernet_tiene;
        $this->ethernet_es_gigabit = (bool) $equipo->ethernet_es_gigabit;
        $this->pantalla_es_touch   = (bool) $equipo->pantalla_es_touch;

        // Defaults defensivos
        $this->monitor_entradas_rows ??= [];
        $this->puertos_usb ??= [];
        $this->puertos_video ??= [];
        $this->lectores ??= [];
        $this->slots_almacenamiento ??= [];
        

        $this->detalles_esteticos_checks ??= [];
        $this->detalles_funcionamiento_checks ??= [];
    }

    // =========================
    // Snapshot (para auditoría/diff en Editar)
    // =========================
    public function snapshotPersistible(): array
    {
        return [
            'lote_id' => $this->lote_id,
            'lote_modelo_id' => $this->lote_modelo_id,
            'proveedor_id' => $this->proveedor_id,

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

            'ram_total' => $this->ram_total,
            'ram_capacidad' => $this->ram_capacidad,
            'ram_tipo' => $this->ram_tipo,
            'ram_es_soldada' => (bool) $this->ram_es_soldada,
            'ram_cantidad_soldada' => $this->ram_cantidad_soldada,
            'ram_sin_slots' => (bool) $this->ram_sin_slots,
            'ram_expansion_max' => $this->ram_expansion_max,
            'ram_slots_totales' => $this->ram_slots_totales,

            'almacenamiento_principal_capacidad' => $this->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo' => $this->almacenamiento_principal_tipo,
            'almacenamiento_secundario_capacidad' => $this->almacenamiento_secundario_capacidad,
            'almacenamiento_secundario_tipo' => $this->almacenamiento_secundario_tipo,

            'slots_alm_ssd' => $this->slots_alm_ssd,
            'slots_alm_m2' => $this->slots_alm_m2,
            'slots_alm_m2_micro' => $this->slots_alm_m2_micro,
            'slots_alm_hdd' => $this->slots_alm_hdd,
            'slots_alm_msata' => $this->slots_alm_msata,

            'ethernet_tiene' => (bool) $this->ethernet_tiene,
            'ethernet_es_gigabit' => (bool) $this->ethernet_es_gigabit,
            'puertos_conectividad' => $this->puertos_conectividad,
            'dispositivos_entrada' => $this->dispositivos_entrada,

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

            'teclado_idioma' => $this->teclado_idioma,
            'notas_generales' => $this->notas_generales,

            'detalles_esteticos' => $this->detalles_esteticos,
            'detalles_funcionamiento' => $this->detalles_funcionamiento,

            'pantalla_pulgadas' => $this->pantalla_pulgadas,
            'pantalla_resolucion' => $this->pantalla_resolucion,
            'pantalla_es_touch' => (bool) $this->pantalla_es_touch,
            'pantalla_tipo' => $this->pantalla_tipo,

            'monitor_incluido' => $this->monitor_incluido,
            'monitor_pulgadas' => $this->monitor_pulgadas,
            'monitor_resolucion' => $this->monitor_resolucion,
            'monitor_es_touch' => (bool) $this->monitor_es_touch,
            'monitor_tipo_panel' => $this->monitor_tipo_panel,

            'monitor_entradas_rows' => $this->normalizeRows($this->monitor_entradas_rows),

            'monitor_detalles_esteticos_checks' => (string) $this->monitor_detalles_esteticos_checks,
            'monitor_detalles_esteticos_otro' => (string) $this->monitor_detalles_esteticos_otro,
            'monitor_detalles_funcionamiento_checks' => (string) $this->monitor_detalles_funcionamiento_checks,
            'monitor_detalles_funcionamiento_otro' => (string) $this->monitor_detalles_funcionamiento_otro,

            // GPU LEGACY (si Editar aún lo usa)
            'grafica_integrada_modelo' => $this->grafica_integrada_modelo,
            'grafica_dedicada_modelo' => $this->grafica_dedicada_modelo,
            'tiene_tarjeta_dedicada' => $this->tiene_tarjeta_dedicada,
            'grafica_dedicada_vram' => $this->grafica_dedicada_vram,
            'gpu_driver' => $this->gpu_driver,
            'gpu_notas' => $this->gpu_notas,

            // GPU NUEVO (para que Editar también pueda comparar en el futuro)
            'gpu_integrada_tiene' => (bool) $this->gpu_integrada_tiene,
            'gpu_integrada_marca' => $this->gpu_integrada_marca,
            'gpu_integrada_modelo' => $this->gpu_integrada_modelo,
            'gpu_integrada_vram' => $this->gpu_integrada_vram,
            'gpu_integrada_vram_unidad' => $this->gpu_integrada_vram_unidad,

            'gpu_dedicada_tiene' => (bool) $this->gpu_dedicada_tiene,
            'gpu_dedicada_marca' => $this->gpu_dedicada_marca,
            'gpu_dedicada_modelo' => $this->gpu_dedicada_modelo,
            'gpu_dedicada_vram' => $this->gpu_dedicada_vram,
            'gpu_dedicada_vram_unidad' => $this->gpu_dedicada_vram_unidad,

            // Baterías
            'bateria_tiene' => (bool) $this->bateria_tiene,
            'bateria1_tipo' => $this->bateria1_tipo,
            'bateria1_salud' => $this->bateria1_salud,
            'bateria2_tiene' => (bool) $this->bateria2_tiene,
            'bateria2_tipo' => $this->bateria2_tipo,
            'bateria2_salud' => $this->bateria2_salud,
        ];
    }

    /**
     * Normaliza arrays para evitar cambios falsos por orden/vacíos.
     */
    protected function normalizeRows(array $rows): array
    {
        $rows = array_values(array_filter($rows, function ($r) {
            if (!is_array($r)) return false;
            $v = trim((string) ($r['value'] ?? $r['entrada'] ?? $r['tipo'] ?? ''));
            return $v !== '';
        }));

        $rows = array_map(function ($r) {
            $v = trim((string) ($r['value'] ?? $r['entrada'] ?? $r['tipo'] ?? ''));
            return ['value' => $v];
        }, $rows);

        sort($rows);

        return $rows;
    }




    // =========================
    // Rules (seguras para create)
    // =========================
    public function rules(): array
    {
        return [
            'numero_serie' => [
                'required',
                'string',
                Rule::unique('equipos', 'numero_serie')->ignore($this->equipo?->id),
            ],

            'puertos_conectividad' => 'required|string',
            'dispositivos_entrada' => 'required|string',

            'lote_id' => 'nullable|exists:lotes,id',
            'lote_modelo_id' => 'nullable|exists:lote_modelos_recibidos,id',

            'ethernet_tiene' => ['nullable','boolean'],
            'ethernet_es_gigabit' => [
                'nullable',
                'boolean',
                Rule::requiredIf(fn () => (bool) $this->ethernet_tiene),
            ],

            'teclado_idioma' => ['required','string','max:50'],

            // GPU nuevo (opcional, pero listo para when-needed)
            'gpu_integrada_tiene' => ['boolean'],
            'gpu_dedicada_tiene'  => ['boolean'],
            'gpu_dedicada_marca'  => [Rule::requiredIf(fn() => (bool)$this->gpu_dedicada_tiene), 'nullable','string','max:120'],
            'gpu_dedicada_modelo' => [Rule::requiredIf(fn() => (bool)$this->gpu_dedicada_tiene), 'nullable','string','max:180'],
            'gpu_dedicada_vram'   => ['nullable','integer','min:0','max:1024'],
        ];
    }



    public function clearAfterSave(): void
{
    // 1) Slots almacenamiento (filas dinámicas)
    $this->almacenamiento_slots_rows = [];

    // 2) Lectores / Ranuras (filas dinámicas)
    $this->lectores_rows = [];


    // 3) Chips detalles estéticos / funcionamiento (arrays)
    $this->detalles_esteticos_checks = [];
    $this->detalles_esteticos_otro = null;

    $this->detalles_funcionamiento_checks = [];
    $this->detalles_funcionamiento_otro = null;


    // Si quieres dejar 1 fila por defecto en cada sección:
    // $this->almacenamiento_slots_rows = [['tipo' => '', 'cantidad' => 1]];
    // $this->lectores_rows = [['tipo' => '', 'cantidad' => 1]];
}

}
