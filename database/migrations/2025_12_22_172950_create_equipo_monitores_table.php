<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_monitores', function (Blueprint $table) {
            $table->id();

            // 1:1 con equipos
            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->cascadeOnDelete();
            $table->unique('equipo_id');

            // Identificador del origen de la pantalla
            $table->enum('origen_pantalla', ['INTEGRADA', 'EXTERNA'])->index();

            // Solo relevante para EXTERNA (ESCRITORIO/MICRO PC/GAMER)
            // Si NO trae monitor, NO crearemos registro (así que aquí normalmente sería true),
            // pero lo dejamos por claridad.
            $table->boolean('incluido')->default(true);

            // Datos generales (aplican a ambos, pero pueden ser null si aún no se capturan)
            $table->string('pulgadas', 20)->nullable();
            $table->string('resolucion', 30)->nullable();
            $table->string('tipo_panel', 30)->nullable();
            $table->boolean('es_touch')->nullable();

            // Entradas del monitor (solo para EXTERNA normalmente)
            $table->unsignedTinyInteger('in_hdmi')->nullable();
            $table->unsignedTinyInteger('in_mini_hdmi')->nullable();
            $table->unsignedTinyInteger('in_vga')->nullable();
            $table->unsignedTinyInteger('in_dvi')->nullable();
            $table->unsignedTinyInteger('in_displayport')->nullable();
            $table->unsignedTinyInteger('in_mini_displayport')->nullable();

            // (Opcional) USB en monitor (si en tus capturas aplica)
            $table->unsignedTinyInteger('in_usb_2')->nullable();
            $table->unsignedTinyInteger('in_usb_3')->nullable();
            $table->unsignedTinyInteger('in_usb_31')->nullable();
            $table->unsignedTinyInteger('in_usb_32')->nullable();
            $table->unsignedTinyInteger('in_usb_c')->nullable();

            // Detalles (tipo modal/checklist, como ya manejas en otras secciones)
            $table->text('detalles_esteticos_checks')->nullable();
            $table->text('detalles_esteticos_otro')->nullable();
            $table->text('detalles_funcionamiento_checks')->nullable();
            $table->text('detalles_funcionamiento_otro')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_monitores');
    }
};
