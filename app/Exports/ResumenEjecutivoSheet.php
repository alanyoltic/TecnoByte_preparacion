<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ResumenEjecutivoSheet implements FromCollection
{
    public function collection()
    {
        $rows = new Collection();

        // Definimos número fijo de columnas (la tabla más grande usa 7)
        $columnas = 7;

        $pad = function ($array) use ($columnas) {
            return array_pad($array, $columnas, '');
        };

        /*
        |--------------------------------------------------------------------------
        | TABLA 1 - TOTAL EQUIPOS POR MARCA Y MODELO (NORMALIZADO)
        |--------------------------------------------------------------------------
        */

        $rows->push($pad(['===== TOTAL EQUIPOS POR MARCA Y MODELO =====']));
        $rows->push($pad([]));

        $conteoModelos = DB::table('equipos')
            ->whereNull('deleted_at')
            ->selectRaw('
                UPPER(TRIM(marca)) as marca_normalizada,
                UPPER(TRIM(modelo)) as modelo_normalizado,
                COUNT(id) as total
            ')
            ->groupBy(
                DB::raw('UPPER(TRIM(marca))'),
                DB::raw('UPPER(TRIM(modelo))')
            )
            ->orderBy('marca_normalizada')
            ->orderBy('modelo_normalizado')
            ->get();

        $rows->push($pad(['Marca','Modelo','Total']));

        foreach ($conteoModelos as $c) {
            $rows->push($pad([
                $c->marca_normalizada,
                $c->modelo_normalizado,
                $c->total
            ]));
        }

        $rows->push($pad([]));
        $rows->push($pad([]));

        /*
        |--------------------------------------------------------------------------
        | TABLA 2 - AVANCE POR LOTE Y MODELO
        |--------------------------------------------------------------------------
        */

        $rows->push($pad(['===== AVANCE POR LOTE Y MODELO =====']));
        $rows->push($pad([]));

        $avance = DB::table('lote_modelos_recibidos as lmr')
            ->join('lotes as l', 'lmr.lote_id', '=', 'l.id')
            ->leftJoin('equipos as e', function ($join) {
                $join->on('e.lote_modelo_id', '=', 'lmr.id')
                     ->whereNull('e.deleted_at');
            })
            ->selectRaw('
                l.nombre_lote,
                lmr.marca,
                lmr.modelo,
                lmr.cantidad_recibida,
                COUNT(e.id) as creados
            ')
            ->groupBy(
                'lmr.id',
                'l.nombre_lote',
                'lmr.marca',
                'lmr.modelo',
                'lmr.cantidad_recibida'
            )
            ->get();

        $rows->push($pad([
            'Lote',
            'Marca',
            'Modelo',
            'Total Lote',
            'Creados',
            'Pendientes',
            '% Avance'
        ]));

        foreach ($avance as $a) {

            $pendientes = $a->cantidad_recibida - $a->creados;

            $porcentaje = $a->cantidad_recibida > 0
                ? round(($a->creados / $a->cantidad_recibida) * 100, 2)
                : 0;

            $rows->push($pad([
                $a->nombre_lote,
                $a->marca,
                $a->modelo,
                $a->cantidad_recibida,
                $a->creados,
                $pendientes,
                $porcentaje
            ]));
        }

        $rows->push($pad([]));
        $rows->push($pad([]));

        /*
        |--------------------------------------------------------------------------
        | TABLA 3 - RESUMEN GLOBAL
        |--------------------------------------------------------------------------
        */

        $rows->push($pad(['===== RESUMEN GLOBAL =====']));
        $rows->push($pad([]));

        $totalRecibido = DB::table('lote_modelos_recibidos')
            ->sum('cantidad_recibida');

        $totalCreados = DB::table('equipos')
            ->whereNull('deleted_at')
            ->count();

        $pendientes = $totalRecibido - $totalCreados;

        $porcentaje = $totalRecibido > 0
            ? round(($totalCreados / $totalRecibido) * 100, 2)
            : 0;

        $rows->push($pad([
            'Total Recibido',
            'Total Creados',
            'Pendientes',
            '% Global'
        ]));

        $rows->push($pad([
            $totalRecibido,
            $totalCreados,
            $pendientes,
            $porcentaje
        ]));

        return $rows;
    }
}