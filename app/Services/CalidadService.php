<?php

namespace App\Services;

use App\Models\Asignacion;
use App\Models\AsignacionEquipo;
use App\Models\Equipo;
use App\Models\ValidacionCalidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalidadService
{
    /**
     * Valida (aprueba) un equipo en calidad.
     *
     * Transición: EN_CALIDAD → FINALIZADO
     * AsignacionEquipo.camino: EN_CALIDAD → COMPLETADO
     */
    public function validarEquipo(
        int $equipoId,
        ?int $calificacion = null,
        ?array $qsalioBien = null,
        ?string $notas = null
    ): ValidacionCalidad {
        return DB::transaction(function () use ($equipoId, $calificacion, $qsalioBien, $notas) {
            $equipo = Equipo::findOrFail($equipoId);

            // Validar que está en EN_CALIDAD
            if ($equipo->estatus_area !== Equipo::AREA_EN_CALIDAD) {
                throw new \RuntimeException(
                    "Equipo #{$equipoId} no está en calidad. Estado actual: {$equipo->estatus_area}"
                );
            }

            // Obtener la asignación_equipo más reciente en EN_CALIDAD
            $ae = AsignacionEquipo::where('equipo_id', $equipoId)
                ->where('camino', AsignacionEquipo::EN_CALIDAD)
                ->latest('fin_en')
                ->firstOrFail();

            // Crear registro de validación (APROBADO)
            $validacion = ValidacionCalidad::create([
                'equipo_id' => $equipoId,
                'asignacion_equipo_id' => $ae->id,
                'validado_por_user_id' => Auth::id(),
                'estado' => ValidacionCalidad::APROBADO,
                'calificacion_general' => $calificacion,
                'checklist_qué_salió_bien' => $qsalioBien ?? [],
                'notas_adicionales' => $notas,
            ]);

            // Actualizar asignación_equipo: EN_CALIDAD → COMPLETADO
            $ae->update(['camino' => AsignacionEquipo::COMPLETADO]);

            // Actualizar equipo: EN_CALIDAD → FINALIZADO (estatus_area)
            $equipo->update([
                'estatus_area' => Equipo::AREA_FINALIZADO,
                'estatus_ciclo' => Equipo::CICLO_CALIDAD, // Se mantiene en CALIDAD
            ]);

            // Verificar si la asignación está completa (todos los equipos terminados)
            $this->verificarAsignacionCompleta($ae->asignacion_id);

            return $validacion;
        });
    }

    /**
     * Rechaza un equipo en calidad y lo regresa a EN_PROCESO.
     *
     * Transición: EN_CALIDAD → EN_PROCESO
     * AsignacionEquipo.camino: EN_CALIDAD → EN_PROCESO
     *
     * El equipo vuelve a manos del técnico original.
     */
    public function rechazarEquipo(
        int $equipoId,
        string $motivo,
        ?array $qSalioMal = null,
        ?array $qSalioBien = null,
        ?int $calificacion = null,
        ?string $notas = null
    ): ValidacionCalidad {
        if (empty(trim($motivo))) {
            throw new \RuntimeException('El motivo del rechazo es obligatorio.');
        }

        return DB::transaction(function () use (
            $equipoId, $motivo, $qSalioMal, $qSalioBien, $calificacion, $notas
        ) {
            $equipo = Equipo::findOrFail($equipoId);

            // Validar que está en EN_CALIDAD
            if ($equipo->estatus_area !== Equipo::AREA_EN_CALIDAD) {
                throw new \RuntimeException(
                    "Equipo #{$equipoId} no está en calidad. Estado actual: {$equipo->estatus_area}"
                );
            }

            // Obtener la asignación_equipo más reciente en EN_CALIDAD
            $ae = AsignacionEquipo::where('equipo_id', $equipoId)
                ->where('camino', AsignacionEquipo::EN_CALIDAD)
                ->latest('fin_en')
                ->firstOrFail();

            // Crear registro de validación (RECHAZADO)
            $validacion = ValidacionCalidad::create([
                'equipo_id' => $equipoId,
                'asignacion_equipo_id' => $ae->id,
                'validado_por_user_id' => Auth::id(),
                'estado' => ValidacionCalidad::RECHAZADO,
                'motivo' => trim($motivo),
                'checklist_qué_salió_mal' => $qSalioMal ?? [],
                'checklist_qué_salió_bien' => $qSalioBien ?? [],
                'calificacion_general' => $calificacion,
                'notas_adicionales' => $notas,
            ]);

            // Actualizar asignación_equipo: EN_CALIDAD → EN_PROCESO
            $ae->update([
                'camino' => AsignacionEquipo::EN_PROCESO,
                'rechazado_en' => now(),
                'num_rechazos' => ($ae->num_rechazos ?? 0) + 1,
            ]);

            // Actualizar equipo: EN_CALIDAD → EN_PROCESO (vuelve a preparación)
            $equipo->update([
                'estatus_area' => Equipo::AREA_EN_PROCESO,
                'estatus_ciclo' => Equipo::CICLO_PREPARACION,
            ]);

            return $validacion;
        });
    }

    /**
     * Verifica si una asignación está completada (todos sus equipos terminados).
     * Si es así, cambia el estado de la asignación a ENTREGADO.
     */
    private function verificarAsignacionCompleta(int $asignacionId): void
    {
        $asignacion = Asignacion::findOrFail($asignacionId);

        // Contar equipos NO terminados (aún en estado activo)
        $activos = AsignacionEquipo::where('asignacion_id', $asignacionId)
            ->whereIn('camino', [
                AsignacionEquipo::PENDIENTE,
                AsignacionEquipo::PRE_ASIGNADO,
                AsignacionEquipo::EN_PROCESO,
                AsignacionEquipo::EN_CALIDAD,
            ])
            ->count();

        // Si no hay activos, la asignación está entregada
        if ($activos === 0) {
            $asignacion->update([
                'estatus' => Asignacion::ENTREGADO,
                'fecha_entrega' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Obtiene estadísticas de validaciones para un período
     */
    public function obtenerEstadisticas(?\Carbon\Carbon $desde = null, ?\Carbon\Carbon $hasta = null): array
    {
        return ValidacionCalidad::estadisticas($desde, $hasta);
    }

    /**
     * Obtiene el historial completo de validaciones de un equipo
     */
    public function obtenerHistorialEquipo(int $equipoId): \Illuminate\Database\Eloquent\Collection
    {
        return ValidacionCalidad::where('equipo_id', $equipoId)
            ->recientes()
            ->get();
    }

    /**
     * Obtiene la validación más reciente de un equipo
     */
    public function obtenerUltimaValidacion(int $equipoId): ?ValidacionCalidad
    {
        return ValidacionCalidad::where('equipo_id', $equipoId)
            ->latest()
            ->first();
    }

    /**
     * Cuenta equipos rechazados en calidad
     */
    public function contarRechazados(?\Carbon\Carbon $desde = null, ?\Carbon\Carbon $hasta = null): int
    {
        $query = ValidacionCalidad::rechazadas();

        if ($desde && $hasta) {
            $query->entreFechas($desde, $hasta);
        }

        return $query->count();
    }

    /**
     * Obtiene equipos rechazados en calidad (para mostrar en UI)
     */
    public function obtenerRechazados(
        ?\Carbon\Carbon $desde = null,
        ?\Carbon\Carbon $hasta = null,
        int $limit = 50
    ): \Illuminate\Database\Eloquent\Collection {
        $query = ValidacionCalidad::rechazadas()
            ->with(['equipo', 'validadoPor', 'asignacionEquipo'])
            ->recientes();

        if ($desde && $hasta) {
            $query->entreFechas($desde, $hasta);
        }

        return $query->limit($limit)->get();
    }
}
