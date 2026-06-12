<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LotesPendientesSheet implements FromCollection, WithHeadings
{
    public function collection()
    {
        $rows = new Collection;

        $lotes = DB::table('lote_modelos_recibidos as lmr')
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
        lmr.cantidad_recibida,
        COUNT(e.id) as total_creados
    ')
            ->groupBy(
                'lmr.id',
                'l.nombre_lote',
                'l.fecha_llegada',
                'lmr.marca',
                'lmr.modelo',
                'lmr.cantidad_recibida'
            )
            ->get();

        foreach ($lotes as $lote) {

            $pendientes = $lote->cantidad_recibida - $lote->total_creados;

            for ($i = 0; $i < $pendientes; $i++) {

                $rows->push([
                    'lote' => $lote->nombre_lote,
                    'fecha_llegada' => $lote->fecha_llegada,
                    'marca' => $lote->marca,
                    'modelo' => $lote->modelo,
                    'estatus' => 'PENDIENTE POR CREAR',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Lote',
            'Fecha Llegada',
            'Marca',
            'Modelo',
            'Estatus',
        ];
    }
}
