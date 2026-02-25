<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaDetalle extends Model
{
    protected $fillable = [
        'transferencia_id',
        'movible_id',
        'movible_type',
        'cantidad'
    ];

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class);
    }

    public function movible()
    {
        return $this->morphTo();
    }
}
