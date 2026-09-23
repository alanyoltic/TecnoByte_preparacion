<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $fillable = [
        'sku',
        'codigo_barras',
        'nombre',
        'categoria',
        'marca',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta'  => 'decimal:2',
        'activo'        => 'boolean',
    ];

    // ─── Catálogos estáticos ────────────────────────────────────────────────

    public static array $categorias = [
        'ACCESORIO'  => 'Accesorio',
        'CABLE'      => 'Cable',
        'PERIFERICO' => 'Periférico',
        'BOLSA'      => 'Bolsa / Mochila',
        'LIMPIEZA'   => 'Limpieza',
        'OTRO'       => 'Otro',
    ];

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function inventarios(): HasMany
    {
        return $this->hasMany(InventarioProducto::class);
    }

    // ─── Helpers de stock ───────────────────────────────────────────────────

    /**
     * Stock disponible en un almacén específico.
     */
    public function stockEnAlmacen(int $almacenId): int
    {
        return $this->inventarios()
            ->where('almacen_id', $almacenId)
            ->value('cantidad') ?? 0;
    }

    /**
     * Stock total sumado de todos los almacenes.
     */
    public function stockTotal(): int
    {
        return $this->inventarios()->sum('cantidad');
    }

    /**
     * Incrementa el stock en un almacén. Crea el registro si no existe.
     */
    public function incrementarStock(int $almacenId, int $cantidad): void
    {
        InventarioProducto::firstOrCreate(
            ['producto_id' => $this->id, 'almacen_id' => $almacenId],
            ['cantidad' => 0]
        )->increment('cantidad', $cantidad);
    }

    /**
     * Decrementa el stock en un almacén. Lanza excepción si no hay suficiente.
     */
    public function decrementarStock(int $almacenId, int $cantidad): void
    {
        $inv = InventarioProducto::where('producto_id', $this->id)
            ->where('almacen_id', $almacenId)
            ->lockForUpdate()
            ->first();

        if (! $inv || $inv->cantidad < $cantidad) {
            throw new \RuntimeException(
                "Stock insuficiente para '{$this->nombre}'. Disponible: " . ($inv->cantidad ?? 0)
            );
        }

        $inv->decrement('cantidad', $cantidad);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('sku', 'like', "%{$termino}%")
              ->orWhere('codigo_barras', 'like', "%{$termino}%")
              ->orWhere('marca', 'like', "%{$termino}%");
        });
    }
}
