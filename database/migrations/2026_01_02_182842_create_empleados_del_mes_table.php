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
        Schema::create('empleados_del_mes', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7); // "YYYY-MM"
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo')->nullable(); // opcional: "Empleado del mes"
            $table->text('mensaje')->nullable();  // opcional: motivo/reconocimiento
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['month']); // 1 por mes
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados_del_mes');
    }
};
