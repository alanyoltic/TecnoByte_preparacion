<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Almacen;
use App\Models\DespachoVentas;
use App\Models\Equipo;
use App\Models\Sucursal;
use App\Services\DespachoVentasService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Nuevo Despacho a Ventas'])]
class DespachoVentasCrear extends Component
{
    // ── Paso 1: Configuración del despacho ────────────────────
    public int $sucursalDestinoId = 0;
    public int $almacenDestinoId  = 0;
    public string $motivo         = '';

    // ── Búsqueda de equipos ───────────────────────────────────
    public string $busquedaEquipo = '';

    // ── Equipos seleccionados ─────────────────────────────────
    /** @var array<int, array{equipo_id: int, precio_sugerido: float|null, observacion: string}> */
    public array $equiposSeleccionados = [];

    // ── Estado interno ────────────────────────────────────────
    public int $paso = 1; // 1=Configurar, 2=Seleccionar equipos, 3=Revisar y enviar
    public ?DespachoVentas $despachoActual = null;
    public string $mensajeError  = '';
    public bool $confirmandoEnvio = false;

    // =========================================================
    // COMPUTED PROPERTIES
    // =========================================================

    #[Computed]
    public function sucursales(): Collection
    {
        return Sucursal::activas()->get();
    }

    #[Computed]
    public function almacenesDestino(): Collection
    {
        if (! $this->sucursalDestinoId) {
            return collect();
        }

        return Almacen::where('sucursal_id', $this->sucursalDestinoId)
            ->whereHas('area', fn ($q) => $q->where('clave', 'VENTAS'))
            ->where('activo', true)
            ->get();
    }

    #[Computed]
    public function equiposDisponibles(): Collection
    {
        if (strlen($this->busquedaEquipo) < 2) {
            return collect();
        }

        $idsYaSeleccionados = array_column($this->equiposSeleccionados, 'equipo_id');

        return Equipo::query()
            ->with(['loteModelo.catalogoEquipo'])
            ->where('estatus_area', Equipo::AREA_FINALIZADO)
            ->where('estatus_ciclo', Equipo::CICLO_CALIDAD)
            ->whereNotIn('id', $idsYaSeleccionados)
            // Verificar que no esté ya en un despacho activo
            ->whereDoesntHave('despachosVentas', fn ($q) => $q->whereHas('despacho', fn ($dq) =>
                $dq->whereIn('estatus', [DespachoVentas::BORRADOR, DespachoVentas::ENVIADO])
            ))
            ->where(fn ($q) => $q
                ->where('numero_serie', 'like', "%{$this->busquedaEquipo}%")
                ->orWhere('marca', 'like', "%{$this->busquedaEquipo}%")
                ->orWhere('modelo', 'like', "%{$this->busquedaEquipo}%")
            )
            ->limit(20)
            ->get();
    }

    // =========================================================
    // ACCIONES — PASO 1
    // =========================================================

    public function avanzarPaso1(DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        $this->validate([
            'sucursalDestinoId' => 'required|integer|min:1',
            'almacenDestinoId'  => 'required|integer|min:1',
        ], [
            'sucursalDestinoId.required' => 'Selecciona una sucursal de destino.',
            'almacenDestinoId.required'  => 'Selecciona un almacén de destino.',
        ]);

        try {
            $this->despachoActual = $service->crear(
                $this->sucursalDestinoId,
                $this->almacenDestinoId,
                $this->motivo ?: null
            );
            $this->paso = 2;
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    // =========================================================
    // ACCIONES — PASO 2 (selección de equipos)
    // =========================================================

    public function agregarEquipo(int $equipoId, DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (! $this->despachoActual) {
            $this->mensajeError = 'Primero completa el paso 1.';
            return;
        }

        $equipo = Equipo::find($equipoId);
        if (! $equipo) {
            $this->mensajeError = 'Equipo no encontrado.';
            return;
        }

        try {
            $precioBase = $service->calcularPrecioBase($equipo);

            $service->agregarEquipo(
                $this->despachoActual,
                $equipo,
                $precioBase
            );

            // Agregar al array local para mostrar en UI
            $this->equiposSeleccionados[] = [
                'equipo_id'       => $equipo->id,
                'numero_serie'    => $equipo->numero_serie,
                'marca'           => $equipo->marca,
                'modelo'          => $equipo->modelo,
                'precio_sugerido' => $precioBase,
                'observacion'     => '',
            ];

            $this->busquedaEquipo = '';
            unset($this->equiposDisponibles);

        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function quitarEquipo(int $equipoId, DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (! $this->despachoActual) {
            return;
        }

        $equipo = Equipo::find($equipoId);
        if ($equipo) {
            try {
                $service->quitarEquipo($this->despachoActual, $equipo);
            } catch (\Exception $e) {
                $this->mensajeError = $e->getMessage();
                return;
            }
        }

        $this->equiposSeleccionados = array_values(
            array_filter($this->equiposSeleccionados, fn ($e) => $e['equipo_id'] !== $equipoId)
        );
    }

    public function actualizarPrecio(int $index, string $valor, DespachoVentasService $service): void
    {
        if (! isset($this->equiposSeleccionados[$index])) {
            return;
        }

        $precio = (float) str_replace(',', '', $valor);
        if ($precio < 0) {
            return;
        }

        $this->equiposSeleccionados[$index]['precio_sugerido'] = $precio;

        // Obtener el ID del registro en la tabla pivot
        $equipo = Equipo::find($this->equiposSeleccionados[$index]['equipo_id']);
        if ($equipo && $this->despachoActual) {
            try {
                $pivotId = \App\Models\DespachoVentasEquipo::where('despacho_ventas_id', $this->despachoActual->id)
                    ->where('equipo_id', $equipo->id)
                    ->value('id');

                if ($pivotId) {
                    $service->actualizarPrecio($this->despachoActual, $pivotId, $precio);
                }
            } catch (\Exception $e) {
                $this->mensajeError = $e->getMessage();
            }
        }
    }

    public function avanzarPaso2(): void
    {
        if (empty($this->equiposSeleccionados)) {
            $this->mensajeError = 'Agrega al menos un equipo al despacho.';
            return;
        }

        $this->mensajeError = '';
        $this->paso = 3;
    }

    // =========================================================
    // ACCIONES — PASO 3 (confirmar y enviar)
    // =========================================================

    public function confirmarEnvio(DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (! $this->despachoActual) {
            $this->mensajeError = 'No hay un despacho activo.';
            return;
        }

        try {
            $service->enviar($this->despachoActual);
            session()->flash('success', "Despacho {$this->despachoActual->folio} enviado a Ventas para aprobación.");
            $this->redirect(route('preparacion.despachos-ventas'), navigate: true);
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function regresarPaso(): void
    {
        if ($this->paso > 1) {
            $this->paso--;
        }
        $this->mensajeError = '';
    }

    // =========================================================
    // RENDER
    // =========================================================

    public function render()
    {
        return view('livewire.preparacion.inventario.despacho-ventas-crear');
    }
}
