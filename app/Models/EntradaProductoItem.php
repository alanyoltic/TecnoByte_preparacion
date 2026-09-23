<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaProductoItem extends Model
{
    protected $table = 'entrada_producto_items';

    protected $fillable = [
        'entrada_producto_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'almacen_id',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(EntradaProducto::class, 'entrada_producto_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function subtotal(): float
    {
        if (! $this->precio_unitario) {
            return 0;
        }

        return (float) ($this->precio_unitario * $this->cantidad);
    }
}
