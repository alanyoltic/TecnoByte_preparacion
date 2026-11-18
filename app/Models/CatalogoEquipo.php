<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoEquipo extends Model
{
    protected $fillable = [
        'marca',
        'modelo',
        'tipo_equipo',
        'clasificacion_puntos_id',
        'activo',
    ];

    public function clasificacionPuntos()
    {
        return $this->belongsTo(ClasificacionPuntos::class, 'clasificacion_puntos_id');
    }

    public function lotesVinculados()
    {
        return $this->hasMany(LoteModeloRecibido::class, 'catalogo_equipo_id');
    }

    public function equiposVinculados()
    {
        return $this->hasMany(Equipo::class, 'catalogo_equipo_id');
    }

    /**
     * Propaga la clasificación a los LoteModeloRecibido y a los Equipos
     * vinculados que NO hayan sido terminados con puntos registrados.
     */
    public function propagarClasificacion(): void
    {
        if (! $this->clasificacion_puntos_id) {
            return;
        }

        // 1. Actualizar los LoteModeloRecibido de este modelo del catálogo
        LoteModeloRecibido::where('catalogo_equipo_id', $this->id)
            ->update(['clasificacion_puntos_id' => $this->clasificacion_puntos_id]);

        // 2. Proteger equipos que ya tienen puntos técnicos cobrados/registrados
        $protegidos = \DB::table('puntos_tecnicos')
            ->join('asignacion_equipos', 'puntos_tecnicos.asignacion_equipo_id', '=', 'asignacion_equipos.id')
            ->pluck('asignacion_equipos.equipo_id')
            ->unique()
            ->values();

        // 3. Actualizar equipos activos no protegidos
        Equipo::where(function ($q) {
                $q->where('catalogo_equipo_id', $this->id)
                  ->orWhereIn('lote_modelo_id', function ($sub) {
                      $sub->select('id')->from('lote_modelos_recibidos')->where('catalogo_equipo_id', $this->id);
                  });
            })
            ->whereNotIn('id', $protegidos)
            ->update(['clasificacion_puntos_id' => $this->clasificacion_puntos_id]);
    }
}
