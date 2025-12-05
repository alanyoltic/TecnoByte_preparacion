<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use HasFactory, SoftDeletes;

    // Esto permite la importación masiva (como en el Seeder)
    protected $guarded = [];

    /**
     * ====================================================
     * ¡AÑADIMOS LAS RELACIONES!
     * ====================================================
     */

    /**
     * Obtiene el Usuario (técnico) que registró este equipo.
     */
    public function registradoPor()
    {
        // Se conecta con el modelo User en la columna 'registrado_por_user_id'
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }

    /**
     * Obtiene el Proveedor de este equipo.
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * Obtiene la "cuota" del lote de la que salió este equipo.
     */
    public function loteModelo()
    {
        return $this->belongsTo(LoteModeloRecibido::class, 'lote_modelo_id');
    }

    protected $casts = [
    'puertos_usb'   => 'array',
    'puertos_video' => 'array',
    'lectores'      => 'array',
];

}