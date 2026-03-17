<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudPieza extends Model
{
    protected $table = 'solicitudes_piezas';

    protected $fillable = [
        'asignacion_equipo_id', 'solicitado_por_id', 'catalogo_pieza_id',
        'descripcion_libre', 'estatus', 'inventario_pieza_id',
        'respondida_por_id', 'respondida_en', 'notas_respuesta',
    ];

    protected $casts = [
        'respondida_en' => 'datetime',
    ];

    const PENDIENTE           = 'PENDIENTE';
    const SURTIDA_INVENTARIO  = 'SURTIDA_INVENTARIO';
    const PENDIENTE_COMPRA    = 'PENDIENTE_COMPRA';
    const COMPRADA            = 'COMPRADA';
    const CANCELADA           = 'CANCELADA';

    public function asignacionEquipo(): BelongsTo
    {
        return $this->belongsTo(AsignacionEquipo::class, 'asignacion_equipo_id');
    }

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por_id');
    }

    public function catalogoPieza(): BelongsTo
    {
        return $this->belongsTo(CatalogoPieza::class, 'catalogo_pieza_id');
    }

    public function inventarioPieza(): BelongsTo
    {
        return $this->belongsTo(InventarioPieza::class, 'inventario_pieza_id');
    }

    public function respondidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondida_por_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estatus', self::PENDIENTE);
    }

    public function scopePendientesCompra($query)
    {
        return $query->where('estatus', self::PENDIENTE_COMPRA);
    }

    public function estaResuelta(): bool
    {
        return in_array($this->estatus, [
            self::SURTIDA_INVENTARIO,
            self::COMPRADA,
            self::CANCELADA,
        ]);
    }

    public function getNombrePiezaAttribute(): string
    {
        return $this->catalogoPieza?->nombre ?? $this->descripcion_libre ?? 'Sin descripción';
    }

    public static function labelsEstatus(): array
    {
        return [
            self::PENDIENTE          => 'Pendiente',
            self::SURTIDA_INVENTARIO => 'Surtida del Inventario',
            self::PENDIENTE_COMPRA   => 'Pendiente de Compra',
            self::COMPRADA           => 'Comprada',
            self::CANCELADA          => 'Cancelada',
        ];
    }
}
