<?php

namespace App\Console\Commands;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CheckAuth extends Command
{
    protected $signature = 'check:auth';

    protected $description = 'Diagnosticar login del usuario calidad';

    public function handle()
    {
        $email = 'calidad@tecnobytemx.com';
        $user = User::withoutGlobalScopes()->where('email', $email)->first();
        if (! $user) {
            $this->error('NO_USER');

            return 1;
        }

        $this->line('USER_ID: '.$user->id);
        $this->line('PASSWORD_COLUMN: '.$user->getAuthPassword());
        $this->line('HASH_CHECK: '.(Hash::check('12345678', $user->getAuthPassword()) ? 'true' : 'false'));
        $this->line('IS_ACTIVE: '.($user->is_active ? 'true' : 'false'));
        $this->line('DELETED_AT: '.($user->deleted_at ?? 'null'));
        $this->line('ROLE_ID: '.$user->role_id);
        $role = Roles::find($user->role_id);
        $this->line('ROLE_SLUG: '.($role?->slug ?? 'null'));
        $this->line('PERM_MODULO_PREPARACION: '.($user->tienePermiso('modulo.preparacion') ? 'true' : 'false'));
        $this->line('PERM_PREP_CALIDAD_VALIDAR: '.($user->tienePermiso('prep.calidad.validar') ? 'true' : 'false'));

        $attempt = Auth::attempt(['email' => $email, 'password' => '12345678']);
        $this->line('AUTH_ATTEMPT: '.($attempt ? 'true' : 'false'));

        $this->line('AUTH_GUARD_DEFAULT: '.config('auth.defaults.guard'));
        $this->line('AUTH_PROVIDER_DEFAULT: '.config('auth.guards.'.config('auth.defaults.guard').'.provider'));

        return 0;
    }
}
