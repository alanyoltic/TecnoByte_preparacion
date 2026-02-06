<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipo_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->enum('tipo', [
                'ALTA_LOTE',
                'MOVER_ALMACEN',
                'ASIGNAR_TECNICO',
                'FINALIZAR_TECNICO',
                'VENTA',
                'BAJA',
                'AJUSTE',
            ]);

            $table->foreignId('desde_almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('hacia_almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();

            // Referencia al "motivo" del movimiento (lote/venta/traspaso/ticket/etc.)
            $table->string('ref_type', 120)->nullable(); // Ej: App\Models\Lote
            $table->unsignedBigInteger('ref_id')->nullable();

            $table->text('motivo')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Para no explotar: guardamos IP y UA pero sin hacerlo gigante
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['equipo_id', 'created_at']);
            $table->index(['tipo', 'created_at']);
            $table->index(['ref_type', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_movimientos');
    }
};
