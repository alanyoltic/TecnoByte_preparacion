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
            

    // App\Models\Equipo.php



    public function setEquipo(Equipo $equipo)
    {
        $this->equipo = $equipo;
        $this->fill($equipo->toArray());
        
        // Convertir tipos de la DB a booleanos de PHP
        $this->ram_es_soldada = (bool)$equipo->ram_es_soldada;
        $this->ram_sin_slots = (bool)$equipo->ram_sin_slots;
        $this->ethernet_tiene = (bool)$equipo->ethernet_tiene;
        $this->ethernet_es_gigabit = (bool)$equipo->ethernet_es_gigabit;
    }

    public function rules()
    {
        return [
            'numero_serie' => ['required', 'string', Rule::unique('equipos', 'numero_serie')->ignore($this->equipo->id)],
            'puertos_conectividad' => 'required|string',
            'dispositivos_entrada' => 'required|string',
            'lote_id' => 'nullable|exists:lotes,id',
            'lote_modelo_id' => 'nullable|exists:lote_modelos_recibidos,id',
        ];
    }
}