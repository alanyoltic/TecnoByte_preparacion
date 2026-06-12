<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $guarded = [];

    public function compras(): HasMany
    {
        return $this->hasMany(CompraInventario::class, 'proveedor_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->abreviacion
            ? "{$this->nombre_empresa} ({$this->abreviacion})"
            : $this->nombre_empresa;
    }
}
