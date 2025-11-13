<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Equipo;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\LoteModeloRecibido;
use App\Models\User;
use App\Models\Roles; // ¡Asegúrate de que apunte a tu modelo 'Roles' (plural)!
use Illuminate\Support\Str;

class EquiposViejosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando importación de CSV...');

        // 1. OBTENER LOS "PADRES" QUE YA EXISTEN
        $ceoUser = User::where('email', 'ceo@tecnobyte.com')->first();
        if (!$ceoUser) {
            $this->command->error('El usuario CEO (ceo@tecnobyte.com) no existe. Corre el DatabaseSeeder primero.');
            return;
        }

        // 2. CREAR EL "LOTE CONTENEDOR"
        $proveedorSistema = Proveedor::firstOrCreate(
            ['nombre_empresa' => 'Sistema (Migración)'],
            ['abreviacion' => 'MIG']
        );

        $loteContenedor = Lote::firstOrCreate(
            ['nombre_lote' => 'Datos Antiguos (Migración)'],
            ['proveedor_id' => $proveedorSistema->id]
        );

        $cuotaContenedora = LoteModeloRecibido::firstOrCreate(
            [
                'lote_id' => $loteContenedor->id,
                'modelo' => 'Importado de Excel'
            ],
            [
                'marca' => 'N/A',
                'cantidad_recibida' => 99999
            ]
        );
        $this->command->info('Lote contenedor creado/encontrado.');

        // 3. LEER Y PROCESAR EL ARCHIVO CSV
        $path = database_path('seeders/data/equipos_viejos.csv');

        if (!file_exists($path)) {
            $this->command->error("Archivo no encontrado en: $path");
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file); // Lee la primera fila (Cabeceras)
        $count = 0;

        while ($row = fgetcsv($file)) {
            $data = array_combine($header, $row);

            if (empty($data['numero de serie'])) {
                continue;
            }

            // 4. "TRADUCIR" DATOS Y GUARDAR
            
            // Busca o crea el Proveedor real de la fila
            $proveedorReal = Proveedor::firstOrCreate(
                ['nombre_empresa' => $data['PROVEEDOR']],
                ['abreviacion' => strtoupper(Str::substr($data['PROVEEDOR'], 0, 3))] 
            );

            // Mapea los datos del CSV a la tabla 'equipos'
            try {
                Equipo::create([
                    'lote_modelo_id' => $cuotaContenedora->id,
                    'registrado_por_user_id' => $ceoUser->id,
                    'proveedor_id' => $proveedorReal->id,
                    'estatus_general' => 'Aprobado', // ¡Marcado como listo!

                    'created_at' => $data['FECHA DE REGISTRO'] ?? now(),
                    'numero_serie' => $data['numero de serie'],
                    'marca' => $data['MODELOS'], 
                    'modelo' => $data['Modelo del equipo'],
                    'tipo_equipo' => $data['TIPO DE EQUIPO'],
                    'sistema_operativo' => $data['SISTEMA OPERATIVO'],
                    'procesador_modelo' => $data['MODELO DEL PROCESADOR Y FRECUENCIA EN GHZ'],
                    'procesador_generacion' => $data['GENERACION'],
                    'procesador_nucleos' => (int) filter_var($data['NUCLEOS'], FILTER_SANITIZE_NUMBER_INT),
                    'pantalla_pulgadas' => $data['PULGADAS DEL MONITOR'],
                    'pantalla_resolucion' => $data['RESOLUCION'],
                    'pantalla_es_touch' => (strtoupper($data['TOUCH']) == 'SI'),
                    'ram_total' => $data['RAM TOTAL DEL EQUIPO PREPARADO'],
                    'ram_tipo' => $data['TIPO DE RAM'],
                    'ram_es_soldada' => (strtoupper($data['RAM SOLDADA?']) == 'SI'),
                    'ram_expansion_max' => $data['CAPACIDAD DE EXPANSION RAM [CAPACIDAD DE EXPANSION]'],
                    'ram_slots_totales' => $data['CANTITDAD DE SLOTS [CANTIDAD]'],
                    'almacenamiento_principal_capacidad' => $data['ALMACENAMIENTO [PRINCIPAL]'],
                    'almacenamiento_principal_tipo' => $data['TIPO DE DISCOS [PRINCIPAL]'],
                    'almacenamiento_secundario_capacidad' => $data['ALMACENAMIENTO [SECUNDARIO]'],
                    'almacenamiento_secundario_tipo' => $data['TIPO DE DISCOS [SECUNDARIO]'],
                    'grafica_dedicada_modelo' => $data['MODELO DE TARJETA DEDICada'], // (Revisa esta cabecera en tu CSV)
                    'grafica_dedicada_vram' => $data['Cantidad de VRAM'],
                    'grafica_integrada_modelo' => $data['MODELO DE LA TARJETA INTEGRADA'],
                    'teclado_idioma' => $data['IDIOMA DEL TECLADO'],
                    'area_tienda' => $data['AREA DE TIENDA'],
                    'notas_generales' => "Detalles Estéticos: " . $data['DETALLES ESTETICOS'] . " | Detalles Funcionales: " . $data['DETALLES DE FUNCIONAMIENTO'],
                ]);

                $count++;

            } catch (\Exception $e) {
                // Si algo falla (ej. N/S duplicado), solo lo reporta y continúa
                $this->command->warn("Error al importar N/S: " . $data['numero de serie'] . " - " . $e->getMessage());
            }
        }
        
        fclose($file);
        $this->command->info("¡Importación completada! Se cargaron $count equipos.");
    }
}