<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaTecnico extends Model
{
    protected $table = 'metas_tecnicos';

    protected $fillable = [
        'tecnico_id', 'periodo', 'meta_puntos', 'asignada_por_id', 'notas',
    ];

    protected $casts = [
        'meta_puntos' => 'decimal:2',
    ];

    // Meta global por defecto — CAMBIAR AQUÍ si cambia la meta general
    const META_DEFAULT = 140.00;

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function asignadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignada_por_id');
    }

    /**
     * Obtener meta de un técnico en un período.
     * Si no tiene meta asignada, devuelve la meta global por defecto.
     */
    public static function obtenerMeta(int $tecnicoId, string $periodo): float
    {
        return (float) (static::where('tecnico_id', $tecnicoId)
            ->where('periodo', $periodo)
            ->value('meta_puntos') ?? self::META_DEFAULT);
    }

    /**
     * Progreso del técnico en el período actual.
     * Devuelve ['puntos' => X, 'meta' => Y, 'porcentaje' => Z]
     */
    public static function progreso(int $tecnicoId, ?string $periodo = null): array
    {
        $periodo  = $periodo ?? now()->format('Y-m');
        $puntos   = PuntoTecnico::totalDelPeriodo($tecnicoId, $periodo);
        $meta     = static::obtenerMeta($tecnicoId, $periodo);
        $pct      = $meta > 0 ? round(($puntos / $meta) * 100, 1) : 0;

        return [
            'puntos'      => $puntos,
            'meta'        => $meta,
            'porcentaje'  => $pct,
            'cumplida'    => $puntos >= $meta,
            'faltante'    => max($meta - $puntos, 0),
        ];
    }
}
