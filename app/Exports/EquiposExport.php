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
        'Fecha',
        'Responsable/Técnico',
        'Proveedor',
        'Estatus',
        'Tipo de equipo',
        'Marca',
        'Modelo',
        'Num. de serie',
        'Área',
        'Sistema operativo',

        'Modelo proce',
        'Frecuencia proce',
        'Generación proce',
        'Núcleos',

        'RAM total (equipo preparado)',
        'Tipo de RAM',
        'RAM max expansión',
        'Slots de RAM disponibles',
        'Cantidad de slots RAM',
        'Cantidad de RAM soldada',


        'Tipo de monitor',
        'Es touch',
        'Resolución',

        'Almacenamiento principal',
        'Tipo alm principal',
        'Almacenamiento secundario',
        'Tipo alm secundario',

        'SLOTS ALMACENAMIENTO [SSD]',
        'SLOTS ALMACENAMIENTO [M.2]',
        'SLOTS ALMACENAMIENTO [M.2 MICRO]',
        'SLOTS ALMACENAMIENTO [HDD]',
        'SLOTS ALMACENAMIENTO [MSATA]',

        '¿TIENE PUERTO ETHERNET?',
        '¿ES GIGABYTE?',

        'Puertos de conectividad',
        'DISPOSITIVOS DE ENTRADA',
        'IDIOMA DEL TECLADO',

        '[HDMI]',
        '[MINI HDMI]',
        '[DVI]',
        'DISPLAYPORT',
        '[MINI DP]',
        '[USB 2.0]',
        '[USB 3.0]',
        '[USB 3.1]',
        '[USB 3.2]',
        '[USB TIPO C]',

        'LECTORES [SD]',
        'LECTORES [SC]',
        'LECTORES [ESATA]',
        'LECTORES [SIM]',
        // opcionales que sí existen en tu tabla (por si los quieres):
        // 'LECTORES [MICROSD]',
        // 'LECTORES OTRO',

        '¿TIENE BATERIA?',
        'CANTIDAD DE BATERIAS',
        'CONDICION DE LA BATERIA',

        'Gráfica integrada',
        'Gráfica dedicada',
        'VRAM',

        'DETALLES ESTETICOS',
        'DETALLES DE FUNCIONAMIENTO',

        'ENTRADAS DE MONITOR [HDMI]',
        'ENTRADAS DE MONITOR [VGA]',
        'ENTRADAS DE MONITOR [DVI]',
        'ENTRADAS DE MONITOR [DISPLAYPORT]',
        'ENTRADAS DE MONITOR [USB 2.0]',
        'ENTRADAS DE MONITOR [USB 3.0]',
        'ENTRADAS DE MONITOR [USB 3.1]',
        'ENTRADAS DE MONITOR [USB 3.2]',
        'ENTRADAS DE MONITOR [USB TIPO C]',

        'DETALLES DE FUNCIONAMIENTO MONITOR',
        'DETALLES ESTETICOS MONITOR',

        'LOTE QUE PERTENECE',
    ];
}


public function map($e): array
{
    $loteModelo = $e->loteModelo;
    $lote       = $loteModelo->lote ?? null;
    $proveedor  = $lote->proveedor ?? null;
    $usuario    = $e->registradoPor ?? null;

    // ===== GPU (equipo_gpus) =====
    $gpuInt = $e->gpus?->firstWhere('tipo', 'INTEGRADA');
    $gpuDed = $e->gpus?->firstWhere('tipo', 'DEDICADA');

    $gpuIntegradaTxt = trim(($gpuInt->marca ?? '') . ' ' . ($gpuInt->modelo ?? ''));
    if ($gpuIntegradaTxt === '') $gpuIntegradaTxt = 'Integrada (N/D)';

    $gpuDedicadaTxt = '';
    $vramTxt = '';
    if ($gpuDed && (int)($gpuDed->activo ?? 1) === 1) {
        $gpuDedicadaTxt = trim(($gpuDed->marca ?? '') . ' ' . ($gpuDed->modelo ?? ''));
        $vramVal = $gpuDed->vram ?? null;
        $vramUni = $gpuDed->vram_unidad ?? null;
        if (!is_null($vramVal) && $vramVal !== '') {
            $vramTxt = $vramVal . ($vramUni ? " {$vramUni}" : '');
        }
    }

    // ===== Monitor / Pantalla (equipo_monitores) =====
    $m = $e->monitor ?? null;

    $tipoMonitor = $m->tipo_panel ?? '';
    $touchTxt    = isset($m->es_touch) ? (((int)$m->es_touch) === 1 ? 'Sí' : 'No') : '';
    $resMonitor  = $m->resolucion ?? '';

    // si es EXTERNA y NO incluye monitor -> vaciar
    if ($m && strtoupper((string)($m->origen_pantalla ?? '')) === 'EXTERNA' && (int)($m->incluido ?? 0) === 0) {
        $tipoMonitor = '';
        $touchTxt    = '';
        $resMonitor  = '';
    }

    // Entradas de monitor
    $inMonHdmi = $m->in_hdmi ?? '';
    $inMonVga  = $m->in_vga ?? '';
    $inMonDvi  = $m->in_dvi ?? '';
    $inMonDp   = $m->in_displayport ?? '';
    $inMonUsb2 = $m->in_usb_2 ?? '';
    $inMonUsb3 = $m->in_usb_3 ?? '';
    $inMonUsb31= $m->in_usb_31 ?? '';
    $inMonUsb32= $m->in_usb_32 ?? '';
    $inMonUsbC = $m->in_usb_c ?? '';

    // Detalles monitor (checks + otro)
    $monFunc = trim(
        trim((string)($m->detalles_funcionamiento_checks ?? '')) .
        (empty($m->detalles_funcionamiento_otro) ? '' : ' | ' . trim((string)$m->detalles_funcionamiento_otro))
    );

    $monEst = trim(
        trim((string)($m->detalles_esteticos_checks ?? '')) .
        (empty($m->detalles_esteticos_otro) ? '' : ' | ' . trim((string)$m->detalles_esteticos_otro))
    );

    // ===== Baterías (equipo_baterias) =====
    // Requiere que cargues relación baterias en el with() (abajo te pongo la línea)
    $bats = $e->baterias ?? collect();
    $bateriaCantidad = $bats->count();

    $bateriaCondTxt = '';
    if ($bateriaCantidad > 0) {
        $bateriaCondTxt = $bats->values()->map(function ($b, $i) {
            $n = $i + 1;
            $p = $b->salud_percent;
            return "B{$n}: " . (is_null($p) ? 'N/D' : "{$p}%");
        })->implode(' | ');
    }

    // ===== RAM total preparada (NO existe en DB) =====
    $ramTotalPreparada = '';

    return [
        // 1-4
        optional($e->created_at)->format('Y-m-d H:i:s'),
        $usuario?->nombre ?? '',
        $proveedor->nombre_empresa ?? '',
        $e->estatus_general,

        // 5-10
        $e->tipo_equipo,
        $e->marca,
        $e->modelo,
        $e->numero_serie,
        $e->area_tienda,
        $e->sistema_operativo,

        // 11-14
        $e->procesador_modelo,
        $e->procesador_frecuencia,
        $e->procesador_generacion,
        $e->procesador_nucleos,

        // 15-21 RAM
    $e->ram_total,
    $e->ram_tipo,
    $e->ram_expansion_max,
    $e->ram_slots_totales,
    $e->ram_es_soldada ? 'Sí' : 'No',
    $e->ram_cantidad_soldada,


        // 22-24 Monitor
        $tipoMonitor,
        $touchTxt,
        $resMonitor,

        // 25-28 Almacenamiento
        $e->almacenamiento_principal_capacidad,
        $e->almacenamiento_principal_tipo,
        $e->almacenamiento_secundario_capacidad,
        $e->almacenamiento_secundario_tipo,

        // 29-33 Slots
        $e->slots_alm_ssd,
        $e->slots_alm_m2,
        $e->slots_alm_m2_micro,
        $e->slots_alm_hdd,
        $e->slots_alm_msata,

        // 34-35 Ethernet
        $e->ethernet_tiene ? 'Sí' : 'No',
        $e->ethernet_es_gigabit ? 'Sí' : 'No',

        // 36-38 texto libre
        $e->puertos_conectividad,
        $e->dispositivos_entrada,
        $e->teclado_idioma,

        // 39-49 Puertos (equipos)
        $e->puertos_hdmi,
        $e->puertos_mini_hdmi,
        $e->puertos_dvi,
        $e->puertos_displayport,
        $e->puertos_mini_dp,
        $e->puertos_usb_2,
        $e->puertos_usb_30,
        $e->puertos_usb_31,
        $e->puertos_usb_32,
        $e->puertos_usb_c,

        // 50-53 Lectores (equipos)
        $e->lectores_sd,
        $e->lectores_sc,
        $e->lectores_esata,
        $e->lectores_sim,

        // 54-56 Batería
        $e->bateria_tiene ? 'Sí' : 'No',
        $bateriaCantidad,
        $bateriaCondTxt,

        // 57-60 GPU
        $gpuIntegradaTxt,
        $gpuDedicadaTxt,
        $vramTxt,

        // 61-62 Detalles equipo
        $e->detalles_esteticos,
        $e->detalles_funcionamiento,

        // 63-71 Entradas monitor
        $inMonHdmi,
        $inMonVga,
        $inMonDvi,
        $inMonDp,
        $inMonUsb2,
        $inMonUsb3,
        $inMonUsb31,
        $inMonUsb32,
        $inMonUsbC,

        // 72-73 Detalles monitor
        $monFunc,
        $monEst,

        // 74 Lote
        $lote->nombre_lote ?? '',
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
