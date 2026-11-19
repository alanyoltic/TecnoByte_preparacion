<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacenes';

    protected $guarded = [];

    // IDs fijos de la tabla almacenes — nunca cambiar sin migración de datos
    const CEDIS = 1;

    const PREPARACION = 2;

    const GARANTIAS_INTERNAS = 3;

    const GARANTIAS_EXTERNAS = 4;

    const CALIDAD = 5;

    const SCRAP = 6;

    const PIEZAS_PENDIENTES = 7;

    const AREA_TRANSFERENCIA = 8;

    // Claves fijas de almacenes de Ventas
    const CLAVE_PISO = 'Piso';
    const CLAVE_BODEGA = 'Bode';
    const CLAVE_APARTADOS = 'Apar';

    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    public function scopeVentas($query)
    {
        return $query->where('departamento_id', 2);
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }

    public function encargados()
    {
        return $this->hasMany(\App\Models\AlmacenEncargado::class, 'almacen_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }
}
