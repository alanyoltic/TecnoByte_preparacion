<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoMonitor extends Model
{
    protected $table = 'equipo_monitores';

    protected $fillable = [
        'equipo_id',
        'origen_pantalla',
        'incluido',
        'pulgadas',
        'resolucion',
        'tipo_panel',
        'es_touch',
        'in_hdmi',
        'in_mini_hdmi',
        'in_vga',
        'in_dvi',
        'in_displayport',
        'in_mini_displayport',
        'in_usb_2',
        'in_usb_3',
        'in_usb_31',
        'in_usb_32',
        'in_usb_c',
        'detalles_esteticos_checks',
        'detalles_esteticos_otro',
        'detalles_funcionamiento_checks',
        'detalles_funcionamiento_otro',
    ];

    protected $casts = [
        'incluido' => 'boolean',
        'es_touch' => 'boolean',
        'in_hdmi' => 'integer',
        'in_mini_hdmi' => 'integer',
        'in_vga' => 'integer',
        'in_dvi' => 'integer',
        'in_displayport' => 'integer',
        'in_mini_displayport' => 'integer',
        'in_usb_2' => 'integer',
        'in_usb_3' => 'integer',
        'in_usb_31' => 'integer',
        'in_usb_32' => 'integer',
        'in_usb_c' => 'integer',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
