<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Nombres/apellidos
            if (!Schema::hasColumn('users', 'segundo_nombre')) {
                $table->string('segundo_nombre')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('users', 'apellido_paterno')) {
                $table->string('apellido_paterno')->nullable()->after('segundo_nombre');
            }
            if (!Schema::hasColumn('users', 'apellido_materno')) {
                $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            }

            // Activo
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }

            // Foto
            if (!Schema::hasColumn('users', 'foto_perfil')) {
                $table->string('foto_perfil')->nullable()->after('sucursal_id');
            }

            // Soft deletes
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // Relaciones (si no existen)
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles');
            }
            if (!Schema::hasColumn('users', 'departamento_id')) {
                $table->foreignId('departamento_id')->nullable()->constrained('departamento');
            }
            if (!Schema::hasColumn('users', 'puesto_id')) {
                $table->foreignId('puesto_id')->nullable()->constrained('puestos');
            }
            if (!Schema::hasColumn('users', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');
            }
        });

        // ✅ Post-fix: poner defaults seguros sin tocar usuarios “bien”
        // Si quieres mantener compatibilidad, dejamos apellido_paterno con ''
        // y role_id con el rol admin/ceo si existe, o el primero si no.
        if (Schema::hasColumn('users', 'apellido_paterno')) {
            DB::table('users')->whereNull('apellido_paterno')->update(['apellido_paterno' => '']);
        }

        // Si role_id quedó nullable pero tú lo quieres obligatorio:
        // llena los NULL con algún rol base existente (ajusta slug si aplica)
        if (Schema::hasColumn('users', 'role_id')) {
            $rolId = DB::table('roles')->whereIn('slug', ['tecnico','admin','ceo'])->value('id')
                ?? DB::table('roles')->orderBy('id')->value('id');

            if ($rolId) {
                DB::table('users')->whereNull('role_id')->update(['role_id' => $rolId]);
            }
        }

        // Si ya llenaste role_id, puedes volverlo NOT NULL (opcional, solo si estás seguro)
        // Nota: MySQL requiere doctrine/dbal para change(); si no lo tienes, lo dejamos nullable.
    }

    public function down(): void
    {
        // En producción normalmente NO se hace rollback de columnas ya usadas.
        // Pero lo dejo mínimo por si lo ocupas.
        Schema::table('users', function (Blueprint $table) {
            // no dropeo llaves/cols aquí para evitar romper datos
        });
    }
};
