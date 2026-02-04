<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamento'; // OJO: tu tabla es singular
    protected $fillable = ['clave', 'nombre', 'activo'];
    public function puestos()
        {
        return $this->belongsToMany(Puesto::class, 'departamento_puestos')
            ->withPivot(['activo'])
            ->withTimestamps();
        }




    
    public $timestamps = true;
}
