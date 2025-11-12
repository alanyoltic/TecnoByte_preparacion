<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  ...string  $roles  Los roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Esta línea ahora buscará la función 'role()'
        // que acabamos de arreglar en el User.php
        foreach ($roles as $role) {
            if ($request->user()->role?->slug === $role) {
                return $next($request);
            }
        }

        abort(403, 'Acción no autorizada.');
    }
}