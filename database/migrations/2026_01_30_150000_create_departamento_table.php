<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamento', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED, PK, AI
            $table->string('clave', 50);   // NOT NULL
            $table->string('nombre', 150); // NOT NULL
            $table->tinyInteger('activo')->default(1); // NOT NULL default 1
            $table->nullableTimestamps(); // created_at, updated_at TIMESTAMP NULL
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento');
    }
};
