<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use App\Models\Asignacion;
use App\Models\AsignacionEquipo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BotonBaja extends Component
{
    public User $usuario;
    
    public bool $modalAbierto = false;
    
    public int $equiposSinEmpezar = 0;
    public int $equiposAMedias = 0;
    
    public $tecnicoReasignarId = null;

    public function mount(User $usuario)
    {
        $this->usuario = $usuario;
    }

    public function intentarBaja()
    {
        // 1. Obtener todas las asignaciones activas del usuario
        $asignacionesActivas = Asignacion::where('tecnico_id', $this->usuario->id)
            ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
            ->with(['equipos' => function ($q) {
                $q->whereNull('fin_en');
            }])
            ->get();

        $this->equiposSinEmpezar = 0;
        $this->equiposAMedias = 0;

        foreach ($asignacionesActivas as $asignacion) {
            foreach ($asignacion->equipos as $ae) {
                if (in_array($ae->camino, [AsignacionEquipo::PENDIENTE, AsignacionEquipo::PRE_ASIGNADO], true)) {
                    $this->equiposSinEmpezar++;
                } else {
                    $this->equiposAMedias++;
                }
            }
        }

        if ($this->equiposAMedias > 0) {
            $this->modalAbierto = true;
        } else {
            // Si solo tiene equipos sin empezar (o no tiene nada), damos de baja directo
            $this->ejecutarBaja(null);
        }
    }

    public function confirmarBaja()
    {
        $this->validate([
            'tecnicoReasignarId' => 'required|exists:users,id',
        ], [
            'tecnicoReasignarId.required' => 'Debes seleccionar a un técnico para reasignar los equipos.',
            'tecnicoReasignarId.exists' => 'El técnico seleccionado no es válido.',
        ]);

        if ((int) $this->tecnicoReasignarId === $this->usuario->id) {
            $this->addError('tecnicoReasignarId', 'No puedes reasignar los equipos al mismo técnico.');
            return;
        }

        $this->ejecutarBaja($this->tecnicoReasignarId);
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
        $this->tecnicoReasignarId = null;
    }

    private function ejecutarBaja($nuevoTecnicoId)
    {
        DB::transaction(function () use ($nuevoTecnicoId) {
            $asignacionesActivas = Asignacion::where('tecnico_id', $this->usuario->id)
                ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
                ->with(['equipos' => function ($q) {
                    $q->whereNull('fin_en');
                }])
                ->get();

            foreach ($asignacionesActivas as $asignacion) {
                $equiposAMediasAsignacion = [];

                foreach ($asignacion->equipos as $ae) {
                    if (in_array($ae->camino, [AsignacionEquipo::PENDIENTE, AsignacionEquipo::PRE_ASIGNADO], true)) {
                        // Liberar
                        $ae->equipo?->update(['estatus_area' => \App\Models\Equipo::AREA_SIN_ASIGNAR]);
                        $ae->delete();
                    } else {
                        // Cerrar histórico del técnico que se va
                        $ae->update([
                            'camino' => AsignacionEquipo::REASIGNADO,
                            'fin_en' => now(),
                            'notas' => trim($ae->notas . "\n[Reasignado por baja de técnico]"),
                        ]);
                        $equiposAMediasAsignacion[] = $ae;
                    }
                }

                // Crear nueva asignación para el nuevo técnico si hay equipos a medias
                if (count($equiposAMediasAsignacion) > 0 && $nuevoTecnicoId) {
                    $nuevaAsignacion = Asignacion::create([
                        'tecnico_id' => $nuevoTecnicoId,
                        'lote_modelo_id' => $asignacion->lote_modelo_id,
                        'cantidad' => count($equiposAMediasAsignacion),
                        'estatus' => Asignacion::EN_PROCESO,
                        'notas_entrega' => 'Asignación generada automáticamente por transferencia/baja.',
                    ]);

                    foreach ($equiposAMediasAsignacion as $viejoAe) {
                        $nuevoAe = AsignacionEquipo::create([
                            'asignacion_id' => $nuevaAsignacion->id,
                            'equipo_id' => $viejoAe->equipo_id,
                            'camino' => AsignacionEquipo::PRE_ASIGNADO, // Reinicia el flujo pero atado a la serie
                            'pre_asignado' => true,
                            'notas' => 'Viene de reasignación del técnico: ' . $this->usuario->nombre,
                        ]);

                        // Transferir solicitudes de piezas pendientes a este nuevo AsignacionEquipo
                        \App\Models\SolicitudPieza::where('asignacion_equipo_id', $viejoAe->id)
                            ->whereIn('estatus', ['PENDIENTE', 'SURTIDA_INVENTARIO'])
                            ->update(['asignacion_equipo_id' => $nuevoAe->id]);
                    }
                }

                // Cerrar la asignación vieja
                $asignacion->update([
                    'estatus' => Asignacion::CANCELADO,
                    'notas_entrega' => trim($asignacion->notas_entrega . "\nCANCELADA por baja del técnico."),
                ]);
            }

            // Desactivar usuario
            $this->usuario->update([
                'is_active' => false,
                'fecha_baja' => now(),
            ]);
        });

        session()->flash('success', 'Usuario dado de baja y equipos procesados correctamente.');
        $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        $tecnicosDisponibles = [];
        if ($this->modalAbierto) {
            $tecnicosDisponibles = User::where('is_active', true)
                ->where('id', '!=', $this->usuario->id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['tecnico', 'lider']);
                })
                ->orderBy('nombre')
                ->get();
        }

        return view('livewire.usuarios.boton-baja', [
            'tecnicosDisponibles' => $tecnicosDisponibles,
        ]);
    }
}
