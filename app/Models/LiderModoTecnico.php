<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiderModoTecnico extends Model
{
    protected $table = 'lider_modo_tecnico';

    protected $fillable = [
        'lider_id', 'es_tecnico', 'configurado_por_id', 'notas',
    ];

    protected $casts = [
        'es_tecnico' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function lider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lider_id')->withoutGlobalScopes();
    }

    public function configuradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'configurado_por_id')->withoutGlobalScopes();
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('es_tecnico', true);
    }

    // ── Helpers estáticos ────────────────────────────────────────────────

    /**
     * Verificar si un líder trabaja como técnico (sin período).
     */
    public static function trabajaComoTecnico(int $liderId): bool
    {
        return static::where('lider_id', $liderId)
            ->where('es_tecnico', true)
            ->exists();
    }

    /**
     * Obtener todos los líderes que trabajan como técnicos.
     */
    public static function lideresActivos(): array
    {
        return static::where('es_tecnico', true)
            ->pluck('lider_id')
            ->toArray();
    }
}
