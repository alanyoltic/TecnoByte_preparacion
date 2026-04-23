<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudPiezaIntento extends Model
{
    protected $table = 'solicitud_pieza_intentos';

    protected $fillable = [
        'solicitud_pieza_id', 'numero_intento', 'inventario_pieza_id',
        'asignado_a_id', 'asignado_por_id', 'asignado_en',
        'puntos_override', 'notas_asignacion', 'iniciada_instalacion_en',
        'confirmada_en', 'funciono', 'notas_confirmacion',
    ];

    protected $casts = [
        'asignado_en'             => 'datetime',
        'iniciada_instalacion_en' => 'datetime',
        'confirmada_en'           => 'datetime',
        'funciono'                => 'boolean',
        'puntos_override'         => 'decimal:2',
    ];

    public function solicitudPieza(): BelongsTo
    {
        return $this->belongsTo(SolicitudPieza::class, 'solicitud_pieza_id');
    }

    public function inventarioPieza(): BelongsTo
    {
        return $this->belongsTo(InventarioPieza::class, 'inventario_pieza_id');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_id')->withoutGlobalScopes();
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por_id')->withoutGlobalScopes();
    }

    public function fueIniciada(): bool
    {
        return $this->iniciada_instalacion_en !== null;
    }

    public function estaCompletado(): bool
    {
        return $this->confirmada_en !== null;
    }
}
