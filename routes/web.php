<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Equipos\RegistrarEquipo;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware('auth')->group(function () {
    Route::get('/equipos/registrar', function () {
        return view('equipos.registrar');
    })->name('equipos.create');
});

});


Route::middleware(['auth', 'role:ceo,admin'])->group(function () {
    
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('usuarios', [UserController::class, 'index'])->name('users.index');


    Route::get('usuarios/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    

    Route::patch('usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    
  

});






require __DIR__.'/auth.php';