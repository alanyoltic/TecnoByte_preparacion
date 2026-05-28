<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SolicitudesPiezasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected Collection $solicitudes;

    public function __construct(Collection $solicitudes)
    {
        $this->solicitudes = $solicitudes;
    }

    public function collection()
    {
        return $this->solicitudes;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Pieza',
            'Cantidad',
            'Modelo',
            'Marca',
            'Técnico',
            'Num. de serie',
        ];
    }

    public function map($s): array
    {
        $equipo = $s->equipo ?? $s->asignacionEquipo?->equipo;

        // Pieza preferente
        $piezaTxt = '';
        if ($s->catalogoPieza) {
            $piezaTxt = ($s->catalogoPieza->categoria ? $s->catalogoPieza->categoria . ' — ' : '') . ($s->catalogoPieza->nombre ?? '');
        } else {
            if (!empty($s->categoria_solicitada_texto) || !empty($s->detalle_solicitado_texto)) {
                $piezaTxt = trim(($s->categoria_solicitada_texto ? $s->categoria_solicitada_texto . ' — ' : '') . ($s->detalle_solicitado_texto ?? ''));
            } else {
                $piezaTxt = $s->descripcion_libre ?? '';
            }
        }

        $tecnico = $s->solicitadoPor?->nombre . ' ' . $s->solicitadoPor?->apellido_paterno;

        return [
            optional($s->created_at)->format('Y-m-d H:i:s'),
            $piezaTxt,
            $s->cantidad ?? 1,
            $equipo?->modelo ?? '',
            $equipo?->marca ?? '',
            trim($tecnico),
            $equipo?->numero_serie ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $headerRange = 'A1:G1';
                $sheet->getDelegate()->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                        'wrapText' => true
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '2B6CB0']
                    ],
                ]);

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(20);
            }
        ];
    }
}
