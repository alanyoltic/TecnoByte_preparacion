<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\PuntoTecnico;

class PuntosTecnicosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    protected Collection $puntos;

    public function __construct(Collection $puntos)
    {
        $this->puntos = $puntos;
    }

    public function collection()
    {
        return $this->puntos;
    }

    public function headings(): array
    {
        return [
            'Fecha Trabajo',
            'Técnico',
            'No. Serie',
            'Modelo',
            'Clasificación',
            'Puntos Base',
            'Porcentaje Aplicado',
            'Tipo de Trabajo',
            'Puntos Obtenidos',
            'Ajuste Manual',
            'Motivo Ajuste',
            'Total Final'
        ];
    }

    public function map($punto): array
    {
        $equipo = $punto->asignacionEquipo->equipo ?? null;
        
        $roles = PuntoTecnico::labelsRol();
        $rolLabel = $roles[$punto->rol_en_equipo] ?? $punto->rol_en_equipo;

        return [
            optional($punto->created_at)->format('Y-m-d H:i:s'),
            $punto->tecnico->nombre ?? 'N/D',
            $equipo->numero_serie ?? 'N/D',
            trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? 'N/D')),
            $punto->clasificacionPuntos->nombre ?? 'N/D',
            $punto->puntos_base,
            $punto->porcentaje_aplicado . '%',
            $rolLabel,
            $punto->puntos_final,
            $punto->ajuste_manual,
            $punto->motivo_ajuste ?? '',
            (float)$punto->puntos_final + (float)$punto->ajuste_manual,
        ];
    }
}
