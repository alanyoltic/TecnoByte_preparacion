<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoBateria extends Model
{
    use HasFactory;

    protected $table = 'equipo_baterias';

    protected $fillable = [
        'equipo_id',
        'tipo',
        'salud_percent',
        'notas',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
