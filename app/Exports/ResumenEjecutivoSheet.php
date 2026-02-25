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

        $rows->push(['===== FINALIZADOS POR MODELO =====']);
        $rows->push([]);

        $finalizados = DB::table('equipos')
            ->whereNull('deleted_at')
            ->selectRaw('
                marca,
                modelo,
                estado_operativo,
                estatus_general,
                COUNT(id) as total
            ')
            ->groupBy('marca','modelo','estado_operativo','estatus_general')
            ->get();

        $rows->push(['Marca','Modelo','Estado Operativo','Estatus General','Total']);

        foreach ($finalizados as $f) {
            $rows->push([
                $f->marca,
                $f->modelo,
                $f->estado_operativo,
                $f->estatus_general,
                $f->total
            ]);
        }

        $rows->push([]);
        $rows->push([]);
        $rows->push(['===== AVANCE POR LOTE Y MODELO =====']);
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