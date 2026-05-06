<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Aviso extends Model
{
    public const MAX_ACTIVE_VISIBLE = 10;
    public const DASHBOARD_LIMIT = 5;
    public const TAGS = ['INFO', 'IMPORTANTE', 'TIP', 'META'];
    public const COLORS = ['slate', 'amber', 'blue', 'emerald', 'rose'];

    protected $table = 'avisos';

    protected $fillable = [
        'titulo',
        'texto',
        'tag',
        'color',
        'icono',
        'is_active',
        'pinned',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pinned'    => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function scopeActivos(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeOrdenDashboard(Builder $query): Builder
    {
        return $query->orderByDesc('pinned')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at');
    }

    public static function tags(): array
    {
        return self::TAGS;
    }

    public static function colors(): array
    {
        return self::COLORS;
    }

    public static function activeVisibleCount(?int $ignoreId = null): int
    {
        return self::query()
            ->activos()
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->count();
    }

    public static function canActivate(?int $ignoreId = null): bool
    {
        return self::activeVisibleCount($ignoreId) < self::MAX_ACTIVE_VISIBLE;
    }
}
