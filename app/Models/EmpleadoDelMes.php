<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoDelMes extends Model
{
    protected $table = 'empleados_del_mes';

    protected $fillable = [
        'month','user_id','titulo','mensaje','is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
