<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRoleAndDepartamento
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->role_id || ! $user->departamento_id) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->with('error', 'Tu usuario no tiene rol o departamento asignado. Contacta a administración.');
        }

        return $next($request);
    }
}
