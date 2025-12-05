<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Equipos\RegistrarEquipo;
use App\Livewire\Inventario\GestionEquipos;
use App\Livewire\Inventario\EditarEquipo;
use App\Livewire\Inventario\EditarInventario;
use App\Models\Equipo;


// ===============================
// RUTAS PÚBLICAS
// ===============================

Route::get('/', function () {
    return view('login');
});

// ===============================
// RUTAS AUTENTICADAS (CUALQUIER USUARIO LOGUEADO)
// ===============================

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Perfil del usuario
Route::middleware('auth')->group(function () {
    // NUEVA: vista principal de perfil (solo lectura)
    Route::get('/perfil', [ProfileController::class, 'show'])
        ->name('profile.show');

Route::get('/perfil/editar', function () {
    return 'ESTOY EN /perfil/editar (profile.edit)';
})->name('profile.edit');


    // Guardar cambios de mi perfil
    Route::patch('/perfil', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Si quieres conservar el destroy de Breeze:
    Route::delete('/perfil', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');



        
Route::middleware('auth')->group(function () {

    // ...tus otras rutas...

    Route::get('/equipos/{equipo}/etiqueta-comando', function (Equipo $equipo) {

        // ============================
        // DATOS BASE DESDE LA BD
        // ============================
        $titulo = strtoupper(trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')));
        $serie  = $equipo->numero_serie ?? (string) $equipo->id;

        // Limpieza básica por si hubiera caracteres raros
        $titulo = preg_replace('/[^A-Z0-9 \-\_]/i', '', $titulo);
        $serie  = preg_replace('/[^A-Z0-9\-\_]/i', '', $serie);

        // ============================
        // ARMAMOS EL TSPL A MANO
        // ============================
        $lines = [];

        $lines[] = 'SIZE 77 mm,50 mm';
        $lines[] = 'GAP 2 mm,0';
        $lines[] = 'CLS';
        $lines[] = 'DENSITY 8';
        $lines[] = 'SPEED 4';
        $lines[] = 'DIRECTION 0';
        $lines[] = 'REFERENCE 0,0';

        // TÍTULO (MARCA + MODELO)
        $lines[] = 'TEXT 40,60,"0",0,2,2,"' . $titulo . '"';

        // SERIE EN TEXTO
        $lines[] = 'TEXT 40,120,"0",0,1,1,"SERIE: ' . $serie . '"';

        // CÓDIGO DE BARRAS + TEXTO DEBAJO
        $lines[] = 'BARCODE 140,200,"128",60,1,0,2,2,"' . $serie . '"';
        $lines[] = 'TEXT 170,270,"0",0,1,1,"*' . $serie . '*"';

        $lines[] = 'PRINT 1,1';

        $tspl = implode("\r\n", $lines);

        return response($tspl, 200)
            ->header('Content-Type', 'text/plain; charset=US-ASCII');
    })->name('equipos.etiqueta.comando');

});


 
});

    /// edicion de perfil 
    
    // Editar perfil (formulario para que cada usuario cambie SUS datos)
    Route::get('/perfil/editar', [ProfileController::class, 'edit'])
        ->name('profile.edit');






    // ===========================
    // Equipos (solo usuarios logueados)
    // ===========================
    Route::prefix('equipos')->group(function () {

        // Registrar equipo
        Route::get('/registrar', function () {
            return view('equipos.registrar');
            // Si quisieras montar el componente Livewire directo:
            // return \Livewire\Livewire::mount(RegistrarEquipo::class);
        })->name('equipos.create');

        // Equipos con piezas pendientes
        Route::get('/piezas-pendientes', function () {
            return view('equipos.pendientes-piezas');
        })->name('equipos.piezas-pendientes');
    });

    // Inventario
    Route::get('/inventario/listo', function () {
        return view('inventario.listo');
    })->name('inventario.listo');
});

// ===============================
// RUTAS SOLO CEO / ADMIN
// ===============================

Route::middleware(['auth', 'onlyAdminCeo'])->group(function ()  {

    // Registro de nuevos usuarios (solo CEO/Admin)
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Gestión de usuarios
    Route::get('usuarios', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('usuarios/{user}/edit', [UserController::class, 'edit'])
        ->name('users.edit');

    Route::patch('usuarios/{user}', [UserController::class, 'update'])
        ->name('users.update');



    // Edición individual de un equipo
    Route::get('/inventario/admin/equipos/{equipo}', function (Equipo $equipo) {
        return view('inventario.editar-equipo', compact('equipo'));
    })->middleware('auth')->name('inventario.equipos.editar');

    Route::get('/inventario/admin/gestion', function () {
            return view('inventario.gestion-inventario');
        })->name('inventario.gestion');


});



require __DIR__.'/auth.php';
