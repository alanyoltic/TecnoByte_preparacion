<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompraInventario extends Model
{
    protected $table = 'compras_inventario';

    protected $fillable = [
        'proveedor_id', 'lote_id', 'lote_compra_id', 'fecha_compra',
        'folio', 'total_estimado', 'notas', 'registrado_por_id', 'area',
        'subtotal', 'iva', 'total', 'tipo_iva',
        'estatus', 'nombre_lote_propuesto', 'aprobado_por_id',
        'aprobado_gerente_por_id', 'fecha_aprobacion_gerente',
        'cancelado_por_id', 'fecha_cancelacion',
        'moneda', 'tipo_cambio'
    ];

    const AREA_PREPARACION = 'PREPARACION';
    const AREA_VENTAS      = 'VENTAS';
    const AREA_ADMIN       = 'ADMIN';

    public function scopeDeArea($query, string $area)
    {
        return $query->where('area', $area);
    }

    protected $casts = [
        'fecha_compra' => 'date',
        'total_estimado' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function loteDestino(): BelongsTo
    {
        // El lote de compra formal
        return $this->belongsTo(LoteCompra::class, 'lote_compra_id');
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

    public function cargadores(): HasMany
    {
        return $this->hasMany(Cargador::class, 'compra_inventario_id');
    }
}
