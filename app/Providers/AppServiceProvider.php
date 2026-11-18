<?php

namespace App\Providers;

use App\Models\Equipo;
use App\Models\User;
use App\Policies\EquipoPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Equipo::class, EquipoPolicy::class);

        // Conectar el sistema de permisos custom con los Gates de Laravel
        Gate::before(function ($user, $ability) {
            if ($user->isAdminCeo()) {
                return true;
            }
            if (method_exists($user, 'tienePermiso') && $user->tienePermiso($ability)) {
                return true;
            }
        });
    }
}
