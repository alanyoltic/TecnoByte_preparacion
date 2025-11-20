<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoteModeloRecibido extends Model
{
    protected $table = 'lote_modelos_recibidos';

    protected $fillable = [
        'lote_id',
        'marca',
        'modelo',
        'cantidad_recibida',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'lote_modelo_id');
    }
}
