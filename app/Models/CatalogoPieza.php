<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoPieza extends Model
{
    protected $table = 'catalogo_piezas';

    protected $fillable = [
        'nombre', 'descripcion', 'categoria', 'requiere_serie', 'activo',
    ];

    protected $casts = [
        'requiere_serie' => 'boolean',
        'activo'         => 'boolean',
    ];

    const CATEGORIAS = [
        'RAM', 'SSD', 'HDD', 'Batería', 'Teclado',
        'Bisagra', 'Pantalla', 'Cargador', 'Otro',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(InventarioPieza::class, 'catalogo_pieza_id');
    }

    public function inventarioDisponible(): HasMany
    {
        return $this->hasMany(InventarioPieza::class, 'catalogo_pieza_id')
                    ->where('estatus', 'DISPONIBLE');
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudPieza::class, 'catalogo_pieza_id');
    }

    public static function paraSelect(): array
    {
        return static::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn($p) => [
                $p->id => "[{$p->categoria}] {$p->nombre}"
            ])
            ->toArray();
    }
}
