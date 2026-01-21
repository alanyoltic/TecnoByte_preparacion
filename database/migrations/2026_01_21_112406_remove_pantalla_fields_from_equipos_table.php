<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Verifica que existan antes de eliminar (extra seguro en prod)
            if (Schema::hasColumn('equipos', 'pantalla_pulgadas')) {
                $table->dropColumn('pantalla_pulgadas');
            }

            if (Schema::hasColumn('equipos', 'pantalla_resolucion')) {
                $table->dropColumn('pantalla_resolucion');
            }

            if (Schema::hasColumn('equipos', 'pantalla_es_touch')) {
                $table->dropColumn('pantalla_es_touch');
            }

            if (Schema::hasColumn('equipos', 'pantalla_tipo')) {
                $table->dropColumn('pantalla_tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Se restauran por si haces rollback
            if (!Schema::hasColumn('equipos', 'pantalla_pulgadas')) {
                $table->string('pantalla_pulgadas', 10)->nullable();
            }

            if (!Schema::hasColumn('equipos', 'pantalla_resolucion')) {
                $table->string('pantalla_resolucion', 20)->nullable();
            }

            if (!Schema::hasColumn('equipos', 'pantalla_es_touch')) {
                $table->boolean('pantalla_es_touch')->default(false);
            }

            if (!Schema::hasColumn('equipos', 'pantalla_tipo')) {
                $table->string('pantalla_tipo', 20)->nullable();
            }
        });
    }
};
