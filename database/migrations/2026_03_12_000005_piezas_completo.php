<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ampliar catalogo_piezas ────────────────────────────────────
        Schema::table('catalogo_piezas', function (Blueprint $table) {
            $table->string('categoria', 50)
                ->after('nombre')
                ->nullable()
                ->comment('RAM, SSD, HDD, Batería, Teclado, Bisagra, Pantalla, Otro');
            $table->boolean('requiere_serie')
                ->after('categoria')
                ->default(false)
                ->comment('Si esta pieza requiere número de serie (SSD, RAM, etc.)');
            $table->boolean('activo')
                ->after('requiere_serie')
                ->default(true);
        });

        // ── 2. Crear inventario_piezas ────────────────────────────────────
        Schema::create('inventario_piezas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('catalogo_pieza_id')
                ->constrained('catalogo_piezas')
                ->cascadeOnDelete()
                ->comment('Qué tipo de pieza es');

            $table->string('numero_serie', 100)
                ->nullable()
                ->comment('Número de serie si aplica (SSD, RAM, etc.)');

            $table->enum('origen', ['COMPRA', 'DESHUESO'])
                ->default('COMPRA')
                ->comment('De dónde vino la pieza');

            $table->foreignId('equipo_origen_id')
                ->nullable()
                ->constrained('equipos')
                ->nullOnDelete()
                ->comment('Equipo del que se extrajo (solo si origen=DESHUESO)');

            $table->foreignId('almacen_id')
                ->default(7)
                ->constrained('almacenes')
                ->comment('Almacén donde está físicamente la pieza');

            $table->decimal('costo', 10, 2)
                ->nullable()
                ->comment('Costo de la pieza (registrado por gerente al comprar)');

            $table->foreignId('registrado_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Gerente que registró la pieza');

            $table->enum('estatus', [
                'DISPONIBLE',
                'RESERVADA',
                'USADA',
                'DADA_DE_BAJA',
            ])->default('DISPONIBLE');

            $table->date('fecha_ingreso')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('catalogo_pieza_id');
            $table->index('estatus');
            $table->index('almacen_id');
        });

        // ── 3. Crear solicitudes_piezas ───────────────────────────────────
        Schema::create('solicitudes_piezas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asignacion_equipo_id')
                ->constrained('asignacion_equipos')
                ->cascadeOnDelete()
                ->comment('Equipo en proceso que necesita la pieza');

            $table->foreignId('solicitado_por_id')
                ->constrained('users')
                ->comment('Técnico que hace la solicitud');

            $table->foreignId('catalogo_pieza_id')
                ->nullable()
                ->constrained('catalogo_piezas')
                ->nullOnDelete()
                ->comment('Pieza del catálogo si ya existe');

            $table->string('descripcion_libre', 255)
                ->nullable()
                ->comment('Descripción si la pieza no está en catálogo');

            $table->enum('estatus', [
                'PENDIENTE',
                'SURTIDA_INVENTARIO',
                'PENDIENTE_COMPRA',
                'COMPRADA',
                'CANCELADA',
            ])->default('PENDIENTE');

            $table->foreignId('inventario_pieza_id')
                ->nullable()
                ->constrained('inventario_piezas')
                ->nullOnDelete()
                ->comment('Pieza física asignada para esta solicitud');

            $table->foreignId('respondida_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Gerente/Líder que respondió');

            $table->timestamp('respondida_en')->nullable();
            $table->text('notas_respuesta')->nullable();
            $table->timestamps();

            $table->index('asignacion_equipo_id');
            $table->index('estatus');
            $table->index('solicitado_por_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_piezas');
        Schema::dropIfExists('inventario_piezas');

        Schema::table('catalogo_piezas', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'requiere_serie', 'activo']);
        });
    }
};
