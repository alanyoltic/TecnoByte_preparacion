<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioConsumible extends Model
{
    protected $fillable = [
        'consumible_id',
        'almacen_id',
        'cantidad',
    ];

    public function consumible()
    {
        return $this->belongsTo(Consumible::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}












