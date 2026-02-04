<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\PermisoMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; 

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'         => \App\Http\Middleware\CheckRole::class,
            'onlyAdminCeo' => \App\Http\Middleware\OnlyAdminCeo::class,
            'role_depto'   => \App\Http\Middleware\EnsureUserHasRoleAndDepartamento::class,

            // ✅ NUEVO
            'permiso'      => \App\Http\Middleware\PermisoMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

    })->create();
