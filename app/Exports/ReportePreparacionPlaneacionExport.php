<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportePreparacionPlaneacionExport implements WithMultipleSheets
{
    public function __construct()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
    }

    public function sheets(): array
    {
        return [
            new EquiposActivosSheet,
            new LotesPendientesSheet,
            new ResumenEjecutivoSheet,
        ];
    }
}
