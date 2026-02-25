<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportePreparacionPlaneacionExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new EquiposActivosSheet(),
            new LotesPendientesSheet(),
        ];
    }
}