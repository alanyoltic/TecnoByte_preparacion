<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoPieza extends Model
{
    protected $table = 'catalogo_piezas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
