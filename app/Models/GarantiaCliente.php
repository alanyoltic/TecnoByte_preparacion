<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GarantiaCliente extends Model
{
    protected $table = 'garantias_clientes';

    protected $fillable = [
        'venta_id',
        'equipo_id',
        'cliente_id',
        'reportado_por_id',
        'almacen_origen_id',
        'despacho_retorno_id',
        'estatus',
        'falla_reportada',
        'condiciones_recepcion',
        'resolucion_tecnica',
        'fecha_resolucion',
        'fecha_entrega_cliente'
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
        'fecha_entrega_cliente' => 'datetime',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_id');
    }

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function despachoRetorno(): BelongsTo
    {
        return $this->belongsTo(DespachoVentas::class, 'despacho_retorno_id');
    }
}
