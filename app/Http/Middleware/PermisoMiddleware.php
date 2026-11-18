<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermisoMiddleware
{
    public function handle(Request $request, Closure $next, string $slug)
    {
        $user = $request->user();

        $slugs = explode('|', $slug);
        $ok = false;
        if ($user && method_exists($user, 'tienePermiso')) {
            foreach ($slugs as $s) {
                if ($user->tienePermiso(trim($s))) {
                    $ok = true;
                    break;
                }
            }
        }

        Log::info('PERMISO_DEBUG', [
            'path' => $request->path(),
            'user_id' => $user?->id,
            'role_slug' => optional($user?->role)->slug,
            'required' => $slug,
            'tienePermiso_exists' => $user ? method_exists($user, 'tienePermiso') : false,
            'tienePermiso_result' => $ok,
        ]);

        if (! $ok) {
            abort(403);
        }

        return $next($request);
    }
}
