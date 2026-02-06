<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,          // crea/actualiza roles base (idempotente)
            CoreEstructuraSeeder::class, // sucursales, departamentos, almacenes (ya lo tienes)
            PuestosSeeder::class,        // puestos + depto_puestos + backfill users.puesto_id
                       
        ]);
    }
}
