<?php

use App\Http\Controllers\AfterLoginRedirectController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Inventario\GestionSolicitudesPiezas;
use App\Livewire\Inventario\SolicitudesPiezas;
use App\Livewire\Preparacion\Compras\ComprasPreparacion;
use App\Livewire\Preparacion\Calidad\GestionCalidad;
use App\Livewire\Preparacion\Equipos\Asignaciones;
use App\Livewire\Preparacion\Garantias\GestionGarantias;
use App\Livewire\Preparacion\Equipos\EditarEquipo;
use App\Livewire\Preparacion\Equipos\MiTrabajo;
use App\Livewire\Preparacion\Equipos\RegistrarEquipo;
use App\Livewire\Preparacion\Inventario\CatalogoPiezas;
use App\Livewire\Preparacion\Inventario\ComprasInventario;
use App\Livewire\Preparacion\Inventario\GestionInventario;
use App\Livewire\Preparacion\Inventario\InventarioListo;
use App\Livewire\Preparacion\Inventario\ResumenInventario;
use App\Livewire\Preparacion\Inventario\Transferencias;
use App\Livewire\Preparacion\Inventario\TransferenciasCrear;
use App\Livewire\Preparacion\Lotes\EditarLote;
use App\Livewire\Preparacion\Lotes\ListaLotes;
use App\Livewire\Preparacion\Lotes\RegistrarLote;
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

    /*
    |--------------------------------------------------------------------------
    | PREPARACION (PROTEGIDO POR DEPARTAMENTO)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['departamento:PREPARACION'])->group(function () {

        // Inventario (Preparación)
    Route::prefix('inventario')->group(function () {

        Route::get('/listo', InventarioListo::class)
            ->middleware('permiso:prep.inventario.ver')
            ->name('inventario.listo');

        Route::get('/gestion', GestionInventario::class)
            ->middleware('permiso:prep.inventario.gestion')
            ->name('inventario.gestion');

        Route::get('/transferencias', Transferencias::class)
            ->middleware('permiso:prep.transferencias.ver')
            ->name('inventario.transferencias');

        Route::get('/transferencias/crear', TransferenciasCrear::class)
            ->middleware('permiso:prep.transferencias.crear')
            ->name('inventario.prep.transferencias.crear');

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

        // Catálogo de Equipos
        Route::get('/preparacion/catalogo-equipos', \App\Livewire\Preparacion\CatalogoEquipos::class)
            ->middleware('permiso:prep.inventario.gestion')
            ->name('preparacion.catalogo-equipos');

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
                ->middleware('permiso:ventas.pos.ver')
                ->name('ventas.pos');

            // Administración de Inventario
            Route::prefix('inventario')->group(function () {
                Route::get('/almacenes', \App\Livewire\Ventas\Inventario\AdministracionAlmacenes::class)
                    // ->middleware('permiso:ventas.almacenes.gestion')
                    ->name('ventas.inventario.almacenes');
                
                Route::get('/', \App\Livewire\Ventas\Inventario\GestionInventario::class)
                    ->name('ventas.inventario.index');
                    
                Route::get('/transferencias', \App\Livewire\Ventas\Inventario\GestionTransferencias::class)
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
