<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Crear tabla clasificaciones_puntos ──────────────────────────
        Schema::create('clasificaciones_puntos', function (Blueprint $table) {
            $table->id();
            $table->char('clave', 1)->unique()->comment('A, B, C, D, E, F');
            $table->string('nombre', 100)->comment('Nombre descriptivo de la clasificación');
            $table->string('descripcion', 255)->nullable()->comment('Qué tipo de equipos aplican');
            $table->decimal('puntos_base', 4, 2)->comment('Puntos que genera un equipo de esta clasificación');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── 2. Insertar catálogo inicial A-F ──────────────────────────────
        DB::table('clasificaciones_puntos')->insert([
            [
                'clave' => 'A',
                'nombre' => 'Básico',
                'descripcion' => 'Equipos de gama baja, preparación sencilla',
                'puntos_base' => 1.0,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'B',
                'nombre' => 'Estándar',
                'descripcion' => 'Equipos de gama media, preparación regular',
                'puntos_base' => 1.3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'C',
                'nombre' => 'Intermedio',
                'descripcion' => 'Equipos con componentes adicionales o configuración especial',
                'puntos_base' => 1.2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'D',
                'nombre' => 'Avanzado',
                'descripcion' => 'Equipos de gama alta, mayor tiempo de preparación',
                'puntos_base' => 1.4,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'E',
                'nombre' => 'Premium',
                'descripcion' => 'Workstations y equipos de alto rendimiento',
                'puntos_base' => 1.6,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'F',
                'nombre' => 'Deshueso',
                'descripcion' => 'Equipos para canibalizar piezas, preparación mínima',
                'puntos_base' => 0.4,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ── 3. Agregar columna clasificacion_puntos_id a equipos ──────────
        Schema::table('equipos', function (Blueprint $table) {
            $table->foreignId('clasificacion_puntos_id')
                ->nullable()
                ->after('tipo_equipo')
                ->constrained('clasificaciones_puntos')
                ->nullOnDelete()
                ->comment('Clasificación A-F para cálculo de puntos del técnico');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['clasificacion_puntos_id']);
            $table->dropColumn('clasificacion_puntos_id');
        });

        Schema::dropIfExists('clasificaciones_puntos');
    }
};
