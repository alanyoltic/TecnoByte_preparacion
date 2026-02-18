<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreparacionMetaMensual extends Model
{
    protected $table = 'preparacion_metas_mensuales';

    protected $fillable = [
        'anio',
        'mes',
        'tecnicos_iniciales',
        'meta_total',
        'hubo_movimientos',
    ];
}
