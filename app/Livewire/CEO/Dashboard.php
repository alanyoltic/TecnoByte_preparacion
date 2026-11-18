<?php

namespace App\Livewire\CEO;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Equipo;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\GarantiaCliente;
use Illuminate\Support\Carbon;

#[Layout('layouts.app', ['pageTitle' => 'Gerencia General'])]
class Dashboard extends Component
{
    public $filtroSucursal = 'todas';
    public $filtroMes;
    public $filtroAnio;

    public function mount()
    {
        if (!$this->filtroMes) {
            $this->filtroMes = Carbon::now()->month;
        }
        if (!$this->filtroAnio) {
            $this->filtroAnio = Carbon::now()->year;
        }
    }

    public function updated($property)
    {
        // Disparador reactivo al cambiar cualquier filtro
    }

    public function render()
    {
        $year = (int) ($this->filtroAnio ?: Carbon::now()->year);
        $mes  = (int) ($this->filtroMes ?: Carbon::now()->month);

        $inicio = Carbon::createFromDate($year, $mes, 1)->startOfMonth();
        $fin    = Carbon::createFromDate($year, $mes, 1)->endOfMonth();

        // 1. VENTAS TOTALES (Datos Reales de la BD)
        $queryVentas = Venta::completadas()->whereBetween('created_at', [$inicio, $fin]);

        if ($this->filtroSucursal !== 'todas') {
            $queryVentas->where('sucursal_id', $this->filtroSucursal);
        }

        $ventaTotal     = (float) $queryVentas->sum('total');
        $cantidadVentas = $queryVentas->count();
        $ticketPromedio = $cantidadVentas > 0 ? $ventaTotal / $cantidadVentas : 0.0;

        // 2. DETALLES DE VENTA EN EL PERIODO
        $detalles = VentaDetalle::whereHas('venta', function ($q) use ($inicio, $fin) {
            $q->completadas()->whereBetween('created_at', [$inicio, $fin]);
            if ($this->filtroSucursal !== 'todas') {
                $q->where('sucursal_id', $this->filtroSucursal);
            }
        })->with('vendible')->get();

        // 3. EQUIPOS (Unidades y Facturación Real)
        $detallesEquipos       = $detalles->where('vendible_type', Equipo::class);
        $unidadesEquipos       = (int) $detallesEquipos->sum('cantidad');
        $ventaEquiposMonetario = (float) $detallesEquipos->sum('subtotal');

        // 4. ACCESORIOS Y OTROS PRODUCTOS COMERCIALES
        $detallesProductos = $detalles->where('vendible_type', Producto::class);
        $ventaAccesorios   = (float) $detallesProductos->sum('subtotal');

        // 5. GARANTIAS REPORTADAS EN EL PERIODO
        $queryGarantias = GarantiaCliente::whereBetween('created_at', [$inicio, $fin]);
        if ($this->filtroSucursal !== 'todas') {
            $queryGarantias->whereHas('almacenOrigen', function ($q) {
                $q->where('sucursal_id', $this->filtroSucursal);
            });
        }
        $garantiasUnidades = $queryGarantias->count();

        // 6. DESEMPEÑO POR SUCURSALES (Dinámico según base de datos)
        $sucursalesList = Sucursal::activas()->orderBy('nombre')->get();
        $sucursalesData = [];

        $totalGlobalMes = (float) Venta::completadas()->whereBetween('created_at', [$inicio, $fin])->sum('total');

        foreach ($sucursalesList as $suc) {
            $ventasSuc = (float) Venta::completadas()
                ->where('sucursal_id', $suc->id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->sum('total');

            $porcentaje = $totalGlobalMes > 0 ? round(($ventasSuc / $totalGlobalMes) * 100, 1) : 0.0;

            // Tendencia comparada con el mes anterior
            $inicioAnt = (clone $inicio)->subMonth()->startOfMonth();
            $finAnt    = (clone $inicio)->subMonth()->endOfMonth();
            $ventasSucAnt = (float) Venta::completadas()
                ->where('sucursal_id', $suc->id)
                ->whereBetween('created_at', [$inicioAnt, $finAnt])
                ->sum('total');

            if ($ventasSucAnt > 0) {
                $dif = (($ventasSuc - $ventasSucAnt) / $ventasSucAnt) * 100;
                $tendencia = ($dif >= 0 ? '+' : '') . round($dif, 1) . '%';
            } else {
                $tendencia = $ventasSuc > 0 ? '+100%' : '0%';
            }

            $sucursalesData[] = [
                'id'         => $suc->id,
                'nombre'     => $suc->nombre,
                'ventas'     => $ventasSuc,
                'porcentaje' => $porcentaje,
                'tendencia'  => $tendencia,
            ];
        }

        // Si el usuario seleccionó una sucursal específica, mostramos esa primero o filtrada
        if ($this->filtroSucursal !== 'todas') {
            $sucursalesFiltradas = array_values(array_filter($sucursalesData, fn($s) => (string)$s['id'] === (string)$this->filtroSucursal));
            if (!empty($sucursalesFiltradas)) {
                $sucursalesData = $sucursalesFiltradas;
            }
        }

        // 7. SEGMENTOS ECONÓMICOS POR PRECIO DE EQUIPOS
        $segmentos = [
            'Económico' => 0.0,
            'Medio'     => 0.0,
            'Premium'   => 0.0,
        ];

        foreach ($detallesEquipos as $eq) {
            $precio = (float) $eq->precio_unitario;
            if ($precio < 6000) {
                $segmentos['Económico'] += (float) $eq->subtotal;
            } elseif ($precio <= 14000) {
                $segmentos['Medio'] += (float) $eq->subtotal;
            } else {
                $segmentos['Premium'] += (float) $eq->subtotal;
            }
        }

        $mejorSegmento = (max($segmentos) > 0) ? array_keys($segmentos, max($segmentos))[0] : 'Sin ventas';

        // 8. DESGLOSE POR LÍNEA DE NEGOCIO REAL
        $desgloseTipos = [
            'Laptops'       => 0.0,
            'Desktop / PCs' => 0.0,
            'Monitores'     => 0.0,
            'Accesorios'    => 0.0,
            'Servicios'     => 0.0,
        ];

        foreach ($detalles as $d) {
            if ($d->vendible_type === Equipo::class) {
                $tipoEq = strtoupper($d->vendible?->tipo_equipo ?? '');
                if (in_array($tipoEq, ['LAPTOP', '2 EN 1', 'GAMER', 'PORTATIL'])) {
                    $desgloseTipos['Laptops'] += (float) $d->subtotal;
                } elseif (in_array($tipoEq, ['ESCRITORIO', 'ALL IN ONE', 'DESKTOP', 'CPU', 'TINY', 'MINI PC', 'ENSAMBLE'])) {
                    $desgloseTipos['Desktop / PCs'] += (float) $d->subtotal;
                } elseif (in_array($tipoEq, ['MONITOR', 'PANTALLA'])) {
                    $desgloseTipos['Monitores'] += (float) $d->subtotal;
                } else {
                    $desgloseTipos['Desktop / PCs'] += (float) $d->subtotal;
                }
            } elseif ($d->vendible_type === Producto::class) {
                $cat = strtolower($d->vendible?->categoria ?? '');
                $nom = strtolower($d->vendible?->nombre ?? '');
                if (str_contains($cat, 'monitor') || str_contains($nom, 'monitor')) {
                    $desgloseTipos['Monitores'] += (float) $d->subtotal;
                } elseif (str_contains($cat, 'servicio') || str_contains($nom, 'servicio') || str_contains($nom, 'mantenimiento')) {
                    $desgloseTipos['Servicios'] += (float) $d->subtotal;
                } else {
                    $desgloseTipos['Accesorios'] += (float) $d->subtotal;
                }
            }
        }

        // 9. TIPO DE COMPROBANTE (Factura vs Remisión en unidades reales)
        $facturasUnidades   = (clone $queryVentas)->where('aplica_iva', true)->count();
        $remisionesUnidades = (clone $queryVentas)->where(function ($q) {
            $q->where('aplica_iva', false)->orWhereNull('aplica_iva');
        })->count();

        // 10. PROYECCIONES DE VENTA (Calculadas matemáticamente según ritmo real)
        $hoy = Carbon::now();
        $esMesActual = ($mes === $hoy->month && $year === $hoy->year);
        $diasEnMes   = $inicio->daysInMonth;

        if ($esMesActual) {
            $diasTranscurridos = max(1, $hoy->day);
            $proyeccionDiaria  = $ventaTotal > 0 ? ($ventaTotal / $diasTranscurridos) : 0.0;
            $proyeccionSemanal = $proyeccionDiaria * 7;
            $proyeccionMensual = $proyeccionDiaria * $diasEnMes;
        } else {
            $proyeccionDiaria  = $diasEnMes > 0 ? ($ventaTotal / $diasEnMes) : 0.0;
            $proyeccionSemanal = $proyeccionDiaria * 7;
            $proyeccionMensual = $ventaTotal;
        }

        return view('livewire.c-e-o.dashboard', [
            'ventaTotal'            => $ventaTotal,
            'cantidadVentas'        => $cantidadVentas,
            'unidadesEquipos'       => $unidadesEquipos,
            'ventaEquiposMonetario' => $ventaEquiposMonetario,
            'ventaAccesorios'       => $ventaAccesorios,
            'garantiasUnidades'     => $garantiasUnidades,
            'ticketPromedio'        => $ticketPromedio,
            'sucursales'            => $sucursalesData,
            'sucursalesSelect'      => $sucursalesList,
            'segmentos'             => $segmentos,
            'mejorSegmento'         => $mejorSegmento,
            'desgloseTipos'         => $desgloseTipos,
            'facturasUnidades'      => $facturasUnidades,
            'remisionesUnidades'    => $remisionesUnidades,
            'proyeccionDiaria'      => $proyeccionDiaria,
            'proyeccionSemanal'     => $proyeccionSemanal,
            'proyeccionMensual'     => $proyeccionMensual,
        ]);
    }
}
