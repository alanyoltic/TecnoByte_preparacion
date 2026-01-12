<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Equipo;
use Illuminate\Validation\Rule;

class EquipoForm extends Form
{
    public ?Equipo $equipo;

    // Propiedades del Modelo Equipo
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
    public $ram_total;
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
    public $slots_alm_ssd;
    public $slots_alm_m2;
    public $slots_alm_m2_micro;
    public $slots_alm_hdd;
    public $slots_alm_msata;
    public $ethernet_tiene = false;
    public $ethernet_es_gigabit = false;
    public $puertos_conectividad;
    public $dispositivos_entrada;
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
    public $teclado_idioma = 'N/A';
    public $notas_generales;
    public $detalles_esteticos;
    public $detalles_funcionamiento;
    public $pantalla_pulgadas = null;
    public $pantalla_resolucion = null;
    public $pantalla_es_touch = false;
    public $monitor_incluido = 'NO'; 
    public $monitor_pulgadas = null;
    public $monitor_resolucion = null;
    public $monitor_es_touch = false;
    public array $monitor_entradas_rows = [];
    public $monitor_detalles_esteticos_checks = '';
    public $monitor_detalles_esteticos_otro = '';
    public $monitor_detalles_funcionamiento_checks = '';
    public $monitor_detalles_funcionamiento_otro = '';
    public $grafica_integrada_modelo;              
    public $grafica_dedicada_modelo;             
    public $tiene_tarjeta_dedicada = 'NO';            
    public $grafica_dedicada_vram;           
    public $gpu_driver;            
    public $gpu_notas;
    public bool $bateria_tiene = true;
    public ?string $bateria1_tipo = null;
    public ?string $bateria1_salud = null;
    public bool $bateria2_tiene = false;
    public ?string $bateria2_tipo = null;
    public ?string $bateria2_salud = null;
            

   

    public function fillFromModel(Equipo $equipo): void
    {
        
        $this->fill($equipo->toArray());

        // Casts a boolean (DB -> PHP)
        $this->ram_es_soldada        = (bool) $equipo->ram_es_soldada;
        $this->ram_sin_slots         = (bool) $equipo->ram_sin_slots;
        $this->ethernet_tiene        = (bool) $equipo->ethernet_tiene;
        $this->ethernet_es_gigabit   = (bool) $equipo->ethernet_es_gigabit;
    }



    public function snapshotPersistible(): array
{
    // Solo campos que realmente representan "estado" guardable.
    // OJO: no incluimos arrays dinámicos ni cosas de UI que cambian de forma.

    return [
        // Relaciones / claves
        'lote_id' => $this->lote_id,
        'lote_modelo_id' => $this->lote_modelo_id,
        'proveedor_id' => $this->proveedor_id,

        // Identidad / estado
        'numero_serie' => $this->numero_serie,
        'estatus_general' => $this->estatus_general,

        // Datos base
        'marca' => $this->marca,
        'modelo' => $this->modelo,
        'tipo_equipo' => $this->tipo_equipo,
        'sistema_operativo' => $this->sistema_operativo,
        'area_tienda' => $this->area_tienda,

        // Procesador
        'procesador_modelo' => $this->procesador_modelo,
        'procesador_generacion' => $this->procesador_generacion,
        'procesador_nucleos' => $this->procesador_nucleos,
        'procesador_frecuencia' => $this->procesador_frecuencia,

        // RAM
        'ram_total' => $this->ram_total,
        'ram_capacidad' => $this->ram_capacidad,
        'ram_tipo' => $this->ram_tipo,
        'ram_es_soldada' => (bool)$this->ram_es_soldada,
        'ram_cantidad_soldada' => $this->ram_cantidad_soldada,
        'ram_sin_slots' => (bool)$this->ram_sin_slots,
        'ram_expansion_max' => $this->ram_expansion_max,
        'ram_slots_totales' => $this->ram_slots_totales,

        // Almacenamiento
        'almacenamiento_principal_capacidad' => $this->almacenamiento_principal_capacidad,
        'almacenamiento_principal_tipo' => $this->almacenamiento_principal_tipo,
        'almacenamiento_secundario_capacidad' => $this->almacenamiento_secundario_capacidad,
        'almacenamiento_secundario_tipo' => $this->almacenamiento_secundario_tipo,

        // Slots almacenamiento (si esto es guardable como columnas)
        'slots_alm_ssd' => $this->slots_alm_ssd,
        'slots_alm_m2' => $this->slots_alm_m2,
        'slots_alm_m2_micro' => $this->slots_alm_m2_micro,
        'slots_alm_hdd' => $this->slots_alm_hdd,
        'slots_alm_msata' => $this->slots_alm_msata,

        // Red / conectividad
        'ethernet_tiene' => (bool)$this->ethernet_tiene,
        'ethernet_es_gigabit' => (bool)$this->ethernet_es_gigabit,
        'puertos_conectividad' => $this->puertos_conectividad,
        'dispositivos_entrada' => $this->dispositivos_entrada,

        // Puertos / lectores
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

        // Teclado / notas
        'teclado_idioma' => $this->teclado_idioma,
        'notas_generales' => $this->notas_generales,

        // Detalles (texto final guardado en equipos)
        'detalles_esteticos' => $this->detalles_esteticos,
        'detalles_funcionamiento' => $this->detalles_funcionamiento,

        // Pantalla integrada (si son columnas legacy en equipos)
        'pantalla_pulgadas' => $this->pantalla_pulgadas,
        'pantalla_resolucion' => $this->pantalla_resolucion,
        'pantalla_es_touch' => (bool)$this->pantalla_es_touch,

        // Monitor externo (si parte se guarda en tabla aparte, igual lo consideramos para "cambios")
        'monitor_incluido' => $this->monitor_incluido,
        'monitor_pulgadas' => $this->monitor_pulgadas,
        'monitor_resolucion' => $this->monitor_resolucion,
        'monitor_es_touch' => (bool)$this->monitor_es_touch,

        // ✅ Normalizamos entradas para que no marque cambios por orden / vacíos
        'monitor_entradas_rows' => $this->normalizeRows($this->monitor_entradas_rows),

        'monitor_detalles_esteticos_checks' => (string)$this->monitor_detalles_esteticos_checks,
        'monitor_detalles_esteticos_otro' => (string)$this->monitor_detalles_esteticos_otro,
        'monitor_detalles_funcionamiento_checks' => (string)$this->monitor_detalles_funcionamiento_checks,
        'monitor_detalles_funcionamiento_otro' => (string)$this->monitor_detalles_funcionamiento_otro,

        // GPU
        'grafica_integrada_modelo' => $this->grafica_integrada_modelo,
        'grafica_dedicada_modelo' => $this->grafica_dedicada_modelo,
        'tiene_tarjeta_dedicada' => $this->tiene_tarjeta_dedicada,
        'grafica_dedicada_vram' => $this->grafica_dedicada_vram,
        'gpu_driver' => $this->gpu_driver,
        'gpu_notas' => $this->gpu_notas,

        // Baterías (aquí solo para detectar cambios; luego veremos tu punto 2)
        'bateria_tiene' => (bool)$this->bateria_tiene,
        'bateria1_tipo' => $this->bateria1_tipo,
        'bateria1_salud' => $this->bateria1_salud,
        'bateria2_tiene' => (bool)$this->bateria2_tiene,
        'bateria2_tipo' => $this->bateria2_tipo,
        'bateria2_salud' => $this->bateria2_salud,
    ];
}

/**
 * Normaliza arrays para evitar "cambios falsos" por orden o valores vacíos.
 */
protected function normalizeRows(array $rows): array
{
    $rows = array_values(array_filter($rows, function ($r) {
        if (!is_array($r)) return false;
        $v = trim((string)($r['value'] ?? $r['entrada'] ?? $r['tipo'] ?? ''));
        return $v !== '';
    }));

    $rows = array_map(function ($r) {
        $v = trim((string)($r['value'] ?? $r['entrada'] ?? $r['tipo'] ?? ''));
        return ['value' => $v];
    }, $rows);

    sort($rows); // orden estable para comparar

    return $rows;
}





    public function setEquipo(Equipo $equipo): void
    {
        $this->equipo = $equipo;
        $this->fillFromModel($equipo);
    }


    public function rules()
    {
        return [
            'numero_serie' => ['required', 'string', Rule::unique('equipos', 'numero_serie')->ignore($this->equipo->id)],
            'puertos_conectividad' => 'required|string',
            'dispositivos_entrada' => 'required|string',
            'lote_id' => 'nullable|exists:lotes,id',
            'lote_modelo_id' => 'nullable|exists:lote_modelos_recibidos,id',
            'ethernet_tiene' => ['nullable','boolean'],
            'ethernet_es_gigabit' => ['nullable','boolean'],
            'teclado_idioma' => ['required','string','max:50'],
            'ethernet_es_gigabit' => ['nullable','boolean', Rule::requiredIf(fn() => (bool)$this->ethernet_tiene)],


        ];
    }
}