<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DespachoVentas extends Model
{
    use SoftDeletes;

    protected $table = 'despachos_ventas';

    protected $guarded = [];

    protected $casts = [
        'enviado_at'   => 'datetime',
        'aprobado_at'  => 'datetime',
    ];

    // =========================================================
    // CONSTANTES DE ESTATUS
    // =========================================================

    const BORRADOR  = 'BORRADOR';
    const ENVIADO   = 'ENVIADO';
    const APROBADO  = 'APROBADO';
    const RECHAZADO = 'RECHAZADO';
    const CANCELADO = 'CANCELADO';

    // =========================================================
    // HELPERS DE ESTADO
    // =========================================================

    public function esBorrador(): bool
    {
        return $this->estatus === self::BORRADOR;
    }

    public function estaEnviado(): bool
    {
        return $this->estatus === self::ENVIADO;
    }

    public function estaAprobado(): bool
    {
        return $this->estatus === self::APROBADO;
    }

    public function estaRechazado(): bool
    {
        return $this->estatus === self::RECHAZADO;
    }

    public function estaCancelado(): bool
    {
        return $this->estatus === self::CANCELADO;
    }

    /** ¿Puede modificarse (agregar/quitar equipos)? */
    public function esEditable(): bool
    {
        return $this->estatus === self::BORRADOR;
    }

    /** ¿Puede enviarse a Ventas? */
    public function puedeEnviarse(): bool
    {
        return $this->estatus === self::BORRADOR && $this->equipos()->count() > 0;
    }

    // =========================================================
    // LABELS PARA UI
    // =========================================================

    public static function labelsEstatus(): array
    {
        return [
            self::BORRADOR  => 'Borrador',
            self::ENVIADO   => 'Enviado a Ventas',
            self::APROBADO  => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
            self::CANCELADO => 'Cancelado',
        ];
    }

    public function getLabelEstatusAttribute(): string
    {
        return self::labelsEstatus()[$this->estatus] ?? $this->estatus;
    }

    public function getColorEstatusAttribute(): string
    {
        return match ($this->estatus) {
            self::BORRADOR  => 'yellow',
            self::ENVIADO   => 'blue',
            self::APROBADO  => 'green',
            self::RECHAZADO => 'red',
            self::CANCELADO => 'gray',
            default         => 'gray',
        };
    }

    // =========================================================
    // GENERADOR DE FOLIO
    // =========================================================

    /**
     * Genera el siguiente folio en formato DV-YYYY-NNNN.
     */
    public static function generarFolio(): string
    {
        $anio = now()->format('Y');
        $prefijo = "DV-{$anio}-";

        $ultimo = static::where('folio', 'like', "{$prefijo}%")
            ->orderByDesc('id')
            ->value('folio');

        $siguiente = 1;
        if ($ultimo) {
            $partes = explode('-', $ultimo);
            $siguiente = (int) end($partes) + 1;
        }

        return $prefijo . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================
    // RELACIONES
    // =========================================================

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id')->withoutGlobalScopes();
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_user_id')->withoutGlobalScopes();
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(DespachoVentasEquipo::class, 'despacho_ventas_id');
    }

    public function equiposConDetalle(): HasMany
    {
        return $this->hasMany(DespachoVentasEquipo::class, 'despacho_ventas_id')
            ->with(['equipo.loteModelo.catalogoEquipo']);
    }
}
