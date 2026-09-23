<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartamento
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$departamentoClaves
     */
    public function handle(Request $request, Closure $next, string ...$departamentoClaves): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $roleSlug = strtolower(optional($user->role)->slug ?? '');

        // CEO y Administradores de sistema tienen acceso global
        if (in_array($roleSlug, ['ceo', 'admin_sistema', 'sistemas'], true)) {
            return $next($request);
        }

        $userDeptoClave = strtoupper(optional($user->departamento)->clave ?? '');

        // Normalizar las claves esperadas
        $expectedClaves = array_map('strtoupper', $departamentoClaves);

        if (in_array($userDeptoClave, $expectedClaves, true)) {
            return $next($request);
        }

        // Si no coincide el departamento, 403 Forbidden
        abort(403, 'No tienes permiso para acceder a este módulo de departamento.');
    }
}
