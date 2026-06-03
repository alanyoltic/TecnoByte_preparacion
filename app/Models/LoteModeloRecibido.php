<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoteModeloRecibido extends Model
{
    protected $table = 'lote_modelos_recibidos';

    protected $fillable = [
        'lote_id',
        'catalogo_equipo_id',
        'marca',
        'modelo',
        'cantidad_recibida',
        'valor_unitario',
        'clasificacion_puntos_id',
    ];

    public function catalogoEquipo()
    {
        return $this->belongsTo(CatalogoEquipo::class, 'catalogo_equipo_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'lote_modelo_id');
    }

    public function clasificacionPuntos()
    {
        return $this->belongsTo(\App\Models\ClasificacionPuntos::class, 'clasificacion_puntos_id');
    }

    /**
     * Propaga la clasificación a todos los equipos de esta línea
     * que aún no tienen puntos registrados (protege los ya completados).
     */
    public function propagarClasificacion(): void
    {
        $protegidos = \DB::table('puntos_tecnicos')
            ->join('asignacion_equipos', 'puntos_tecnicos.asignacion_equipo_id', '=', 'asignacion_equipos.id')
            ->join('equipos', 'asignacion_equipos.equipo_id', '=', 'equipos.id')
            ->where('equipos.lote_modelo_id', $this->id)
            ->pluck('asignacion_equipos.equipo_id')
            ->unique()
            ->values();

        \DB::table('equipos')
            ->where('lote_modelo_id', $this->id)
            ->whereNotIn('id', $protegidos)
            ->update(['clasificacion_puntos_id' => $this->clasificacion_puntos_id]);
    }
}
