<?php

namespace Database\Seeders\Disabled;

use App\Models\User;
use App\Models\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ceoRoleId = Roles::where('slug', 'ceo')->value('id');

        // Si por alguna razón aún no existe el rol, no trona
        if (! $ceoRoleId) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'ceo@tecnobyte.com'],
            [
                'nombre'            => 'Admin',
                'apellido_paterno'  => 'CEO',
                'password'          => Hash::make('password'),
                'role_id'           => $ceoRoleId,
                'is_active'         => true,
            ]
        );
    }
}
