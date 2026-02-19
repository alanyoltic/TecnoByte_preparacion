<?php

namespace App\Models;

// ¡Asegúrate de que esta importación esté aquí!
use App\Models\Roles; 
use App\Models\Puesto;
use App\Models\Departamento;
use Illuminate\Contracts\Auth\MustVerifyEmail; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Asegúrate de que 'implements MustVerifyEmail' esté aquí
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;



    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }


    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }


    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'email',
        'password',
        'role_id',
        'is_active',
        'foto_perfil',
        'departamento_id',
        'puesto_id',
        'sucursal_id',

    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

 
    public function role()
    {

        return $this->belongsTo(Roles::class, 'role_id');
    }





public function tienePermiso(string $slug): bool
{
    // CEO = todo
    if (optional($this->role)->slug === 'ceo') {
        return true;
    }

    // Por rol
    $porRol = \DB::table('rol_permiso')
        ->join('permisos', 'permisos.id', '=', 'rol_permiso.permiso_id')
        ->where('rol_permiso.rol_id', $this->role_id)
        ->where('permisos.slug', $slug)
        ->exists();

    if ($porRol) return true;

    // Por usuario (override)
    return \DB::table('usuario_permiso')
        ->join('permisos', 'permisos.id', '=', 'usuario_permiso.permiso_id')
        ->where('usuario_permiso.user_id', $this->id)
        ->where('permisos.slug', $slug)
        ->exists();
}



    public function isAdminCeo()
    {
        return in_array($this->role?->slug, ['admin', 'ceo']);
    }


        public function getNombreInicialAttribute()
    {
        $nombre = $this->nombre ?? '';
        $apellido = $this->apellido_paterno ?? '';

        $inicial = $apellido ? strtoupper(mb_substr($apellido, 0, 1)) . '.' : '';

        return trim("$nombre $inicial");
    }

    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = ucwords(mb_strtolower($value));
    }

    public function setApellidoPaternoAttribute($value)
    {
        $this->attributes['apellido_paterno'] = ucwords(mb_strtolower($value));
    }

    public function setApellidoMaternoAttribute($value)
    {
        $this->attributes['apellido_materno'] = ucwords(mb_strtolower($value));
    }


    




}
