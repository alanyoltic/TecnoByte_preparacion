<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipo_estancias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();

            $table->dateTime('inicio_at');
            $table->dateTime('fin_at')->nullable();

            $table->foreignId('abierto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['equipo_id', 'fin_at']);
            $table->index(['almacen_id', 'inicio_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_estancias');
    }
};
