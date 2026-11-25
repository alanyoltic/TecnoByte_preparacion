<?php

namespace App\Livewire\Preparacion\Lotes;

use App\Models\Equipo;
use App\Models\Lote;
use App\Models\LoteModeloRecibido;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Ver Lote'])]
class VerLote extends Component
{
    public Lote $lote;

    public $nombre_lote;

    public $fecha_llegada;

    public $proveedor_nombre;

    public array $modelos = [];

    public function mount(Lote $lote): void
    {
        abort_unless(Auth::user()?->tienePermiso('prep.lotes.ver') || Auth::user()?->tienePermiso('prep.lotes.gestion'), 403);

        $this->lote = $lote->load([
            'compraInventario.items.catalogoEquipo.clasificacionPuntos',
            'modelosRecibidos.clasificacionPuntos',
            'modelosRecibidos.catalogoEquipo.clasificacionPuntos',
            'modelosRecibidos.equipos.asignacionEquipos.asignacion.tecnico',
            'proveedor',
        ]);

        $this->nombre_lote = $lote->nombre_lote;
        $this->fecha_llegada = $lote->fecha_llegada ? date('Y-m-d', strtotime($lote->fecha_llegada)) : null;
        $this->proveedor_nombre = $lote->proveedor->nombre_empresa ?? $lote->proveedor->nombre ?? 'Proveedor Desconocido';

        $this->cargarModelosSoloLectura();
    }

    /**
     * Construye el desglose 100% de solo lectura de los modelos y equipos del lote.
     */
    private function cargarModelosSoloLectura(): void
    {
        $this->modelos = [];

        if ($this->lote->modelosRecibidos->isNotEmpty()) {
            foreach ($this->lote->modelosRecibidos as $lm) {
                $equiposDB = $lm->equipos->sortBy('id')->values();
                $clasificacion = $lm->clasificacionPuntos ?? $lm->catalogoEquipo?->clasificacionPuntos;

                $this->modelos[] = $this->formatearResumenModelo(
                    catalogoEquipoId: $lm->catalogo_equipo_id,
                    marca: $lm->marca ?: ($lm->catalogoEquipo?->marca ?: 'Genérica'),
                    modelo: $lm->modelo ?: ($lm->catalogoEquipo?->modelo ?: 'Genérico'),
                    cantidad: (int) $lm->cantidad_recibida,
                    valorUnitario: (float) ($lm->valor_unitario ?? 0),
                    clasificacionLabel: $clasificacion
                        ? "Tipo {$clasificacion->clave} (" . number_format((float) $clasificacion->puntos_base, 2) . ' pts)'
                        : null,
                    equiposDB: $equiposDB,
                );
            }

            return;
        }

        // Respaldo para lotes históricos ligados a OC que aún no generaban LoteModeloRecibido
        if ($this->lote->compraInventario && $this->lote->compraInventario->items->isNotEmpty()) {
            foreach ($this->lote->compraInventario->items as $item) {
                if (! $item->catalogo_equipo_id) {
                    continue;
                }

                $loteModeloIds = LoteModeloRecibido::where('lote_id', $this->lote->id)
                    ->where('catalogo_equipo_id', $item->catalogo_equipo_id)
                    ->pluck('id');

                $equiposDB = $loteModeloIds->isNotEmpty()
                    ? Equipo::with('asignacionEquipos.asignacion.tecnico')
                        ->whereIn('lote_modelo_id', $loteModeloIds)
                        ->orderBy('id')
                        ->get()
                    : collect();

                $clasificacion = $item->catalogoEquipo?->clasificacionPuntos;

                $this->modelos[] = $this->formatearResumenModelo(
                    catalogoEquipoId: $item->catalogo_equipo_id,
                    marca: optional($item->catalogoEquipo)->marca ?: 'Genérica',
                    modelo: optional($item->catalogoEquipo)->modelo ?: 'Genérico',
                    cantidad: (int) $item->cantidad,
                    valorUnitario: (float) ($item->precio_unitario ?? 0),
                    clasificacionLabel: $clasificacion
                        ? "Tipo {$clasificacion->clave} (" . number_format((float) $clasificacion->puntos_base, 2) . ' pts)'
                        : null,
                    equiposDB: $equiposDB,
                );
            }
        }
    }

    private function formatearResumenModelo(
        ?int $catalogoEquipoId,
        string $marca,
        string $modelo,
        int $cantidad,
        float $valorUnitario,
        ?string $clasificacionLabel,
        $equiposDB
    ): array {
        $conSerie = $equiposDB->filter(fn ($eq) => ! empty(trim((string) $eq->numero_serie)))->count();
        $sinSerieExistentes = $equiposDB->filter(fn ($eq) => empty(trim((string) $eq->numero_serie)))->count();
        $sinRegistrar = max(0, $cantidad - $equiposDB->count());
        $pendientesSerie = $sinSerieExistentes + $sinRegistrar;
        $usados = $equiposDB->where('estatus_area', '!=', Equipo::AREA_SIN_ASIGNAR)->count();

        $registrosExistentes = [];
        foreach ($equiposDB as $eq) {
            $aeReciente = $eq->asignacionEquipos->sortByDesc('id')->first();
            $tecnicoNombre = $aeReciente?->asignacion?->tecnico?->name ?? 'Sin asignar';

            $registrosExistentes[] = [
                'id' => $eq->id,
                'numero_serie' => $eq->numero_serie,
                'en_proceso' => ($eq->estatus_area !== Equipo::AREA_SIN_ASIGNAR),
                'tecnico' => $tecnicoNombre,
                'estatus_area' => $eq->estatus_area,
                'estatus_ciclo' => $eq->estatus_ciclo,
                'area_label' => $eq->estatus_area_label,
                'ciclo_label' => $eq->estatus_ciclo_label,
            ];
        }

        return [
            'catalogo_equipo_id' => $catalogoEquipoId,
            'marca' => $marca,
            'modelo' => $modelo,
            'cantidad' => $cantidad,
            'con_serie' => $conSerie,
            'pendientes' => $pendientesSerie,
            'usados' => $usados,
            'valor_unitario' => $valorUnitario,
            'clasificacion_label' => $clasificacionLabel,
            'registros_existentes' => $registrosExistentes,
        ];
    }

    public function render()
    {
        return view('livewire.preparacion.lotes.ver-lote', [
            'lote' => $this->lote,
            'nombre_lote' => $this->nombre_lote,
            'fecha_llegada' => $this->fecha_llegada,
            'proveedor_nombre' => $this->proveedor_nombre,
            'modelos' => $this->modelos,
        ]);
    }
}
