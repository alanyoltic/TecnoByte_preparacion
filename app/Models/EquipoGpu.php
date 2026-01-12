<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoGpu extends Model
{
    protected $table = 'equipo_gpus';

    protected $fillable = [
        'equipo_id','tipo','activo','marca','modelo','vram','vram_unidad','notas',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'vram' => 'integer',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}
