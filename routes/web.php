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

// ===============================
// RUTAS PÚBLICAS
// ===============================
Route::get('/', fn () => view('auth.login'));


// ========================================================================
// ============================== CORE (GLOBAL) ===========================
// ========================================================================

Route::middleware(['auth', 'role_depto'])->group(function () {

    // ===========================
    // PUERTA INTELIGENTE (GLOBAL)
    // ===========================
    Route::get('/dashboard', AfterLoginRedirectController::class)->name('dashboard');

    // ===========================
    // PERFIL (GLOBAL)
    // ===========================
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/perfil/editar', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===========================
    // ETIQUETA TSPL (GLOBAL)
    // ===========================
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


    // ====================================================================
    // =========================== PREPARACION =============================
    // ====================================================================
    Route::prefix('preparacion')
        ->middleware('permiso:modulo.preparacion')
        ->group(function () {

            // Dashboard real de preparación (para redirección / acceso directo)
            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('preparacion.dashboard');

            // ---------------------------
            // EQUIPOS
            // ---------------------------
            Route::prefix('equipos')->group(function () {

                // Registrar / Pendientes
                Route::get('/registrar', fn () => view('equipos.registrar'))
                    ->middleware('permiso:prep.equipos.ver')
                    ->name('equipos.create'); // mantengo tu name para no romper vistas

                Route::get('/piezas-pendientes', fn () => view('equipos.pendientes-piezas'))
                    ->middleware('permiso:prep.equipos.ver')
                    ->name('equipos.piezas-pendientes'); // mantengo tu name

                // Editar equipo (antes era /equipos/admin/{equipo}/editar)
                Route::get('/{equipo}/editar', function (Equipo $equipo) {
                    return view('equipos.editar-equipo', compact('equipo'));
                })
                ->middleware('permiso:prep.equipos.editar')
                ->name('equipos.editar'); // mantengo tu name
            });

            // ---------------------------
            // INVENTARIO
            // ---------------------------
            Route::prefix('inventario')->group(function () {

                Route::get('/listo', fn () => view('inventario.listo'))
                    ->middleware('permiso:prep.inventario.ver')
                    ->name('inventario.listo'); // mantengo tu name

                // Gestión inventario (antes era /inventario/admin/gestion)
                Route::get('/gestion', fn () => view('inventario.gestion-inventario'))
                    ->middleware('permiso:prep.inventario.gestion')
                    ->name('inventario.gestion'); // mantengo tu name
            });

            // ---------------------------
            // LOTES
            // ---------------------------
            Route::prefix('lotes')->group(function () {

                Route::get('/registrar', [LoteController::class, 'registrar'])
                    ->middleware('permiso:prep.lotes.gestion')
                    ->name('lotes.registrar'); // mantengo tu name

                Route::get('/editar', fn () => view('lotes.listalotes'))
                    ->middleware('permiso:prep.lotes.ver')
                    ->name('lotes.editar'); // mantengo tu name

                Route::get('/{lote}/editar', function (Lote $lote) {
                    return view('lotes.editarlote', compact('lote'));
                })
                ->middleware('permiso:prep.lotes.gestion')
                ->name('lotes.edit'); // mantengo tu name
            });
        });


    // ====================================================================
    // ============================= SISTEMA ===============================
    // (Compartido: Usuarios / Avisos, con scope por zona en controller)
    // ====================================================================
    Route::prefix('sistema')
        ->middleware('permiso:modulo.sistema')
        ->group(function () {

            // USUARIOS
            Route::get('/usuarios', [UserController::class, 'index'])
                ->middleware('permiso:sistema.usuarios.ver')
                ->name('users.index'); // mantengo tu name viejo

            Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])
                ->middleware('permiso:sistema.usuarios.editar')
                ->name('users.edit'); // mantengo tu name viejo

            Route::patch('/usuarios/{user}', [UserController::class, 'update'])
                ->middleware('permiso:sistema.usuarios.editar')
                ->name('users.update'); // mantengo tu name viejo

            // Crear usuario (antes /register)
            Route::get('/usuarios/crear', [RegisteredUserController::class, 'create'])
                ->middleware('permiso:sistema.usuarios.crear')
                ->name('register'); // mantengo tu name viejo

            Route::post('/usuarios/crear', [RegisteredUserController::class, 'store'])
                ->middleware('permiso:sistema.usuarios.crear');

            // AVISOS
            Route::get('/avisos', \App\Livewire\Avisos\Index::class)
                ->middleware('permiso:sistema.avisos.ver')
                ->name('avisos.index'); // mantengo tu name viejo
        });


    // ====================================================================
    // ========================== OTRAS AREAS ==============================
    // Por ahora solo dashboards (cuando exista el modulo real, se migra igual)
    // ====================================================================

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
