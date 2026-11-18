<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'folio',
        'cliente_id',
        'vendedor_id',
        'almacen_id',
        'sucursal_id',
        'metodo_pago',
        'subtotal',
        'tipo_descuento',
        'valor_descuento',
        'descuento',
        'tasa_iva',
        'aplica_iva',
        'iva',
        'total',
        'estatus',
        'notas',
        'cancelada_por_id',
        'cancelada_en',
        'corte_caja_id',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'valor_descuento' => 'decimal:2',
        'descuento'       => 'decimal:2',
        'tasa_iva'        => 'decimal:2',
        'aplica_iva'      => 'boolean',
        'iva'             => 'decimal:2',
        'total'           => 'decimal:2',
        'cancelada_en'    => 'datetime',
    ];

    // ─── Constantes ─────────────────────────────────────────────────────────

    const ESTATUS_PENDIENTE  = 'PENDIENTE';
    const ESTATUS_COMPLETADA = 'COMPLETADA';
    const ESTATUS_CANCELADA  = 'CANCELADA';

    const METODOS_PAGO = [
        'EFECTIVO'      => 'Efectivo',
        'TDC'           => 'Tarjeta de Crédito (TDC)',
        'TDD'           => 'Tarjeta de Débito (TDD)',
        'TRANSFERENCIA' => 'Transferencia',
        'MULTIPLE'      => 'Pago múltiple',
    ];

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id')->withoutGlobalScopes();
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function scopeDeSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    public function canceladaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelada_por_id')->withoutGlobalScopes();
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Genera el siguiente folio disponible. Formato: TKT-YYYY-NNNNN
     */
    public static function generarFolio(): string
    {
        $año = now()->format('Y');
        $prefijo = "TKT-{$año}-";

        $ultimo = static::where('folio', 'like', "{$prefijo}%")
            ->orderByDesc('folio')
            ->value('folio');

        $siguiente = $ultimo
            ? (int) substr($ultimo, strlen($prefijo)) + 1
            : 1;

        return $prefijo . str_pad($siguiente, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Recalcula y persiste subtotal, descuento, iva y total desde los detalles.
     */
    public function recalcularTotales(): void
    {
        $subtotal = (float) $this->detalles()->sum('subtotal');

        $descuentoMonto = 0.00;
        if ($this->tipo_descuento === 'PORCENTAJE') {
            $descuentoMonto = round(($subtotal * (float) $this->valor_descuento) / 100, 2);
        } else {
            $descuentoMonto = min($subtotal, (float) ($this->valor_descuento ?? $this->descuento ?? 0));
        }

        $baseGravable = max(0, $subtotal - $descuentoMonto);
        $tasaIva = $this->aplica_iva ? (float) ($this->tasa_iva ?: 16.00) : 0.00;
        $iva = $tasaIva > 0 ? round($baseGravable * ($tasaIva / 100), 2) : 0.00;

        $this->update([
            'subtotal'        => $subtotal,
            'descuento'       => $descuentoMonto,
            'valor_descuento' => (float) ($this->valor_descuento ?? 0),
            'tasa_iva'        => $tasaIva,
            'aplica_iva'      => $tasaIva > 0,
            'iva'             => $iva,
            'total'           => $baseGravable + $iva,
        ]);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeCompletadas($query)
    {
        return $query->where('estatus', self::ESTATUS_COMPLETADA);
    }

    public function scopePendientes($query)
    {
        return $query->where('estatus', self::ESTATUS_PENDIENTE);
    }

    public function corteCaja(): BelongsTo
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }
}

