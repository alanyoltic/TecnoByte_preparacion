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
        Schema::create('catalogo_piezas_eliminaciones', function (Blueprint $table) {
            $table->id();
            
            // Datos básicos para buscar rápido
            $table->unsignedBigInteger('catalogo_pieza_id')->nullable()->index();
            $table->string('nombre');
            $table->string('categoria');
            
            // Auditoría
            $table->string('motivo', 255)->default('Eliminación híbrida');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Datos completos eliminados
            $table->json('snapshot')->nullable()->comment('Contiene la pieza, sus items de inventario y items de compra que fueron eliminados físicamente');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_piezas_eliminaciones');
    }
};
