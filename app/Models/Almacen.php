<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacenes';

    protected $guarded = [];

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}