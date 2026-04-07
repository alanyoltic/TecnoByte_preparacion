<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioPieza extends Model
{
    protected $table = 'inventario_piezas';

    protected $fillable = [
        'catalogo_pieza_id', 'numero_serie', 'origen',
        'compra_item_id', 'equipo_origen_id', 'equipo_destino_id',
        'almacen_id', 'costo', 'registrado_por_id',
        'estatus', 'fecha_ingreso', 'notas',
        'cantidad_inicial', 'cantidad_disponible',
        'cantidad_reservada', 'cantidad_usada', 'cantidad_baja',
    ];

    protected $casts = [
        'costo'              => 'decimal:2',
        'fecha_ingreso'      => 'date',
        'cantidad_inicial'   => 'integer',
        'cantidad_disponible'=> 'integer',
        'cantidad_reservada' => 'integer',
        'cantidad_usada'     => 'integer',
        'cantidad_baja'      => 'integer',
    ];

    const COMPRA   = 'COMPRA';
    const DESHUESO = 'DESHUESO';

    const DISPONIBLE   = 'DISPONIBLE';
    const AGOTADA      = 'AGOTADA';
    const DADA_DE_BAJA = 'DADA_DE_BAJA';

    // ── Relaciones ────────────────────────────────────────────────────

    public function catalogoPieza(): BelongsTo
    {
        return $this->belongsTo(CatalogoPieza::class, 'catalogo_pieza_id');
    }

    public function compraItem(): BelongsTo
    {
        return $this->belongsTo(CompraInventarioItem::class, 'compra_item_id');
    }

    public function equipoOrigen(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_origen_id');
    }

    public function equipoDestino(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_destino_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeDisponibles($query)
    {
        return $query->where('cantidad_disponible', '>', 0);
    }

    public function scopePorCatalogo($query, int $catalogoPiezaId)
    {
        return $query->where('catalogo_pieza_id', $catalogoPiezaId);
    }

    // ── Helpers de estado ─────────────────────────────────────────────

    public function estaDisponible(): bool
    {
        return $this->cantidad_disponible > 0;
    }

    /** Actualiza el estatus calculado a partir de las cantidades. */
    public function actualizarEstatus(): void
    {
        // DADA_DE_BAJA es un estado manual: no se sobreescribe automáticamente
        if ($this->estatus === self::DADA_DE_BAJA) {
            return;
        }

        $nuevoEstatus = $this->cantidad_disponible > 0
            ? self::DISPONIBLE
            : self::AGOTADA;

        $this->update(['estatus' => $nuevoEstatus]);
    }

    /** Da de baja manual toda la entrada de stock. */
    public function darDeBaja(): void
    {
        $this->update([
            'estatus'            => self::DADA_DE_BAJA,
            'cantidad_baja'      => $this->cantidad_baja + $this->cantidad_disponible,
            'cantidad_disponible'=> 0,
        ]);
    }

    /** Información del origen para mostrar en UI. */
    public function getOrigenDescripcionAttribute(): string
    {
        if ($this->origen === self::DESHUESO) {
            $equipo = $this->equipoOrigen;
            return $equipo
                ? "Computadora Despiece — {$equipo->numero_serie}"
                : 'Pendiente Despiece';
        }

        $compra = $this->compraItem?->compra;
        if ($compra) {
            $proveedor = $compra->proveedor->nombre_empresa ?? '?';
            $folio     = $compra->folio ? " #{$compra->folio}" : '';
            return "Compra{$folio} — {$proveedor}";
        }

        return 'Compra';
    }
}
