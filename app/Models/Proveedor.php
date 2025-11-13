<?php
namespace App\Models;
// ...
class Proveedor extends Model
{
    use HasFactory;
    protected $guarded = []; // <-- ¡Asegúrate de que esta línea esté!
}