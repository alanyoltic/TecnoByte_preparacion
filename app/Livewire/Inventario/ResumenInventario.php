<?php

namespace App\Livewire\Inventario;

use App\Models\Equipo;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Roles;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;


class ResumenInventario extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?int $tecnico_id = null;
    public array $tecnicos = [];

    // Filtros
    public ?string $search = null;
    public string $filtroEstado = 'todos';
    public string $filtroLote   = 'todos';
    public string $filtroProveedor = 'todos';

    // Filtros avanzados
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $filtroTipoEquipo = 'todos';
    public string $filtroArea       = 'todos';
    public string $filtroGpu        = 'todos';     // todos|dedicada|sin_dedicada
    public string $filtroBateria    = 'todos';     // todos|baja|media|alta
    public string $filtroSO         = 'todos';

    public int $perPage = 25;

    // Opciones precargadas
    public $lotes = [];
    public $proveedores = [];
    public $tiposEquipo = [];
    public $areas = [];
    public $sistemasOperativos = [];
    public array $selected = [];


    // Modal Resumen
    public bool $modalResumen = false;
    public ?Equipo $equipoResumen = null;

    // 🔥 ESTO ES LO QUE TE ESTÁ FALTANDO (tu Blade usa estas vars)
    public string $resumenTitulo = '';
    public array $resumenLineas = [];

    public function mount(): void
    {
        $this->lotes = Lote::query()->orderByDesc('fecha_llegada')->get();
        $this->proveedores = Proveedor::query()->orderBy('nombre_empresa')->get();

        $this->tiposEquipo = Equipo::query()
            ->select('tipo_equipo')->whereNotNull('tipo_equipo')->distinct()->orderBy('tipo_equipo')
            ->pluck('tipo_equipo')->toArray();

        $this->areas = Equipo::query()
            ->select('area_tienda')->whereNotNull('area_tienda')->distinct()->orderBy('area_tienda')
            ->pluck('area_tienda')->toArray();

        $this->sistemasOperativos = Equipo::query()
            ->select('sistema_operativo')->whereNotNull('sistema_operativo')->distinct()->orderBy('sistema_operativo')
            ->pluck('sistema_operativo')->toArray();

        $rolTecnicoId = Roles::where('slug', 'tecnico')->value('id');

        $this->tecnicos = User::query()
            ->select('id', 'nombre')
            ->whereNull('deleted_at')
            ->when($rolTecnicoId, fn($q) => $q->where('role_id', $rolTecnicoId))
            ->orderBy('nombre')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'nombre' => $u->nombre])
            ->toArray();
    }

    public function updating($name): void
    {
        if (in_array($name, [
            'search','tecnico_id','filtroEstado','filtroLote','filtroProveedor','perPage',
            'fechaDesde','fechaHasta','filtroTipoEquipo','filtroArea','filtroGpu','filtroBateria','filtroSO',
        ], true)) {
            $this->resetPage();
        }
    }

    public function resetFiltros(): void
    {
        $this->search = null;
        $this->filtroEstado = 'todos';
        $this->filtroLote = 'todos';
        $this->filtroProveedor = 'todos';
        $this->tecnico_id = null;
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->filtroTipoEquipo = 'todos';
        $this->filtroArea = 'todos';
        $this->filtroGpu = 'todos';
        $this->filtroBateria = 'todos';
        $this->filtroSO = 'todos';
        $this->perPage = 25;

        $this->resetPage();
    }

    public function abrirResumen(int $equipoId): void
    {
        $this->equipoResumen = Equipo::with([
            'loteModelo.lote.proveedor',
            'registradoPor',
            'gpus',
            'baterias',
            'monitor',
        ])->findOrFail($equipoId);

        // Construye el contenido que tu modal espera
        [$titulo, $lineas] = $this->buildResumen($this->equipoResumen);

        $this->resumenTitulo = $titulo;
        $this->resumenLineas = $lineas;

        $this->modalResumen = true;
    }

    public function cerrarResumen(): void
    {
        $this->modalResumen = false;
        $this->equipoResumen = null;

        $this->resumenTitulo = '';
        $this->resumenLineas = [];
    }

    protected function equiposQuery()
    {
        $q = Equipo::query()
            ->with(['loteModelo.lote.proveedor', 'registradoPor'])
            ->when($this->search, function ($q) {
                $s = trim((string) $this->search);
                $q->where(function ($q) use ($s) {
                    $q->where('numero_serie', 'like', "%{$s}%")
                        ->orWhere('marca', 'like', "%{$s}%")
                        ->orWhere('modelo', 'like', "%{$s}%")
                        ->orWhere('tipo_equipo', 'like', "%{$s}%");
                });
            })
            ->when($this->filtroEstado !== 'todos', fn($q) => $q->where('estatus_general', $this->filtroEstado))
            ->when($this->filtroLote !== 'todos', function ($q) {
                $q->whereHas('loteModelo.lote', fn($q2) => $q2->where('id', $this->filtroLote));
            })
            ->when($this->filtroProveedor !== 'todos', function ($q) {
                $q->whereHas('loteModelo.lote.proveedor', fn($q2) => $q2->where('id', $this->filtroProveedor));
            })
            ->when($this->tecnico_id, fn($q) => $q->where('registrado_por_user_id', $this->tecnico_id));

        if ($this->fechaDesde) $q->whereDate('created_at', '>=', $this->fechaDesde);
        if ($this->fechaHasta) $q->whereDate('created_at', '<=', $this->fechaHasta);

        if ($this->filtroTipoEquipo !== 'todos') $q->where('tipo_equipo', $this->filtroTipoEquipo);
        if ($this->filtroArea !== 'todos') $q->where('area_tienda', $this->filtroArea);

        // ✅ GPU (ya no usar columnas legacy)
        if ($this->filtroGpu === 'dedicada') {
            $q->whereHas('gpus', function ($q2) {
                $q2->where('tipo', 'DEDICADA')->where('activo', 1);
            });
        } elseif ($this->filtroGpu === 'sin_dedicada') {
            $q->whereDoesntHave('gpus', function ($q2) {
                $q2->where('tipo', 'DEDICADA')->where('activo', 1);
            });
        }

        // ✅ Batería (tabla equipo_baterias: salud_percent)
        if ($this->filtroBateria === 'baja') {
            $q->whereHas('baterias', fn($b) => $b->whereNotNull('salud_percent')->where('salud_percent', '<', 70));
        } elseif ($this->filtroBateria === 'media') {
            $q->whereHas('baterias', fn($b) => $b->whereNotNull('salud_percent')->whereBetween('salud_percent', [70, 89]));
        } elseif ($this->filtroBateria === 'alta') {
            $q->whereHas('baterias', fn($b) => $b->whereNotNull('salud_percent')->where('salud_percent', '>=', 90));
        }

        if ($this->filtroSO !== 'todos') $q->where('sistema_operativo', $this->filtroSO);

        // Nuevo: más nuevo (registrado) primero
return $q->orderByDesc('created_at')->orderByDesc('id');

    }

    /**
     * Genera líneas estilo tu imagen.
     * Ajusta aquí si quieres cambiar textos/orden/íconos.
     */
    protected function buildResumen(Equipo $e): array
    {
        $titulo = trim(($e->marca ?? '') . ' ' . ($e->modelo ?? ''));

        // CPU
        $cpuParts = array_filter([
            $e->procesador_modelo,
            $e->procesador_generacion ? "({$e->procesador_generacion})" : null,
            $e->procesador_frecuencia,
            $e->procesador_nucleos ? "{$e->procesador_nucleos} núcleos" : null,
        ]);
        $cpu = $cpuParts ? implode(' ', $cpuParts) : null;

        // RAM
        $ramParts = array_filter([
            $e->ram_total ? "Memoria RAM de {$e->ram_total}" : null,
            $e->ram_tipo,
            $e->ram_expansion_max ? "expandible a {$e->ram_expansion_max}" : null,
        ]);
        $ram = $ramParts ? implode(', ', $ramParts) : null;

        // Almacenamiento (prioriza M.2 si lo tienes en slots_alm_m2)
        $storageParts = array_filter([
            $e->almacenamiento_principal_capacidad ? "SSD de {$e->almacenamiento_principal_capacidad}" : null,
            $e->slots_alm_m2 ? "M.2 ({$e->slots_alm_m2})" : null,
            $e->slots_alm_ssd ? "SSD SATA ({$e->slots_alm_ssd})" : null,
        ]);
        $storage = $storageParts ? implode(' con ', $storageParts) : null;

        // Pantalla / Monitor (tabla equipo_monitores)
        $pantalla = null;
        if ($e->monitor) {
            $pulgadas = $e->monitor->pulgadas ? "{$e->monitor->pulgadas}\"" : null;
            $res = $e->monitor->resolucion ? "Resolución {$e->monitor->resolucion}" : null;

            if ($e->monitor->origen_pantalla === 'INTEGRADA') {
                $pantalla = trim("Pantalla de {$pulgadas} pulgadas");
                if ($res) $pantalla .= " · {$res}";
            } else {
                // externa
                if ((int)$e->monitor->incluido === 1) {
                    $pantalla = trim("Monitor incluido {$pulgadas}");
                    if ($res) $pantalla .= " · {$res}";
                }
            }
        }

        // GPUs
        $gpuIntegrada = $e->gpus?->firstWhere('tipo', 'INTEGRADA');
        $gpuDedicada  = $e->gpus?->firstWhere('tipo', 'DEDICADA');

        $gpuIntTxt = null;
        if ($gpuIntegrada) {
            $gpuIntTxt = trim(implode(' ', array_filter([$gpuIntegrada->marca, $gpuIntegrada->modelo])));
        }

        $gpuDedTxt = null;
        if ($gpuDedicada) {
            $vram = $gpuDedicada->vram ? "{$gpuDedicada->vram} {$gpuDedicada->vram_unidad}" : null;
            $gpuDedTxt = trim(implode(' ', array_filter([$gpuDedicada->marca, $gpuDedicada->modelo, $vram])));
        }

        // Baterías (regla funcional > 60)
        $bats = $e->baterias ?? collect();
        $batLineas = [];
        if ($bats->count() > 0) {
            foreach ($bats->values() as $i => $b) {
                $idx = $bats->count() > 1 ? ('Batería ' . ($i + 1) . ': ') : '';
                $salud = is_null($b->salud_percent) ? null : (int)$b->salud_percent;
                $funcional = is_null($salud) ? null : ($salud >= 60);

                $texto = $idx;
                if (is_null($salud)) {
                    $texto .= "Batería registrada";
                } else {
                    $texto .= $funcional ? "Batería funcional ({$salud}%)" : "Batería NO funcional ({$salud}%)";
                }
                $batLineas[] = $texto;
            }
        }

        // Conectividad / periféricos (muy simple, tú puedes refinar con tus campos)
        $extras = [];
        if ($e->puertos_conectividad) {
            $extras[] = $e->puertos_conectividad; // si ya guardas “Wifi, Bluetooth…”
        }
        if ($e->dispositivos_entrada) {
            $extras[] = $e->dispositivos_entrada; // si ya guardas “Cámara, teclado…”
        }
        if ($e->ethernet_tiene) {
            $extras[] = $e->ethernet_es_gigabit ? 'Ethernet Gigabyte' : 'Ethernet';
        }

        // Puertos (ejemplos)
        $puertos = [];
        if ($e->puertos_hdmi) $puertos[] = "{$e->puertos_hdmi} Puerto HDMI";
        if ($e->puertos_usb_30) $puertos[] = "{$e->puertos_usb_30} Puerto(s) USB 3.0";
        if ($e->puertos_usb_c) $puertos[] = "{$e->puertos_usb_c} Puerto(s) tipo C";
        if ($puertos) $extras = array_merge($extras, $puertos);

        // Teclado
        if ($e->teclado_idioma && $e->teclado_idioma !== 'N/A') {
            $extras[] = "Teclado ({$e->teclado_idioma})";
        }

        $lineas = [];

        // helper local
        $push = function (?string $text, string $icon) use (&$lineas) {
            $text = trim((string) $text);
            if ($text !== '') {
                $lineas[] = ['icon' => $icon, 'text' => $text];
            }
        };

        // orden principal
        $push($cpu, '🧠');
        $push($ram, '💾');
        $push($storage, '🗄️');
        $push($pantalla, '🖥️');

        if ($gpuIntTxt) $push("Gráfica integrada: {$gpuIntTxt}", '🎮');
        if ($gpuDedTxt) $push("Gráfica dedicada: {$gpuDedTxt}", '🚀');

        // baterías
        foreach ($batLineas as $btxt) {
            $push($btxt, '🔋');
        }

        // extras
        foreach ($extras as $ex) {
            $push($ex, '✨');
        }

        return [$titulo ?: 'Equipo', $lineas];

    }

    public function render()
    {
        $equipos = $this->equiposQuery()->paginate($this->perPage);

        $stats = [
            'total'        => Equipo::count(),
            'en_revision'  => Equipo::where('estatus_general', 'En Revisión')->count(),
            'aprobados'    => Equipo::where('estatus_general', 'Aprobado')->count(),
            'finalizados'  => Equipo::where('estatus_general', 'Finalizado')->count(),
        ];

        return view('livewire.inventario.resumen-inventario', [
            'equipos' => $equipos,
            'stats'   => $stats,
            'lotes'   => $this->lotes,
            'proveedores' => $this->proveedores,
            'tiposEquipo' => $this->tiposEquipo,
            'areas' => $this->areas,
            'sistemasOperativos' => $this->sistemasOperativos,
            'tecnicos' => $this->tecnicos,

            // ✅ ESTO ARREGLA tu Undefined variable $resumenTitulo
            'resumenTitulo' => $this->resumenTitulo,
            'resumenLineas' => $this->resumenLineas,
            'modalResumen'  => $this->modalResumen,
            'equipoResumen' => $this->equipoResumen,
        ]);
    }

    public function exportarSeleccionWord()
{
    if (empty($this->selected)) {
        return; // o un toast si usas
    }

    $equipos = Equipo::query()
        ->with(['loteModelo.lote.proveedor', 'gpus', 'baterias', 'monitor'])
        ->whereIn('id', $this->selected)
        ->orderBy('id')
        ->get();

    if ($equipos->isEmpty()) {
        return;
    }

    $phpWord = new PhpWord();
    $phpWord->setDefaultFontName('Calibri');
    $phpWord->setDefaultFontSize(11);

    $section = $phpWord->addSection([
        'marginTop' => 900,
        'marginBottom' => 900,
        'marginLeft' => 900,
        'marginRight' => 900,
    ]);

    // Estilos simples
    $titleStyle = ['bold' => true, 'size' => 16];
    $subStyle   = ['bold' => true, 'size' => 11];
    $mutedStyle = ['italic' => true, 'color' => '666666'];

    foreach ($equipos->values() as $idx => $e) {
        // Título (marca + modelo)
        $titulo = collect([$e->marca, $e->modelo])->filter()->implode(' ');
        $titulo = $titulo !== '' ? $titulo : 'Equipo';

        $section->addText($titulo, $titleStyle);
        $section->addText("Serie: " . ($e->numero_serie ?? '—'), $subStyle);

        // Resumen (usa tu buildResumen ya existente)
        [$resTitulo, $lineas] = $this->buildResumen($e);

        $section->addTextBreak(1);

        // ✅ FORMATO EDITABLE: tabla “icon | texto”
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'CCCCCC',
            'cellMargin' => 100,
        ]);

        if (!empty($lineas)) {
            foreach ($lineas as $l) {
                $icon = is_array($l) ? ($l['icon'] ?? '•') : '•';
                $text = is_array($l) ? ($l['text'] ?? '') : (string)$l;

                $table->addRow();
                $table->addCell(700)->addText($icon);
                $table->addCell(9000)->addText($text);
            }
        } else {
            $section->addText('Sin información para este equipo.', $mutedStyle);
        }

        // ✅ Separación POR EQUIPO (1 página por equipo)
        if ($idx < $equipos->count() - 1) {
            $section->addPageBreak();
        }
    }

    // Guardar temporal y descargar
    $dir = storage_path('app/exports');
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $filename = 'ResumenEquipos_' . now()->format('Ymd_His') . '.docx';
    $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

    IOFactory::createWriter($phpWord, 'Word2007')->save($fullpath);

    return response()->download($fullpath)->deleteFileAfterSend(true);
}

    




}
