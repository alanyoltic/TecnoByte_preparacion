<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompraInventario extends Model
{
    protected $table = 'compras_inventario';

    protected $fillable = [
        'proveedor_id', 'lote_id', 'fecha_compra',
        'folio', 'total_estimado', 'notas', 'registrado_por_id',
    ];

    protected $casts = [
        'fecha_compra'    => 'date',
        'total_estimado'  => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id')->withoutGlobalScopes();
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompraInventarioItem::class, 'compra_inventario_id');
    }

    /** Total de piezas compradas sumando todos los ítems. */
    public function totalPiezas(): int
    {
        return $this->items()->sum('cantidad');
    }

    /** Total calculado multiplicando precio_unitario × cantidad por ítem. */
    public function totalCalculado(): float
    {
        return (float) $this->items()
            ->whereNotNull('precio_unitario')
            ->selectRaw('SUM(cantidad * precio_unitario) as total')
            ->value('total') ?? 0;
    }
}
