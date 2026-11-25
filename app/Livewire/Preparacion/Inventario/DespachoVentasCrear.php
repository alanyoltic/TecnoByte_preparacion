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

    // ── Productos / Accesorios comerciales ────────────────────
    public string $busquedaProducto = '';
    public int $cantidadProducto = 1;
    /** @var array<int, array{producto_id: int, nombre: string, sku: ?string, cantidad: int, precio_sugerido: ?float}> */
    public array $productosSeleccionados = [];

    // ── Estado interno ────────────────────────────────────────
    public int $paso = 1; // 1=Configurar, 2=Seleccionar equipos, 3=Revisar y enviar
    public ?DespachoVentas $despachoActual = null;
    public string $mensajeError  = '';
    public bool $confirmandoEnvio = false;

    public function mount(): void
    {
        $continuarId = request()->query('continuar');
        if ($continuarId) {
            $despacho = DespachoVentas::with([
                'equipos.equipo',
                'productos.producto',
                'sucursalDestino',
                'almacenDestino',
            ])->find($continuarId);

            if ($despacho && $despacho->esBorrador()) {
                $this->despachoActual = $despacho;
                $this->sucursalDestinoId = $despacho->sucursal_destino_id;
                $this->almacenDestinoId = $despacho->almacen_destino_id;
                $this->motivo = $despacho->motivo ?? '';

                $this->equiposSeleccionados = [];
                foreach ($despacho->equipos as $item) {
                    if ($item->equipo) {
                        $this->equiposSeleccionados[] = [
                            'equipo_id'       => $item->equipo_id,
                            'numero_serie'    => $item->equipo->numero_serie,
                            'marca'           => $item->equipo->marca,
                            'modelo'          => $item->equipo->modelo,
                            'tipo_equipo'     => $item->equipo->tipo_equipo,
                            'lote_id'         => $item->equipo->lote_id,
                            'lote_nombre'     => $item->equipo->lote_nombre,
                            'precio_sugerido' => (float) $item->precio_sugerido,
                            'observacion'     => $item->observacion ?? '',
                        ];
                    }
                }

                $this->productosSeleccionados = [];
                foreach ($despacho->productos as $item) {
                    if ($item->producto) {
                        $this->productosSeleccionados[] = [
                            'producto_id'     => $item->producto_id,
                            'nombre'          => $item->producto->nombre,
                            'sku'             => $item->producto->sku,
                            'cantidad'        => $item->cantidad,
                            'precio_sugerido' => (float) $item->precio_unitario,
                        ];
                    }
                }

                $this->paso = 2;
            }
        }
    }

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
            ->where('departamento_id', 2)
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
            ->with(['loteModelo.catalogoEquipo', 'loteModelo.lote'])
            ->where('estatus_area', Equipo::AREA_FINALIZADO)
            ->where('estatus_ciclo', Equipo::CICLO_CALIDAD)
            ->whereNotNull('numero_serie')
            ->whereRaw("TRIM(numero_serie) != ''")
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

    #[Computed]
    public function productosDisponibles(): Collection
    {
        if (strlen($this->busquedaProducto) < 2) {
            return collect();
        }

        return \App\Models\Producto::where('activo', 1)
            ->whereHas('inventarios', fn ($q) => $q
                ->where('almacen_id', Almacen::PREPARACION)
                ->where('cantidad', '>', 0)
            )
            ->with(['inventarios' => fn ($q) => $q->where('almacen_id', Almacen::PREPARACION)])
            ->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$this->busquedaProducto}%")
                ->orWhere('sku', 'like', "%{$this->busquedaProducto}%")
                ->orWhere('categoria', 'like', "%{$this->busquedaProducto}%")
            )
            ->limit(15)
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
            if ($this->despachoActual && $this->despachoActual->esBorrador()) {
                $this->despachoActual->update([
                    'sucursal_destino_id' => $this->sucursalDestinoId,
                    'almacen_destino_id'  => $this->almacenDestinoId,
                    'motivo'              => $this->motivo ?: null,
                ]);
            } else {
                $this->despachoActual = $service->crear(
                    $this->sucursalDestinoId,
                    $this->almacenDestinoId,
                    $this->motivo ?: null
                );
            }
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
                'tipo_equipo'     => $equipo->tipo_equipo,
                'lote_id'         => $equipo->lote_id,
                'lote_nombre'     => $equipo->lote_nombre,
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

    public function agregarProducto(int $productoId, DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (! $this->despachoActual) {
            $this->mensajeError = 'Primero completa el paso 1.';
            return;
        }

        $producto = \App\Models\Producto::find($productoId);
        if (! $producto) {
            $this->mensajeError = 'Producto no encontrado.';
            return;
        }

        $cant = max(1, (int) $this->cantidadProducto);

        try {
            $service->agregarProducto(
                $this->despachoActual,
                $producto,
                $cant,
                $producto->precio_venta
            );

            $existenteIdx = null;
            foreach ($this->productosSeleccionados as $k => $p) {
                if ($p['producto_id'] === $producto->id) {
                    $existenteIdx = $k;
                    break;
                }
            }

            if ($existenteIdx !== null) {
                $this->productosSeleccionados[$existenteIdx]['cantidad'] += $cant;
            } else {
                $this->productosSeleccionados[] = [
                    'producto_id'     => $producto->id,
                    'nombre'          => $producto->nombre,
                    'sku'             => $producto->sku,
                    'cantidad'        => $cant,
                    'precio_sugerido' => $producto->precio_venta,
                ];
            }

            $this->busquedaProducto = '';
            $this->cantidadProducto = 1;

        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
        }
    }

    public function quitarProducto(int $productoId, DespachoVentasService $service): void
    {
        $this->mensajeError = '';

        if (! $this->despachoActual) {
            return;
        }

        try {
            $service->quitarProducto($this->despachoActual, $productoId);
        } catch (\Exception $e) {
            $this->mensajeError = $e->getMessage();
            return;
        }

        $this->productosSeleccionados = array_values(
            array_filter($this->productosSeleccionados, fn ($p) => $p['producto_id'] !== $productoId)
        );
    }

    public function avanzarPaso2(): void
    {
        if (empty($this->equiposSeleccionados) && empty($this->productosSeleccionados)) {
            $this->mensajeError = 'Agrega al menos un equipo o producto/accesorio al despacho.';
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
