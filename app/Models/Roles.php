<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getHierarchyAttribute()
    {
        $map = [
            'ceo'           => 100,
            'gerente'       => 90,
            'supervisor'    => 80,
            'lider'         => 80,
            'admin_sistema' => 70,
            'sistemas'      => 60,
            'calidad'       => 50,
            'tecnico'       => 40,
            'asesor_tienda' => 40,
            'asesor_linea'  => 40,
            'usuario'       => 10,
            'exhibicion'    => 0,
        ];

        return $map[strtolower($this->slug)] ?? 0;
    }
}
