<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Proveedor; // <-- ¡Asegúrate de que esta importación esté!

class Lote extends Model
{
    use HasFactory;

    // Esto permite la creación masiva (del seeder)
    protected $guarded = [];

    /**
     * ====================================================
     * ¡AQUÍ ESTÁ LA FUNCIÓN QUE FALTABA!
     * ====================================================
     */
    public function proveedor()
    {
        // Esto le dice que la columna 'proveedor_id' 
        // se conecta con el modelo 'Proveedor'
        return $this->belongsTo(Proveedor::class);
    }
}