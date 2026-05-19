<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Ceo',                   'slug' => 'ceo'],
            ['nombre' => 'Gerente',               'slug' => 'gerente'],
            ['nombre' => 'Tecnico',               'slug' => 'tecnico'],
            ['nombre' => 'Lider de Area',         'slug' => 'lider'],
            ['nombre' => 'Usuario Personalizado', 'slug' => 'usuario'],
            ['nombre' => 'Admin del Sistema',     'slug' => 'admin_sistema'],
            ['nombre' => 'Sistemas',              'slug' => 'sistemas'],
            ['nombre' => 'Exhibicion',            'slug' => 'exhibicion'],
            ['nombre' => 'Calidad',               'slug' => 'calidad'],
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
