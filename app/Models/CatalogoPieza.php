<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoPieza extends Model
{
    protected $table = 'catalogo_piezas';

    protected $fillable = [
        'nombre', 'descripcion', 'categoria',
        'especificacion', 'notas_compatibilidad',
        'requiere_serie', 'activo',
    ];

    protected $casts = [
        'requiere_serie' => 'boolean',
        'activo'         => 'boolean',
    ];

    const CATEGORIAS = [
        'RAM', 'SSD', 'HDD', 'Batería', 'Pantalla',
        'Teclado', 'Carcasa', 'Palmrest', 'Bisagra',
        'Cargador', 'Placa Base', 'Ventilador', 'Otro',
    ];

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ── Relaciones ────────────────────────────────────────────────────

    public function inventario(): HasMany
    {
        return $this->hasMany(InventarioPieza::class, 'catalogo_pieza_id');
    }

    public function inventarioDisponible(): HasMany
    {
        return $this->hasMany(InventarioPieza::class, 'catalogo_pieza_id')
                    ->where('cantidad_disponible', '>', 0);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudPieza::class, 'catalogo_pieza_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /** Stock disponible total sumando todas las entradas. */
    public function stockDisponible(): int
    {
        return (int) $this->inventario()->sum('cantidad_disponible');
    }

    public static function paraSelect(): array
    {
        return static::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn($p) => [
                $p->id => "[{$p->categoria}] {$p->nombre}" . ($p->especificacion ? " — {$p->especificacion}" : '')
            ])
            ->toArray();
    }
}
