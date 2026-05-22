<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ValidacionCalidad extends Model
{
    use HasFactory;

    protected $table = 'validaciones_calidad';

    // ──────────────────────────────────────────────────────────────────────
    // CONSTANTES DE ESTADO
    // ──────────────────────────────────────────────────────────────────────

    const APROBADO  = 'APROBADO';
    const RECHAZADO = 'RECHAZADO';

    // ──────────────────────────────────────────────────────────────────────
    // FILLABLES & CASTS
    // ──────────────────────────────────────────────────────────────────────

    protected $fillable = [
        'equipo_id',
        'asignacion_equipo_id',
        'validado_por_user_id',
        'estado',
        'calificacion_general',
        'checklist_qué_salió_bien',
        'checklist_qué_salió_mal',
        'motivo',
        'notas_adicionales',
    ];

    protected $casts = [
        'checklist_qué_salió_bien' => 'array',
        'checklist_qué_salió_mal'  => 'array',
        'calificacion_general'     => 'integer',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // RELACIONES
    // ──────────────────────────────────────────────────────────────────────

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function asignacionEquipo(): BelongsTo
    {
        return $this->belongsTo(AsignacionEquipo::class, 'asignacion_equipo_id');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por_user_id');
    }

    // ──────────────────────────────────────────────────────────────────────
    // SCOPES
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Filtrar validaciones aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', self::APROBADO);
    }

    /**
     * Filtrar validaciones rechazadas
     */
    public function scopeRechazadas($query)
    {
        return $query->where('estado', self::RECHAZADO);
    }

    /**
     * Filtrar por usuario que validó
     */
    public function scopeValidadoPor($query, int $userId)
    {
        return $query->where('validado_por_user_id', $userId);
    }

    /**
     * Filtrar por equipo
     */
    public function scopeDelEquipo($query, int $equipoId)
    {
        return $query->where('equipo_id', $equipoId);
    }

    /**
     * Ordenar por fecha más reciente primero
     */
    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Obtener la validación más reciente de un equipo
     */
    public function scopeUltimaDelEquipo($query, int $equipoId)
    {
        return $query->where('equipo_id', $equipoId)
            ->orderByDesc('created_at')
            ->limit(1);
    }

    /**
     * Filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    /**
     * Filtrar por calificación mínima
     */
    public function scopeCalificacionMinima($query, int $minimo)
    {
        return $query->whereNotNull('calificacion_general')
            ->where('calificacion_general', '>=', $minimo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // MÉTODOS & HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Label amigable del estado
     */
    public function getLabelEstadoAttribute(): string
    {
        return match ($this->estado) {
            self::APROBADO  => '✅ Aprobado',
            self::RECHAZADO => '❌ Rechazado',
            default         => $this->estado,
        };
    }

    /**
     * Indica si fue aprobado
     */
    public function estaAprobado(): bool
    {
        return $this->estado === self::APROBADO;
    }

    /**
     * Indica si fue rechazado
     */
    public function estaRechazado(): bool
    {
        return $this->estado === self::RECHAZADO;
    }

    /**
     * Obtiene el motivo formateado (con fallback)
     */
    public function obtenerMotivo(): string
    {
        if ($this->estaRechazado()) {
            return trim($this->motivo ?? 'No especificado');
        }

        return 'Equipo aprobado en calidad';
    }

    /**
     * Cuenta los elementos en checklist de "qué salió mal"
     */
    public function cantidadDefectos(): int
    {
        $items = $this->checklist_qué_salió_mal ?? [];

        return is_array($items) ? count(array_filter($items)) : 0;
    }

    /**
     * Cuenta los elementos en checklist de "qué salió bien"
     */
    public function cantidadAciertos(): int
    {
        $items = $this->checklist_qué_salió_bien ?? [];

        return is_array($items) ? count(array_filter($items)) : 0;
    }

    /**
     * Obtiene los defectos como array limpio
     */
    public function obtenerDefectos(): array
    {
        $items = $this->checklist_qué_salió_mal ?? [];

        return is_array($items) ? array_filter($items) : [];
    }

    /**
     * Obtiene los aciertos como array limpio
     */
    public function obtenerAciertos(): array
    {
        $items = $this->checklist_qué_salió_bien ?? [];

        return is_array($items) ? array_filter($items) : [];
    }

    /**
     * Obtiene un resumen legible de la validación
     */
    public function obtenerResumen(): string
    {
        $lines = [];
        $lines[] = "**Validación: {$this->labelEstado}**";
        $lines[] = "Validador: {$this->validadoPor->nombre}";
        $lines[] = "Fecha: " . $this->created_at->format('d/m/Y H:i');

        if ($this->estaRechazado()) {
            $lines[] = "**Motivo:** {$this->obtenerMotivo()}";

            $defectos = $this->obtenerDefectos();
            if (!empty($defectos)) {
                $lines[] = "**Qué salió mal:** " . implode(', ', $defectos);
            }
        }

        $aciertos = $this->obtenerAciertos();
        if (!empty($aciertos)) {
            $lines[] = "**Qué salió bien:** " . implode(', ', $aciertos);
        }

        if (!empty(trim($this->notas_adicionales ?? ''))) {
            $lines[] = "**Notas:** {$this->notas_adicionales}";
        }

        return implode("\n", $lines);
    }

    /**
     * Estadísticas globales para reportes
     */
    public static function estadisticas(?\Carbon\Carbon $desde = null, ?\Carbon\Carbon $hasta = null): array
    {
        $query = self::query();

        if ($desde && $hasta) {
            $query->entreFechas($desde, $hasta);
        }

        $total      = $query->count();
        $aprobados  = (clone $query)->aprobadas()->count();
        $rechazados = (clone $query)->rechazadas()->count();

        return [
            'total'       => $total,
            'aprobados'   => $aprobados,
            'rechazados'  => $rechazados,
            'tasa_aprobacion' => $total > 0 ? round(($aprobados / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Obtiene el estado labels para UI
     */
    public static function estados(): array
    {
        return [
            self::APROBADO  => '✅ Aprobado',
            self::RECHAZADO => '❌ Rechazado',
        ];
    }

    /**
     * Opciones de calificación
     */
    public static function calificaciones(): array
    {
        return [
            1 => '⭐ Deficiente',
            2 => '⭐⭐ Malo',
            3 => '⭐⭐⭐ Regular',
            4 => '⭐⭐⭐⭐ Bueno',
            5 => '⭐⭐⭐⭐⭐ Excelente',
        ];
    }
}
