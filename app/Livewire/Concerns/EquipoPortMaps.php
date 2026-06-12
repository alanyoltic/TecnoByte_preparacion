<?php

namespace App\Livewire\Concerns;

/**
 * Constantes de mapeo entre etiquetas UI y columnas de BD para puertos,
 * lectores, slots y entradas de monitor del equipo.
 *
 * Centralizado aquí para que RegistrarEquipo, EditarEquipo y MiTrabajo
 * usen siempre la misma definición sin duplicar.
 */
final class EquipoPortMaps
{
    public const MAP_USB = [
        'USB 2.0' => 'puertos_usb_2',
        'USB 3.0' => 'puertos_usb_30',
        'USB 3.1' => 'puertos_usb_31',
        'USB 3.2' => 'puertos_usb_32',
        'USB-C' => 'puertos_usb_c',
        'USB tipo C' => 'puertos_usb_c',
    ];

    public const MAP_VIDEO = [
        'HDMI' => 'puertos_hdmi',
        'Mini HDMI' => 'puertos_mini_hdmi',
        'VGA' => 'puertos_vga',
        'DVI' => 'puertos_dvi',
        'DisplayPort' => 'puertos_displayport',
        'Mini DisplayPort' => 'puertos_mini_dp',
    ];

    public const MAP_LECTORES = [
        'SD' => 'lectores_sd',
        'microSD' => 'lectores_microsd',
        'SmartCard' => 'lectores_sc',
        'eSATA' => 'lectores_esata',
        'SIM' => 'lectores_sim',
    ];

    public const MAP_SLOTS = [
        'SSD' => 'slots_alm_ssd',
        'M.2' => 'slots_alm_m2',
        'M.2 MICRO' => 'slots_alm_m2_micro',
        'HDD' => 'slots_alm_hdd',
        'MSATA' => 'slots_alm_msata',
    ];

    public const MAP_MONITOR_IN = [
        'HDMI' => 'in_hdmi',
        'Mini HDMI' => 'in_mini_hdmi',
        'VGA' => 'in_vga',
        'DVI' => 'in_dvi',
        'DisplayPort' => 'in_displayport',
        'Mini DisplayPort' => 'in_mini_displayport',
        'USB 2.0' => 'in_usb_2',
        'USB 3.0' => 'in_usb_3',
        'USB 3.1' => 'in_usb_31',
        'USB 3.2' => 'in_usb_32',
        'USB-C' => 'in_usb_c',
    ];
}
