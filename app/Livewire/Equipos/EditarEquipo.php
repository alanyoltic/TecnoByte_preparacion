<?php

namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Livewire\Forms\EquipoForm;
use App\Models\{
    Equipo,
    Lote,
    Proveedor,
    LoteModeloRecibido,
    EquipoBateria,
    EquipoMonitor,
    EquipoGpu
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EditarEquipo extends Component
{
    public Equipo $equipo;

    /**
     * ✅ Form Object estable
     * En Blade usar: wire:model="form.campo"
     */
    public EquipoForm $form;

    // =======================
    // Catálogos / listas (UI)
    // =======================
    public array $proveedores = [];
    public array $lotes = [];                 // OJO: aquí guardamos MODELOS (objetos) -> evita error fecha_llegada
    public array $modelosLote = [];
    public array $lotesTerminadosIds = [];

    public array $monitorEntradasOptions = [
        'HDMI', 'Mini HDMI', 'VGA', 'DVI',
        'DisplayPort', 'Mini DisplayPort',
        'USB 2.0', 'USB 3.0', 'USB 3.1', 'USB 3.2', 'USB-C',
    ];

    // =======================
    // Control de cambios (opcional)
    // =======================
    public array $baseline = [];
    public bool $hasChanges = false;

    // =======================
    // MAPS (UI -> columnas DB)
    // =======================
    private const MAP_USB = [
        'USB 2.0'    => 'puertos_usb_2',
        'USB 3.0'    => 'puertos_usb_30',
        'USB 3.1'    => 'puertos_usb_31',
        'USB 3.2'    => 'puertos_usb_32',
        'USB-C'      => 'puertos_usb_c',
        
    ];

    private const MAP_VIDEO = [
        'HDMI'             => 'puertos_hdmi',
        'Mini HDMI'        => 'puertos_mini_hdmi',
        'VGA'              => 'puertos_vga',
        'DVI'              => 'puertos_dvi',
        'DisplayPort'      => 'puertos_displayport',
        'Mini DisplayPort' => 'puertos_mini_dp',
    ];

    private const MAP_LECTORES = [
        'SD'        => 'lectores_sd',
        'microSD'   => 'lectores_microsd',
        'SmartCard' => 'lectores_sc',
        'eSATA'     => 'lectores_esata',
        'SIM'       => 'lectores_sim',
    ];

    private const MAP_SLOTS = [
        'SSD'       => 'slots_alm_ssd',
        'M.2'       => 'slots_alm_m2',
        'M.2 MICRO' => 'slots_alm_m2_micro',
        'HDD'       => 'slots_alm_hdd',
        'MSATA'     => 'slots_alm_msata',
    ];

    // Entradas monitor -> columnas equipo_monitores (ajusta si tu tabla difiere)
    private const MAP_MONITOR_IN = [
        'HDMI'             => 'in_hdmi',
        'Mini HDMI'        => 'in_mini_hdmi',
        'VGA'              => 'in_vga',
        'DVI'              => 'in_dvi',
        'DisplayPort'      => 'in_displayport',
        'Mini DisplayPort' => 'in_mini_displayport',
        'USB 2.0'          => 'in_usb_2',
        'USB 3.0'          => 'in_usb_3',
        'USB 3.1'          => 'in_usb_31',
        'USB 3.2'          => 'in_usb_32',
        'USB-C'            => 'in_usb_c',
    ];

    // =======================
    // Lifecycle
    // =======================



    public ?string $motivo = null;     // input opcional
    public bool $pedirMotivo = false;  // para UI/validación

    public bool $isHydrating = true;

    public function mount(Equipo $equipo): void
    {


        $this->isHydrating = true;
    
        $this->equipo = $equipo;

        // 1) Catálogos (como objetos)
        $this->cargarCatalogos();

        // 2) Hidratar Form desde Equipo (tu Form ya tiene esto en Editar)
        //    (si tu método se llama distinto, cámbialo aquí)
        $this->form->setEquipo($equipo);

        $this->hidratarLoteModeloProveedorDesdeLoteModelo();
        $this->hidratarRelacionadas();

        $this->hidratarDinamicosDesdeEquipo();

        $this->ensureDefaultsEnForm();

        $this->hidratarDinamicosDesdeEquipo();
        $this->syncPuertosExpansionesToColumns();

        // 3) Asegurar lote_id/proveedor/modelo (porque en equipos guardas lote_modelo_id)
        $this->hidratarLoteModeloProveedorDesdeLoteModelo();

        // 4) Hidratar tablas hijas a form (baterías/monitor/gpus)
        $this->hidratarRelacionadas();

        // 5) Defaults sin pisar data existente
        $this->ensureDefaultsEnForm();

    $this->baseline = method_exists($this->form, 'snapshotPersistible')
        ? $this->form->snapshotPersistible()
        : $this->form->all();

    $this->hasChanges = false;
    $this->isHydrating = false;
    }

    private function ensureDefaultsEnForm(): void
    {
        $f = $this->form;

        $f->almacenamiento_secundario_capacidad = $f->almacenamiento_secundario_capacidad ?: 'N/A';
        $f->almacenamiento_secundario_tipo      = $f->almacenamiento_secundario_tipo ?: 'N/A';
        $f->teclado_idioma                      = $f->teclado_idioma ?: 'N/A';

        $f->bateria_tiene  = (bool) ($f->bateria_tiene ?? true);
        $f->bateria2_tiene = (bool) ($f->bateria2_tiene ?? false);

        $f->ethernet_tiene      = (bool) ($f->ethernet_tiene ?? false);
        $f->ethernet_es_gigabit = (bool) ($f->ethernet_es_gigabit ?? false);

        $f->puertos_usb ??= [];
        $f->puertos_video ??= [];
        $f->lectores ??= [];
        $f->slots_almacenamiento ??= [];
        $f->monitor_entradas_rows ??= [];

        $f->gpu_integrada_tiene = (bool) ($f->gpu_integrada_tiene ?? false);
        $f->gpu_dedicada_tiene  = (bool) ($f->gpu_dedicada_tiene ?? false);
        $f->gpu_integrada_vram_unidad = $f->gpu_integrada_vram_unidad ?: 'GB';
        $f->gpu_dedicada_vram_unidad  = $f->gpu_dedicada_vram_unidad ?: 'GB';

        $f->monitor_incluido = $f->monitor_incluido ?: 'NO';
    }

    private function hidratarLoteModeloProveedorDesdeLoteModelo(): void
    {
        $f = $this->form;

        if (!$f->lote_modelo_id) return;

        $lm = LoteModeloRecibido::with('lote')->find($f->lote_modelo_id);
        if (!$lm) return;

        $f->lote_id = $lm->lote_id;
        $f->marca = $lm->marca;
        $f->modelo = $lm->modelo;

        // proveedor_id por lote (fuente de verdad)
        $f->proveedor_id = $lm->lote?->proveedor_id ?? $this->equipo->proveedor_id;

        // Cargar modelos del lote para el select (incluye el actual aunque esté lleno)
        $this->cargarModelosDelLote((int)$lm->lote_id, true);
    }

    private function hidratarRelacionadas(): void
    {
        $f = $this->form;

        // =======================
        // Monitor / Pantalla
        // =======================
        $m = EquipoMonitor::where('equipo_id', $this->equipo->id)->first();

        if ($m) {
            if ($m->origen_pantalla === 'INTEGRADA') {
                $f->monitor_incluido = 'NO';

                $f->pantalla_pulgadas   = $m->pulgadas;
                $f->pantalla_resolucion = $m->resolucion;
                $f->pantalla_es_touch   = (bool)$m->es_touch;

                // limpiar externo
                $f->monitor_pulgadas = null;
                $f->monitor_resolucion = null;
                $f->monitor_es_touch = false;
                $f->monitor_entradas_rows = [];

                $f->monitor_detalles_esteticos_checks = '';
                $f->monitor_detalles_esteticos_otro = null;
                $f->monitor_detalles_funcionamiento_checks = '';
                $f->monitor_detalles_funcionamiento_otro = null;
            } else {
                // EXTERNA
                $f->monitor_incluido = ((int)($m->incluido ?? 1) === 1) ? 'SI' : 'NO';

                $f->monitor_pulgadas   = $m->pulgadas;
                $f->monitor_resolucion = $m->resolucion;
                $f->monitor_es_touch   = (bool)$m->es_touch;

                // limpiar integrada
                $f->pantalla_pulgadas = null;
                $f->pantalla_resolucion = null;
                $f->pantalla_es_touch = false;

                $f->monitor_detalles_esteticos_checks = (string)($m->detalles_esteticos_checks ?? '');
                $f->monitor_detalles_esteticos_otro  = (string)($m->detalles_esteticos_otro ?? '');

                $f->monitor_detalles_funcionamiento_checks = (string)($m->detalles_funcionamiento_checks ?? '');
                $f->monitor_detalles_funcionamiento_otro  = (string)($m->detalles_funcionamiento_otro ?? '');

                // Entradas in_* -> rows
                $rows = [];
                foreach (self::MAP_MONITOR_IN as $label => $col) {
                    $qty = (int)($m->{$col} ?? 0);
                    if ($qty > 0) $rows[] = ['tipo' => $label, 'cantidad' => $qty];
                }
                $f->monitor_entradas_rows = $rows;
            }
        } else {
            // Defaults si no hay registro
            $f->monitor_incluido = $f->monitor_incluido ?: 'NO';
            $f->monitor_entradas_rows = $f->monitor_entradas_rows ?: [];

            $f->pantalla_pulgadas = $f->pantalla_pulgadas ?: null;
            $f->pantalla_resolucion = $f->pantalla_resolucion ?: null;
            $f->pantalla_es_touch = (bool)($f->pantalla_es_touch ?? false);

            $f->monitor_pulgadas = $f->monitor_pulgadas ?: null;
            $f->monitor_resolucion = $f->monitor_resolucion ?: null;
            $f->monitor_es_touch = (bool)($f->monitor_es_touch ?? false);

            $f->monitor_detalles_esteticos_checks = $f->monitor_detalles_esteticos_checks ?: '';
            $f->monitor_detalles_esteticos_otro = $f->monitor_detalles_esteticos_otro ?: null;
            $f->monitor_detalles_funcionamiento_checks = $f->monitor_detalles_funcionamiento_checks ?: '';
            $f->monitor_detalles_funcionamiento_otro = $f->monitor_detalles_funcionamiento_otro ?: null;
        }

        // =======================
        // Baterías
        // =======================
        $bats = EquipoBateria::query()
            ->where('equipo_id', $this->equipo->id)
            ->orderBy('id')
            ->get()
            ->values();

        $f->bateria_tiene = $bats->isNotEmpty();

        $f->bateria1_tipo  = $bats[0]->tipo ?? null;
        $f->bateria1_salud = isset($bats[0]) ? (string)((int)$bats[0]->salud_percent) : null;

        $f->bateria2_tiene = isset($bats[1]);
        $f->bateria2_tipo  = $bats[1]->tipo ?? null;
        $f->bateria2_salud = isset($bats[1]) ? (string)((int)$bats[1]->salud_percent) : null;

        // =======================
        // GPUs (equipo_gpus)
        // =======================
        $gpus = EquipoGpu::query()
            ->where('equipo_id', $this->equipo->id)
            ->get()
            ->keyBy(fn ($g) => strtoupper((string)$g->tipo));

        $ig = $gpus->get('INTEGRADA');
        $dg = $gpus->get('DEDICADA');

        $f->gpu_integrada_tiene = (bool) ($ig?->activo ?? false);
        $f->gpu_integrada_marca = $ig?->marca;
        $f->gpu_integrada_modelo = $ig?->modelo;
        $f->gpu_integrada_vram = $ig?->vram !== null ? (string)((int)$ig->vram) : null;
        $f->gpu_integrada_vram_unidad = $ig?->vram_unidad ?: ($f->gpu_integrada_vram_unidad ?: 'GB');

        $f->gpu_dedicada_tiene = (bool) ($dg?->activo ?? false);
        $f->gpu_dedicada_marca = $dg?->marca;
        $f->gpu_dedicada_modelo = $dg?->modelo;
        $f->gpu_dedicada_vram = $dg?->vram !== null ? (string)((int)$dg->vram) : null;
        $f->gpu_dedicada_vram_unidad = $dg?->vram_unidad ?: ($f->gpu_dedicada_vram_unidad ?: 'GB');
    }


    private function hidratarDinamicosDesdeEquipo(): void
    {
        // 1) Reconstruir filas dinámicas de puertos/lectores/slots desde columnas del equipo
        $this->form->puertos_usb = $this->buildRowsFromMap(self::MAP_USB);
        $this->form->puertos_video = $this->buildRowsFromMap(self::MAP_VIDEO);
        $this->form->lectores = $this->buildRowsFromMap(self::MAP_LECTORES);

        $this->form->slots_almacenamiento = $this->buildRowsFromSlots([
            'SSD'       => 'slots_alm_ssd',
            'M.2'       => 'slots_alm_m2',
            'M.2 MICRO' => 'slots_alm_m2_micro',
            'HDD'       => 'slots_alm_hdd',
            'MSATA'     => 'slots_alm_msata',
        ]);

        // 2) Parsear detalles_esteticos / detalles_funcionamiento a checks + otro
        [$checksE, $otroE] = $this->parseChecksAndOtro($this->form->detalles_esteticos ?? '');
        $this->form->detalles_esteticos_checks = $checksE;
        $this->form->detalles_esteticos_otro = $otroE;

        [$checksF, $otroF] = $this->parseChecksAndOtro($this->form->detalles_funcionamiento ?? '');
        $this->form->detalles_funcionamiento_checks = $checksF;
        $this->form->detalles_funcionamiento_otro = $otroF;
    }

    /**
     * Construye rows tipo: [ ['tipo' => 'USB 3.0', 'cantidad' => 2], ... ]
     * a partir de un MAP label => columna (ej. 'USB 3.0' => 'puertos_usb_30').
     */
    private function buildRowsFromMap(array $mapLabelToColumn): array
    {
        $rows = [];

        foreach ($mapLabelToColumn as $label => $col) {
            $val = $this->form->{$col} ?? null;

            if ($val === null || $val === '' || $val === '0' || $val === 0) {
                continue;
            }

            $qty = is_numeric($val) ? (int) $val : 1;
            if ($qty <= 0) continue;

            $rows[] = [
                'tipo' => $label,
                'cantidad' => $qty,
            ];
        }

        return $rows;
    }

    private function buildRowsFromSlots(array $labelToColumn): array
    {
        $rows = [];

        foreach ($labelToColumn as $label => $col) {
            $val = $this->form->{$col} ?? null;

            if ($val === null || $val === '' || $val === '0' || $val === 0) {
                continue;
            }

            $qty = is_numeric($val) ? (int) $val : 1;
            if ($qty <= 0) continue;

            $rows[] = [
                'tipo' => $label,
                'cantidad' => $qty,
            ];
        }

        return $rows;
    }

    /**
     * Convierte:
     * "A, B, C | Otro: algo"
     * en: [['A','B','C'], 'algo']
     *
     * Si viene "N/A", regresa ['N/A'] y otro null.
     */
    private function parseChecksAndOtro(string $text): array
    {
        $text = trim($text);
        if ($text === '') return [[], null];

        $otro = null;

        // separa por "|"
        $parts = array_map('trim', explode('|', $text));

        // primera parte: checks CSV
        $checksPart = $parts[0] ?? '';
        $checks = array_values(array_filter(array_map('trim', explode(',', $checksPart))));

        // detectar "Otro:"
        foreach ($parts as $p) {
            if (stripos($p, 'otro:') === 0) {
                $otro = trim(substr($p, 5));
            }
        }

        // si contiene N/A -> deja solo N/A
        if (in_array('N/A', $checks, true)) {
            return [['N/A'], null];
        }

        return [$checks, ($otro !== '' ? $otro : null)];
    }


    // =======================
    // Catálogos (igual que Registrar)
    // =======================
    private function cargarCatalogos(): void
    {
        $this->proveedores = Proveedor::orderBy('nombre_empresa')->get()->all();

        $todosLotes = Lote::with([
                'proveedor',
                'modelosRecibidos' => fn ($q) => $q->withCount('equipos'),
            ])
            ->orderBy('fecha_llegada', 'desc')
            ->get();

        $lotesConPendientes = collect();
        $lotesTerminados = collect();

        foreach ($todosLotes as $lote) {
            $tienePendientes = $lote->modelosRecibidos->contains(function ($m) {
                $total = (int) $m->cantidad_recibida;
                $registrados = (int) ($m->equipos_count ?? 0);
                return $registrados < $total;
            });

            $tienePendientes ? $lotesConPendientes->push($lote) : $lotesTerminados->push($lote);
        }

        // Tomamos 2 terminados como en registrar
        $terminadosTomados = $lotesTerminados->take(2);

        // Asegurar que el lote actual esté incluido aunque sea terminado viejo
        $currentLoteId = null;
        if ($this->equipo?->lote_modelo_id) {
            $lm = LoteModeloRecibido::find($this->equipo->lote_modelo_id);
            $currentLoteId = $lm?->lote_id;
        }

        if ($currentLoteId) {
            $yaIncluido = $lotesConPendientes->contains('id', $currentLoteId)
                || $terminadosTomados->contains('id', $currentLoteId);

            if (!$yaIncluido) {
                $loteActual = $lotesTerminados->firstWhere('id', $currentLoteId);
                if ($loteActual) {
                    $terminadosTomados->push($loteActual);
                }
            }
        }

        $this->lotesTerminadosIds = $terminadosTomados->pluck('id')->unique()->values()->toArray();
        $this->lotes = $lotesConPendientes->concat($terminadosTomados)->values()->all();
    }

    // =======================
    // Lote / Modelo dependiente
    // =======================
    public function actualizarLote($loteId): void
    {
        $f = $this->form;

        $f->lote_id = $loteId ?: null;
        $f->lote_modelo_id = null;
        $f->marca = null;
        $f->modelo = null;
        $this->modelosLote = [];

        if (!$loteId) {
            $f->proveedor_id = null;
            return;
        }

        $lote = Lote::with(['proveedor', 'modelosRecibidos'])->find($loteId);
        if (!$lote) {
            $f->proveedor_id = null;
            return;
        }

        $f->proveedor_id = $lote->proveedor_id;

        // En editar: mostramos modelos del lote, incluyendo "llenos" pero marcados
        $this->cargarModelosDelLote((int)$loteId, false);
    }

    private function cargarModelosDelLote(int $loteId, bool $includeAllEvenIfFull): void
    {
        $f = $this->form;

        $actualModeloId = (int) ($f->lote_modelo_id ?? 0);
        $actualEquipoId = (int) ($this->equipo->id ?? 0);

        $modelos = LoteModeloRecibido::query()
            ->where('lote_id', $loteId)
            ->withCount('equipos')
            ->get()
            ->map(function ($m) use ($actualModeloId, $actualEquipoId, $includeAllEvenIfFull) {
                $total = (int) $m->cantidad_recibida;
                $registrados = (int) ($m->equipos_count ?? 0);

                // Si este es el modelo actual del equipo, “liberamos” 1 del conteo para permitir permanecer
                if ($actualModeloId && (int)$m->id === $actualModeloId && $actualEquipoId) {
                    $registrados = max(0, $registrados - 1);
                }

                $hayCupo = $registrados < $total;

                if (!$includeAllEvenIfFull && !$hayCupo && (int)$m->id !== $actualModeloId) {
                    // lo omitimos si está lleno y no es el actual
                    return null;
                }

                return [
                    'id' => $m->id,
                    'marca' => $m->marca,
                    'modelo' => $m->modelo,
                    'hay_cupo' => $hayCupo,
                    'total' => $total,
                    'registrados' => $registrados,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        $this->modelosLote = $modelos;
    }

    public function actualizarModelo($modeloId): void
    {
        $f = $this->form;

        $f->lote_modelo_id = $modeloId ?: null;
        $f->marca = null;
        $f->modelo = null;

        if (!$modeloId) return;

        $lm = LoteModeloRecibido::find($modeloId);
        if (!$lm) return;

        $f->marca = $lm->marca;
        $f->modelo = $lm->modelo;
    }

    // =======================
    // Helpers UI dinámicos (EN FORM)
    // =======================
    public function addPuertoUsb(): void { $this->form->puertos_usb[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoUsb($i): void { $this->unsetIndex($this->form->puertos_usb, $i); }

    public function addPuertoVideo(): void { $this->form->puertos_video[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoVideo($i): void { $this->unsetIndex($this->form->puertos_video, $i); }

    public function addLector(): void { $this->form->lectores[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeLector($i): void { $this->unsetIndex($this->form->lectores, $i); }

    public function addSlotAlmacenamiento(): void { $this->form->slots_almacenamiento[] = ['tipo' => '', 'cantidad' => null]; }
    public function removeSlotAlmacenamiento($i): void { $this->unsetIndex($this->form->slots_almacenamiento, $i); }

    public function addMonitorEntrada(): void { $this->form->monitor_entradas_rows[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeMonitorEntrada($i): void { $this->unsetIndex($this->form->monitor_entradas_rows, $i); }

    private function unsetIndex(array &$arr, $i): void
    {
        if (!isset($arr[$i])) return;
        unset($arr[$i]);
        $arr = array_values($arr);
        $this->recalcChanges();
    }

    // =======================
    // Tipo equipo: integrada vs externa
    // =======================
    private function tipoKey(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        $v = preg_replace('/\s+/', ' ', $v);
        return $v;
    }

    private function isLaptopLikeTipo(?string $tipo): bool
    {
        return in_array($this->tipoKey($tipo), ['laptop','2 en 1','all in one','tablet','gamer'], true)
            || in_array(trim((string)$tipo), ['LAPTOP','2 EN 1','ALL IN ONE','TABLET','GAMER'], true);
    }

    private function isPcLikeTipo(?string $tipo): bool
    {
        return in_array($this->tipoKey($tipo), ['escritorio','micro pc',], true)
            || in_array(trim((string)$tipo), ['ESCRITORIO','MICRO PC',], true);
    }

    public function getPantallaIntegradaProperty(): bool
    {
        return $this->isLaptopLikeTipo($this->form->tipo_equipo ?? null);
    }

    public function getPantallaExternaProperty(): bool
    {
        return $this->isPcLikeTipo($this->form->tipo_equipo ?? null);
    }

    // =======================
    // Livewire hooks (igual que Registrar)
    // =======================
    public function updated($name, $value): void
    {
        if ($this->isHydrating) return;
        $this->recalcChanges();
    }


    public function updatedFormEthernetTiene($value): void
    {
        if (!(bool)$value) $this->form->ethernet_es_gigabit = false;
    }

    public function updatedFormTipoEquipo($value): void
    {
        $tipo = strtoupper(trim((string) $value));

        $pantallaIntegrada = in_array($tipo, ['LAPTOP','2 EN 1','ALL IN ONE','TABLET','GAMER'], true);
        $pantallaExterna   = in_array($tipo, ['ESCRITORIO','MICRO PC',], true);

        if ($pantallaIntegrada) {
            // Limpia monitor externo
            $this->form->monitor_incluido = null;
            $this->form->monitor_pulgadas = null;
            $this->form->monitor_resolucion = null;
            $this->form->monitor_entradas_rows = [];
            $this->form->monitor_detalles_esteticos_checks = '';
            $this->form->monitor_detalles_esteticos_otro = null;
            $this->form->monitor_detalles_funcionamiento_checks = '';
            $this->form->monitor_detalles_funcionamiento_otro = null;
        }

        if ($pantallaExterna) {
            // Limpia pantalla integrada
            $this->form->pantalla_pulgadas = null;
            $this->form->pantalla_resolucion = null;
            $this->form->pantalla_es_touch = false;
        }

        // Regla GPU por tipo
        $this->syncGpuDefaultsByTipo();
    }

    public function updatedFormMonitorIncluido($value): void
    {
        if ($value !== 'SI') {
            $this->form->monitor_pulgadas = null;
            $this->form->monitor_resolucion = null;

            $this->form->monitor_entradas_rows = [];

            $this->form->monitor_detalles_esteticos_checks = '';
            $this->form->monitor_detalles_esteticos_otro = null;

            $this->form->monitor_detalles_funcionamiento_checks = '';
            $this->form->monitor_detalles_funcionamiento_otro = null;
        }
    }

    public function updatedFormBateriaTiene($value): void
    {
        if (! $value) {
            $this->form->bateria1_tipo = null;
            $this->form->bateria1_salud = null;
        }
    }

    public function updatedFormBateria2Tiene($value): void
    {
        if (! $value) {
            $this->form->bateria2_tipo = null;
            $this->form->bateria2_salud = null;
        }
    }

    // RAM hooks (los mismos que tu registrar)
    public function updatedFormRamEsSoldada($value): void
    {
        if (! $value) {
            $this->form->ram_cantidad_soldada = '';

            $this->form->ram_sin_slots = false;

            if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) {
                $this->form->ram_slots_totales = '';
            }
            if ($this->form->ram_expansion_max === '0 GB') {
                $this->form->ram_expansion_max = '';
            }
        }
    }

    public function updatedFormRamSinSlots($value): void
    {
        if ($value && ! $this->form->ram_es_soldada) {
            $this->form->ram_sin_slots = false;
            return;
        }

        if ($value) {
            $this->form->ram_slots_totales = '0';
            $this->form->ram_expansion_max = '0 GB';
            return;
        }

        if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) {
            $this->form->ram_slots_totales = '';
        }
        if ($this->form->ram_expansion_max === '0 GB') {
            $this->form->ram_expansion_max = '';
        }
    }

    public function updatedFormRamSlotsTotales($value): void
    {
        $isZero = ($value === '0' || $value === 0);

        if ($isZero && ! $this->form->ram_es_soldada) {
            $this->form->ram_slots_totales = '';
            $this->form->ram_sin_slots = false;
            $this->form->ram_expansion_max = '';
            return;
        }

        $this->form->ram_sin_slots = $isZero;

        if ($isZero) {
            $this->form->ram_expansion_max = '0 GB';
        } elseif ($this->form->ram_expansion_max === '0 GB') {
            $this->form->ram_expansion_max = '';
        }
    }

    public function updatedFormRamExpansionMax($value): void
    {
        if ($value === '0 GB' && ! $this->form->ram_es_soldada) {
            $this->form->ram_expansion_max = '';
            return;
        }

        if ($value === '0 GB') {
            $this->form->ram_slots_totales = '0';
            $this->form->ram_sin_slots = true;
            return;
        }

        if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) {
            if (! $this->form->ram_sin_slots) {
                $this->form->ram_slots_totales = '';
            }
        }
    }

    // =======================
    // GPU hooks (igual que Registrar)
    // =======================
    private function syncGpuDefaultsByTipo(): void
    {
        if ($this->isLaptopLikeTipo($this->form->tipo_equipo)) {
            $this->form->gpu_integrada_tiene = true;
        }
    }

    public function updatedFormGpuIntegradaTiene(): void
    {
        if ($this->isLaptopLikeTipo($this->form->tipo_equipo)) {
            $this->form->gpu_integrada_tiene = true;
            return;
        }

        if (!$this->form->gpu_integrada_tiene) {
            $this->form->gpu_integrada_marca = null;
            $this->form->gpu_integrada_modelo = null;
            $this->form->gpu_integrada_vram = null;
        }
    }

    public function updatedFormGpuDedicadaTiene(): void
    {
        if (!$this->form->gpu_dedicada_tiene) {
            $this->form->gpu_dedicada_marca = null;
            $this->form->gpu_dedicada_modelo = null;
            $this->form->gpu_dedicada_vram = null;
            $this->form->gpu_dedicada_vram_unidad = 'GB';
        }
    }

    public function updatedFormGpuIntegradaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->form->gpu_integrada_marca, ['INTEL','AMD','NVIDIA'], true)) {
            $this->form->gpu_integrada_marca = '';
        }
    }

    public function updatedFormGpuDedicadaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->form->gpu_dedicada_marca, ['INTEL','AMD','NVIDIA'], true)) {
            $this->form->gpu_dedicada_marca = '';
        }
    }

    // =======================
    // Changes
    // =======================
    public function recalcChanges(): void
    {
        // ✅ Sincroniza dinámicos -> columnas antes de comparar baseline
        $this->syncPuertosExpansionesToColumns();

        $current = method_exists($this->form, 'snapshotPersistible')
            ? $this->form->snapshotPersistible()
            : $this->form->all();

        $this->hasChanges = $current !== $this->baseline;
    }


    private function syncPuertosExpansionesToColumns(): void
    {
        $f = $this->form;

        // Reset a 0/null primero (MUY IMPORTANTE para detectar borrados)
        foreach (self::MAP_USB as $label => $col) {
            $f->{$col} = null;
        }
        foreach (self::MAP_VIDEO as $label => $col) {
            $f->{$col} = null;
        }
        foreach (self::MAP_LECTORES as $label => $col) {
            $f->{$col} = null;
        }

        $f->slots_alm_ssd = null;
        $f->slots_alm_m2 = null;
        $f->slots_alm_m2_micro = null;
        $f->slots_alm_hdd = null;
        $f->slots_alm_msata = null;

        // Aplicar desde rows actuales
        $this->mapSlotsToDbColumns();
        $this->applyAggregatesToEquipoColumns();
    }



    // =======================
    // Validación (prefijada a form.)
    // =======================
    protected function rules(): array
    {
        $f = $this->form;

        $pref = $this->prefixRules($f->rules());

        // Parche para unique de numero_serie en edición (ignora el equipo actual)
        if (array_key_exists('form.numero_serie', $pref)) {
            $pref['form.numero_serie'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('equipos', 'numero_serie')->ignore($this->equipo->id),
            ];
        }

        $extra = [
            'form.lote_id'        => 'required|exists:lotes,id',
            'form.lote_modelo_id' => 'required|exists:lote_modelos_recibidos,id',
            'form.proveedor_id'   => 'required|exists:proveedores,id',

            'form.modelo'         => 'required|string|max:255',

            'form.monitor_incluido' => 'nullable|in:SI,NO',

            'form.monitor_entradas_rows' => 'array',
            'form.monitor_entradas_rows.*.tipo' => 'nullable|in:' . implode(',', $this->monitorEntradasOptions),
            'form.monitor_entradas_rows.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.slots_almacenamiento' => 'array',
            'form.slots_almacenamiento.*.tipo' => 'nullable|in:SSD,M.2,M.2 MICRO,HDD,MSATA',
            'form.slots_almacenamiento.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.puertos_usb' => 'array',
            'form.puertos_usb.*.tipo' => 'nullable|string|max:50',
            'form.puertos_usb.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.puertos_video' => 'array',
            'form.puertos_video.*.tipo' => 'nullable|string|max:50',
            'form.puertos_video.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.lectores' => 'array',
            'form.lectores.*.tipo' => 'nullable|string|max:50',
            'form.lectores.*.cantidad' => 'nullable|integer|min:1|max:10',

            'form.procesador_frecuencia' => ['nullable', 'string', 'max:20'],
        ];

        return array_merge($pref, $extra);
    }

    protected function messages(): array
    {
        return [
            'form.lote_id.required' => 'Selecciona un lote.',
            'form.lote_modelo_id.required' => 'Selecciona un modelo del lote.',
            'form.proveedor_id.required' => 'Selecciona un proveedor.',
            'form.numero_serie.unique' => 'Este número de serie ya está registrado.',
            'form.puertos_conectividad.required' => 'Selecciona al menos un puerto de conectividad.',
            'form.dispositivos_entrada.required' => 'Selecciona al menos un dispositivo de entrada.',
            'form.procesador_frecuencia.max' => 'La frecuencia del procesador no debe exceder 20 caracteres (ej: 3.6 GHz).',
        ];
    }

    private function prefixRules(array $rules): array
    {
        $out = [];
        foreach ($rules as $k => $v) $out["form.$k"] = $v;
        return $out;
    }




    private function currentSnapshotForAudit(): array
{
    // Importante: antes de tomar snapshot, asegúrate que derivados (puertos/slots/etc) estén sincronizados
    if (method_exists($this, 'syncPuertosExpansionesToColumns')) {
        $this->syncPuertosExpansionesToColumns();
    }

    return method_exists($this->form, 'snapshotPersistible')
        ? $this->form->snapshotPersistible()
        : $this->form->all();
}

/**
 * Construye un diff simple:
 * [
 *   'campo' => ['from' => old, 'to' => new],
 *   ...
 * ]
 */
private function buildAuditDiff(array $before, array $after): array
{
    // ignora ruido típico
    $ignore = [
        'updated_at', 'created_at',
    ];

    $diff = [];

    // Unimos llaves (por si hay campos presentes en uno y no en otro)
    $keys = array_unique(array_merge(array_keys($before), array_keys($after)));

    foreach ($keys as $k) {
        if (in_array($k, $ignore, true)) continue;

        $old = $before[$k] ?? null;
        $new = $after[$k] ?? null;

        // Normaliza strings vacíos vs null para que no cuente como cambio “falso”
        $oldN = $this->normAuditVal($old);
        $newN = $this->normAuditVal($new);

        if ($oldN !== $newN) {
            $diff[$k] = ['from' => $old, 'to' => $new];
        }
    }

    return $diff;
}

private function normAuditVal($v)
{
    // normaliza para comparar
    if ($v === '') return null;
    if (is_string($v)) return trim($v);
    if (is_bool($v)) return (int)$v;
    return $v;
}

/**
 * Reglas:
 * - motivo obligatorio si: ELIMINADO, RESTAURADO (si lo manejas), REASIGNADO (lote/modelo/proveedor),
 *   o cambió numero_serie
 * - EDITADO normal: motivo opcional
 */
private function classifyAuditAction(array $diff): array
{
    $keys = array_keys($diff);

    $cambioRelacion = false;
    foreach (['lote_id', 'lote_modelo_id', 'proveedor_id'] as $k) {
        if (in_array($k, $keys, true)) {
            $cambioRelacion = true;
            break;
        }
    }

    $cambioSerie = in_array('numero_serie', $keys, true);

    // Si tienes acciones de eliminar/restaurar en este mismo componente, aquí se detectan por diff
    // (ejemplo: deleted_at)
    if (in_array('deleted_at', $keys, true)) {
        $to = $diff['deleted_at']['to'] ?? null;
        if ($to !== null) {
            return ['ELIMINADO', true];
        }
        return ['RESTAURADO', true];
    }

    if ($cambioRelacion) {
        return ['REASIGNADO', true];
    }

    if ($cambioSerie) {
        return ['SERIE_CAMBIADA', true];
    }

    return ['EDITADO', false];
}

private function registrarAuditoria(int $equipoId, string $accion, ?string $motivo, array $cambios): void
{
    // si no hubo cambios, no registres nada
    if (empty($cambios)) return;

    \App\Models\EquipoAuditoria::create([
        'equipo_id'   => $equipoId,
        'user_id'     => (int) auth()->id(),
        'accion'      => $accion,
        'motivo'      => filled($motivo) ? $motivo : null,
        'cambios'     => $cambios,                 // cast a array/json
        'ip'          => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);
}


    // =======================
    // Guardar (update) - alias
    // =======================
    public function actualizar(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: $e->validator->errors()->first());
            throw $e;
        }

        $this->recalcChanges();
        if (!$this->hasChanges) {
            $this->dispatch('toast', type: 'info', message: 'No hay cambios por guardar.');
            return;
        }

        $f = $this->form;

        // Pre-procesamiento (igual que registrar)
        $f->almacenamiento_secundario_capacidad = $f->almacenamiento_secundario_capacidad ?: 'N/A';
        $f->almacenamiento_secundario_tipo      = $f->almacenamiento_secundario_tipo ?: 'N/A';
        $f->teclado_idioma                      = $f->teclado_idioma ?: 'N/A';

        $f->detalles_esteticos = $this->buildChecksText($f->detalles_esteticos_checks ?? [], $f->detalles_esteticos_otro);
        $f->detalles_funcionamiento = $this->buildChecksText($f->detalles_funcionamiento_checks ?? [], $f->detalles_funcionamiento_otro);

        $f->puertos_conectividad = $this->truncate($f->puertos_conectividad, 255);
        $f->dispositivos_entrada = $this->truncate($f->dispositivos_entrada, 255);

        // Mappers a columnas (antes del payload)
        $this->mapSlotsToDbColumns();
        $this->applyAggregatesToEquipoColumns();

        DB::transaction(function () use ($f) {
            // Seguridad: el modelo debe pertenecer al lote
            $belongs = LoteModeloRecibido::query()
                ->whereKey($f->lote_modelo_id)
                ->where('lote_id', $f->lote_id)
                ->exists();

            if (!$belongs) {
                throw ValidationException::withMessages([
                    'form.lote_modelo_id' => 'El modelo seleccionado no pertenece al lote seleccionado.',
                ]);
            }

            // Cupo (en edición: excluye el equipo actual)
            $lm = LoteModeloRecibido::query()
                ->whereKey($f->lote_modelo_id)
                ->lockForUpdate()
                ->firstOrFail();

            $registrados = (int) Equipo::query()
                ->where('lote_modelo_id', $f->lote_modelo_id)
                ->where('id', '!=', $this->equipo->id)
                ->count();

            if ($registrados >= (int) $lm->cantidad_recibida) {
                throw ValidationException::withMessages([
                    'form.lote_modelo_id' => 'Ya se registraron todos los equipos disponibles de este modelo en este lote.',
                ]);
            }

            // Update equipo (solo columnas reales)
            $payload = $this->equipoPayloadParaUpdate();

            $cols = Schema::getColumnListing('equipos');
            $payload = array_intersect_key($payload, array_flip($cols));

            // Nunca tocar estas columnas
            unset($payload['id'], $payload['created_at'], $payload['updated_at'], $payload['deleted_at']);



            $diff = $this->buildAuditDiff($this->baseline, $this->currentSnapshotForAudit());

            // decide acción según diff
            [$accion, $motivoRequerido] = $this->classifyAuditAction($diff);

            // si requiere motivo y no viene, lanza validation
            if ($motivoRequerido && !filled($this->motivo)) {
                throw ValidationException::withMessages([
                    'motivo' => 'Motivo es obligatorio para esta acción.',
                ]);
            }
            $this->registrarAuditoria(
                equipoId: (int) $this->equipo->id,
                accion: $accion,
                motivo: $this->motivo,
                cambios: $diff
            );







            $this->equipo->update($payload);

            // Relacionadas (reemplazo total)
            $this->guardarBaterias((int) $this->equipo->id);
            $this->guardarMonitor((int) $this->equipo->id);
            $this->guardarGpus((int) $this->equipo->id);
        });

        // baseline reset
        $this->baseline = method_exists($this->form, 'snapshotPersistible')
            ? $this->form->snapshotPersistible()
            : $this->form->all();

        $this->hasChanges = false;

        $this->dispatch('toast', type: 'success', message: 'Actualizado correctamente.');
        $this->resetValidation();
    }

    /**
     * Compat: si tu vista usa wire:submit.prevent="guardar"
     */
    public function guardar(): void
    {
        $this->actualizar();
    }

    // =======================
    // Payload principal (update)
    // =======================
    private function equipoPayloadParaUpdate(): array
    {
        $f = $this->form;

        return [
            // NO tocamos registrado_por_user_id en edición

            'lote_modelo_id' => $f->lote_modelo_id,
            'proveedor_id'   => $f->proveedor_id,

            'estatus_general' => $f->estatus_general,

            'marca'             => $f->marca,
            'modelo'            => $f->modelo,
            'tipo_equipo'       => $f->tipo_equipo,
            'sistema_operativo' => $f->sistema_operativo,
            'area_tienda'       => $f->area_tienda,

            'procesador_modelo'     => $f->procesador_modelo,
            'procesador_generacion' => $f->procesador_generacion,
            'procesador_nucleos'    => $f->procesador_nucleos,
            'procesador_frecuencia' => $f->procesador_frecuencia,

            'numero_serie' => $f->numero_serie,

            'ram_total'            => $f->ram_total,
            'ram_tipo'             => $f->ram_tipo,
            'ram_es_soldada'       => (bool) $f->ram_es_soldada,
            'ram_slots_totales'    => $f->ram_slots_totales,
            'ram_expansion_max'    => $f->ram_expansion_max,
            'ram_cantidad_soldada' => $f->ram_cantidad_soldada,

            'almacenamiento_principal_capacidad'  => $f->almacenamiento_principal_capacidad,
            'almacenamiento_principal_tipo'       => $f->almacenamiento_principal_tipo,
            'almacenamiento_secundario_capacidad' => $f->almacenamiento_secundario_capacidad,
            'almacenamiento_secundario_tipo'      => $f->almacenamiento_secundario_tipo,

            'slots_alm_ssd'      => $f->slots_alm_ssd,
            'slots_alm_m2'       => $f->slots_alm_m2,
            'slots_alm_m2_micro' => $f->slots_alm_m2_micro,
            'slots_alm_hdd'      => $f->slots_alm_hdd,
            'slots_alm_msata'    => $f->slots_alm_msata,

            'ethernet_tiene'      => (bool) $f->ethernet_tiene,
            'ethernet_es_gigabit' => (bool) $f->ethernet_es_gigabit,

            'puertos_conectividad' => $f->puertos_conectividad,
            'dispositivos_entrada' => $f->dispositivos_entrada,

            'puertos_hdmi'        => $f->puertos_hdmi,
            'puertos_mini_hdmi'   => $f->puertos_mini_hdmi,
            'puertos_vga'         => $f->puertos_vga,
            'puertos_dvi'         => $f->puertos_dvi,
            'puertos_displayport' => $f->puertos_displayport,
            'puertos_mini_dp'     => $f->puertos_mini_dp,

            'puertos_usb_2'  => $f->puertos_usb_2,
            'puertos_usb_30' => $f->puertos_usb_30,
            'puertos_usb_31' => $f->puertos_usb_31,
            'puertos_usb_32' => $f->puertos_usb_32,
            'puertos_usb_c'  => $f->puertos_usb_c,

            'lectores_sd'      => $f->lectores_sd,
            'lectores_microsd' => $f->lectores_microsd,
            'lectores_sc'      => $f->lectores_sc,
            'lectores_esata'   => $f->lectores_esata,
            'lectores_sim'     => $f->lectores_sim,

            'bateria_tiene' => (bool) $f->bateria_tiene,

            'teclado_idioma'          => $f->teclado_idioma,
            'notas_generales'         => $f->notas_generales,
            'detalles_esteticos'      => $f->detalles_esteticos,
            'detalles_funcionamiento' => $f->detalles_funcionamiento,
        ];
    }

    // =======================
    // Relacionadas: Baterías
    // =======================
private function guardarBaterias(int $equipoId): void
{
    $f = $this->form;

    $existentes = EquipoBateria::where('equipo_id', $equipoId)
        ->orderBy('id')
        ->get()
        ->values();

    // Si ya no tiene baterías: borra las existentes (esto sí elimina, pero no crea nuevas)
    if (! $f->bateria_tiene) {
        if ($existentes->isNotEmpty()) {
            EquipoBateria::where('equipo_id', $equipoId)->delete();
        }
        return;
    }

    // === BATERÍA 1 ===
    if ($f->bateria1_tipo) {
        if (isset($existentes[0])) {
            $existentes[0]->update([
                'tipo'          => $f->bateria1_tipo,
                'salud_percent' => $f->bateria1_salud ?: null,
                'notas'         => null,
            ]);
        } else {
            EquipoBateria::create([
                'equipo_id'     => $equipoId,
                'tipo'          => $f->bateria1_tipo,
                'salud_percent' => $f->bateria1_salud ?: null,
                'notas'         => null,
            ]);
        }
    } else {
        // si no hay tipo, y existe fila 1, podrías borrarla (opcional)
        if (isset($existentes[0])) {
            $existentes[0]->delete();
        }
    }

    // === BATERÍA 2 ===
    $quiereBateria2 = (bool) $f->bateria2_tiene && (bool) $f->bateria2_tipo;

    if ($quiereBateria2) {
        if (isset($existentes[1])) {
            $existentes[1]->update([
                'tipo'          => $f->bateria2_tipo,
                'salud_percent' => $f->bateria2_salud ?: null,
                'notas'         => null,
            ]);
        } else {
            EquipoBateria::create([
                'equipo_id'     => $equipoId,
                'tipo'          => $f->bateria2_tipo,
                'salud_percent' => $f->bateria2_salud ?: null,
                'notas'         => null,
            ]);
        }
    } else {
        // si existe fila 2 y ya no debe estar, borrar solo esa
        if (isset($existentes[1])) {
            $existentes[1]->delete();
        }
    }

    // Si llegaran a existir más de 2 por datos viejos, limpia excedentes
    if ($existentes->count() > 2) {
        EquipoBateria::where('equipo_id', $equipoId)
            ->whereNotIn('id', $existentes->take(2)->pluck('id'))
            ->delete();
    }
}


    // =======================
    // Relacionadas: Monitor/Pantalla
    // =======================
    private function guardarMonitor(int $equipoId): void
    {
        $f = $this->form;

        // INTEGRADA
        if ($this->pantallaIntegrada) {
            EquipoMonitor::updateOrCreate(
                ['equipo_id' => $equipoId],
                [
                    'origen_pantalla' => 'INTEGRADA',
                    'incluido'        => 1,
                    'pulgadas'        => $f->pantalla_pulgadas ?: null,
                    'resolucion'      => $f->pantalla_resolucion ?: null,
                    'tipo_panel'      => $f->pantalla_tipo ?: null,
                    'es_touch'        => (int) ((bool) $f->pantalla_es_touch),
                ] + $this->monitorInputsPayload([]) // limpia entradas
            );
            return;
        }

        // EXTERNA
        if (!$this->pantallaExterna) {
            return;
        }

        if ($f->monitor_incluido !== 'SI') {
            // Mantener registro con incluido=0 y resto NULL/0
            EquipoMonitor::updateOrCreate(
                ['equipo_id' => $equipoId],
                [
                    'origen_pantalla' => 'EXTERNA',
                    'incluido'        => 0,
                    'pulgadas'        => null,
                    'resolucion'      => null,
                    'tipo_panel'      => null,
                    'es_touch'        => 0,

                    'detalles_esteticos_checks'      => null,
                    'detalles_esteticos_otro'        => null,
                    'detalles_funcionamiento_checks' => null,
                    'detalles_funcionamiento_otro'   => null,
                ] + $this->monitorInputsPayload([])
            );
            return;
        }

        $counts = $this->aggregateCounters($f->monitor_entradas_rows ?? [], 'tipo', 'cantidad');
        $inputsPayload = $this->monitorInputsPayload($counts);

        EquipoMonitor::updateOrCreate(
            ['equipo_id' => $equipoId],
            [
                'origen_pantalla' => 'EXTERNA',
                'incluido'        => 1,
                'pulgadas'        => $f->monitor_pulgadas ?: null,
                'resolucion'      => $f->monitor_resolucion ?: null,
                'tipo_panel'      => $f->monitor_tipo_panel ?: null,
                'es_touch'        => (int) ((bool) $f->monitor_es_touch),

                'detalles_esteticos_checks'      => $this->truncate($f->monitor_detalles_esteticos_checks ?: null, 255),
                'detalles_esteticos_otro'        => $this->truncate($f->monitor_detalles_esteticos_otro ?: null, 255),
                'detalles_funcionamiento_checks' => $this->truncate($f->monitor_detalles_funcionamiento_checks ?: null, 255),
                'detalles_funcionamiento_otro'   => $this->truncate($f->monitor_detalles_funcionamiento_otro ?: null, 255),
            ] + $inputsPayload
        );
    }

    private function monitorInputsPayload(array $countsByLabel): array
    {
        $payload = [];
        foreach (self::MAP_MONITOR_IN as $label => $col) {
            $qty = (int) ($countsByLabel[$label] ?? 0);
            $payload[$col] = $qty > 0 ? $qty : null;
        }
        return $payload;
    }

    // =======================
    // Relacionadas: GPU (equipo_gpus)
    // =======================
private function guardarGpus(int $equipoId): void
{
    $f = $this->form;

    $esLaptopLike = $this->isLaptopLikeTipo($f->tipo_equipo);

    // ===== INTEGRADA =====
    $debeTenerIntegrada = (bool) $f->gpu_integrada_tiene || $esLaptopLike;

    if ($debeTenerIntegrada) {
        EquipoGpu::updateOrCreate(
            ['equipo_id' => $equipoId, 'tipo' => 'INTEGRADA'],
            [
                'activo'      => 1,
                'marca'       => $f->gpu_integrada_marca ?: null,
                'modelo'      => $f->gpu_integrada_modelo ?: null,
                'vram'        => filled($f->gpu_integrada_vram) ? (int)$f->gpu_integrada_vram : null,
                'vram_unidad' => filled($f->gpu_integrada_vram) ? ($f->gpu_integrada_vram_unidad ?: 'GB') : null,
                'notas'       => null,
            ]
        );
    } else {
        // si realmente ya no debe existir
        EquipoGpu::where('equipo_id', $equipoId)->where('tipo', 'INTEGRADA')->delete();
    }

    // ===== DEDICADA =====
    if ((bool) $f->gpu_dedicada_tiene) {
        EquipoGpu::updateOrCreate(
            ['equipo_id' => $equipoId, 'tipo' => 'DEDICADA'],
            [
                'activo'      => 1,
                'marca'       => $f->gpu_dedicada_marca ?: null,
                'modelo'      => $f->gpu_dedicada_modelo ?: null,
                'vram'        => filled($f->gpu_dedicada_vram) ? (int)$f->gpu_dedicada_vram : null,
                'vram_unidad' => filled($f->gpu_dedicada_vram) ? ($f->gpu_dedicada_vram_unidad ?: 'GB') : null,
                'notas'       => null,
            ]
        );
    } else {
        EquipoGpu::where('equipo_id', $equipoId)->where('tipo', 'DEDICADA')->delete();
    }
}


    // =======================
    // Agregadores / Mappers (Form UI -> columnas Equipo)
    // =======================
    private function applyAggregatesToEquipoColumns(): void
    {
        $f = $this->form;

        $usbCounts = $this->aggregateCounters($f->puertos_usb ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($usbCounts, self::MAP_USB);

        $videoCounts = $this->aggregateCounters($f->puertos_video ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($videoCounts, self::MAP_VIDEO);

        $lectorCounts = $this->aggregateCounters($f->lectores ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($lectorCounts, self::MAP_LECTORES);
    }

    private function aggregateCounters(array $rows, string $keyField, ?string $qtyField): array
    {
        $counts = [];

        foreach ($rows as $row) {
            $k = trim((string) ($row[$keyField] ?? ''));
            if ($k === '') continue;

            $qty = 1;
            if ($qtyField) {
                $qty = (int) ($row[$qtyField] ?? 1);
                $qty = $qty > 0 ? $qty : 1;
            }

            $counts[$k] = ($counts[$k] ?? 0) + $qty;
        }

        return $counts;
    }

private function applyMapCountsToEquipo(array $countsByLabel, array $mapLabelToEquipoColumn): void
{
    $f = $this->form;

    foreach ($mapLabelToEquipoColumn as $label => $col) {
        $count = (int) ($countsByLabel[$label] ?? 0);

        // Sobrescribe SIEMPRE:
        // - cantidad si existe
        // - null si ya no hay
        $f->{$col} = $count > 0 ? (string) $count : null;
    }
}


    private function mapSlotsToDbColumns(): void
    {
        $f = $this->form;

        $f->slots_alm_ssd = null;
        $f->slots_alm_m2 = null;
        $f->slots_alm_m2_micro = null;
        $f->slots_alm_hdd = null;
        $f->slots_alm_msata = null;

        foreach (($f->slots_almacenamiento ?? []) as $row) {
            $tipo = trim((string) ($row['tipo'] ?? ''));
            if ($tipo === '' || !isset(self::MAP_SLOTS[$tipo])) continue;

            $cant = $row['cantidad'] ?? null;
            $valor = (is_numeric($cant) && (int) $cant > 0) ? (string) ((int) $cant) : null;

            $col = self::MAP_SLOTS[$tipo];
            $f->{$col} = $valor;
        }
    }

    // =======================
    // Text helpers
    // =======================
    private function buildChecksText(array $checks, ?string $otro): string
    {
        $checks = $checks ?? [];

        if (in_array('N/A', $checks, true)) {
            $checks = ['N/A'];
            $otro = null;
        }

        $txt = implode(', ', $checks);

        if (!empty($otro)) {
            $txt .= ($txt ? ' | ' : '') . 'Otro: ' . $otro;
        }

        return $txt;
    }

    private function truncate($value, int $max)
    {
        if ($value === null) return null;
        $value = (string) $value;
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }

    public function render()
    {
        return view('livewire.equipos.editar-equipo');
    }
}
