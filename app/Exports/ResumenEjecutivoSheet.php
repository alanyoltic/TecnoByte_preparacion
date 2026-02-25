<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ResumenEjecutivoSheet implements FromCollection, WithStrictNullComparison, WithEvents
{
    protected $rowPositions = [];

    public function collection()
    {
        $rows = new Collection();
        $columnas = 7;

        $pad = function ($array) use ($columnas) {
            return array_values(array_pad($array, $columnas, ''));
        };

        $currentRow = 1;

        /*
        |--------------------------------------------------------------------------
        | TABLA 1
        |--------------------------------------------------------------------------
        */

        $this->rowPositions['tabla1_title'] = $currentRow;
        $rows->push($pad(['TOTAL EQUIPOS POR MARCA Y MODELO']));
        $currentRow++;

        $rows->push($pad([]));
        $currentRow++;

        $this->rowPositions['tabla1_header'] = $currentRow;
        $rows->push($pad(['Marca','Modelo','Total']));
        $currentRow++;

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
            ->get();

        foreach ($conteo as $c) {
            $rows->push($pad([$c->marca, $c->modelo, $c->total]));
            $currentRow++;
        }

        $rows->push($pad([]));
        $currentRow++;

        /*
        |--------------------------------------------------------------------------
        | TABLA 2
        |--------------------------------------------------------------------------
        */

        $this->rowPositions['tabla2_title'] = $currentRow;
        $rows->push($pad(['ANALISIS POR LOTE Y MODELO']));
        $currentRow++;

        $rows->push($pad([]));
        $currentRow++;

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

        $loteActual = null;

        foreach ($avance as $a) {

            if ($loteActual !== $a->nombre_lote) {

                $loteActual = $a->nombre_lote;

                $rows->push($pad([]));
                $currentRow++;

                $rows->push($pad(["LOTE: {$loteActual}"]));
                $this->rowPositions['lote_titles'][] = $currentRow;
                $currentRow++;

                $rows->push($pad([
                    'Marca',
                    'Modelo',
                    'Total Lote',
                    'Creados',
                    'Pendientes',
                    '% Avance'
                ]));
                $this->rowPositions['tabla2_headers'][] = $currentRow;
                $currentRow++;
            }

            $pendientes = $a->cantidad_recibida - $a->creados;
            $porcentaje = $a->cantidad_recibida > 0
                ? round(($a->creados / $a->cantidad_recibida) * 100, 2)
                : 0;

            $rows->push($pad([
                $a->marca,
                $a->modelo,
                $a->cantidad_recibida,
                $a->creados,
                $pendientes,
                $porcentaje
            ]));

            $currentRow++;
        }

        $rows->push($pad([]));
        $currentRow++;

        /*
        |--------------------------------------------------------------------------
        | TABLA 3
        |--------------------------------------------------------------------------
        */

        $this->rowPositions['tabla3_title'] = $currentRow;
        $rows->push($pad(['CONSOLIDADO GLOBAL POR MODELO']));
        $currentRow++;

        $rows->push($pad([]));
        $currentRow++;

        $this->rowPositions['tabla3_header'] = $currentRow;
        $rows->push($pad([
            'Marca',
            'Modelo',
            'Total Recibido',
            'Finalizados',
            'Libres',
            '% Avance'
        ]));
        $currentRow++;

        $modelosGlobal = DB::table('lote_modelos_recibidos as lmr')
            ->leftJoin('equipos as e', function ($join) {
                $join->on('e.lote_modelo_id', '=', 'lmr.id')
                     ->whereNull('e.deleted_at');
            })
            ->selectRaw('
                UPPER(TRIM(lmr.marca)) as marca_normalizada,
                UPPER(TRIM(lmr.modelo)) as modelo_normalizado,
                SUM(lmr.cantidad_recibida) as total_recibido,
                COUNT(e.id) as total_finalizados
            ')
            ->groupBy(
                DB::raw('UPPER(TRIM(lmr.marca))'),
                DB::raw('UPPER(TRIM(lmr.modelo))')
            )
            ->get();

        $totalGlobalLibres = 0;

        foreach ($modelosGlobal as $m) {

            $libres = $m->total_recibido - $m->total_finalizados;
            $porcentaje = $m->total_recibido > 0
                ? round(($m->total_finalizados / $m->total_recibido) * 100, 2)
                : 0;

            $totalGlobalLibres += $libres;

            $rows->push($pad([
                $m->marca_normalizada,
                $m->modelo_normalizado,
                $m->total_recibido,
                $m->total_finalizados,
                $libres,
                $porcentaje
            ]));

            $currentRow++;
        }

        $rows->push($pad([]));
        $currentRow++;

        $this->rowPositions['total_libres'] = $currentRow;
        $rows->push($pad(['TOTAL EQUIPOS LIBRES', '', '', '', $totalGlobalLibres]));

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $azul = '1E3A8A';
                $azulClaro = 'DBEAFE';

                foreach(range('A','G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Títulos principales
                foreach (['tabla1_title','tabla2_title','tabla3_title'] as $key) {
                    if(isset($this->rowPositions[$key])) {
                        $sheet->getStyle("A{$this->rowPositions[$key]}:G{$this->rowPositions[$key]}")
                            ->applyFromArray([
                                'font' => ['bold' => true, 'size' => 14, 'color'=>['rgb'=>'FFFFFF']],
                                'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>$azul]]
                            ]);
                    }
                }

                // Headers
                foreach (['tabla1_header','tabla3_header'] as $key) {
                    if(isset($this->rowPositions[$key])) {
                        $sheet->getStyle("A{$this->rowPositions[$key]}:G{$this->rowPositions[$key]}")
                            ->applyFromArray([
                                'font' => ['bold'=>true],
                                'fill'=>['fillType'=>'solid','startColor'=>['rgb'=>$azulClaro]]
                            ]);
                    }
                }

                if(isset($this->rowPositions['tabla2_headers'])){
                    foreach($this->rowPositions['tabla2_headers'] as $row){
                        $sheet->getStyle("A{$row}:G{$row}")
                            ->applyFromArray([
                                'font'=>['bold'=>true],
                                'fill'=>['fillType'=>'solid','startColor'=>['rgb'=>$azulClaro]]
                            ]);
                    }
                }

                // Total libres resaltado
                if(isset($this->rowPositions['total_libres'])){
                    $sheet->getStyle("A{$this->rowPositions['total_libres']}:G{$this->rowPositions['total_libres']}")
                        ->applyFromArray([
                            'font'=>['bold'=>true,'size'=>12],
                            'fill'=>['fillType'=>'solid','startColor'=>['rgb'=>'FEF3C7']]
                        ]);
                }

            }
        ];
    }
}