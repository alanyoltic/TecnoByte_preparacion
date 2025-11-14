<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteModeloRecibido extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     */
    protected $table = 'lote_modelos_recibidos'; // <-- ¡AÑADE ESTA LÍNEA!

    protected $guarded = [];
}