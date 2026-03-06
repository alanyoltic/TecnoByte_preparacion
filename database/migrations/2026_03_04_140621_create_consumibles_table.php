<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de consumibles (Cargador Dell 65W, Mouse USB, etc.)
        Schema::create('consumibles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                          // Cargador Dell 65W
            $table->string('categoria')->nullable();           // ACCESORIO, CABLE, PIEZA, etc.
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Stock de consumibles por almacén
        Schema::create('inventario_consumibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumible_id')->constrained('consumibles')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(0);
            $table->timestamps();

            $table->unique(['consumible_id', 'almacen_id']); // Un registro por combo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_consumibles');
        Schema::dropIfExists('consumibles');
    }
};