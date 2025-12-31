<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\LoteController;

use App\Models\Equipo;

// ===============================
// RUTAS PÚBLICAS
// ===============================
Route::get('/', function () {
    return view('auth.login');
});

// ===============================
// RUTAS AUTENTICADAS (CUALQUIER USUARIO LOGUEADO)
// ===============================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ===========================
    // PERFIL
    // ===========================
    Route::get('/perfil', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/perfil/editar', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/perfil', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/perfil', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // ===========================
    // ETIQUETA (TSPL) - requiere login
    // ===========================
    Route::get('/equipos/{equipo}/etiqueta-comando', function (Equipo $equipo) {
        
$lines[] = 'TEXT 40,30,"0",0,1,1,"ORIGEN: WEB.PHP"';

        $titulo = strtoupper(trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')));
        $serie  = $equipo->numero_serie ?? (string) $equipo->id;

        // Limpieza básica
        $titulo = preg_replace('/[^A-Z0-9 \-\_]/i', '', $titulo);
        $serie  = preg_replace('/[^A-Z0-9\-\_]/i', '', $serie);

        // (Opcional) recorte para evitar desbordamiento en 2,2
        // $MAX = 18;
        // if (mb_strlen($titulo) > $MAX) {
        //     $titulo = mb_substr($titulo, 0, $MAX - 3) . '...';
        // }

        

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

        $tspl = implode("\r\n", $lines);

        return response($tspl, 200)
            ->header('Content-Type', 'text/plain; charset=US-ASCII');

    })->name('equipos.etiqueta.comando');

    // ===========================
    // EQUIPOS
    // ===========================
    Route::prefix('equipos')->group(function () {

        Route::get('/registrar', function () {
            return view('equipos.registrar');
        })->name('equipos.create');

        Route::get('/piezas-pendientes', function () {
            return view('equipos.pendientes-piezas');
        })->name('equipos.piezas-pendientes');
    });

    // ===========================
    // INVENTARIO
    // ===========================
    Route::get('/inventario/listo', function () {
        return view('inventario.listo');
    })->name('inventario.listo');
});

// ===============================
// RUTAS SOLO CEO / ADMIN
// ===============================
Route::middleware(['auth', 'onlyAdminCeo'])->group(function () {

    // Registro de nuevos usuarios (solo CEO/Admin)
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // Gestión de usuarios
    Route::get('/usuarios', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])
        ->name('users.edit');

    Route::patch('/usuarios/{user}', [UserController::class, 'update'])
        ->name('users.update');

    // Inventario admin
    Route::get('/inventario/admin/equipos/{equipo}', function (Equipo $equipo) {
        return view('inventario.editar-equipo', compact('equipo'));
    })->name('inventario.equipos.editar');

    Route::get('/inventario/admin/gestion', function () {
        return view('inventario.gestion-inventario');
    })->name('inventario.gestion');

    // Lotes (CEO/Admin)
    Route::get('/lotes/registrar', [LoteController::class, 'registrar'])
        ->middleware('role:ceo,admin')
        ->name('lotes.registrar');

    Route::get('/lotes/editar', function () {
        return view('lotes.listalotes');
    })->name('lotes.editar');

    Route::get('/lotes/{lote}/editar', function (\App\Models\Lote $lote) {
        return view('lotes.editarlote', compact('lote'));
    })->name('lotes.edit');

    Route::middleware(['auth', 'role:ceo,admin'])->group(function () {
    Route::get('/avisos', \App\Livewire\Avisos\Index::class)->name('avisos.index');
});


});

require __DIR__ . '/auth.php';
