<?php

namespace App\Livewire\Ventas;

use App\Models\Equipo;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\VentaService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Punto de Venta'])]
class PuntoDeVenta extends Component
{
    // ── Carrito ──────────────────────────────────────────────────────────────
    public array $carrito = [];           // Array de ítems en el carrito

    // ── Buscador ─────────────────────────────────────────────────────────────
    public string $busquedaSerial  = '';  // Buscar Equipo por serie
    public string $busquedaProducto = ''; // Buscar Producto por nombre/SKU
    public string $errorBusqueda   = '';

    // ── Venta ────────────────────────────────────────────────────────────────
    public ?int  $clienteId    = null;
    public string $metodoPago  = 'EFECTIVO';
    public string $notas       = '';

    // ── Variables UI de compatibilidad (Nuevas) ──────────────────────────────
    public string $tipoComprobante = 'NOTA'; // FACTURA o NOTA
    public ?int $sucursalId = null;
    public ?int $almacenId = null;
    public string $direccionFiscal = '';
    public string $emailNotificacion = '';
    public bool $enviarNotificacion = false;
    public string $tipoDescuento = '$'; // '$' o '%'
    public $valorDescuento = 0;
    public $descuento = 0;
    public $tasaIva = 0; // 0 o 16 (default 0 para no forzar IVA)
    public bool $aplicaIva = false;

    // ── Resultados de búsqueda de producto ───────────────────────────────────
    public array $resultadosProducto = [];

    // ── Modal pago y Venta ───────────────────────────────────────────────────
    public bool $modalExito   = false;
    public string $folioVenta = '';

    // ── Modal Clientes ───────────────────────────────────────────────────────
    public bool $modalCliente = false;

    public bool $modalAsignarCargador = false;
    public string $escaneoCargador = '';
    public int $equipoIndexActualCargador = -1;

    public function abrirModalCargador($index)
    {
        $this->equipoIndexActualCargador = $index;
        $this->escaneoCargador = '';
        $this->modalAsignarCargador = true;
    }

    public function asignarCargador()
    {
        $this->validate(['escaneoCargador' => 'required|string']);
        
        $cargador = \App\Models\Cargador::where('serie', $this->escaneoCargador)
            ->where('estatus', 'DISPONIBLE')
            ->first();

        if (!$cargador) {
            $this->addError('escaneoCargador', 'Cargador no encontrado o no disponible.');
            return;
        }

        $this->carrito[$this->equipoIndexActualCargador]['cargador_id'] = $cargador->id;
        $this->carrito[$this->equipoIndexActualCargador]['cargador_serie'] = $cargador->serie;
        
        $this->modalAsignarCargador = false;
        $this->dispatch('toast', type: 'success', message: 'Cargador vinculado exitosamente.');
    }

    public string $busquedaClienteModal = '';
    public array $resultadosClienteModal = [];
    public bool $modoCrearCliente = false;

    // Campos de nuevo cliente
    public string $nuevoClienteTipo = 'FISICA';
    public string $nuevoClienteNombres = '';
    public string $nuevoClienteApellidos = '';
    public string $nuevoClienteRazonSocial = '';
    public string $nuevoClienteRfc = '';
    public string $nuevoClienteRegimen = '';
    public string $nuevoClienteTelefono = '';
    public string $nuevoClienteCorreo = '';
    public string $nuevoClienteComoSeEntero = '';
    public ?int $nuevoClienteVendedorId = null;
    public string $nuevoClientePais = 'México';
    public string $nuevoClienteEstado = '';
    public string $nuevoClienteMunicipio = '';
    public string $nuevoClienteLocalidad = '';
    public string $nuevoClienteCalle = '';
    public string $nuevoClienteColonia = '';
    public string $nuevoClienteCp = '';
    public string $nuevoClienteNoExt = '';
    public string $nuevoClienteNoInt = '';
    public string $nuevoClienteCodigoColonia = '';
    public string $nuevoClienteCodigoLocalidad = '';

    // ── Venta properties adicionales ─────────────────────────────────────────
    public string $usoCfdi = '';

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        abort_unless(auth()->user()?->tienePermiso('ventas.venta.crear'), 403, 'No tienes permiso para el Punto de Venta.');
        $user = auth()->user();
        $this->sucursalId = $user?->sucursal_id;
        $this->almacenId = $this->almacenId
            ?? $user?->sucursal?->almacenesDeVentas()->value('id')
            ?? \App\Models\Almacen::where('departamento_id', 2)->value('id');
    }

    public function updatedSucursalId($val): void
    {
        if (! $val) {
            $this->almacenId = null;
            return;
        }

        $this->almacenId = \App\Models\Almacen::where('sucursal_id', $val)
            ->where('departamento_id', 2)
            ->where('activo', 1)
            ->where('clave', \App\Models\Almacen::CLAVE_PISO)
            ->value('id')
            ?? \App\Models\Almacen::where('sucursal_id', $val)
            ->where('departamento_id', 2)
            ->where('activo', 1)
            ->value('id');
    }

    // ── Búsqueda de Equipo por número de serie ────────────────────────────────
    public function buscarEquipo(): void
    {
        $this->errorBusqueda = '';
        $serial = trim($this->busquedaSerial);

        if (! $serial) return;

        $equipo = Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)
            ->where('estatus_area', Equipo::AREA_DISPONIBLE_VENTA)
            ->when($this->sucursalId, fn ($q) => $q->where('sucursal_id', $this->sucursalId))
            ->where(function ($q) use ($serial) {
                $q->whereRaw('UPPER(numero_serie) = ?', [strtoupper($serial)]);
                if (is_numeric($serial)) {
                    $q->orWhere('id', (int) $serial);
                }
            })
            ->first();

        if (! $equipo) {
            $this->errorBusqueda = "No se encontró un equipo disponible con: {$serial}";
            return;
        }

        // Verificar que no esté ya en el carrito
        foreach ($this->carrito as $item) {
            if ($item['tipo'] === 'equipo' && $item['id'] === $equipo->id) {
                $this->errorBusqueda = "El equipo {$equipo->numero_serie} ya está en el carrito.";
                return;
            }
        }

        $this->carrito[] = [
            'tipo'              => 'equipo',
            'id'                => $equipo->id,
            'tipo_equipo'       => $equipo->tipo_equipo,
            'lote_id'           => $equipo->lote_id,
            'lote_nombre'       => $equipo->lote_nombre,
            'requiere_cargador' => $equipo->requiere_cargador_externo,
            'nombre'            => "{$equipo->marca} {$equipo->modelo}",
            'detalle'           => $equipo->numero_serie,
            'cantidad'          => 1,
            'precio'            => $equipo->despachoVentasActivo?->precio_sugerido ?? 0,
            'precio_manual'     => false,
            'cargador_id'       => null,
            'cargador_serie'    => null,
        ];

        $this->busquedaSerial = '';
    }

    // ── Búsqueda de Productos por nombre/SKU ──────────────────────────────────
    public function updatedBusquedaProducto(): void
    {
        $termino = trim($this->busquedaProducto);

        if (strlen($termino) < 2) {
            $this->resultadosProducto = [];
            return;
        }

        $this->resultadosProducto = Producto::activos()
            ->buscar($termino)
            ->select('id', 'nombre', 'marca', 'sku', 'precio_venta')
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function agregarProducto(int $productoId, int $cantidad = 1): void
    {
        $producto = Producto::findOrFail($productoId);

        // Si ya está en el carrito, incrementar cantidad
        foreach ($this->carrito as &$item) {
            if ($item['tipo'] === 'producto' && $item['id'] === $producto->id) {
                $item['cantidad'] += $cantidad;
                $this->busquedaProducto  = '';
                $this->resultadosProducto = [];
                return;
            }
        }

        $this->carrito[] = [
            'tipo'     => 'producto',
            'id'       => $producto->id,
            'nombre'   => $producto->nombre,
            'detalle'  => $producto->marca ?? $producto->sku ?? '',
            'cantidad' => $cantidad,
            'precio'   => $producto->precio_venta ?? 0,
            'precio_manual' => false,
        ];

        $this->busquedaProducto  = '';
        $this->resultadosProducto = [];
    }

    #[Computed]
    public function cargadoresDisponibles()
    {
        return \App\Models\Cargador::where('estatus', \App\Enums\CargadorEstatus::DISPONIBLE)
            ->get(['id', 'serie', 'marca', 'punta', 'voltaje', 'amperaje', 'costo']);
    }

    // ── Edición de carrito ────────────────────────────────────────────────────
    public function cambiarCantidad(int $index, int $delta): void
    {
        if (! isset($this->carrito[$index])) return;

        $nueva = $this->carrito[$index]['cantidad'] + $delta;

        if ($this->carrito[$index]['tipo'] === 'equipo') return; // siempre 1

        if ($nueva < 1) {
            $this->quitarItem($index);
            return;
        }

        $this->carrito[$index]['cantidad'] = $nueva;
    }

    public function actualizarPrecio(int $index, string $valor): void
    {
        if (! isset($this->carrito[$index])) return;
        $this->carrito[$index]['precio']        = max(0, (float) $valor);
        $this->carrito[$index]['precio_manual']  = true;
    }

    public function quitarItem(int $index): void
    {
        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito);
    }

    public function alternarRequiereCargador(int $index): void
    {
        if (isset($this->carrito[$index]) && $this->carrito[$index]['tipo'] === 'equipo') {
            $nuevoEstado = !($this->carrito[$index]['requiere_cargador'] ?? false);
            $this->carrito[$index]['requiere_cargador'] = $nuevoEstado;
            if (!$nuevoEstado) {
                $this->carrito[$index]['cargador_id'] = null;
                $this->carrito[$index]['cargador_serie'] = null;
            }
        }
    }

    public function limpiarCarrito(): void
    {
        $this->carrito          = [];
        $this->errorBusqueda    = '';
        $this->busquedaSerial   = '';
        $this->busquedaProducto = '';
        $this->resultadosProducto = [];
        $this->descuento        = 0;
        $this->valorDescuento   = 0;
        $this->tipoDescuento    = '$';
        $this->tasaIva          = 0;
        $this->aplicaIva        = false;
    }

    // ── Clientes ─────────────────────────────────────────────────────────────
    public function updatedBusquedaClienteModal(): void
    {
        if (strlen($this->busquedaClienteModal) < 2) {
            $this->resultadosClienteModal = [];
            return;
        }

        $term = '%' . $this->busquedaClienteModal . '%';
        $this->resultadosClienteModal = \App\Models\Cliente::where('activo', true)
            ->where(function ($q) use ($term) {
                $q->where('nombres', 'like', $term)
                  ->orWhere('apellidos', 'like', $term)
                  ->orWhere('razon_social', 'like', $term)
                  ->orWhere('telefono', 'like', $term);
            })
            ->take(10)
            ->get()
            ->toArray();
    }

    public function seleccionarCliente($id): void
    {
        $this->clienteId = $id;
        $this->modalCliente = false;
        $this->busquedaClienteModal = '';
        $this->resultadosClienteModal = [];
    }

    public function guardarNuevoCliente(): void
    {
        $this->validate([
            'nuevoClienteTipo' => 'required|in:FISICA,MORAL',
            'nuevoClienteNombres' => 'required_if:nuevoClienteTipo,FISICA|nullable|string|max:255',
            'nuevoClienteRazonSocial' => 'required_if:nuevoClienteTipo,MORAL|nullable|string|max:255',
            'nuevoClienteTelefono' => 'nullable|string|max:20',
        ], [
            'nuevoClienteNombres.required_if' => 'El nombre es obligatorio.',
            'nuevoClienteRazonSocial.required_if' => 'La razón social es obligatoria.',
        ]);

        $cliente = \App\Models\Cliente::create([
            'tipo_persona' => $this->nuevoClienteTipo,
            'nombres' => $this->nuevoClienteTipo === 'FISICA' ? $this->nuevoClienteNombres : null,
            'apellidos' => $this->nuevoClienteTipo === 'FISICA' ? $this->nuevoClienteApellidos : null,
            'razon_social' => $this->nuevoClienteTipo === 'MORAL' ? $this->nuevoClienteRazonSocial : null,
            'rfc' => $this->nuevoClienteRfc ?: null,
            'regimen_fiscal' => $this->nuevoClienteRegimen ?: null,
            'telefono' => $this->nuevoClienteTelefono ?: null,
            'correo' => $this->nuevoClienteCorreo ?: null,
            'como_se_entero' => $this->nuevoClienteComoSeEntero ?: null,
            'vendedor_id' => $this->nuevoClienteVendedorId ?: null,
            'pais' => $this->nuevoClientePais ?: 'México',
            'estado' => $this->nuevoClienteEstado ?: null,
            'municipio' => $this->nuevoClienteMunicipio ?: null,
            'localidad' => $this->nuevoClienteLocalidad ?: null,
            'calle' => $this->nuevoClienteCalle ?: null,
            'colonia' => $this->nuevoClienteColonia ?: null,
            'codigo_postal' => $this->nuevoClienteCp ?: null,
            'no_ext' => $this->nuevoClienteNoExt ?: null,
            'no_int' => $this->nuevoClienteNoInt ?: null,
            'codigo_colonia' => $this->nuevoClienteCodigoColonia ?: null,
            'codigo_localidad' => $this->nuevoClienteCodigoLocalidad ?: null,
            'activo' => true,
        ]);

        $this->clienteId = $cliente->id;
        $this->modoCrearCliente = false;
        $this->modalCliente = false;
        
        $this->nuevoClienteTipo = 'FISICA';
        $this->nuevoClienteNombres = '';
        $this->nuevoClienteApellidos = '';
        $this->nuevoClienteRazonSocial = '';
        $this->nuevoClienteRfc = '';
        $this->nuevoClienteRegimen = '';
        $this->nuevoClienteCp = '';
        $this->nuevoClienteTelefono = '';
        $this->nuevoClienteCorreo = '';
        $this->nuevoClienteComoSeEntero = '';
        $this->nuevoClienteVendedorId = null;
        $this->nuevoClientePais = 'México';
        $this->nuevoClienteEstado = '';
        $this->nuevoClienteMunicipio = '';
        $this->nuevoClienteLocalidad = '';
        $this->nuevoClienteCalle = '';
        $this->nuevoClienteColonia = '';
        $this->nuevoClienteNoExt = '';
        $this->nuevoClienteNoInt = '';
        $this->nuevoClienteCodigoColonia = '';
        $this->nuevoClienteCodigoLocalidad = '';
        $this->busquedaClienteModal = '';
        $this->resultadosClienteModal = [];
    }

    // ── Totales (computed) ────────────────────────────────────────────────────
    public function getSubtotalProperty(): float
    {
        return (float) collect($this->carrito)
            ->sum(fn ($i) => (float)$i['precio'] * (int)$i['cantidad']);
    }

    public function getDescuentoMontoProperty(): float
    {
        $subtotal = $this->subtotal;
        $val = (float) ($this->valorDescuento ?: $this->descuento ?: 0);

        if ($val <= 0 || $subtotal <= 0) {
            return 0.00;
        }

        if ($this->tipoDescuento === '%') {
            return round(($subtotal * min(100, $val)) / 100, 2);
        }

        return min($subtotal, $val);
    }

    public function getIvaProperty(): float
    {
        $tasa = (float) ($this->tasaIva ?? 0);
        if ($tasa <= 0) {
            return 0.00;
        }

        $base = max(0, $this->subtotal - $this->descuentoMonto);
        return round($base * ($tasa / 100), 2);
    }

    public function getTotalProperty(): float
    {
        $base = max(0, $this->subtotal - $this->descuentoMonto);
        return $base + $this->iva;
    }

    public function updatedValorDescuento($value = null): void
    {
        $this->valorDescuento = max(0, (float) ($value ?? $this->valorDescuento ?? 0));
        $this->descuento = $this->descuentoMonto;
    }

    public function updatedDescuento($value = null): void
    {
        $this->valorDescuento = max(0, (float) ($value ?? $this->descuento ?? 0));
        $this->descuento = $this->descuentoMonto;
    }

    public function updatedTipoDescuento(): void
    {
        $this->descuento = $this->descuentoMonto;
    }

    public function updatedTasaIva(): void
    {
        $this->aplicaIva = ((float) $this->tasaIva) > 0;
    }

    // ── Cobrar ────────────────────────────────────────────────────────────────
    public function abrirModalPago(): void
    {
        abort_unless(auth()->user()?->tienePermiso('ventas.pos.cobrar'), 403);

        if (empty($this->carrito)) {
            $this->errorBusqueda = 'El carrito está vacío.';
            return;
        }

        // 1. Validar cargadores / eliminadores según requerimiento de energía
        foreach ($this->carrito as $index => $item) {
            if ($item['tipo'] === 'equipo') {
                $requiere = $item['requiere_cargador'] ?? false;

                if ($requiere && empty($item['cargador_id'])) {
                    $esMini = str_contains(strtoupper($item['nombre'] ?? ''), 'MICRO')
                           || str_contains(strtoupper($item['nombre'] ?? ''), 'TINY')
                           || str_contains(strtoupper($item['nombre'] ?? ''), 'MINI')
                           || in_array(strtoupper(trim($item['tipo_equipo'] ?? '')), ['ESCRITORIO', 'DESKTOP', 'CPU', 'MINI PC'], true);
                    $etiqueta = $esMini ? 'eliminador externo' : 'cargador';

                    $this->addError('general', "Debes escanear y asignar un {$etiqueta} para el equipo {$item['nombre']} antes de cobrar.");
                    return;
                }
            }
        }

        // 2. Regla de Oro Financiera: Combos de Computadora de Escritorio + Monitor deben pertenecer al MISMO LOTE
        $equiposEscritorio = [];
        $monitores = [];

        foreach ($this->carrito as $item) {
            if ($item['tipo'] === 'equipo') {
                $tipoEq = strtoupper(trim($item['tipo_equipo'] ?? ''));
                if (in_array($tipoEq, ['ESCRITORIO', 'DESKTOP', 'CPU', 'PC'], true)) {
                    $equiposEscritorio[] = $item;
                } elseif ($tipoEq === 'MONITOR' || str_contains(strtoupper($item['nombre'] ?? ''), 'MONITOR')) {
                    $monitores[] = $item;
                }
            } elseif ($item['tipo'] === 'producto') {
                if (str_contains(strtoupper($item['nombre'] ?? ''), 'MONITOR') || str_contains(strtoupper($item['detalle'] ?? ''), 'MONITOR')) {
                    $monitores[] = $item;
                }
            }
        }

        if (!empty($equiposEscritorio) && !empty($monitores)) {
            foreach ($equiposEscritorio as $pc) {
                if (!empty($pc['lote_id'])) {
                    foreach ($monitores as $mon) {
                        if (!empty($mon['lote_id']) && $mon['lote_id'] !== $pc['lote_id']) {
                            $this->addError('general', "Incompatibilidad de Lote en Combo: El equipo de escritorio {$pc['nombre']} (" . ($pc['lote_nombre'] ?? "Lote #{$pc['lote_id']}") . ") y el monitor {$mon['nombre']} pertenecen a lotes de compra distintos. Para mantener la garantía y costeo de combo, deben provenir del mismo lote.");
                            return;
                        }
                    }
                }
            }
        }

        $this->modalPago = true;
    }

    public function procesarVenta(): void
    {
        abort_unless(auth()->user()?->tienePermiso('ventas.pos.cobrar'), 403);

        $user = auth()->user();

        $corteActual = \App\Models\CorteCaja::where('user_id', $user->id)->where('estatus', 'ABIERTA')->first();
        if (!$corteActual) {
            $this->dispatch('toast', type: 'error', message: 'No puedes realizar ventas sin abrir tu turno en Cortes de Caja.');
            return;
        }

        // Determinar almacén del vendedor
        $almacenId = $this->almacenId
            ?? $user->sucursal?->almacenesDeVentas()->value('id')
            ?? \App\Models\Almacen::where('departamento_id', 2)->value('id');

        abort_unless($almacenId, 422, 'No tienes un almacén de ventas asignado.');

        $almacenObj = \App\Models\Almacen::find($almacenId);
        $sucursalId = $almacenObj?->sucursal_id ?? $user->sucursal_id ?? $corteActual->sucursal_id ?? null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $almacenId, $sucursalId, $corteActual) {
            // Crear cabecera de venta
            $venta = Venta::create([
                'folio'           => Venta::generarFolio(),
                'cliente_id'      => $this->clienteId ?: null,
                'vendedor_id'     => $user->id,
                'almacen_id'      => $almacenId,
                'sucursal_id'     => $sucursalId,
                'corte_caja_id'   => $corteActual->id,
                'metodo_pago'     => $this->metodoPago,
                'subtotal'        => $this->subtotal,
                'tipo_descuento'  => $this->tipoDescuento === '%' ? 'PORCENTAJE' : 'MONTO',
                'valor_descuento' => (float) ($this->valorDescuento ?: $this->descuento ?: 0),
                'descuento'       => $this->descuentoMonto,
                'tasa_iva'        => (float) ($this->tasaIva ?? 0),
                'aplica_iva'      => ((float) ($this->tasaIva ?? 0)) > 0,
                'iva'             => $this->iva,
                'total'           => $this->total,
                'estatus'         => Venta::ESTATUS_PENDIENTE,
                'notas'           => $this->notas ?: null,
            ]);

            // Crear detalles y asignar cargadores si aplica
            foreach ($this->carrito as $item) {
                $venta->detalles()->create([
                    'vendible_type'   => $item['tipo'] === 'equipo' ? Equipo::class : Producto::class,
                    'vendible_id'     => $item['id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal'        => $item['precio'] * $item['cantidad'],
                ]);

                // Si es un equipo y se seleccionó un cargador, casarlo y marcarlo como vendido
                if ($item['tipo'] === 'equipo' && !empty($item['cargador_id'])) {
                    $cargador = \App\Models\Cargador::find($item['cargador_id']);
                    if ($cargador) {
                        $cargador->update([
                            'equipo_id' => $item['id'],
                            'estatus'   => \App\Enums\CargadorEstatus::VENDIDO,
                        ]);
                        \App\Services\CargadorTraceService::log($cargador, 'ASIGNADO_VENTA', "Casado con equipo vendido en Venta #" . $venta->folio);
                    }
                }
            }

            // Completar venta (mueve stock, marca equipo como VENDIDO, etc.)
            app(VentaService::class)->completar($venta);

            $this->folioVenta  = $venta->folio;
        });

        $this->modalPago   = false;
        $this->modalExito  = true;
        $this->limpiarCarrito();
    }

    public function nuevaVenta(): void
    {
        $this->modalExito     = false;
        $this->folioVenta     = '';
        $this->clienteId      = null;
        $this->notas          = '';
        $this->metodoPago     = 'EFECTIVO';
        $this->descuento      = 0;
        $this->valorDescuento = 0;
        $this->tipoDescuento  = '$';
        $this->tasaIva        = 0;
        $this->aplicaIva      = false;
    }

    // ── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        $clienteSeleccionado = null;
        if ($this->clienteId) {
            $clienteSeleccionado = \App\Models\Cliente::find($this->clienteId);
        }

        return view('livewire.ventas.punto-de-venta', [
            'metodosPago' => Venta::METODOS_PAGO,
            'sucursales' => \App\Models\Sucursal::where('activo', true)->get(),
            'almacenes' => \App\Models\Almacen::where('departamento_id', 2)
                ->where('activo', 1)
                ->when($this->sucursalId, fn ($q) => $q->where('sucursal_id', $this->sucursalId))
                ->get(),
            'clienteSeleccionado' => $clienteSeleccionado,
            'opcionesRegimen' => \App\Models\Cliente::opcionesRegimenFiscal(),
            'opcionesUsoCfdi' => \App\Models\Cliente::opcionesUsoCfdi(),
            'opcionesComoSeEntero' => \App\Models\Cliente::opcionesComoSeEntero(),
        ]);
    }
}
