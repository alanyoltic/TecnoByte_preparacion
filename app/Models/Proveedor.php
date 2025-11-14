<?php

namespace App\Models;

// --- ¡ESTAS LÍNEAS FALTABAN! ---
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // (Para el 'deleted_at')

class Proveedor extends Model
{
    use HasFactory, SoftDeletes; // (Añade SoftDeletes)

    protected $table = 'proveedores';

    protected $guarded = [];
}