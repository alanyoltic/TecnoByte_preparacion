<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioPieza extends Model
{
    protected $table = 'inventario_piezas';

    protected $fillable = [
        'catalogo_pieza_id', 'numero_serie', 'origen',
        'equipo_origen_id', 'almacen_id', 'costo',
        'registrado_por_id', 'estatus', 'fecha_ingreso', 'notas',
    ];

    protected $casts = [
        'costo'         => 'decimal:2',
        'fecha_ingreso' => 'date',
    ];

    const COMPRA   = 'COMPRA';
    const DESHUESO = 'DESHUESO';

    const DISPONIBLE   = 'DISPONIBLE';
    const RESERVADA    = 'RESERVADA';
    const USADA        = 'USADA';
    const DADA_DE_BAJA = 'DADA_DE_BAJA';

    public function catalogoPieza(): BelongsTo
    {
        return $this->belongsTo(CatalogoPieza::class, 'catalogo_pieza_id');
    }

    public function equipoOrigen(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_origen_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estatus', self::DISPONIBLE);
    }

    public function estaDisponible(): bool
    {
        return $this->estatus === self::DISPONIBLE;
    }
}
