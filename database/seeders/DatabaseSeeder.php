<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crea los 3 Roles
        $ceoRole = Rol::create(['nombre' => 'CEO', 'slug' => 'ceo']);
        Rol::create(['nombre' => 'Administrador', 'slug' => 'admin']);
        Rol::create(['nombre' => 'Técnico', 'slug' => 'tecnico']);

        // 2. Crea tu primer usuario (el CEO) con los campos nuevos
        User::create([
            'nombre' => 'Admin',
            'apellido_paterno' => 'CEO',
            'email' => 'ceo@tecnobyte.com',
            'password' => Hash::make('password'), // Contraseña: "password"
            'role_id' => $ceoRole->id,
            'is_active' => true,
        ]);
    }
}