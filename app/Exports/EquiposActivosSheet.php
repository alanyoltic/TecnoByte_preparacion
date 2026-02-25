<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EquiposActivosSheet implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('equipos')
            ->whereNull('deleted_at')
            ->get();
    }

    public function headings(): array
    {
        return array_keys(
            (array) DB::table('equipos')->first()
        );
    }
}