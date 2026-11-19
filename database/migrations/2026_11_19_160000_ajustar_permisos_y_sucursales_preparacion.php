<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permisosMap = DB::table('permisos')->pluck('id', 'slug');

        $asignarPermisos = function(int $rolId, array $slugs) use ($permisosMap) {
            foreach ($slugs as $slug) {
                if (isset($permisosMap[$slug])) {
                    DB::table('rol_permiso')->insertOrIgnore([
                        'rol_id'     => $rolId,
                        'permiso_id' => $permisosMap[$slug],
                    ]);
                }
            }
        };

        // 1. Líder de Área (Rol 10): Garantías, Despachos a Ventas y Ver Calidad
        $asignarPermisos(10, [
            'prep.garantias.ver',
            'prep.garantias.gestionar',
            'prep.despachos.ver',
            'prep.despachos.crear',
            'prep.calidad.ver',
        ]);

        // 2. Calidad (Rol 15): Ver Inventario y Ver Equipos (necesario para Inventario Listo y detalles)
        $asignarPermisos(15, [
            'prep.inventario.ver',
            'prep.equipos.ver',
        ]);

        // 3. Gerente (Rol 2): Ver y Validar Calidad a nivel de Rol
        $asignarPermisos(2, [
            'prep.calidad.ver',
            'prep.calidad.validar',
            'prep.despachos.cancelar',
        ]);

        // 4. Admin del Sistema (Rol 12): Garantías, Despachos y Calidad
        $asignarPermisos(12, [
            'prep.garantias.ver',
            'prep.garantias.gestionar',
            'prep.despachos.ver',
            'prep.despachos.crear',
            'prep.despachos.cancelar',
            'prep.calidad.ver',
            'prep.calidad.validar',
        ]);

        // 5. Asignar sucursal Querétaro Corregidora (ID: 1) a colaboradores de Preparación que tenían sucursal_id NULL
        DB::table('users')
            ->where('departamento_id', 1)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => 1]);

        // 6. Asignar puesto Técnico (ID: 1) a técnicos y líderes de Preparación sin puesto_id
        DB::table('users')
            ->where('departamento_id', 1)
            ->whereIn('role_id', [3, 10, 15]) // Técnico, Líder, Calidad
            ->whereNull('puesto_id')
            ->update(['puesto_id' => 1]);

        // 7. Asignar puesto Administración (ID: 5) a gerentes de Preparación sin puesto_id
        DB::table('users')
            ->where('departamento_id', 1)
            ->where('role_id', 2)
            ->whereNull('puesto_id')
            ->update(['puesto_id' => 5]);

        // 8. Asignar puesto Dirección (ID: 6) al CEO
        DB::table('users')
            ->where('role_id', 1)
            ->whereNull('puesto_id')
            ->update(['puesto_id' => 6]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo en producción
    }
};
