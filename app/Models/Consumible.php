<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumible extends Model
{
    protected $fillable = [
        'nombre',
        'categoria',
        'descripcion',
        'activo',
    ];

    // Stock en todos los almacenes
    public function inventarios()
    {
        return $this->hasMany(InventarioConsumible::class);
    }

    // Stock en un almacén específico
    public function stockEnAlmacen(int $almacenId): int
    {
        return $this->inventarios()
            ->where('almacen_id', $almacenId)
            ->value('cantidad') ?? 0;
    }
}
