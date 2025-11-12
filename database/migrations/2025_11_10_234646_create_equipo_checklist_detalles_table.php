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
        Schema::create('equipo_checklist_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('catalogo_checklist_items')->onDelete('cascade');
            $table->enum('estatus', ['OK', 'Falla', 'No Aplica', 'Presente']);
            $table->integer('cantidad')->unsigned()->default(1);
            $table->string('notas')->nullable();
            $table->unique(['equipo_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_checklist_detalles');
    }
};
