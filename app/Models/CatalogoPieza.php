<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogoPieza extends Model
{
    use SoftDeletes;

    protected $table = 'catalogo_piezas';

    protected $fillable = [
        'nombre', 'descripcion', 'categoria',
        'especificacion', 'notas_compatibilidad',
        'requiere_serie', 'activo',
    ];

    protected $casts = [
        'requiere_serie' => 'boolean',
        'activo' => 'boolean',
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
            ->mapWithKeys(fn ($p) => [
                $p->id => "[{$p->categoria}] {$p->nombre}".($p->especificacion ? " — {$p->especificacion}" : ''),
            ])
            ->toArray();
    }

    /**
     * Evalúa si la pieza solo existe en almacén como disponible (sin usos, sin solicitudes, sin faltantes en equipos)
     */
    public function sePuedeBorrarFisicamente(): bool
    {
        // Si hay piezas en inventario con un estatus diferente a DISPONIBLE, no se puede.
        $tieneUsoEnInventario = $this->inventario()->where('estatus', '!=', InventarioPieza::DISPONIBLE)->exists();
        if ($tieneUsoEnInventario) {
            return false;
        }

        // Si tiene solicitudes asociadas, no se puede.
        $tieneSolicitudes = $this->solicitudes()->exists();
        if ($tieneSolicitudes) {
            return false;
        }

        // Si hay equipos reportados que les falta esta pieza, no se puede.
        $tieneFaltantesEnEquipos = EquipoPiezaFaltante::where('pieza_id', $this->id)->exists();
        if ($tieneFaltantesEnEquipos) {
            return false;
        }

        return true;
    }
}
