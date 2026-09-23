<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntradaProducto extends Model
{
    protected $table = 'entradas_productos';

    protected $fillable = [
        'proveedor_id',
        'fecha',
        'folio_factura',
        'total_estimado',
        'notas',
        'registrado_por_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_estimado' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id')->withoutGlobalScopes();
    }

    public function items(): HasMany
    {
        return $this->hasMany(EntradaProductoItem::class, 'entrada_producto_id');
    }

    /**
     * Total de piezas ingresadas sumando todos los ítems.
     */
    public function totalPiezas(): int
    {
        return $this->items()->sum('cantidad');
    }

    /**
     * Total calculado multiplicando precio_unitario × cantidad por ítem.
     */
    public function totalCalculado(): float
    {
        return (float) $this->items()
            ->whereNotNull('precio_unitario')
            ->selectRaw('SUM(cantidad * precio_unitario) as total')
            ->value('total') ?? 0;
    }
}
