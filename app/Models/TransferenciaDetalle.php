<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaDetalle extends Model
{
    protected $fillable = [
        'transferencia_id',
        'movible_id',
        'movible_type',
        'cantidad',       // Siempre 1 para equipos serializados, N para consumibles
    ];

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class);
    }

    // Relación polimórfica → Equipo o Consumible
    public function movible()
    {
        return $this->morphTo();
    }

    // Helper: saber si es equipo serializado
    public function esEquipo(): bool
    {
        return $this->movible_type === Equipo::class;
    }

    // Helper: saber si es consumible genérico
    public function esConsumible(): bool
    {
        return $this->movible_type === Consumible::class;
    }
}