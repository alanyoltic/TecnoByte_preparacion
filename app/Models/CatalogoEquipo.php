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

    public function lotesVinculados()
    {
        return $this->hasMany(LoteModeloRecibido::class, 'catalogo_equipo_id');
    }

    public function equiposVinculados()
    {
        return $this->hasMany(Equipo::class, 'catalogo_equipo_id');
    }
}
