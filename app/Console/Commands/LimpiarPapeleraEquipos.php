<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Services\EquipoTraceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LimpiarPapeleraEquipos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipos:limpiar-papelera';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia definitivamente los equipos en la papelera por más de 30 días, guardando un snapshot JSON.';

    /**
     * Execute the console command.
     */
    public function handle(EquipoTraceService $traceService)
    {
        $this->info('Iniciando limpieza de papelera de equipos...');

        // Equipos que llevan más de 30 días en la papelera
        $equiposABorrar = Equipo::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->get();

        if ($equiposABorrar->isEmpty()) {
            $this->info('No hay equipos para limpiar hoy.');
            return;
        }

        $eliminados = 0;
        $errores = 0;

        foreach ($equiposABorrar as $equipo) {
            try {
                DB::transaction(function () use ($equipo, $traceService) {
                    // Generar snapshot independientemente de su estatus
                    $snapshot = $traceService->crearSnapshotEliminacion($equipo, 'Eliminación definitiva automática (más de 30 días en papelera)');

                    // Eliminar dependencias
                    $equipo->gpus()->delete();
                    $equipo->baterias()->delete();
                    if ($equipo->monitor) {
                        $equipo->monitor()->delete();
                    }
                    \App\Models\EquipoAuditoria::where('equipo_id', $equipo->id)->delete();
                    
                    // Borrado físico
                    $equipo->forceDelete();

                    if ($snapshot) {
                        $traceService->marcarEliminacionConfirmada($snapshot);
                    }
                });

                $eliminados++;
            } catch (\Throwable $e) {
                Log::error("Error limpiando equipo {$equipo->id} de papelera: " . $e->getMessage());
                $errores++;
            }
        }

        $this->info("Proceso terminado. Eliminados: {$eliminados}. Errores: {$errores}.");
    }
}
