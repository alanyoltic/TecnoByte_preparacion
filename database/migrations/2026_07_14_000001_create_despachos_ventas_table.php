<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despachos_ventas', function (Blueprint $table) {
            $table->id();

            // Folio auto-generado: DV-2026-0001
            $table->string('folio', 30)->unique();

            // Destino: sucursal y almacén específico de Ventas
            $table->foreignId('sucursal_destino_id')->constrained('sucursales')->restrictOnDelete();
            $table->foreignId('almacen_destino_id')->constrained('almacenes')->restrictOnDelete();

            // Usuarios
            $table->foreignId('creado_por_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('aprobado_por_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Estado del despacho
            $table->enum('estatus', [
                'BORRADOR',   // Gerente de Prep lo está armando
                'ENVIADO',    // Ya lo mandó a Ventas para aprobación
                'APROBADO',   // Ventas lo aprobó, equipos ya pasaron
                'RECHAZADO',  // Ventas lo rechazó con motivo
                'CANCELADO',  // Gerente de Prep lo canceló antes de enviar
            ])->default('BORRADOR');

            // Contexto / notas
            $table->text('motivo')->nullable();            // ¿Por qué se manda este despacho?
            $table->text('notas_rechazo')->nullable();     // Razón de rechazo por Ventas

            // Timestamps de flujo
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('aprobado_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['estatus', 'sucursal_destino_id']);
            $table->index('creado_por_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despachos_ventas');
    }
};
