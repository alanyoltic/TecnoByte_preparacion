<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transferencia extends Model
{
    protected $fillable = [
        'almacen_origen_id',
        'almacen_destino_id',
        'created_by',
        'approved_by',
        'estatus',
        'enviada_at',
        'aprobada_at',
        'observaciones',
    ];

    protected $casts = [
        'enviada_at' => 'datetime',
        'aprobada_at' => 'datetime',
    ];

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TransferenciaDetalle::class);
    }
}