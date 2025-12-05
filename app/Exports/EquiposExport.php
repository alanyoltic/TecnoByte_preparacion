<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EquiposExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected Collection $equipos;

    public function __construct(Collection $equipos)
    {
        $this->equipos = $equipos;
    }

    public function collection()
    {
        return $this->equipos;
    }

    public function headings(): array
    {
        return [
            'ID', 'Lote', 'Proveedor', 'Serie', 'Marca', 'Modelo', 'Tipo equipo',
            'Área / tienda', 'Sistema operativo',
            'Procesador modelo', 'Procesador frecuencia', 'Procesador generación', 'Procesador núcleos',
            'RAM total', 'RAM tipo', 'RAM es soldada', 'RAM slots totales', 'RAM expansión máxima',
            'Pantalla pulgadas', 'Pantalla resolución', 'Pantalla tipo', 'Pantalla touch',
            'Almacenamiento 1 capacidad', 'Almacenamiento 1 tipo',
            'Almacenamiento 2 capacidad', 'Almacenamiento 2 tipo',
            'Gráfica integrada', 'Gráfica dedicada', 'VRAM',
            'Batería salud %', 'Batería cantidad',
            'Teclado idioma', 'Estatus general', 'Notas',
            'USB 2.0', 'USB 3.0', 'USB 3.1', 'USB 3.2', 'USB-C',
            'HDMI', 'Mini HDMI', 'VGA', 'DVI', 'DisplayPort', 'Mini DP',
            'Lectores SD', 'SmartCard', 'eSATA', 'SIM',
            'Registrado por', 'Correo usuario',
            'Fecha creado', 'Última actualización',
        ];
    }

    public function map($e): array
    {
        $loteModelo = $e->loteModelo;
        $lote       = $loteModelo->lote ?? null;
        $proveedor  = $lote->proveedor ?? null;
        $usuario    = $e->registradoPor ?? null;

        return [
            $e->id,
            $lote->nombre_lote ?? '',
            $proveedor->nombre_empresa ?? '',
            $e->numero_serie,
            $e->marca,
            $e->modelo,
            $e->tipo_equipo,
            $e->area_tienda,
            $e->sistema_operativo,

            $e->procesador_modelo,
            $e->procesador_frecuencia,
            $e->procesador_generacion,
            $e->procesador_nucleos,

            $e->ram_total,
            $e->ram_tipo,
            $e->ram_es_soldada ? "Sí" : "No",
            $e->ram_slots_totales,
            $e->ram_expansion_max,

            $e->pantalla_pulgadas,
            $e->pantalla_resolucion,
            $e->pantalla_tipo,
            $e->pantalla_es_touch ? "Sí" : "No",

            $e->almacenamiento_principal_capacidad,
            $e->almacenamiento_principal_tipo,
            $e->almacenamiento_secundario_capacidad,
            $e->almacenamiento_secundario_tipo,

            $e->grafica_integrada_modelo,
            $e->grafica_dedicada_modelo,
            $e->grafica_dedicada_vram,

            $e->bateria_salud_percent,
            $e->bateria_cantidad,

            $e->teclado_idioma,

            $e->estatus_general,
            $e->notas_generales,

            $e->puertos_usb_2,
            $e->puertos_usb_30,
            $e->puertos_usb_31,
            $e->puertos_usb_32,
            $e->puertos_usb_c,

            $e->puertos_hdmi,
            $e->puertos_mini_hdmi,
            $e->puertos_vga,
            $e->puertos_dvi,
            $e->puertos_displayport,
            $e->puertos_mini_dp,

            $e->lectores_sd,
            $e->lectores_sc,
            $e->lectores_esata,
            $e->lectores_sim,

            $usuario?->nombre ?? '',
            $usuario?->email ?? '',

            optional($e->created_at)->format('Y-m-d H:i:s'),
            optional($e->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $headerRange = 'A1:BE1'; // Ajusta si hay más columnas

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
                        'startColor' => ['rgb' => 'FF9521']
                    ],
                ]);

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(28);
            }
        ];
    }
}
