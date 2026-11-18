<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->string('folio')->unique()
                ->comment('Formato: TKT-YYYY-NNNNN');

            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete()
                ->comment('Nullable: permite ventas a público general sin registro');

            $table->foreignId('vendedor_id')
                ->constrained('users')
                ->comment('Usuario que realizó la venta');

            $table->foreignId('almacen_id')
                ->constrained('almacenes')
                ->comment('Sucursal/almacén desde donde se vende');

            $table->enum('metodo_pago', [
                'EFECTIVO',
                'TARJETA',
                'TRANSFERENCIA',
                'MULTIPLE',
            ])->default('EFECTIVO');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->enum('estatus', [
                'PENDIENTE',    // Carrito abierto en POS
                'COMPLETADA',   // Cobrada y cerrada
                'CANCELADA',    // Anulada
            ])->default('PENDIENTE');

            $table->text('notas')->nullable();

            // Auditoría de cancelación
            $table->foreignId('cancelada_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('cancelada_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
