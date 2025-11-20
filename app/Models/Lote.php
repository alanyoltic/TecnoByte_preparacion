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

    protected $table = 'lotes';

        protected $fillable = [
        'nombre_lote',
        'proveedor_id',
        'fecha_llegada',
    ];




    public function proveedor()
    {
        // Esto le dice que la columna 'proveedor_id' 
        // se conecta con el modelo 'Proveedor'
        return $this->belongsTo(Proveedor::class);
    }

        public function modelosRecibidos()
    {
        return $this->hasMany(LoteModeloRecibido::class, 'lote_id');
    }
}