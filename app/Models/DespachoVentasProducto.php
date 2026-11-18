<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DespachoVentasProducto extends Model
{
    protected $table = 'despacho_ventas_productos';

    protected $guarded = [];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_sugerido' => 'float',
    ];

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(DespachoVentas::class, 'despacho_ventas_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
