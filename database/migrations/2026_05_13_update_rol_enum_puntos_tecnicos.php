<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: Cambiar enum manualmente a través de MODIFY
        // Primero añadimos los nuevos valores y mantenemos los antiguos
        DB::statement("ALTER TABLE puntos_tecnicos MODIFY COLUMN rol_en_equipo ENUM('COMPLETO', 'INICIO_PIEZA', 'PIEZA_PENDIENTE', 'TERMINO_PIEZA', 'PIEZA_COMPLETADA', 'GARANTIA', 'DESPIECE')");

        // Cambiar los datos
        DB::statement("UPDATE puntos_tecnicos SET rol_en_equipo = 'PIEZA_PENDIENTE' WHERE rol_en_equipo = 'INICIO_PIEZA'");
        DB::statement("UPDATE puntos_tecnicos SET rol_en_equipo = 'PIEZA_COMPLETADA' WHERE rol_en_equipo = 'TERMINO_PIEZA'");

        // Finalmente, solo dejamos los nuevos valores
        DB::statement("ALTER TABLE puntos_tecnicos MODIFY COLUMN rol_en_equipo ENUM('COMPLETO', 'PIEZA_PENDIENTE', 'PIEZA_COMPLETADA', 'GARANTIA', 'DESPIECE')");
    }

    public function down(): void
    {
        // Revertir: permitir valores antiguos
        DB::statement("ALTER TABLE puntos_tecnicos MODIFY COLUMN rol_en_equipo ENUM('COMPLETO', 'INICIO_PIEZA', 'PIEZA_PENDIENTE', 'TERMINO_PIEZA', 'PIEZA_COMPLETADA', 'GARANTIA', 'DESPIECE')");

        DB::statement("UPDATE puntos_tecnicos SET rol_en_equipo = 'INICIO_PIEZA' WHERE rol_en_equipo = 'PIEZA_PENDIENTE'");
        DB::statement("UPDATE puntos_tecnicos SET rol_en_equipo = 'TERMINO_PIEZA' WHERE rol_en_equipo = 'PIEZA_COMPLETADA'");

        DB::statement("ALTER TABLE puntos_tecnicos MODIFY COLUMN rol_en_equipo ENUM('COMPLETO', 'INICIO_PIEZA', 'TERMINO_PIEZA', 'GARANTIA')");
    }
};
