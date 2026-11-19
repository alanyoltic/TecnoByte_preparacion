<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $guarded = [];

    protected $casts = [
        'es_virtual' => 'boolean',
        'es_matriz'  => 'boolean',
        'activo'     => 'boolean',
    ];

    // =========================================================
    // CLAVES FIJAS
    // =========================================================

    const QRO = 'QRO_CORREGIDORA';
    const LEON = 'LEON';

    // =========================================================
    // SCOPES
    // =========================================================

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeMatriz($query)
    {
        return $query->where('es_matriz', true);
    }

    // =========================================================
    // RELACIONES
    // =========================================================

    public function encargado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encargado_id')->withoutGlobalScopes();
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'sucursal_id');
    }

    public function almacenesDeVentas(): HasMany
    {
        return $this->hasMany(Almacen::class, 'sucursal_id')
            ->where('departamento_id', 2)
            ->where('activo', 1);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id')->withoutGlobalScopes();
    }

    // =========================================================
    // HELPERS & ACCESSORS
    // =========================================================

    public function getDireccionCompletaAttribute(): string
    {
        $partes = array_filter([
            $this->direccion,
            $this->colonia ? 'Col. ' . $this->colonia : null,
            $this->codigo_postal ? 'C.P. ' . $this->codigo_postal : null,
            $this->ciudad,
            $this->estado,
        ]);

        return implode(', ', $partes);
    }

    public static function matriz(): ?self
    {
        return static::where('es_matriz', true)->first() ?? static::qro();
    }

    public static function qro(): ?self
    {
        return static::where('clave', self::QRO)->orWhere('clave', 'QRO')->first();
    }

    public static function leon(): ?self
    {
        return static::where('clave', self::LEON)->first();
    }
}
