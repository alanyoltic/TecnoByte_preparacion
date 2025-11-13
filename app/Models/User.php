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


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

 
    public function role()
    {

        return $this->belongsTo(Roles::class, 'role_id');
    }
}