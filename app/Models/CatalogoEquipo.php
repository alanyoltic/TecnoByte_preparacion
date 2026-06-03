<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoEquipo extends Model
{
    protected $fillable = [
        'marca',
        'modelo',
        'tipo_equipo',
        'activo',
    ];
}
