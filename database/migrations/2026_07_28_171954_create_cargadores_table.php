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
        Schema::create('cargadores', function (Blueprint $table) {
            $table->id();
            $table->string('serie')->nullable()->unique();
            $table->string('marca')->nullable();
            $table->string('voltaje')->nullable();
            $table->string('amperaje')->nullable();
            $table->string('punta')->nullable();
            $table->string('estatus')->default('DISPONIBLE');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->foreignId('compra_inventario_id')->nullable()->constrained('compras_inventario')->nullOnDelete();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->decimal('costo', 10, 2)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargadores');
    }
};
