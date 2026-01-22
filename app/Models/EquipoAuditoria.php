<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoAuditoria extends Model
{
    protected $table = 'equipo_auditorias';

    protected $fillable = [
        'equipo_id',
        'user_id',
        'accion',
        'motivo',
        'cambios',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'cambios' => 'array',
    ];
}
