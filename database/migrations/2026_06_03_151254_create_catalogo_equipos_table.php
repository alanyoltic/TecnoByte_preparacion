<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalogo_equipos', function (Blueprint $table) {
            $table->id();
            $table->string('marca');
            $table->string('modelo');
            $table->string('tipo_equipo')->nullable()->comment('Laptop, Desktop, All-in-one, etc.');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Evitamos duplicados exactos a nivel base de datos
            $table->unique(['marca', 'modelo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_equipos');
    }
};
