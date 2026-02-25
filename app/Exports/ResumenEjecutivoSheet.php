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

        /*
        |--------------------------------------------------------------------------
        | TABLA 1 - FINALIZADOS POR MODELO
        |--------------------------------------------------------------------------
        */

/*
|--------------------------------------------------------------------------
| TABLA 1 - TOTAL POR MARCA Y MODELO (SIN IMPORTAR ESTATUS)
|--------------------------------------------------------------------------
*/

$rows->push(['===== TOTAL EQUIPOS POR MARCA Y MODELO =====']);
$rows->push([]);

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

$rows->push(['Marca','Modelo','Total']);

foreach ($conteoModelos as $c) {
    $rows->push([
        $c->marca_normalizada,
        $c->modelo_normalizado,
        $c->total
    ]);
}

$rows->push([]);
$rows->push([]);

        /*
        |--------------------------------------------------------------------------
        | TABLA 2 - AVANCE POR LOTE / MODELO
        |--------------------------------------------------------------------------
        */

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

        $rows->push(['Lote','Marca','Modelo','Total Lote','Creados','Pendientes','% Avance']);

        foreach ($avance as $a) {

            $pendientes = $a->cantidad_recibida - $a->creados;
            $porcentaje = $a->cantidad_recibida > 0
                ? round(($a->creados / $a->cantidad_recibida) * 100, 2)
                : 0;

            $rows->push([
                $a->nombre_lote,
                $a->marca,
                $a->modelo,
                $a->cantidad_recibida,
                $a->creados,
                $pendientes,
                $porcentaje
            ]);
        }

        $rows->push([]);
        $rows->push([]);
        $rows->push(['===== RESUMEN GLOBAL =====']);
        $rows->push([]);

        /*
        |--------------------------------------------------------------------------
        | TABLA 3 - RESUMEN GLOBAL
        |--------------------------------------------------------------------------
        */

        $totalRecibido = DB::table('lote_modelos_recibidos')
            ->sum('cantidad_recibida');

        $totalCreados = DB::table('equipos')
            ->whereNull('deleted_at')
            ->count();

        $pendientes = $totalRecibido - $totalCreados;

        $porcentaje = $totalRecibido > 0
            ? round(($totalCreados / $totalRecibido) * 100, 2)
            : 0;

        $rows->push(['Total Recibido','Total Creados','Pendientes','% Global']);

        $rows->push([
            $totalRecibido,
            $totalCreados,
            $pendientes,
            $porcentaje
        ]);

        return $rows;
    }
}