<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'CEO',           'slug' => 'ceo'],
            ['nombre' => 'Administrador', 'slug' => 'admin'],
            ['nombre' => 'Tecnico',       'slug' => 'tecnico'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $r['slug']],
                [
                    'nombre'     => $r['nombre'],
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
