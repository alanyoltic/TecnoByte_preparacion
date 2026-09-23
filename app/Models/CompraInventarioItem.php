<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompraInventarioItem extends Model
{
    protected $table = 'compras_inventario_items';

    protected $fillable = [
        'compra_inventario_id', 'catalogo_pieza_id', 'producto_id', 'consumible_id', 'catalogo_equipo_id',
        'cantidad', 'precio_unitario', 'almacen_id', 'notas',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(CompraInventario::class, 'compra_inventario_id');
    }

    public function catalogoPieza(): BelongsTo
    {
        return $this->belongsTo(CatalogoPieza::class, 'catalogo_pieza_id')->withTrashed();
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }

    public function consumible(): BelongsTo
    {
        return $this->belongsTo(Consumible::class, 'consumible_id')->withTrashed();
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function inventarioPieza(): HasOne
    {
        return $this->hasOne(InventarioPieza::class, 'compra_item_id');
    }

    public function subtotal(): float
    {
        if (! $this->precio_unitario) {
            return 0;
        }

        return (float) ($this->precio_unitario * $this->cantidad);
    }
}
