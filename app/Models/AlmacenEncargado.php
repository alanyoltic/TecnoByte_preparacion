<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmacenEncargado extends Model
{
    protected $table = 'almacen_encargados';

    protected $fillable = [
        'almacen_id',
        'user_id',
        'desde',
        'hasta',
        'es_principal',
        'activo',
        'creado_por',
        'motivo',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }
}