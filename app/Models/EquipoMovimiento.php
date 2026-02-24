<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoMovimiento extends Model
{

    protected $table = 'equipo_movimientos';

        protected $fillable = [
        'equipo_id',
        'tipo',
        'desde_almacen_id',
        'hacia_almacen_id',
        'motivo',
        'created_by',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false; // tu tabla usa created_at automático

    protected $guarded = [];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function desde()
    {
        return $this->belongsTo(Almacen::class, 'desde_almacen_id');
    }

    public function hacia()
    {
        return $this->belongsTo(Almacen::class, 'hacia_almacen_id');
    }
}