<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Refactor de estatus en tabla equipos
 *
 * 1. Renombra estado_operativo → estatus_ciclo  (visión macro del equipo en TecnoByte)
 * 2. Renombra estatus_general  → estatus_area   (detalle interno dentro de cada área)
 * 3. Actualiza los valores ENUM de ambas columnas
 * 4. Migra los datos existentes al nuevo esquema
 * 5. Agrega tipo_equipo_id (FK a tipos_equipo, nullable)
 */
return new class extends Migration
{
    // =========================================================
    // MAPEOS de valores viejos → nuevos (para UPDATE de datos)
    // =========================================================

    private array $mapCiclo = [
        'PENDIENTE_PREPARACION' => 'CEDIS',
        'EN_PREPARACION' => 'PREPARACION',
        'EN_QA' => 'CALIDAD',
        'EN_VENTAS' => 'VENTAS',
        'APARTADO' => 'APARTADO',
        'VENDIDO' => 'VENDIDO',
        'BAJA' => 'SCRAP',
    ];

    private array $mapArea = [
        'En Revisión' => 'EN_PROCESO',
        'Aprobado' => 'LISTO',
        'Pendiente Pieza' => 'PENDIENTE_PIEZA',
        'Pendiente Garantía' => 'PENDIENTE_GARANTIA',
        'Pendiente Deshueso' => 'PENDIENTE_DESHUESO',
        'Finalizado' => 'TRANSFERIDO',
    ];

    // =========================================================
    // UP
    // =========================================================
    public function up(): void
    {
        // ----------------------------------------------------------
        // PASO 1: Temporalmente cambiamos las columnas a VARCHAR
        // para poder renombrar y cambiar valores sin restricción ENUM
        // ----------------------------------------------------------
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('estado_operativo', 60)->nullable()->change();
            $table->string('estatus_general', 60)->nullable()->change();
        });

        // ----------------------------------------------------------
        // PASO 2: Migrar datos existentes al nuevo vocabulario
        // ----------------------------------------------------------
        foreach ($this->mapCiclo as $viejo => $nuevo) {
            DB::table('equipos')
                ->where('estado_operativo', $viejo)
                ->update(['estado_operativo' => $nuevo]);
        }

        foreach ($this->mapArea as $viejo => $nuevo) {
            DB::table('equipos')
                ->where('estatus_general', $viejo)
                ->update(['estatus_general' => $nuevo]);
        }

        // Cualquier valor que no esté en el mapa (datos inesperados)
        // lo dejamos en el valor por defecto seguro
        DB::table('equipos')
            ->whereNotIn('estado_operativo', array_values($this->mapCiclo))
            ->update(['estado_operativo' => 'CEDIS']);

        DB::table('equipos')
            ->whereNotIn('estatus_general', array_values($this->mapArea))
            ->whereNotNull('estatus_general')
            ->update(['estatus_general' => 'SIN_ASIGNAR']);

        // ----------------------------------------------------------
        // PASO 3: Renombrar columnas
        // ----------------------------------------------------------
        Schema::table('equipos', function (Blueprint $table) {
            $table->renameColumn('estado_operativo', 'estatus_ciclo');
            $table->renameColumn('estatus_general', 'estatus_area');
        });

        // ----------------------------------------------------------
        // PASO 4: Aplicar los ENUM definitivos con los nuevos nombres
        // ----------------------------------------------------------
        Schema::table('equipos', function (Blueprint $table) {
            $table->enum('estatus_ciclo', [
                'CEDIS',        // Llegó al lote, nadie lo ha tocado aún
                'PREPARACION',  // Está siendo trabajado por el área de preparación
                'CALIDAD',      // En revisión de calidad / QA
                'VENTAS',       // Listo y transferido al área de ventas
                'APARTADO',     // Apartado por un cliente
                'VENDIDO',      // Vendido — estado final positivo
                'SCRAP',        // Dado de baja — estado final negativo
            ])->default('CEDIS')->change();

            $table->enum('estatus_area', [
                'SIN_ASIGNAR',        // Llegó pero no tiene asignación aún
                'ASIGNADO',           // Gerente lo asignó a un técnico
                'EN_PROCESO',         // Técnico está trabajando en él
                'LISTO',              // Terminado, esperando transferencia
                'TRANSFERIDO',        // Ya fue enviado al siguiente área
                'PENDIENTE_PIEZA',    // Bloqueado por falta de pieza
                'PENDIENTE_GARANTIA', // En espera de resolución de garantía
                'PENDIENTE_DESHUESO', // Esperando partes de otro equipo
                'GARANTIA_INT',       // En garantía interna (TecnoByte cubre)
                'GARANTIA_EXT',       // En garantía del proveedor
            ])->default('SIN_ASIGNAR')->change();
        });

        // ----------------------------------------------------------
        // PASO 5: Agregar tipo_equipo_id (FK a tipos_equipo)
        // Nullable porque los equipos existentes aún no tienen tipo A-F
        // Se asigna cuando el técnico registra su trabajo
        // ----------------------------------------------------------
        Schema::table('equipos', function (Blueprint $table) {
            // Solo agregar si la tabla tipos_equipo ya existe
            if (Schema::hasTable('tipos_equipo')) {
                $table->foreignId('tipo_equipo_id')
                    ->nullable()
                    ->after('tipo_equipo')
                    ->constrained('tipos_equipo')
                    ->nullOnDelete();
            } else {
                // Si tipos_equipo aún no existe, agregamos sin FK
                // (se agrega la FK en la migración de tipos_equipo)
                $table->unsignedBigInteger('tipo_equipo_id')
                    ->nullable()
                    ->after('tipo_equipo');
            }
        });
    }

    // =========================================================
    // DOWN — revierte todo en orden inverso
    // =========================================================
    public function down(): void
    {
        // Quitar tipo_equipo_id
        Schema::table('equipos', function (Blueprint $table) {
            if (Schema::hasColumn('equipos', 'tipo_equipo_id')) {
                if (Schema::hasTable('tipos_equipo')) {
                    $table->dropForeign(['tipo_equipo_id']);
                }
                $table->dropColumn('tipo_equipo_id');
            }
        });

        // Volver a VARCHAR para poder manipular datos
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('estatus_ciclo', 60)->nullable()->change();
            $table->string('estatus_area', 60)->nullable()->change();
        });

        // Revertir datos
        foreach (array_flip($this->mapCiclo) as $nuevo => $viejo) {
            DB::table('equipos')
                ->where('estatus_ciclo', $nuevo)
                ->update(['estatus_ciclo' => $viejo]);
        }

        foreach (array_flip($this->mapArea) as $nuevo => $viejo) {
            DB::table('equipos')
                ->where('estatus_area', $nuevo)
                ->update(['estatus_area' => $viejo]);
        }

        // Renombrar de vuelta
        Schema::table('equipos', function (Blueprint $table) {
            $table->renameColumn('estatus_ciclo', 'estado_operativo');
            $table->renameColumn('estatus_area', 'estatus_general');
        });

        // Restaurar ENUMs originales
        Schema::table('equipos', function (Blueprint $table) {
            $table->enum('estado_operativo', [
                'PENDIENTE_PREPARACION',
                'EN_PREPARACION',
                'EN_QA',
                'EN_VENTAS',
                'APARTADO',
                'VENDIDO',
                'BAJA',
            ])->default('PENDIENTE_PREPARACION')->change();

            $table->enum('estatus_general', [
                'En Revisión',
                'Aprobado',
                'Pendiente Pieza',
                'Pendiente Garantía',
                'Pendiente Deshueso',
                'Finalizado',
            ])->default('En Revisión')->change();
        });
    }
};
