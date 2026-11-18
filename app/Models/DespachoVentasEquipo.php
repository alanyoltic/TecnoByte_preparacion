<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DespachoVentasEquipo extends Model
{
    protected $table = 'despacho_ventas_equipos';

    protected $guarded = [];

    protected $casts = [
        'precio_sugerido' => 'decimal:2',
    ];

    // =========================================================
    // RELACIONES
    // =========================================================

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(DespachoVentas::class, 'despacho_ventas_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Descripción corta del equipo para mostrar en la UI.
     */
    public function getDescripcionEquipoAttribute(): string
    {
        $equipo = $this->equipo;
        if (! $equipo) {
            return 'Equipo no encontrado';
        }

        $partes = array_filter([
            $equipo->marca,
            $equipo->modelo,
            $equipo->numero_serie ? "({$equipo->numero_serie})" : null,
        ]);

        return implode(' ', $partes);
    }
}
