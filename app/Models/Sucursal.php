<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $guarded = [];

    protected $casts = [
        'es_virtual' => 'boolean',
        'activo'     => 'boolean',
    ];

    // =========================================================
    // CLAVES FIJAS
    // =========================================================

    const QRO = 'QRO';

    // =========================================================
    // SCOPES
    // =========================================================

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    // =========================================================
    // RELACIONES
    // =========================================================

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'sucursal_id');
    }

    public function almacenesDeVentas(): HasMany
    {
        return $this->hasMany(Almacen::class, 'sucursal_id')
            ->whereHas('area', fn ($q) => $q->where('clave', 'VENTAS'));
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id')->withoutGlobalScopes();
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public static function qro(): ?self
    {
        return static::where('clave', self::QRO)->first();
    }
}
