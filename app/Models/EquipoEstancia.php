<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoEstancia extends Model
{
    protected $table = 'equipo_estancias';

    protected $guarded = [];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}
