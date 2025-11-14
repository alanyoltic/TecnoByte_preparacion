<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; 
use Illuminate\Support\Facades\DB;
use App\Models\Equipo;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\LoteModeloRecibido;
use App\Models\User;
use App\Models\Roles;
use Illuminate\Support\Str;
use Carbon\Carbon; // ¡Importante para las fechas!

class EquiposViejosSeeder extends Seeder
{
    /**
     * Función de limpieza de UTF-8.
     * Esto arreglará los caracteres 'raros' como  y ESPAOL
     */
    private function clean_string($string)
    {
        // Intenta convertir de ISO-8859-1 (Windows) a UTF-8
        $string = @mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');

        // Si falla, usa este reemplazo de caracteres
        $string = preg_replace('/[^\x20-\x7E\p{L}]/u', '', $string); 

        // Limpia el símbolo específico de las pulgadas
        $string = str_replace('', '"', $string);
        $string = str_replace('´´', '"', $string);
        
        return trim($string);
    }

    /**
     * Función para parsear las fechas raras
     */
    private function parse_date($dateString)
    {
        if (empty($dateString)) {
            return now();
        }
        try {
            // Intenta parsear el formato DD/MM/YYYY
            return Carbon::createFromFormat('d/m/Y', $this->clean_string($dateString))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Si falla, intenta parsear el formato de EE.UU.
            try {
                return Carbon::parse($this->clean_string($dateString))->format('Y-m-d H:i:s');
            } catch (\Exception $e2) {
                // Si todo falla, devuelve la fecha actual
                return now();
            }
        }
    }

    private function findOrCreateProveedor(string $nombreCompleto): Proveedor
    {
        $nombreLimpio = $this->clean_string($nombreCompleto);
        if (empty($nombreLimpio)) {
            $nombreLimpio = "SIN PROVEEDOR"; // Asigna uno por defecto si está vacío
        }

        $proveedor = Proveedor::where('nombre_empresa', $nombreLimpio)->first();
        if ($proveedor) {
            return $proveedor;
        }

        $baseAbbr = strtoupper(Str::substr($nombreLimpio, 0, 3));
        $abbr = $baseAbbr;
        $counter = 1;
        while (Proveedor::where('abreviacion', $abbr)->exists()) {
            $abbr = $baseAbbr . $counter;
            $counter++;
        }

        return Proveedor::create([
            'nombre_empresa' => $nombreLimpio,
            'abreviacion' => $abbr
        ]);
    }

    public function run(): void
    {
        $this->command->info('Iniciando importación de CSV...');
        
        $ceoUser = User::where('email', 'ceo@tecnobyte.com')->first();
        if (!$ceoUser) { /* ... (código del CEO igual) ... */ }

        $proveedorSistema = $this->findOrCreateProveedor('Sistema (Migración)');
        $loteContenedor = Lote::firstOrCreate(/* ... (código del Lote igual) ... */);
        $cuotaContenedora = LoteModeloRecibido::firstOrCreate(/* ... (código de la Cuota igual) ... */);
        
        $this->command->info('Lote contenedor creado/encontrado.');

        $path = database_path('seeders/data/equipos_viejos.csv');
        if (!file_exists($path)) { /* ... (código del path igual) ... */ }

        $file = fopen($path, 'r');
        $header = fgetcsv($file);
        $count = 0;

        while ($row = fgetcsv($file)) {
            if (empty($row[0])) continue;
            
            // --- ¡LIMPIEZA DE DATOS PRIMERO! ---
            // Le decimos a PHP que use UTF-8 para leer la fila
            $data = array_map('utf8_encode', $row);
            $data = array_combine($header, $data);

            if (empty($data['numero de serie'])) {
                continue;
            }

            $proveedorReal = $this->findOrCreateProveedor($data['PROVEEDOR']);
            
            // --- MAPEO CON LIMPIEZA ---
            try {
                Equipo::firstOrCreate(
                    ['numero_serie' => $this->clean_string($data['numero de serie'])], 
                    [ 
                        'lote_modelo_id' => $cuotaContenedora->id,
                        'registrado_por_user_id' => $ceoUser->id,
                        'proveedor_id' => $proveedorReal->id,
                        'estatus_general' => 'Aprobado',
                        
                        // ¡ARREGLO DE FECHA!
                        'created_at' => $this->parse_date($data['FECHA DE REGISTRO']),
                        
                        // ¡ARREGLO DE STRINGS!
                        'marca' => $this->clean_string($data['MODELOS']), 
                        'modelo' => $this->clean_string($data['Modelo del equipo']),
                        'tipo_equipo' => $this->clean_string($data['TIPO DE EQUIPO']),
                        'sistema_operativo' => $this->clean_string($data['SISTEMA OPERATIVO']),
                        'procesador_modelo' => $this->clean_string($data['MODELO DEL PROCESADOR Y FRECUENCIA EN GHZ']),
                        'procesador_generacion' => $this->clean_string($data['GENERACION']),
                        'procesador_nucleos' => (int) filter_var($this->clean_string($data['NUCLEOS']), FILTER_SANITIZE_NUMBER_INT),
                        
                        // ¡ARREGLO DE PULGADAS!
                        'pantalla_pulgadas' => $this->clean_string($data['PULGADAS DEL MONITOR']),
                        
                        'pantalla_resolucion' => $this->clean_string($data['RESOLUCION']),
                        'pantalla_es_touch' => (strtoupper($this->clean_string($data['TOUCH'])) == 'SI'),
                        'ram_total' => $this->clean_string($data['RAM TOTAL DEL EQUIPO PREPARADO']),
                        'ram_tipo' => $this->clean_string($data['TIPO DE RAM']),
                        'ram_es_soldada' => (strtoupper($this->clean_string($data['RAM SOLDADA?'])) == 'SI'),
                        'ram_expansion_max' => $this->clean_string($data['CAPACIDAD DE EXPANSION RAM [CAPACIDAD DE EXPANSION]']),
                        'ram_slots_totales' => $this->clean_string($data['CANTITDAD DE SLOTS [CANTIDAD]']),
                        'almacenamiento_principal_capacidad' => $this->clean_string($data['ALMACENAMIENTO [PRINCIPAL]']),
                        'almacenamiento_principal_tipo' => $this->clean_string($data['TIPO DE DISCOS [PRINCIPAL]']),
                        'almacenamiento_secundario_capacidad' => $this->clean_string($data['ALMACENAMIENTO [SECUNDARIO]']),
                        'almacenamiento_secundario_tipo' => $this->clean_string($data['TIPO DE DISCOS [SECUNDARIO]']),
                        'grafica_dedicada_modelo' => $this->clean_string($data['MODELO DE TARJETA DEDICada'] ?? null),
                        'grafica_dedicada_vram' => $this->clean_string($data['Cantidad de VRAM'] ?? null),
                        'grafica_integrada_modelo' => $this->clean_string($data['MODELO DE LA TARJETA INTEGRADA']),
                        
                        // ¡ARREGLO DE 'ESPAÑOL'!
                        'teclado_idioma' => $this->clean_string($data['IDIOMA DEL TECLADO']),
                        
                        'area_tienda' => $this->clean_string($data['AREA DE TIENDA']),
                        'notas_generales' => "Detalles Estéticos: " . $this->clean_string($data['DETALLES ESTETICOS']) . " | Detalles Funcionales: " . $this->clean_string($data['DETALLES DE FUNCIONAMIENTO']),
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->command->warn("Error al importar N/S: " . $this->clean_string($data['numero de serie']) . " - " . $e->getMessage());
            }
        }
        
        fclose($file);
        $this->command->info("¡Importación completada! Se procesaron $count equipos (los duplicados se omitieron).");
    }
}