<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AfterLoginRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $roleSlug = strtoupper((string) optional($user->role)->slug);
        $deptoKey = strtoupper((string) optional($user->departamento)->clave);

        /*
        |--------------------------------------------------------------------------
        | IMPORTANTE
        |--------------------------------------------------------------------------
        | Ya NO existe "admin global".
        | El CEO entra por su departamento como cualquier otro usuario.
        | Si en el futuro quieres un dashboard global, se agrega explícito.
        */

        logger()->info('AFTER_LOGIN_REDIRECT', [
            'user_id'        => $user->id,
            'email'          => $user->email,
            'role_slug'      => $roleSlug,
            'departamento_id' => $user->departamento_id,
            'depto_clave'    => optional($user->departamento)->clave,
            'depto_nombre'   => optional($user->departamento)->nombre,
        ]);

        // Redirección estricta por departamento
        return match ($deptoKey) {
            'PREPARACION'     => redirect()->route('preparacion.dashboard'),
            'VENTAS'          => redirect()->route('ventas.dashboard'),
            'SOPORTE'         => redirect()->route('soporte.dashboard'),
            'RRHH'            => redirect()->route('rrhh.dashboard'),
            'ADMIN',
            'ADMINISTRACION'  => redirect()->route('administracion.dashboard'),

            default => abort(
                403,
                'Departamento no reconocido o sin dashboard configurado.'
            ),
        };
    }
}
