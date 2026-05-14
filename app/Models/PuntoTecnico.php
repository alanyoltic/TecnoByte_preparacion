<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuntoTecnico extends Model
{
    protected $table = 'puntos_tecnicos';

    protected $fillable = [
        'tecnico_id', 'asignacion_equipo_id', 'clasificacion_puntos_id',
        'puntos_base', 'porcentaje_aplicado', 'puntos_final',
        'ajuste_manual', 'ajustado_por_id', 'motivo_ajuste',
        'rol_en_equipo', 'periodo',
    ];

    protected $casts = [
        'puntos_base'          => 'decimal:2',
        'porcentaje_aplicado'  => 'decimal:2',
        'puntos_final'         => 'decimal:2',
        'ajuste_manual'        => 'decimal:2',
    ];

    // ── Roles ─────────────────────────────────────────────────────────────
    const COMPLETO           = 'COMPLETO';
    const PIEZA_PENDIENTE    = 'PIEZA_PENDIENTE';    // Tech terminó equipo, falta pieza
    const PIEZA_COMPLETADA  = 'PIEZA_COMPLETADA';    // Tech terminó pieza
    const GARANTIA           = 'GARANTIA';
    const DESPIECE           = 'DESPIECE';

    // ── Porcentajes por rol — todos los caminos otorgan el 100% del equipo ──
    const PORCENTAJES = [
        self::COMPLETO        => 100.00,
        self::PIEZA_PENDIENTE => 100.00,
        self::PIEZA_INSTALADA => 100.00,
        self::GARANTIA        => 100.00,
        self::DESPIECE        => 100.00,
    ];

    // ── Relaciones ────────────────────────────────────────────────────────
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id')->withoutGlobalScopes();
    }

    public function asignacionEquipo(): BelongsTo
    {
        return $this->belongsTo(AsignacionEquipo::class, 'asignacion_equipo_id');
    }

    public function clasificacionPuntos(): BelongsTo
    {
        return $this->belongsTo(ClasificacionPuntos::class, 'clasificacion_puntos_id');
    }

    public function ajustadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ajustado_por_id')->withoutGlobalScopes();
    }

    // ── Scopes ────────────────────────────────────────────────────────────
    public function scopeDelPeriodo($query, string $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    public function scopeDelTecnico($query, int $tecnicoId)
    {
        return $query->where('tecnico_id', $tecnicoId);
    }

    // ── Helpers estáticos ─────────────────────────────────────────────────

    /**
     * Calcular y registrar puntos cuando un técnico termina un equipo.
     * Llamar desde AsignacionEquipo cuando cambia el camino.
     */
    public static function registrar(
        int    $tecnicoId,
        int    $asignacionEquipoId,
        string $rol,
        float  $puntosBase,
        ?int   $clasificacionId = null,
    ): self {
        // CAMBIO: Siempre 100% en terminación de equipos
        // El 40/60 de piezas se maneja aparte por el gerente
        $porcentaje  = 100.00;
        $puntosCalc  = round($puntosBase * ($porcentaje / 100), 2);

        return static::create([
            'tecnico_id'              => $tecnicoId,
            'asignacion_equipo_id'    => $asignacionEquipoId,
            'clasificacion_puntos_id' => $clasificacionId,
            'puntos_base'             => $puntosBase,
            'porcentaje_aplicado'     => $porcentaje,
            'puntos_final'            => $puntosCalc,
            'ajuste_manual'           => 0,
            'rol_en_equipo'           => $rol,
            'periodo'                 => now()->format('Y-m'),
        ]);
    }

    /**
     * Total de puntos de un técnico en un período.
     */
    public static function totalDelPeriodo(int $tecnicoId, string $periodo): float
    {
        return (float) static::where('tecnico_id', $tecnicoId)
            ->where('periodo', $periodo)
            ->sum(\DB::raw('puntos_final + ajuste_manual'));
    }

    /**
     * Resumen semanal de un técnico (últimas 4 semanas).
     */
    public static function resumenSemanal(int $tecnicoId): array
    {
        return static::where('tecnico_id', $tecnicoId)
            ->where('created_at', '>=', now()->subWeeks(4))
            ->selectRaw('YEARWEEK(created_at, 1) as semana, SUM(puntos_final + ajuste_manual) as total, COUNT(*) as equipos')
            ->groupBy('semana')
            ->orderBy('semana')
            ->get()
            ->toArray();
    }

    public static function labelsRol(): array
    {
        return [
            self::COMPLETO      => 'Equipo completado (100%)',
            self::PIEZA_PENDIENTE => 'Pieza pendiente (100%)',
            self::PIEZA_INSTALADA => 'Pieza instalada (100%)',
            self::GARANTIA      => 'Canalizado a garantía (100%)',
            self::DESPIECE      => 'Enviado a despiece (100%)',
        ];
    }
}
