<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,       // crea/actualiza roles base (idempotente)
            CalidadUserSeeder::class,  // usuario y accesos mínimos de calidad
        ]);
    }
}
