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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_modelo_id')->constrained('lote_modelos_recibidos');
            $table->foreignId('registrado_por_user_id')->constrained('users');
            $table->foreignId('proveedor_id')->constrained('proveedores');

            $table->string('numero_serie')->unique();
            $table->enum('estatus_general', [
                'En Revisión', 'Aprobado', 'Pendiente Pieza', 
                'Pendiente Garantía', 'Pendiente Deshueso', 'Finalizado'
            ])->default('En Revisión');

            $table->string('marca', 100)->nullable();
            $table->string('modelo');
            $table->string('tipo_equipo', 100)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('area_tienda', 100)->nullable();
            $table->string('procesador_modelo')->nullable();
            $table->string('procesador_generacion', 100)->nullable();
            $table->integer('procesador_nucleos')->nullable();
            $table->string('pantalla_pulgadas', 20)->nullable();
            $table->string('pantalla_resolucion', 50)->nullable();
            $table->boolean('pantalla_es_touch')->default(false);
            $table->string('pantalla_tipo', 100)->nullable();
            $table->string('ram_total', 50)->nullable();
            $table->string('ram_tipo', 50)->nullable();
            $table->boolean('ram_es_soldada')->default(false);
            $table->string('ram_slots_totales', 100)->nullable();
            $table->string('ram_expansion_max', 100)->nullable();
            $table->string('almacenamiento_principal_capacidad', 50)->nullable();
            $table->string('almacenamiento_principal_tipo', 50)->nullable();
            $table->string('almacenamiento_secundario_capacidad', 50)->nullable()->default('N/A');
            $table->string('almacenamiento_secundario_tipo', 50)->nullable()->default('N/A');
            $table->string('grafica_integrada_modelo')->nullable();
            $table->string('grafica_dedicada_modelo')->nullable();
            $table->string('grafica_dedicada_vram', 50)->nullable();
            $table->tinyInteger('bateria_salud_percent')->unsigned()->nullable();
            $table->string('bateria_cantidad', 100)->nullable();
            $table->string('teclado_idioma', 50)->nullable()->default('N/A');
            $table->text('notas_generales')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
