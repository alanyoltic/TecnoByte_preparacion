<?php

namespace App\Livewire\Ventas;

use App\Models\Equipo;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\VentaService;
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
    public float $descuento = 0;

    // ── Resultados de búsqueda de producto ───────────────────────────────────
    public array $resultadosProducto = [];

    // ── Modal pago y Venta ───────────────────────────────────────────────────
    public bool $modalExito   = false;
    public string $folioVenta = '';

    // ── Modal Clientes ───────────────────────────────────────────────────────
    public bool $modalCliente = false;
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
        abort_unless(auth()->user()?->tienePermiso('ventas.pos.ver'), 403);
        $this->sucursalId = auth()->user()->sucursal_id;
        $this->almacenId = auth()->user()->almacen_id; // Default al almacen del usuario (Ventas)
    }

    // ── Búsqueda de Equipo por número de serie ────────────────────────────────
    public function buscarEquipo(): void
    {
        $this->errorBusqueda = '';
        $serial = trim($this->busquedaSerial);

        if (! $serial) return;

        $equipo = Equipo::where('estatus_ciclo', Equipo::CICLO_VENTAS)
            ->where('estatus_area', Equipo::AREA_DISPONIBLE_VENTA)
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
            'tipo'           => 'equipo',
            'id'             => $equipo->id,
            'nombre'         => "{$equipo->marca} {$equipo->modelo}",
            'detalle'        => $equipo->numero_serie,
            'cantidad'       => 1,
            'precio'         => $equipo->despachoVentasActivo?->precio_sugerido ?? 0,
            'precio_manual'  => false,
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

    public function limpiarCarrito(): void
    {
        $this->carrito          = [];
        $this->errorBusqueda    = '';
        $this->busquedaSerial   = '';
        $this->busquedaProducto = '';
        $this->resultadosProducto = [];
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
        return collect($this->carrito)
            ->sum(fn ($i) => $i['precio'] * $i['cantidad']);
    }

    public function getIvaProperty(): float
    {
        // Asumiendo que los precios en el carrito no incluyen IVA, o si lo incluyen es otro cálculo.
        // Simularemos un cálculo directo sobre el subtotal descontado para coincidir con la vista.
        $sub = $this->subtotal - (float)$this->descuento;
        return $sub > 0 ? $sub * 0.16 : 0;
    }

    public function getTotalProperty(): float
    {
        $sub = $this->subtotal - (float)$this->descuento;
        return $sub > 0 ? $sub + $this->iva : 0;
    }

    public function updatedDescuento(): void
    {
        $this->descuento = max(0, (float)$this->descuento);
    }

    // ── Cobrar ────────────────────────────────────────────────────────────────
    public function abrirModalPago(): void
    {
        abort_unless(auth()->user()?->tienePermiso('ventas.pos.cobrar'), 403);

        if (empty($this->carrito)) {
            $this->errorBusqueda = 'El carrito está vacío.';
            return;
        }

        $this->modalPago = true;
    }

    public function procesarVenta(): void
    {
        abort_unless(auth()->user()?->tienePermiso('ventas.pos.cobrar'), 403);

        $user = auth()->user();

        // Determinar almacén del vendedor (usamos el almacén de la sucursal de ventas del usuario)
        $almacenId = $user->almacen_id
            ?? optional($user->sucursal)->almacenesDeVentas()->value('id');

        abort_unless($almacenId, 422, 'No tienes un almacén de ventas asignado.');

        // Crear cabecera de venta
        $venta = Venta::create([
            'folio'       => Venta::generarFolio(),
            'cliente_id'  => $this->clienteId ?: null,
            'vendedor_id' => $user->id,
            'almacen_id'  => $almacenId,
            'metodo_pago' => $this->metodoPago,
            'estatus'     => Venta::ESTATUS_PENDIENTE,
            'notas'       => $this->notas ?: null,
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

            // Si es un equipo y se seleccionó un cargador, casarlo
            if ($item['tipo'] === 'equipo' && !empty($item['cargador_id'])) {
                $cargador = \App\Models\Cargador::find($item['cargador_id']);
                if ($cargador && $cargador->estatus === \App\Enums\CargadorEstatus::DISPONIBLE) {
                    $cargador->update([
                        'equipo_id' => $item['id'],
                        'estatus' => \App\Enums\CargadorEstatus::VENDIDO,
                    ]);
                    \App\Services\CargadorTraceService::log($cargador, 'ASIGNADO_VENTA', "Casado con equipo vendido en Venta #" . $venta->folio);
                }
            }
        }

        // Completar venta (mueve stock, marca equipo como VENDIDO, etc.)
        app(VentaService::class)->completar($venta);

        $this->folioVenta  = $venta->folio;
        $this->modalPago   = false;
        $this->modalExito  = true;
        $this->limpiarCarrito();
    }

    public function nuevaVenta(): void
    {
        $this->modalExito = false;
        $this->folioVenta = '';
        $this->clienteId  = null;
        $this->notas      = '';
        $this->metodoPago = 'EFECTIVO';
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
            'almacenes' => \App\Models\Almacen::where('departamento_id', 2)->where('activo', 1)->get(),
            'clienteSeleccionado' => $clienteSeleccionado,
            'opcionesRegimen' => \App\Models\Cliente::opcionesRegimenFiscal(),
            'opcionesUsoCfdi' => \App\Models\Cliente::opcionesUsoCfdi(),
            'opcionesComoSeEntero' => \App\Models\Cliente::opcionesComoSeEntero(),
        ]);
    }
}
