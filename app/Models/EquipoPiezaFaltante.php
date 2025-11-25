<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoPiezaFaltante extends Model
{
    protected $table = 'equipo_piezas_faltantes';

    public $timestamps = false; // tu tabla no tiene created_at / updated_at

    protected $fillable = [
        'equipo_id',
        'pieza_id',
        'cantidad',
        'estatus_pieza',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function pieza()
    {
        return $this->belongsTo(CatalogoPieza::class, 'pieza_id');
    }
}
