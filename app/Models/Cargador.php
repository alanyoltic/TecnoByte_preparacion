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
        'serie', 'serie_fabricante',
        'marca',
        'voltaje',
        'amperaje',
        'punta',
        'especificaciones_tecnicas',
        'estatus',
        'area',
        'lote_id',
        'compra_inventario_id',
        'equipo_id',
        'costo',
    ];

    protected $casts = [
        'especificaciones_tecnicas' => 'array',
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

    /**
     * Genera una serie interna obligatoria y única para TecnoByte.
     * Formato: CARG-{ABREV}{FECHA}-{SECUENCIAL}
     */
    public static function generarSerieInterna(?string $abrevProveedor = null, $fecha = null): string
    {
        $abrev = strtoupper(trim($abrevProveedor ?: 'TB'));
        $abrev = preg_replace('/[^A-Z0-9]/', '', $abrev) ?: 'TB';

        if ($fecha instanceof \DateTimeInterface) {
            $fechaStr = $fecha->format('dmY');
        } elseif (is_string($fecha) && !empty($fecha)) {
            $fechaStr = date('dmY', strtotime($fecha));
        } else {
            $fechaStr = date('dmY');
        }

        $prefijo = "CARG-{$abrev}{$fechaStr}-";

        $secuencial = 1;
        do {
            $candidato = $prefijo . $secuencial;
            $existe = static::where('serie', $candidato)->exists();
            if ($existe) {
                $secuencial++;
            }
        } while ($existe);

        return $candidato;
    }
}
