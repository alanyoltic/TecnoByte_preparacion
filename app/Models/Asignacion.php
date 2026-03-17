<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignacion extends Model
{
    use SoftDeletes;

    protected $table = 'asignaciones';

    protected $fillable = [
        'tecnico_id',
        'asignado_por_id',
        'lote_modelo_id',
        'cantidad',
        'fecha_asignacion',
        'fecha_entrega',
        'estatus',
        'notas',
        'notas_entrega',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_entrega'    => 'date',
    ];

    const PENDIENTE  = 'PENDIENTE';
    const EN_PROCESO = 'EN_PROCESO';
    const ENTREGADO  = 'ENTREGADO';
    const VALIDADO   = 'VALIDADO';
    const CANCELADO  = 'CANCELADO';

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por_id');
    }

    public function loteModelo(): BelongsTo
    {
        return $this->belongsTo(LoteModeloRecibido::class, 'lote_modelo_id');
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(AsignacionEquipo::class, 'asignacion_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estatus', self::PENDIENTE);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estatus', self::EN_PROCESO);
    }

    public function scopeDelTecnico($query, int $tecnicoId)
    {
        return $query->where('tecnico_id', $tecnicoId);
    }

    public function scopeDelPeriodo($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fecha_asignacion', [$desde, $hasta]);
    }

    public function equiposEscaneados(): int
    {
        return $this->equipos()->count();
    }

    public function equiposPendientesEscanear(): int
    {
        return max($this->cantidad - $this->equiposEscaneados(), 0);
    }

    public function equiposCompletados(): int
    {
        return $this->equipos()->where('camino', AsignacionEquipo::COMPLETADO)->count();
    }

    public function estaActiva(): bool
    {
        return in_array($this->estatus, [self::PENDIENTE, self::EN_PROCESO]);
    }

    public static function labelsEstatus(): array
    {
        return [
            self::PENDIENTE  => 'Pendiente',
            self::EN_PROCESO => 'En Proceso',
            self::ENTREGADO  => 'Entregado',
            self::VALIDADO   => 'Validado',
            self::CANCELADO  => 'Cancelado',
        ];
    }

    public function getLabelEstatusAttribute(): string
    {
        return self::labelsEstatus()[$this->estatus] ?? $this->estatus;
    }
}
