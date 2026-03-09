<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\AfterLoginRedirectController;

use App\Models\Equipo;
use App\Models\Lote;

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

        $titulo = strtoupper(trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')));
        $serie  = $equipo->numero_serie ?? (string) $equipo->id;

        $titulo = preg_replace('/[^A-Z0-9 \-\_]/i', '', $titulo);
        $serie  = preg_replace('/[^A-Z0-9\-\_]/i', '', $serie);

        $lines = [];
        $lines[] = 'SIZE 77 mm,50 mm';
        $lines[] = 'GAP 2 mm,0';
        $lines[] = 'CLS';
        $lines[] = 'DENSITY 8';
        $lines[] = 'SPEED 4';
        $lines[] = 'DIRECTION 0';
        $lines[] = 'REFERENCE 0,0';
        $lines[] = 'TEXT 40,60,"0",0,2,2,"' . $titulo . '"';
        $lines[] = 'TEXT 40,120,"0",0,1,1,"SERIE: ' . $serie . '"';
        $lines[] = 'BARCODE 140,200,"128",60,1,0,2,2,"' . $serie . '"';
        $lines[] = 'TEXT 170,270,"0",0,1,1,"*' . $serie . '*"';
        $lines[] = 'PRINT 1,1';

        return response(implode("\r\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=US-ASCII');

    })->name('equipos.etiqueta.comando');


    /*
    |--------------------------------------------------------------------------
    | INVENTARIO (GLOBAL)
    |--------------------------------------------------------------------------
    */

    Route::prefix('inventario')->group(function () {

        Route::get('/listo', fn () => view('inventario.listo'))
            ->middleware('permiso:prep.inventario.ver')
            ->name('inventario.listo');

        Route::get('/gestion', fn () => view('inventario.gestion-inventario'))
            ->middleware('permiso:prep.inventario.gestion')
            ->name('inventario.gestion');

        Route::get('/transferencias', fn () => view('inventario.transferencias'))
            ->middleware('permiso:prep.transferencias.ver')
            ->name('inventario.transferencias');
            
        Route::get('/transferencias/crear', fn () => view('inventario.transferencias-crear'))
            ->middleware('permiso:prep.transferencias.crear')
            ->name('inventario.prep.transferencias.crear');



            });


    /*
    |--------------------------------------------------------------------------
    | EQUIPOS (GLOBAL)
    |--------------------------------------------------------------------------
    */

    Route::prefix('equipos')->group(function () {

        Route::get('/registrar', fn () => view('equipos.registrar'))
            ->middleware('permiso:prep.equipos.ver')
            ->name('equipos.create');

        Route::get('/piezas-pendientes', fn () => view('equipos.pendientes-piezas'))
            ->middleware('permiso:prep.equipos.ver')
            ->name('equipos.piezas-pendientes');

        Route::view('/caracteristicas', 'preparacion.inventario.resumen')
            ->middleware('permiso:prep.equipos.ver')
            ->name('equipos.caracteristicas');

        Route::get('/{equipo}/editar', function (Equipo $equipo) {

            $equipo->load([
                'movimientos.desde',
                'movimientos.hacia'
            ]);

            return view('equipos.editar-equipo', compact('equipo'));

        })->middleware('permiso:prep.equipos.editar')
          ->name('equipos.editar');
    });


    /*
    |--------------------------------------------------------------------------
    | LOTES (GLOBAL)
    |--------------------------------------------------------------------------
    */

    Route::prefix('lotes')->group(function () {

        Route::get('/registrar', [LoteController::class, 'registrar'])
            ->middleware('permiso:prep.lotes.gestion')
            ->name('lotes.registrar');

        Route::get('/editar', fn () => view('lotes.listalotes'))
            ->middleware('permiso:prep.lotes.ver')
            ->name('lotes.editar');

        Route::get('/{lote}/editar', function (Lote $lote) {
            return view('lotes.editarlote', compact('lote'));
        })->middleware('permiso:prep.lotes.gestion')
          ->name('lotes.edit');
    });

    
    /*
    |--------------------------------------------------------------------------
    | TRANSFERENCIAS (GLOBAL)
    |--------------------------------------------------------------------------
    */







    /*
    |--------------------------------------------------------------------------
    | PREPARACION (SOLO DASHBOARD)
    |--------------------------------------------------------------------------
    */

    Route::get('/preparacion/dashboard', [DashboardController::class, 'index'])
        ->middleware('permiso:modulo.preparacion')
        ->name('preparacion.dashboard');


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

    Route::view('/ventas/dashboard', 'ventas.dashboard')
        ->middleware('permiso:modulo.ventas')
        ->name('ventas.dashboard');

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

require __DIR__ . '/auth.php';