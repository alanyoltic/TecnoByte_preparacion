<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    protected $fillable = [
        'almacen_origen_id',
        'almacen_destino_id',
        'created_by',
        'approved_by',
        'estatus',
        'observaciones',
        'enviada_at',
        'aprobada_at'
    ];

    public function detalles()
    {
        return $this->hasMany(TransferenciaDetalle::class);
    }

    public function origen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}