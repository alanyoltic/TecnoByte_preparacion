<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Eliminar tabla legacy de deshueso (vacía) ──────────────
        Schema::dropIfExists('inventario_piezas_deshueso');

        // ── 2. Ampliar catalogo_piezas ────────────────────────────────
        Schema::table('catalogo_piezas', function (Blueprint $table) {
            if (!Schema::hasColumn('catalogo_piezas', 'especificacion')) {
                $table->string('especificacion', 100)
                      ->nullable()
                      ->after('categoria')
                      ->comment('Ej: Para Lenovo, DDR4 2666MHz, 15.6" FHD');
            }
            if (!Schema::hasColumn('catalogo_piezas', 'notas_compatibilidad')) {
                $table->text('notas_compatibilidad')
                      ->nullable()
                      ->after('especificacion')
                      ->comment('Modelos compatibles, restricciones, etc.');
            }
        });

        // ── 3. Crear compras_inventario ───────────────────────────────
        Schema::create('compras_inventario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proveedor_id')
                  ->constrained('proveedores')
                  ->comment('A quién se le compró');

            $table->foreignId('lote_id')
                  ->nullable()
                  ->constrained('lotes')
                  ->nullOnDelete()
                  ->comment('Lote de equipos al que pertenece esta compra, si aplica');

            $table->date('fecha_compra');

            $table->string('folio', 100)
                  ->nullable()
                  ->comment('Número de factura, remisión u orden de compra');

            $table->decimal('total_estimado', 12, 2)
                  ->nullable()
                  ->comment('Total estimado de la compra para referencia');

            $table->text('notas')->nullable();

            $table->foreignId('registrado_por_id')
                  ->constrained('users')
                  ->comment('Usuario que registró la compra');

            $table->timestamps();

            $table->index('proveedor_id');
            $table->index('fecha_compra');
        });

        // ── 4. Crear compras_inventario_items ─────────────────────────
        Schema::create('compras_inventario_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('compra_inventario_id')
                  ->constrained('compras_inventario')
                  ->cascadeOnDelete();

            $table->foreignId('catalogo_pieza_id')
                  ->constrained('catalogo_piezas');

            $table->unsignedInteger('cantidad');

            $table->decimal('precio_unitario', 10, 2)
                  ->nullable()
                  ->comment('Precio por unidad en esta compra');

            $table->foreignId('almacen_id')
                  ->default(7)
                  ->constrained('almacenes')
                  ->comment('Almacén destino de las piezas');

            $table->text('notas')->nullable();

            $table->timestamps();

            $table->index('compra_inventario_id');
            $table->index('catalogo_pieza_id');
        });

        // ── 5. Agregar columnas de cantidad a inventario_piezas ───────
        Schema::table('inventario_piezas', function (Blueprint $table) {
            if (Schema::hasColumn('inventario_piezas', 'compra_item_id')) return;
            $table->foreignId('compra_item_id')
                  ->nullable()
                  ->after('origen')
                  ->constrained('compras_inventario_items')
                  ->nullOnDelete()
                  ->comment('Ítem de compra del que proviene (si origen=COMPRA)');

            $table->unsignedInteger('cantidad_inicial')
                  ->default(1)
                  ->after('notas')
                  ->comment('Cuántas unidades entraron en esta entrada');

            $table->unsignedInteger('cantidad_disponible')
                  ->default(1)
                  ->after('cantidad_inicial')
                  ->comment('Cuántas unidades están disponibles para asignar');

            $table->unsignedInteger('cantidad_reservada')
                  ->default(0)
                  ->after('cantidad_disponible')
                  ->comment('Cuántas unidades están reservadas para solicitudes activas');

            $table->unsignedInteger('cantidad_usada')
                  ->default(0)
                  ->after('cantidad_reservada')
                  ->comment('Cuántas unidades ya fueron instaladas exitosamente');

            $table->unsignedInteger('cantidad_baja')
                  ->default(0)
                  ->after('cantidad_usada')
                  ->comment('Cuántas unidades fallaron o fueron dadas de baja');
        });

        // ── 6. Migrar datos existentes a las nuevas columnas ──────────
        DB::statement("
            UPDATE inventario_piezas SET
                cantidad_inicial    = 1,
                cantidad_disponible = CASE estatus WHEN 'DISPONIBLE' THEN 1 ELSE 0 END,
                cantidad_reservada  = CASE estatus WHEN 'RESERVADA'  THEN 1 ELSE 0 END,
                cantidad_usada      = CASE estatus WHEN 'USADA'      THEN 1 ELSE 0 END,
                cantidad_baja       = CASE estatus WHEN 'DADA_DE_BAJA' THEN 1 ELSE 0 END
        ");

        // ── 7. Cambiar enum de estatus ────────────────────────────────
        // Paso 1: ampliar el enum para incluir AGOTADA sin perder datos actuales
        DB::statement("ALTER TABLE inventario_piezas MODIFY COLUMN estatus ENUM('DISPONIBLE','RESERVADA','USADA','DADA_DE_BAJA','AGOTADA') NOT NULL DEFAULT 'DISPONIBLE'");

        // Paso 2: migrar los valores obsoletos
        DB::statement("UPDATE inventario_piezas SET estatus = 'AGOTADA' WHERE estatus IN ('RESERVADA','USADA')");

        // Paso 3: reducir el enum a los valores finales (ya no hay RESERVADA ni USADA)
        DB::statement("ALTER TABLE inventario_piezas MODIFY COLUMN estatus ENUM('DISPONIBLE','AGOTADA','DADA_DE_BAJA') NOT NULL DEFAULT 'DISPONIBLE'");

        // Paso 4: asegurar DISPONIBLE correcto según cantidad
        DB::statement("UPDATE inventario_piezas SET estatus = 'DISPONIBLE' WHERE cantidad_disponible > 0 AND estatus != 'DADA_DE_BAJA'");
    }

    public function down(): void
    {
        Schema::table('inventario_piezas', function (Blueprint $table) {
            $table->dropForeign(['compra_item_id']);
            $table->dropColumn([
                'compra_item_id', 'cantidad_inicial', 'cantidad_disponible',
                'cantidad_reservada', 'cantidad_usada', 'cantidad_baja',
            ]);
        });

        DB::statement("ALTER TABLE inventario_piezas MODIFY COLUMN estatus ENUM('DISPONIBLE','RESERVADA','USADA','DADA_DE_BAJA') NOT NULL DEFAULT 'DISPONIBLE'");

        Schema::dropIfExists('compras_inventario_items');
        Schema::dropIfExists('compras_inventario');

        Schema::table('catalogo_piezas', function (Blueprint $table) {
            $table->dropColumn(['especificacion', 'notas_compatibilidad']);
        });
    }
};
