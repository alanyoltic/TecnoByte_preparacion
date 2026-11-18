<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VentaDetalle extends Model
{
    protected $fillable = [
        'venta_id',
        'vendible_type',
        'vendible_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Relación polimórfica: retorna un Equipo o un Producto.
     * Los Consumibles NUNCA aparecen aquí (son uso interno).
     */
    public function vendible(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function esEquipo(): bool
    {
        return $this->vendible_type === Equipo::class;
    }

    public function esProducto(): bool
    {
        return $this->vendible_type === Producto::class;
    }
}
