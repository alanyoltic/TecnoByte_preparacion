<?php

namespace App\Livewire\Ventas;

use App\Models\DespachoVentas;
use App\Services\DespachoVentasService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Despachos Entrantes — Ventas'])]
class DespachosEntrada extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'ENVIADO'; // Por default muestra los pendientes de aprobar
    public ?int $despachoSeleccionadoId = null;
    public string $motivoRechazo = '';
    public bool $modalRechazo = false;
    public bool $modalAprobacion = false;
    public string $mensajeError = '';

    // =========================================================
    // PROPIEDADES DE VERIFICACIÓN FÍSICA (ESCÁNER Y CONTEO)
    // =========================================================
    public array $equiposVerificados = []; // [equipo_id => bool]
    public array $productosVerificados = []; // [producto_id => int cantidadRecibida]
    public string $inputScanSerie = '';
    public string $mensajeScan = '';
    public string $mensajeScanTipo = 'info'; // success, error, warning
    public string $notasRecepcion = '';

    // =========================================================
    // RENDER
    // =========================================================

    public function render()
    {
        $despachos = DespachoVentas::query()
            ->with(['sucursalDestino', 'almacenDestino', 'creadoPor'])
            ->withCount(['equipos', 'productos'])
            ->when($this->filtroEstatus, fn ($q) => $q->where('estatus', $this->filtroEstatus))
            ->orderByDesc(
                fn ($q) => $q->selectRaw("FIELD(estatus, 'ENVIADO', 'BORRADOR', 'APROBADO', 'RECHAZADO', 'CANCELADO')")
            )
            ->orderByDesc('enviado_at')
            ->paginate(15);

        // Contadores para las pestañas
        $contadores = DespachoVentas::selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');

        return view('livewire.ventas.despachos-entrada', [
            'despachos'                   => $despachos,
            'contadores'                  => $contadores,
            'estatuses'                   => DespachoVentas::labelsEstatus(),
            'despachoSeleccionado'        => $this->despachoSeleccionado,
            'verificacionCompleta'        => $this->verificacionCompleta,
            'productosDiscrepanciasCount' => $this->productosDiscrepanciasCount,
            'equiposFaltantesCount'       => $this->equiposFaltantesCount,
            'filtroEstatus'               => $this->filtroEstatus,
            'modalAprobacion'             => $this->modalAprobacion,
            'modalRechazo'                => $this->modalRechazo,
            'equiposVerificados'          => $this->equiposVerificados,
            'productosVerificados'        => $this->productosVerificados,
            'inputScanSerie'              => $this->inputScanSerie,
            'mensajeScan'                 => $this->mensajeScan,
            'mensajeScanTipo'             => $this->mensajeScanTipo,
            'notasRecepcion'              => $this->notasRecepcion,
            'motivoRechazo'               => $this->motivoRechazo,
            'mensajeError'                => $this->mensajeError,
        ]);
    }

    // =========================================================
    // ACCIONES
    // =========================================================

    public function getDespachoSeleccionadoProperty(): ?DespachoVentas
    {
        if (! $this->despachoSeleccionadoId) {
            return null;
        }

        return DespachoVentas::with([
            'sucursalDestino',
            'almacenDestino',
            'creadoPor',
            'equipos.equipo',
            'productos.producto',
        ])->find($this->despachoSeleccionadoId);
    }

    public function verDetalle(int $id): void
    {
        $this->despachoSeleccionadoId = $id;
    }

    public function iniciarAprobacion(int $id): void
    {
        $this->despachoSeleccionadoId = $id;
        $this->mensajeError = '';
        $this->mensajeScan = '';
        $this->mensajeScanTipo = 'info';
        $this->inputScanSerie = '';
        $this->notasRecepcion = '';
        $this->equiposVerificados = [];
        $this->productosVerificados = [];

        $despacho = $this->despachoSeleccionado;
        if ($despacho) {
            foreach ($despacho->productos as $dp) {
                $this->productosVerificados[$dp->producto_id] = 0;
            }
        }

        $this->modalAprobacion = true;
    }

    public function escanearSerie(): void
    {
        $this->mensajeScan = '';
        $serie = trim($this->inputScanSerie);

        if (! $serie) {
            return;
        }

        $despacho = $this->despachoSeleccionado;
        if (! $despacho) {
            $this->mensajeScan = 'No hay un despacho activo seleccionado.';
            $this->mensajeScanTipo = 'error';
            return;
        }

        // Buscar entre los equipos del despacho por serie exacta (o ID)
        $despachoEquipo = $despacho->equipos->first(function ($item) use ($serie) {
            $eq = $item->equipo;
            if (! $eq) {
                return false;
            }
            return strtoupper(trim($eq->numero_serie)) === strtoupper($serie) ||
                   (is_numeric($serie) && $eq->id === (int) $serie);
        });

        if (! $despachoEquipo) {
            $this->mensajeScan = "❌ La serie [{$serie}] NO pertenece a este despacho.";
            $this->mensajeScanTipo = 'error';
            $this->inputScanSerie = '';
            return;
        }

        $equipoId = $despachoEquipo->equipo_id;

        if (! empty($this->equiposVerificados[$equipoId])) {
            $this->mensajeScan = "⚠️ El equipo [{$despachoEquipo->equipo?->marca} {$despachoEquipo->equipo?->modelo} - {$serie}] YA fue escaneado previamente.";
            $this->mensajeScanTipo = 'warning';
            $this->inputScanSerie = '';
            return;
        }

        $this->equiposVerificados[$equipoId] = true;
        $this->mensajeScan = "✓ Equipo [{$despachoEquipo->equipo?->marca} {$despachoEquipo->equipo?->modelo}] serie {$serie} verificado exitosamente.";
        $this->mensajeScanTipo = 'success';
        $this->inputScanSerie = '';
    }

    public function alternarVerificacionEquipo(int $equipoId): void
    {
        if (isset($this->equiposVerificados[$equipoId]) && $this->equiposVerificados[$equipoId]) {
            unset($this->equiposVerificados[$equipoId]);
            $this->mensajeScan = 'Equipo desmarcado de verificación.';
            $this->mensajeScanTipo = 'info';
        } else {
            $this->equiposVerificados[$equipoId] = true;
            $this->mensajeScan = 'Equipo marcado manualmente como verificado.';
            $this->mensajeScanTipo = 'success';
        }
    }

    public function incrementarProducto(int $productoId): void
    {
        $actual = $this->productosVerificados[$productoId] ?? 0;
        $this->productosVerificados[$productoId] = $actual + 1;
    }

    public function decrementarProducto(int $productoId): void
    {
        $actual = $this->productosVerificados[$productoId] ?? 0;
        $this->productosVerificados[$productoId] = max(0, $actual - 1);
    }

    public function marcarTodoConformeProductos(): void
    {
        $despacho = $this->despachoSeleccionado;
        if (! $despacho) {
            return;
        }

        foreach ($despacho->productos as $dp) {
            $this->productosVerificados[$dp->producto_id] = $dp->cantidad;
        }
    }

    public function getEquiposFaltantesCountProperty(): int
    {
        $despacho = $this->despachoSeleccionado;
        if (! $despacho) {
            return 0;
        }

        $total = $despacho->equipos->count();
        $verificados = count(array_filter($this->equiposVerificados));

        return max(0, $total - $verificados);
    }

    public function getProductosDiscrepanciasCountProperty(): int
    {
        $despacho = $this->despachoSeleccionado;
        if (! $despacho) {
            return 0;
        }

        $discrepancias = 0;
        foreach ($despacho->productos as $dp) {
            $recibido = $this->productosVerificados[$dp->producto_id] ?? 0;
            if ($recibido !== $dp->cantidad) {
                $discrepancias++;
            }
        }

        return $discrepancias;
    }

    public function getVerificacionCompletaProperty(): bool
    {
        return $this->equiposFaltantesCount === 0 && $this->productosDiscrepanciasCount === 0;
    }

    public function confirmarAprobacion(DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        $despacho = DespachoVentas::with(['equipos.equipo', 'productos.producto'])->find($this->despachoSeleccionadoId);

        if (! $despacho) {
            $this->mensajeError = 'Despacho no encontrado.';
            return;
        }

        // Si no está completo y no se ingresó justificación / notas
        if (! $this->verificacionCompleta && empty(trim($this->notasRecepcion))) {
            $this->mensajeError = 'Hay equipos sin escanear o accesorios con cantidades distintas a las esperadas. Si confirmas con discrepancia, debes ingresar obligatoriamente una nota u observación.';
            return;
        }

        try {
            $service->aprobar($despacho);

            // Si hay notas de recepción o discrepancia, registrarlas en el despacho
            if (! empty(trim($this->notasRecepcion))) {
                $notaExistente = $despacho->motivo ? $despacho->motivo . " | " : "";
                $despacho->update([
                    'motivo' => trim($notaExistente . "Recepción Tienda: " . $this->notasRecepcion),
                ]);
            }

            $this->modalAprobacion = false;
            $this->despachoSeleccionadoId = null;
            session()->flash('success', "Despacho {$despacho->folio} recibido y aprobado correctamente. Los equipos ya están en Ventas.");
            $this->resetPage();
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function iniciarRechazo(int $id): void
    {
        $this->despachoSeleccionadoId = $id;
        $this->motivoRechazo = '';
        $this->mensajeError = '';
        $this->modalRechazo = true;
    }

    public function confirmarRechazo(DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (empty(trim($this->motivoRechazo))) {
            $this->mensajeError = 'Debes indicar el motivo del rechazo.';
            return;
        }

        $despacho = DespachoVentas::find($this->despachoSeleccionadoId);

        if (! $despacho) {
            $this->mensajeError = 'Despacho no encontrado.';
            return;
        }

        try {
            $service->rechazar($despacho, $this->motivoRechazo);
            $this->modalRechazo = false;
            $this->despachoSeleccionadoId = null;
            $this->motivoRechazo = '';
            session()->flash('success', "Despacho {$despacho->folio} rechazado.");
            $this->resetPage();
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function cerrarModales(): void
    {
        $this->modalAprobacion = false;
        $this->modalRechazo = false;
        $this->despachoSeleccionadoId = null;
        $this->mensajeError = '';
        $this->mensajeScan = '';
        $this->inputScanSerie = '';
        $this->equiposVerificados = [];
        $this->productosVerificados = [];
        $this->notasRecepcion = '';
    }

    public function updatedFiltroEstatus(): void
    {
        $this->resetPage();
    }
}
