<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionEquipo extends Model
{
    protected $table = 'asignacion_equipos';

    protected $fillable = [
        'asignacion_id',
        'equipo_id',
        'inicio_en',
        'fin_en',
        'camino',
        'notas',
        'pre_asignado',
    ];

    protected $casts = [
        'inicio_en' => 'datetime',
        'fin_en' => 'datetime',
    ];

    // ── Constantes de camino ──────────────────────────────────────────────
    const PENDIENTE = 'PENDIENTE';

    const PRE_ASIGNADO = 'PRE_ASIGNADO';

    const EN_PROCESO = 'EN_PROCESO';

    const EN_CALIDAD = 'EN_CALIDAD';

    const COMPLETADO = 'COMPLETADO';

    const PIEZA_PENDIENTE = 'PIEZA_PENDIENTE';

    const GARANTIA_INTERNA = 'GARANTIA_INTERNA';

    const GARANTIA_EXTERNA = 'GARANTIA_EXTERNA';

    const DESPIECE = 'DESPIECE';
    
    const REASIGNADO = 'REASIGNADO';

    // ── Relaciones ────────────────────────────────────────────────────────
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class, 'asignacion_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function solicitudesPiezas(): HasMany
    {
        return $this->hasMany(SolicitudPieza::class, 'asignacion_equipo_id');
    }

    public function validacionesCalidad(): HasMany
    {
        return $this->hasMany(ValidacionCalidad::class, 'asignacion_equipo_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────
    public function scopeCompletados($query)
    {
        return $query->where('camino', self::COMPLETADO);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('camino', self::EN_PROCESO);
    }

    public function scopeConProblema($query)
    {
        return $query->whereIn('camino', [
            self::PIEZA_PENDIENTE,
            self::GARANTIA_INTERNA,
            self::GARANTIA_EXTERNA,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Minutos que lleva o llevó trabajándose */
    public function minutosEnProceso(): int
    {
        if (! $this->inicio_en) {
            return 0;
        }
        $fin = $this->fin_en ?? now();

        return (int) $this->inicio_en->diffInMinutes($fin);
    }

    /** Si el técnico ya terminó con este equipo (bien o mal) */
    public function estaTerminado(): bool
    {
        return ! in_array($this->camino, [self::PENDIENTE, self::PRE_ASIGNADO, self::EN_PROCESO]) && $this->fin_en !== null;
    }

    /** Si el equipo está activo (trabajándose o por iniciar) */
    public function estaActivo(): bool
    {
        return in_array($this->camino, [self::PENDIENTE, self::PRE_ASIGNADO, self::EN_PROCESO]);
    }

    /** Obtiene la validación de calidad más reciente */
    public function ultimaValidacion(): ?ValidacionCalidad
    {
        return $this->validacionesCalidad()->latest()->first();
    }

    /** Indica si fue rechazado en calidad */
    public function fueRechazadoEnCalidad(): bool
    {
        $validacion = $this->ultimaValidacion();

        return $validacion && $validacion->estaRechazado();
    }

    public static function labelsCamino(): array
    {
        return [
            self::PENDIENTE => 'Por iniciar',
            self::PRE_ASIGNADO => 'Pre-asignado',
            self::EN_PROCESO => 'En Proceso',
            self::EN_CALIDAD => 'En Calidad',
            self::COMPLETADO => 'Completado',
            self::PIEZA_PENDIENTE => 'Pieza Pendiente',
            self::GARANTIA_INTERNA => 'Garantía Interna',
            self::GARANTIA_EXTERNA => 'Garantía Externa',
            self::DESPIECE => 'Para Despiece',
            self::REASIGNADO => 'Reasignado a otro técnico',
        ];
    }

    public function getLabelCaminoAttribute(): string
    {
        return self::labelsCamino()[$this->camino] ?? $this->camino;
    }
}
