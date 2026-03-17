<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClasificacionPuntos extends Model
{
    protected $table = 'clasificaciones_puntos';

    protected $fillable = [
        'clave',
        'nombre',
        'descripcion',
        'puntos_base',
        'activo',
    ];

    protected $casts = [
        'puntos_base' => 'decimal:2',
        'activo'      => 'boolean',
    ];

    // ── Constantes de claves ───────────────────────────────────────────────
    const BASICO      = 'A';
    const ESTANDAR    = 'B';
    const INTERMEDIO  = 'C';
    const AVANZADO    = 'D';
    const PREMIUM     = 'E';
    const DESHUESO    = 'F';

    // ── Scopes ────────────────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ── Relaciones ────────────────────────────────────────────────────────
    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class, 'clasificacion_puntos_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Devuelve el catálogo completo para selects en formularios.
     * Ejemplo: ['A' => 'A — Básico (1.0 pts)', 'B' => 'B — Estándar (1.3 pts)', ...]
     */
    public static function paraSelect(): array
    {
        return static::activos()
            ->orderBy('clave')
            ->get()
            ->mapWithKeys(fn($c) => [
                $c->clave => "{$c->clave} — {$c->nombre} ({$c->puntos_base} pts)"
            ])
            ->toArray();
    }

    /**
     * Obtener puntos_base por clave directamente.
     */
    public static function puntosParaClave(string $clave): float
    {
        return (float) static::where('clave', $clave)->value('puntos_base') ?? 1.0;
    }

    // ── Accessor ──────────────────────────────────────────────────────────
    public function getLabelAttribute(): string
    {
        return "{$this->clave} — {$this->nombre}";
    }
}
