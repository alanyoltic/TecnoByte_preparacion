<?php

namespace App\Models;

use App\Enums\CargadorEstatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cargadores';

    protected $fillable = [
        'serie',
        'marca',
        'voltaje',
        'amperaje',
        'punta',
        'estatus',
        'area',
        'lote_id',
        'compra_inventario_id',
        'equipo_id',
        'costo',
    ];

    protected $casts = [
        'estatus' => CargadorEstatus::class,
        'costo' => 'decimal:2',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function compraInventario(): BelongsTo
    {
        return $this->belongsTo(CompraInventario::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(CargadorAuditoria::class);
    }
}
