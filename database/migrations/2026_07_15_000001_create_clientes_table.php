<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de Clientes del módulo Ventas.
 *
 * Todos los campos son nullable excepto tipo_persona.
 * Diseñada para soportar CFDI 4.0 desde el día uno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // ── Identidad ─────────────────────────────────────────────────
            $table->enum('tipo_persona', ['FISICA', 'MORAL'])->default('FISICA');

            // Persona Física
            $table->string('nombres', 150)->nullable();
            $table->string('apellidos', 150)->nullable();

            // Persona Moral
            $table->string('razon_social', 200)->nullable();

            // Fiscal (aplica a ambos)
            $table->string('rfc', 13)->nullable();
            $table->string('regimen_fiscal', 100)->nullable(); // Ej: "601 General de Ley Personas Morales"
            $table->string('uso_cfdi', 10)->nullable();        // Código SAT: G01, G03, etc.

            // ── Datos Comerciales ─────────────────────────────────────────
            $table->foreignId('vendedor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('como_se_entero', 100)->nullable(); // Referido, Redes, Búsqueda, etc.

            // ── Contacto ──────────────────────────────────────────────────
            $table->string('correo', 180)->nullable();
            $table->string('telefono', 20)->nullable();

            // ── Dirección (CFDI 4.0 requiere CP) ─────────────────────────
            $table->string('pais', 60)->nullable()->default('México');
            $table->string('estado', 100)->nullable();
            $table->string('municipio', 150)->nullable();
            $table->string('localidad', 150)->nullable();
            $table->string('colonia', 150)->nullable();
            $table->string('calle', 200)->nullable();
            $table->string('no_ext', 20)->nullable();
            $table->string('no_int', 20)->nullable();
            $table->string('codigo_postal', 5)->nullable();
            $table->string('codigo_colonia', 20)->nullable();   // Opcional (SAT)
            $table->string('codigo_localidad', 20)->nullable(); // Opcional (SAT)

            // ── Notas ─────────────────────────────────────────────────────
            $table->text('notas')->nullable();

            // ── Control ───────────────────────────────────────────────────
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices útiles
            $table->index('tipo_persona');
            $table->index('vendedor_id');
            $table->index('rfc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
