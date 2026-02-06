<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamento_puestos', function (Blueprint $table) {
            $table->id();

            // Tu tabla es singular: departamento
            $table->foreignId('departamento_id')
                ->constrained('departamento')
                ->cascadeOnDelete();

            $table->foreignId('puesto_id')
                ->constrained('puestos')
                ->cascadeOnDelete();

            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['departamento_id', 'puesto_id'], 'dep_puesto_unique');
            $table->index(['departamento_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento_puestos');
    }
};
