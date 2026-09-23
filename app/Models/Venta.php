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
        'metodo_pago',
        'subtotal',
        'iva',
        'total',
        'estatus',
        'notas',
        'cancelada_por_id',
        'cancelada_en',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'iva'          => 'decimal:2',
        'total'        => 'decimal:2',
        'cancelada_en' => 'datetime',
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
     * Recalcula y persiste subtotal, iva y total desde los detalles.
     */
    public function recalcularTotales(): void
    {
        $subtotal = $this->detalles()->sum('subtotal');
        $iva      = round($subtotal * 0.16, 2); // IVA 16 % — ajustable

        $this->update([
            'subtotal' => $subtotal,
            'iva'      => $iva,
            'total'    => $subtotal + $iva,
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
}
