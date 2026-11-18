<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargadorAuditoria extends Model
{
    protected $table = 'cargador_auditorias';

    protected $fillable = [
        'cargador_id',
        'user_id',
        'accion',
        'detalles',
    ];

    public function cargador(): BelongsTo
    {
        return $this->belongsTo(Cargador::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
