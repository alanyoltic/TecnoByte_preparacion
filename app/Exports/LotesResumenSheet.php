<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LotesResumenSheet implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('lote_modelos_recibidos as lmr')
            ->join('lotes as l', 'lmr.lote_id', '=', 'l.id')
            ->leftJoin('equipos as e', function ($join) {
                $join->on('e.lote_modelo_id', '=', 'lmr.id')
                    ->whereNull('e.deleted_at');
            })
            ->selectRaw('
                l.nombre_lote,
                l.fecha_llegada,
                lmr.marca,
                lmr.modelo,
                lmr.cantidad_recibida as total_recibido,
                COUNT(e.id) as total_creados,
                (lmr.cantidad_recibida - COUNT(e.id)) as faltantes,
                ROUND((COUNT(e.id) / lmr.cantidad_recibida) * 100, 2) as porcentaje_avance
            ')
            ->groupBy('lmr.id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Lote',
            'Fecha Llegada',
            'Marca',
            'Modelo',
            'Total Recibido',
            'Total Creados',
            'Faltantes',
            '% Avance',
        ];
    }
}
