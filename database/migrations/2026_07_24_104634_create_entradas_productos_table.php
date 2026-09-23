<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entradas_productos', function (Blueprint $table) {
            $table->id();
            
            // Relación con el proveedor (opcional, por si es compra local menor)
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            
            $table->date('fecha');
            $table->string('folio_factura')->nullable();
            
            $table->decimal('total_estimado', 10, 2)->default(0);
            $table->text('notas')->nullable();
            
            // Quién registró la entrada (Usuario de Ventas)
            $table->foreignId('registrado_por_id')->constrained('users');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entradas_productos');
    }
};
