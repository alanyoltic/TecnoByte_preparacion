<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla garantias_externas y agrega los permisos necesarios.
 *
 * Cada registro representa una garantía de proveedor gestionada por el
 * área de preparación. Registra el historial completo:
 *  - quién la reportó (técnico)
 *  - qué proveedor la atiende
 *  - cómo se resolvió (reparado / reemplazado / rechazado)
 *  - quién registró la resolución
 *  - el equipo nuevo si hubo reemplazo
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garantias_externas', function (Blueprint $table) {
            $table->id();

            // Equipo original enviado al proveedor
            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Proveedor que atiende la garantía (siempre el mismo del lote)
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Asignación en la que ocurrió el problema
            $table->foreignId('asignacion_equipo_id')
                ->constrained('asignacion_equipos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Auditoría: quién reportó
            $table->foreignId('reportado_por_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Descripción del defecto detectado por el técnico
            $table->text('descripcion_defecto');

            // Fecha en que se envió físicamente al proveedor
            $table->date('fecha_envio')->nullable();

            // Estado de la garantía en el flujo de gestión
            $table->enum('estatus', ['PENDIENTE', 'RESUELTA', 'CANCELADA'])
                ->default('PENDIENTE');

            // --- Resolución ---
            $table->enum('tipo_resolucion', ['REPARADO', 'REEMPLAZADO', 'RECHAZADO_PROVEEDOR'])
                ->nullable();

            // Si hubo reemplazo: número de serie del equipo nuevo
            $table->string('numero_serie_nuevo')->nullable();

            // Si hubo reemplazo: equipo nuevo dado de alta en el sistema
            $table->foreignId('equipo_nuevo_id')
                ->nullable()
                ->constrained('equipos')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Modelo del equipo nuevo (puede ser diferente al original)
            $table->foreignId('lote_modelo_nuevo_id')
                ->nullable()
                ->constrained('lote_modelos_recibidos')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Técnico al que se asigna el equipo nuevo
            $table->foreignId('tecnico_reingreso_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Quién registró la resolución (auditoría)
            $table->foreignId('resuelto_por_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->date('fecha_resolucion')->nullable();
            $table->text('observaciones_resolucion')->nullable();

            $table->timestamps();

            // Índices de consulta frecuente
            $table->index(['estatus', 'created_at']);
            $table->index('equipo_id');
            $table->index('proveedor_id');
        });

        // Insertar los dos nuevos permisos de garantías
        $now = now();
        DB::table('permisos')->insert([
            [
                'slug'        => 'prep.garantias.ver',
                'descripcion' => 'Ver garantías externas (Preparación)',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'prep.garantias.gestionar',
                'descripcion' => 'Gestionar / resolver garantías externas (Preparación)',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('garantias_externas');

        DB::table('permisos')
            ->whereIn('slug', ['prep.garantias.ver', 'prep.garantias.gestionar'])
            ->delete();
    }
};
