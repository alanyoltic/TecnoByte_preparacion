<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalidadUserSeeder extends Seeder
{
    public function run(): void
    {
        $roleId = Roles::where('slug', 'calidad')->value('id');

        if (! $roleId) {
            $this->command->warn("Rol 'calidad' no encontrado — ejecuta RolesSeeder primero.");

            return;
        }

        $permisos = [
            'modulo.preparacion' => 'Acceso al módulo Preparación',
            'modulo.calidad' => 'Acceso al módulo Calidad',
            'prep.calidad.ver' => 'Ver equipos en calidad (Preparación)',
            'prep.calidad.validar' => 'Validar/rechazar equipos (Calidad)',
        ];

        foreach ($permisos as $slug => $descripcion) {
            DB::table('permisos')->updateOrInsert(
                ['slug' => $slug],
                [
                    'descripcion' => $descripcion,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        $permisosIds = DB::table('permisos')
            ->whereIn('slug', array_keys($permisos))
            ->pluck('id', 'slug');

        foreach ($permisosIds as $permisoId) {
            $exists = DB::table('rol_permiso')
                ->where('rol_id', $roleId)
                ->where('permiso_id', $permisoId)
                ->exists();

            if (! $exists) {
                DB::table('rol_permiso')->insert([
                    'rol_id' => $roleId,
                    'permiso_id' => $permisoId,
                ]);
            }
        }

        $attrs = [
            'nombre' => 'Calidad',
            'apellido_paterno' => 'Usuario',
            'password' => '12345678',
            'email_verified_at' => now(),
            'role_id' => $roleId,
            'is_active' => true,
        ];

        User::updateOrCreate(
            ['email' => 'calidad@tecnobytemx.com'],
            $attrs
        );

        $this->command->info('Usuario de calidad creado/actualizado: calidad@tecnobytemx.com');
    }
}
