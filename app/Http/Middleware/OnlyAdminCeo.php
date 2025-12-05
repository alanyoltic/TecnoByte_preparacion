<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OnlyAdminCeo
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Aquí definimos los roles permitidos
        $rolesPermitidos = ['admin', 'ceo'];

        // Si el usuario no tiene rol o su rol NO está permitido → 403
        if (!Auth::user()->role || 
            !in_array(Auth::user()->role->slug, $rolesPermitidos)) 
        {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
