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
        // Slugs a remover del rol Líder de Área (rol_id = 10)
        $slugsRemover = [
            'prep.despachos.ver',
            'prep.despachos.crear',
            'prep.despachos.cancelar',
            'prep.calidad.ver',
            'prep.calidad.validar',
            'modulo.calidad',
        ];

        $permisoIds = DB::table('permisos')->whereIn('slug', $slugsRemover)->pluck('id');

        DB::table('rol_permiso')
            ->where('rol_id', 10) // Lider de Area
            ->whereIn('permiso_id', $permisoIds)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
