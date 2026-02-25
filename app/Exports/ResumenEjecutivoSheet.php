<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ResumenEjecutivoSheet implements FromCollection, WithStrictNullComparison
{
    public function collection()
    {
        $rows = new Collection();

        $columnas = 7;

        $pad = function ($array) use ($columnas) {
            return array_values(array_pad($array, $columnas, ''));
        };

        /*
        |--------------------------------------------------------------------------
        | TABLA 1 - TOTAL POR MARCA Y MODELO
        |--------------------------------------------------------------------------
        */

        $rows->push($pad(['TOTAL EQUIPOS POR MARCA Y MODELO']));
        $rows->push($pad([]));

        $conteo = DB::table('equipos')
            ->whereNull('deleted_at')
            ->selectRaw('
                UPPER(TRIM(marca)) as marca,
                UPPER(TRIM(modelo)) as modelo,
                COUNT(id) as total
            ')
            ->groupBy(
                DB::raw('UPPER(TRIM(marca))'),
                DB::raw('UPPER(TRIM(modelo))')
            )
            ->orderBy('marca')
            ->orderBy('modelo')
            ->get();

        $rows->push($pad(['Marca','Modelo','Total']));

        foreach ($conteo as $c) {
            $rows->push($pad([
                (string) $c->marca,
                (string) $c->modelo,
                (int) $c->total
            ]));
        }

        $rows->push($pad([]));
        $rows->push($pad([]));

/*
|--------------------------------------------------------------------------
| TABLA 2 - ANALISIS POR LOTE Y MODELO
|--------------------------------------------------------------------------
*/

$rows->push($pad(['ANALISIS POR LOTE Y MODELO']));
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
    ->orderBy('l.nombre_lote')
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

    $pendientes = (int)$a->cantidad_recibida - (int)$a->creados;

    $porcentaje = $a->cantidad_recibida > 0
        ? round(($a->creados / $a->cantidad_recibida) * 100, 2)
        : 0;

    $rows->push($pad([
        (string)$a->nombre_lote,
        (string)$a->marca,
        (string)$a->modelo,
        (int)$a->cantidad_recibida,
        (int)$a->creados,
        (int)$pendientes,
        (float)$porcentaje
    ]));
}

$rows->push($pad([]));
$rows->push($pad([]));

/*
|--------------------------------------------------------------------------
| TABLA 2 - ANALISIS POR LOTE Y MODELO (AGRUPADO VISUALMENTE)
|--------------------------------------------------------------------------
*/

$rows->push($pad(['ANALISIS POR LOTE Y MODELO']));
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
    ->orderBy('l.nombre_lote')
    ->orderBy('lmr.marca')
    ->orderBy('lmr.modelo')
    ->get();

$loteActual = null;

foreach ($avance as $a) {

    // Si cambia el lote, imprimimos encabezado
    if ($loteActual !== $a->nombre_lote) {

        $loteActual = $a->nombre_lote;

        $rows->push($pad([]));
        $rows->push($pad(["LOTE: {$loteActual}"]));
        $rows->push($pad([
            'Marca',
            'Modelo',
            'Total Lote',
            'Creados',
            'Pendientes',
            '% Avance'
        ]));
    }

    $pendientes = (int)$a->cantidad_recibida - (int)$a->creados;

    $porcentaje = $a->cantidad_recibida > 0
        ? round(($a->creados / $a->cantidad_recibida) * 100, 2)
        : 0;

    $rows->push($pad([
        (string)$a->marca,
        (string)$a->modelo,
        (int)$a->cantidad_recibida,
        (int)$a->creados,
        (int)$pendientes,
        (float)$porcentaje
    ]));
}
/*
|--------------------------------------------------------------------------
| TABLA 3 - RESUMEN GLOBAL DEL SISTEMA
|--------------------------------------------------------------------------
*/

$rows->push($pad(['RESUMEN GLOBAL DEL SISTEMA']));
$rows->push($pad([]));

// Total físico recibido
$totalRecibido = (int) DB::table('lote_modelos_recibidos')
    ->sum('cantidad_recibida');

// Total creados (ya existen como equipo)
$totalCreados = (int) DB::table('equipos')
    ->whereNull('deleted_at')
    ->count();

// Pendientes (aún no creados)
$pendientes = $totalRecibido - $totalCreados;

// Porcentajes
$porcentajePreparado = $totalRecibido > 0
    ? round(($totalCreados / $totalRecibido) * 100, 2)
    : 0;

$porcentajePendiente = $totalRecibido > 0
    ? round(($pendientes / $totalRecibido) * 100, 2)
    : 0;

$rows->push($pad([
    'Total Recibido',
    'Total Creados',
    'Pendientes',
    '% Preparado',
    '% Pendiente'
]));

$rows->push($pad([
    $totalRecibido,
    $totalCreados,
    $pendientes,
    $porcentajePreparado,
    $porcentajePendiente
]));

        return $rows;
    }
}