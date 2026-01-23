<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();   // PREPARACION, VENTAS, ...
            $table->string('nombre', 150);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();   // QRO, LEON, ONLINE
            $table->string('nombre', 150);
            $table->boolean('es_virtual')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('area_id')->nullable()->constrained('areas');

            // AREA = pertenece a un area (prep/ventas)
            // COMPARTIDO = varios pueden ver/mover via pivot (por ahora no lo ocupas en piso/ventas)
            // ONLINE = online central
            $table->enum('tipo', ['AREA', 'COMPARTIDO', 'ONLINE'])->default('AREA');

            $table->string('clave', 80)->unique();   // QRO_CEDIS_PREP, ...
            $table->string('nombre', 180);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['sucursal_id', 'area_id', 'tipo']);
        });

        Schema::create('almacen_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();

            $table->boolean('puede_ver')->default(true);
            $table->boolean('puede_mover')->default(false);
            $table->boolean('puede_aceptar')->default(false);

            $table->timestamps();
            $table->unique(['almacen_id', 'area_id']);
        });

        Schema::create('almacen_encargados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->dateTime('desde');
            $table->dateTime('hasta')->nullable();

            $table->boolean('es_principal')->default(true);
            $table->boolean('activo')->default(true);

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo')->nullable();

            $table->timestamps();
            $table->index(['almacen_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacen_encargados');
        Schema::dropIfExists('almacen_areas');
        Schema::dropIfExists('almacenes');
        Schema::dropIfExists('sucursales');
        Schema::dropIfExists('areas');
    }
};
