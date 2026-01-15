<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoEliminacion extends Model
{
    protected $table = 'equipo_eliminaciones';

    protected $fillable = [
        'accion',
        'equipo_id_original',
        'numero_serie',
        'codigo',
        'tipo_equipo',
        'marca',
        'modelo',
        'user_id',
        'motivo',
        'snapshot',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
