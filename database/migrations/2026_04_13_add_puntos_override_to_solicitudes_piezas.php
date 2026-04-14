<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->decimal('puntos_override', 8, 2)->nullable()->after('notas_respuesta')
                ->comment('Valor en puntos definido por el gerente al asignar la pieza. Si es null, se usa el puntos_base del equipo.');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->dropColumn('puntos_override');
        });
    }
};
