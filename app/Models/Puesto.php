<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $table = 'puestos';

    protected $fillable = ['clave', 'nombre', 'activo'];

    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'departamento_puestos')
            ->withPivot(['activo'])
            ->withTimestamps();
    }
}
