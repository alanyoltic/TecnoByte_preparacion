<?php

namespace App\Models;

// ¡Asegúrate de que esta importación esté aquí!
use App\Models\Roles; 
use Illuminate\Contracts\Auth\MustVerifyEmail; // Importa esto
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Asegúrate de que 'implements MustVerifyEmail' esté aquí
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    /**
     * Los atributos que deben estar ocultos.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser casteados.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * ====================================================
     * ¡AQUÍ ESTÁ LA SOLUCIÓN!
     * La función se llama 'role' (singular)
     * pero apunta al modelo 'Roles' (plural).
     * ====================================================
     */
    public function role()
    {
        // Esto le dice: "Esta función se conecta con el modelo
        // 'App\Models\Roles' usando la columna 'role_id'".
        return $this->belongsTo(Roles::class, 'role_id');
    }
}