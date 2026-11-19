<?php

use App\Http\Controllers\AfterLoginRedirectController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Inventario\GestionSolicitudesPiezas;
use App\Livewire\Inventario\SolicitudesPiezas;
use App\Livewire\Preparacion\Calidad\GestionCalidad;
use App\Livewire\Preparacion\Equipos\Asignaciones;
use App\Livewire\Preparacion\Garantias\GestionGarantias;
use App\Livewire\Preparacion\Equipos\EditarEquipo;
use App\Livewire\Preparacion\Equipos\MiTrabajo;
use App\Livewire\Preparacion\Equipos\RegistrarEquipo;
use App\Livewire\Preparacion\Inventario\GestionInventario;
use App\Livewire\Preparacion\Inventario\InventarioListo;
use App\Livewire\Preparacion\Inventario\ResumenInventario;
use App\Livewire\Preparacion\Inventario\Transferencias;
use App\Livewire\Preparacion\Inventario\TransferenciasCrear;
use App\Livewire\Preparacion\Lotes\EditarLote;
use App\Livewire\Preparacion\Lotes\ListaLotes;

use App\Livewire\Preparacion\Inventario\DespachoVentasCrear;
use App\Livewire\Preparacion\Inventario\DespachosVentas;
use App\Livewire\Ventas\Dashboard as VentasDashboard;
use App\Livewire\Ventas\DespachosEntrada;
use App\Livewire\Ventas\Clientes;
use App\Livewire\Ventas\Productos;
use App\Livewire\Ventas\PuntoDeVenta;
use App\Models\Equipo;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('auth.login'));




use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;






Route::get('/ticket-completo', function () {
    try {
        $connector = new WindowsPrintConnector("smb://127.0.0.1/POS-800");
        $printer = new Printer($connector);

        // Función auxiliar para alinear columnas: Cantidad (4), Descripción (22), Importe (10)
        // El ancho estándar de una impresora térmica de 80mm es de 42 a 48 caracteres.
        $formatearLinea = function ($cant, $desc, $importe) {
            $descTruncada = substr($desc, 0, 22); // Corta textos largos para no desalinear
            return sprintf("%-4s %-22s %10s\n", $cant, $descTruncada, $importe);
        };

        // 1. ENCABEZADO
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2); // Texto al doble de tamaño
        $printer->text("TECNOBYTE ERP\n");
        $printer->setTextSize(1, 1); // Regresar al tamaño normal
        $printer->text("Sucursal Querétaro, Qro.\n");
        $printer->text("RFC: XAXX010101000\n");
        $printer->text("------------------------------------------\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Folio: VENTA-001042\n");
        $printer->text("Fecha: " . date('Y-m-d H:i:s') . "\n");
        $printer->text("Cajero: Ala Guzman\n");
        $printer->text("Destino: Preparación\n");
        $printer->text("------------------------------------------\n");

        // 2. CUERPO (10 Artículos)
        $printer->setEmphasis(true);
        $printer->text(sprintf("%-4s %-22s %10s\n", "CANT", "DESCRIPCION", "IMPORTE"));
        $printer->setEmphasis(false);
        $printer->text("------------------------------------------\n");

        $printer->text($formatearLinea("1", "Monitor Gaming 24 pulg", "$2,500.00"));
        $printer->text($formatearLinea("2", "Memoria RAM DDR4 16GB", "$1,400.00"));
        $printer->text($formatearLinea("1", "SSD NVMe 1TB Kingston", "$1,100.00"));
        $printer->text($formatearLinea("1", "Teclado Mecánico RGB", "$850.00"));
        $printer->text($formatearLinea("1", "Mouse Inalámbrico", "$350.00"));
        $printer->text($formatearLinea("3", "Cable de red Cat6 2m", "$150.00"));
        $printer->text($formatearLinea("1", "Switch TP-Link Omada", "$1,200.00"));
        $printer->text($formatearLinea("2", "Pasta Térmica Arctic", "$400.00"));
        $printer->text($formatearLinea("1", "Fuente de Poder 650W", "$950.00"));
        $printer->text($formatearLinea("1", "Gabinete ATX Cristal", "$1,300.00"));

        $printer->text("------------------------------------------\n");

        // 3. TOTALES
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text("Subtotal: $8,793.10\n");
        $printer->text("IVA (16%): $1,406.90\n");
        $printer->setEmphasis(true);
        $printer->setTextSize(1, 2); // Hacer el total más alto visualmente
        $printer->text("TOTAL: $10,200.00\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text("\n");
        
        $printer->text("Pago en: Tarjeta de Crédito\n");
        $printer->text("Autorización: 458902\n");
        
        // 4. PIE DE PÁGINA
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("------------------------------------------\n");
        $printer->text("¡Gracias por tu compra!\n");
        $printer->text("Conserva este ticket para garantias.\n");
        
        // Código QR para facilitar búsqueda del folio en la base de datos
        $printer->feed(1);
        $printer->qrCode("VENTA-001042", Printer::QR_ECLEVEL_L, 5);
        
        $printer->feed(3); // Avanzar papel suficiente para que no corte el texto
        $printer->cut();
        $printer->close();

        return "Ticket completo enviado con éxito.";
    } catch (Exception $e) {
        return "Error al imprimir: " . $e->getMessage();
    }
});

/*
|--------------------------------------------------------------------------
| CORE GLOBAL (auth + role_depto)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role_depto'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD INTELIGENTE
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', AfterLoginRedirectController::class)->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | PERFIL
    |--------------------------------------------------------------------------
    */
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/perfil/editar', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ETIQUETA TSPL
    |--------------------------------------------------------------------------
    */
    Route::get('/equipos/{equipo}/etiqueta-comando', function (Equipo $equipo) {

        $titulo = strtoupper(trim(($equipo->marca ?? '').' '.($equipo->modelo ?? '')));
        $serie = $equipo->numero_serie ?? (string) $equipo->id;

        $titulo = preg_replace('/[^A-Z0-9 \-\_]/i', '', $titulo);
        $serie = preg_replace('/[^A-Z0-9\-\_]/i', '', $serie);

        $lines = [];
        $lines[] = 'SIZE 77 mm,50 mm';
        $lines[] = 'GAP 2 mm,0';
        $lines[] = 'CLS';
        $lines[] = 'DENSITY 8';
        $lines[] = 'SPEED 4';
        $lines[] = 'DIRECTION 0';
        $lines[] = 'REFERENCE 0,0';
        $lines[] = 'TEXT 40,60,"0",0,2,2,"'.$titulo.'"';
        $lines[] = 'TEXT 40,120,"0",0,1,1,"SERIE: '.$serie.'"';
        $lines[] = 'BARCODE 140,200,"128",60,1,0,2,2,"'.$serie.'"';
        $lines[] = 'TEXT 170,270,"0",0,1,1,"*'.$serie.'*"';
        $lines[] = 'PRINT 1,1';

        return response(implode("\r\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=US-ASCII');

    })->middleware('permiso:prep.equipos.imprimir')
        ->name('equipos.etiqueta.comando');

    /*
    |--------------------------------------------------------------------------
    | INVENTARIO (GLOBAL)
    |--------------------------------------------------------------------------
    */



    /*
    |--------------------------------------------------------------------------
    | EQUIPOS (GLOBAL)
    |--------------------------------------------------------------------------
    */



    /*
    |--------------------------------------------------------------------------


    /*
    |--------------------------------------------------------------------------
    | TRANSFERENCIAS (GLOBAL)
    |--------------------------------------------------------------------------
    */
    Route::prefix('transferencias')->group(function () {
        Route::get('/', Transferencias::class)
            ->name('transferencias.index');

        Route::get('/crear', TransferenciasCrear::class)
            ->middleware('permiso:prep.transferencias.crear|ventas.transferencias.crear')
            ->name('transferencias.crear');
    });

    // Alias global para compatibilidad
    Route::get('/inventario/transferencias/crear', fn () => redirect()->route('transferencias.crear'))
        ->name('inventario.prep.transferencias.crear');

    /*
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | PREPARACION (PROTEGIDO POR DEPARTAMENTO)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['departamento:PREPARACION'])->group(function () {

        // Lotes
        Route::prefix('lotes')->group(function () {
            Route::get('/', \App\Livewire\Preparacion\Lotes\ListaLotes::class)
                ->middleware('permiso:prep.lotes.ver')
                ->name('preparacion.lotes');
            
            Route::get('/{lote}/editar', \App\Livewire\Preparacion\Lotes\EditarLote::class)
                ->middleware('permiso:prep.lotes.gestion')
                ->name('preparacion.lotes.editar');
            Route::get('/{lote}/procesar', \App\Livewire\Preparacion\Lotes\VerLote::class)
                ->middleware('permiso:prep.lotes.gestion')
                ->name('preparacion.lotes.ver');

        });

        
        // Direccion / CEO
    Route::prefix('ceo')->group(function () {
        Route::get('/dashboard', \App\Livewire\CEO\Dashboard::class)->name('ceo.dashboard');
        Route::get('/aprobaciones', \App\Livewire\CEO\AprobacionesCompra::class)
            ->middleware('permiso:prep.compras.aprobar_ceo')
            ->name('ceo.aprobaciones');
        Route::get('/aprobaciones/{compraId}', \App\Livewire\CEO\RevisionCompra::class)
            ->middleware('permiso:prep.compras.aprobar_ceo')
            ->name('ceo.aprobaciones.revision');
    });

        // Inventario (Preparación)
    Route::prefix('inventario')->group(function () {
        Route::get('/listo', InventarioListo::class)
            ->middleware('permiso:prep.inventario.ver')
            ->name('inventario.listo');

        Route::get('/gestion', GestionInventario::class)
            ->middleware('permiso:prep.inventario.gestion')
            ->name('inventario.gestion');

        Route::get('/transferencias', fn () => redirect()->route('transferencias.index'))
            ->name('inventario.transferencias');





    });

        // Equipos (Preparación)
    Route::prefix('equipos')->group(function () {

        Route::get('/registrar', RegistrarEquipo::class)
            ->middleware('permiso:prep.equipos.crear')
            ->name('equipos.create');

        Route::get('/caracteristicas', ResumenInventario::class)
            ->middleware('permiso:prep.equipos.ver')
            ->name('equipos.caracteristicas');

        Route::get('/{equipo}/editar', EditarEquipo::class)
            ->middleware('permiso:prep.equipos.editar')
            ->name('equipos.editar');
    });

        Route::get('/preparacion/dashboard', Dashboard::class)
            ->middleware('permiso:modulo.preparacion')
            ->name('preparacion.dashboard');

        // ── NUEVO ──
        Route::get('/preparacion/mi-trabajo', MiTrabajo::class)
            ->middleware('permiso:prep.equipos.ver')
            ->name('preparacion.mi-trabajo');

        Route::get('/preparacion/asignaciones', Asignaciones::class)
            ->middleware('permiso:prep.inventario.gestion')
            ->name('preparacion.asignaciones');

        Route::get('/preparacion/calidad', GestionCalidad::class)
            ->middleware('permiso:prep.calidad.validar')
            ->name('preparacion.calidad');

        Route::get('/preparacion/calidad/escaner', \App\Livewire\Preparacion\Calidad\EscanerCalidad::class)
            ->middleware('permiso:prep.calidad.validar')
            ->name('preparacion.calidad.escaner');


        
        Route::get('/preparacion/compras', \App\Livewire\Compras\OrdenesCompra::class)
            ->middleware('permiso:prep.compras.ver')
            ->name('preparacion.compras');

        // Ruta legacy — redirige al nuevo módulo
        Route::redirect('/inventario/compras', '/preparacion/compras')
            ->name('inventario.compras');

        Route::get('/inventario/cargadores', \App\Livewire\Preparacion\Inventario\GestionCargadores::class)
            ->middleware('permiso:prep.inventario.gestion')
            ->name('inventario.cargadores');

        // Vista de solicitudes de piezas (técnico — filtrada por auth)
        Route::get('/inventario/piezas/solicitudes', SolicitudesPiezas::class)
            ->name('inventario.piezas.solicitudes')
            ->middleware('permiso:prep.equipos.ver');

        // Vista de gestión de solicitudes (líder/gerente)
        Route::get('/inventario/piezas/gestionar', GestionSolicitudesPiezas::class)
            ->name('inventario.solicitudes.gestionar')
            ->middleware('permiso:prep.inventario.gestion');


        // Despachos a Ventas
        Route::prefix('preparacion/despachos-ventas')->group(function () {
            Route::get('/', DespachosVentas::class)
                ->middleware('permiso:prep.despachos.ver')
                ->name('preparacion.despachos-ventas');

            Route::get('/crear', DespachoVentasCrear::class)
                ->middleware('permiso:prep.despachos.crear')
                ->name('preparacion.despachos-ventas.crear');
        });

        // Estadísticas
        Route::get('/preparacion/estadisticas-equipos', \App\Livewire\Preparacion\Dashboard\EstadisticasEquipos::class)
            ->middleware('permiso:prep.inventario.gestion')
            ->name('preparacion.estadisticas-equipos');

        // Garantías Externas
        Route::get('/preparacion/garantias', GestionGarantias::class)
            ->middleware('permiso:prep.garantias.ver')
            ->name('preparacion.garantias');
    });

    /*
    |--------------------------------------------------------------------------
    | SISTEMA
    |--------------------------------------------------------------------------
    */

    Route::prefix('sistema')
        ->middleware('permiso:modulo.sistema')
        ->group(function () {

            Route::get('/usuarios', [UserController::class, 'index'])
                ->middleware('permiso:sistema.usuarios.ver')
                ->name('users.index');

            Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])
                ->middleware('permiso:sistema.usuarios.editar')
                ->name('users.edit');

            Route::patch('/usuarios/{user}', [UserController::class, 'update'])
                ->middleware('permiso:sistema.usuarios.editar')
                ->name('users.update');

            Route::get('/usuarios/crear', [RegisteredUserController::class, 'create'])
                ->middleware('permiso:sistema.usuarios.crear')
                ->name('register');

            Route::post('/usuarios/crear', [RegisteredUserController::class, 'store'])
                ->middleware('permiso:sistema.usuarios.crear');

            Route::get('/avisos', \App\Livewire\Avisos\Index::class)
                ->middleware('permiso:sistema.avisos.ver')
                ->name('avisos.index');

            Route::patch('/usuarios/{user}/baja', [UserController::class, 'baja'])
                ->name('usuarios.baja')
                ->middleware('permiso:sistema.usuarios.editar');
        });

    /*
    |--------------------------------------------------------------------------
    | OTROS DASHBOARDS
    |--------------------------------------------------------------------------
    */



    // ──────────────────────────────────────────────────────────────────
    // MÓDULO COMPRAS (Global)
    // ──────────────────────────────────────────────────────────────────
    Route::prefix('compras')->group(function () {
        Route::get('/catalogo', \App\Livewire\Compras\CatalogoGlobal::class)
            ->name('compras.catalogo');

        Route::get('/{compraId}/recepcion', \App\Livewire\Compras\CentroRecepcion::class)
            ->middleware('permiso:prep.compras.ver|ventas.compras.ver')
            ->name('compras.recepcion');
    });

    // ──────────────────────────────────────────────────────────────────
    // MÓDULO VENTAS (namespace /ventas)
    // ──────────────────────────────────────────────────────────────────
    Route::prefix('ventas')
        ->middleware(['departamento:VENTAS', 'permiso:modulo.ventas'])
        ->group(function () {
            Route::get('/dashboard', VentasDashboard::class)
                ->name('ventas.dashboard');

            Route::get('/despachos', DespachosEntrada::class)
                ->middleware('permiso:ventas.despachos.ver')
                ->name('ventas.despachos');

            Route::get('/clientes', Clientes::class)
                ->middleware('permiso:ventas.clientes.ver')
                ->name('ventas.clientes');



            Route::get('/compras', \App\Livewire\Compras\OrdenesCompra::class)
                ->middleware('permiso:ventas.compras.ver')
                ->name('ventas.compras');
                
            Route::prefix('entradas')->group(function () {
                Route::get('/', \App\Livewire\Ventas\Entradas\HistorialEntradas::class)
                    ->name('ventas.entradas.historial');
                    
                Route::get('/registrar', \App\Livewire\Ventas\Entradas\CrearEntrada::class)
                    ->name('ventas.entradas.crear');
            });

            Route::get('/pos', PuntoDeVenta::class)
                ->middleware('permiso:ventas.venta.crear')
                ->name('ventas.pos');

            Route::get('/historial', \App\Livewire\Ventas\HistorialVentas::class)
                ->middleware('permiso:ventas.historial.ver')
                ->name('ventas.historial');

            Route::get('/garantias', \App\Livewire\Ventas\Garantias\GarantiasIndex::class)
                ->middleware('permiso:ventas.garantias.ver')
                ->name('ventas.garantias');
                
            Route::get('/caja', \App\Livewire\Ventas\Caja\CajaIndex::class)
                ->middleware('permiso:ventas.caja.ver')
                ->name('ventas.caja');

            // Administración de Inventario
            Route::prefix('inventario')->group(function () {
                Route::get('/almacenes', \App\Livewire\Ventas\Inventario\AdministracionAlmacenes::class)
                    // ->middleware('permiso:ventas.almacenes.gestion')
                    ->name('ventas.inventario.almacenes');
                
                Route::get('/', \App\Livewire\Ventas\Inventario\GestionInventario::class)
                    ->name('ventas.inventario.index');
                    
                Route::get('/transferencias', fn () => redirect()->route('transferencias.index'))
                    ->name('ventas.inventario.transferencias');
            });
        });

    Route::view('/soporte/dashboard', 'soporte.dashboard')
        ->middleware('permiso:modulo.soporte')
        ->name('soporte.dashboard');

    Route::view('/rrhh/dashboard', 'rrhh.dashboard')
        ->middleware('permiso:modulo.rrhh')
        ->name('rrhh.dashboard');

    Route::view('/administracion/dashboard', 'administracion.dashboard')
        ->middleware('permiso:modulo.administracion')
        ->name('administracion.dashboard');

});

require __DIR__.'/auth.php';
