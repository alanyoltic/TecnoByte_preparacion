<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermisoMiddleware
{
    public function handle(Request $request, Closure $next, string $slug)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'tienePermiso') || ! $user->tienePermiso($slug)) {
            abort(403);
        }

        return $next($request);
    }
}
