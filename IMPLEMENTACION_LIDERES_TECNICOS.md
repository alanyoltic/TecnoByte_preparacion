# Implementación: Líderes trabajando como técnicos (por período)

## Descripción
Ahora es posible que líderes cuenten como técnicos en las métricas y metas mensuales, pero solo para los períodos específicos en que realmente trabajan equipos. Esto es flexible: un líder puede trabajar en julio pero no en agosto.

## Archivos creados/modificados

### 1. **Migración** (nueva)
- **Archivo**: `database/migrations/2026_05_20_create_lider_modo_tecnico_table.php`
- **Tabla**: `lider_modo_tecnico`
- **Campos**:
  - `lider_id` (FK a users)
  - `periodo` (Y-m, ej: 2026-05)
  - `activo` (boolean)
  - `configurado_por_id` (FK a users)
  - `notas` (opcional)
  - Índice único: `(lider_id, periodo)`

### 2. **Modelo** (nuevo)
- **Archivo**: `app/Models/LiderModoTecnico.php`
- **Helpers útiles**:
  - `LiderModoTecnico::trabajaComoTecnico($liderId, $periodo)` → boolean
  - `LiderModoTecnico::lideresActivosDelPeriodo($periodo)` → array de IDs

### 3. **Dashboard Livewire** (modificado)
- **Archivo**: `app/Livewire/Dashboard/Dashboard.php`
- **Cambios**:
  - Importa `LiderModoTecnico`
  - Calcula líderes activos para el período seleccionado
  - Incluye a técnicos + líderes activos en `$colaboradoresCount`
  - Los colaboradores se pasan a la vista correctamente

### 4. **Componente UI** (nuevo)
- **Archivo**: `app/Livewire/Dashboard/ConfigurarLideresTecnicos.php`
- **Vista**: `resources/views/livewire/dashboard/configurar-lideres-tecnicos.blade.php`
- **Funcionalidad**:
  - Selector de período
  - Listado de todos los líderes con toggle on/off
  - Visual feedback con colores (emerald = activo, gris = inactivo)
  - Muestra resumen de líderes activos

## Flujo de uso

### Para el gerente/líder (configuración):
1. Ir a Dashboard
2. En la nueva sección "Líderes trabajando como técnicos"
3. Seleccionar el período (mes/año)
4. Hacer clic en los líderes que trabajan ese mes
5. Automáticamente se guardan los cambios

### Para las métricas (automático):
1. Cuando se calculan métricas para un período
2. El Dashboard busca: técnicos + líderes marcados como activos en `lider_modo_tecnico`
3. Ambos se incluyen en `$colaboradoresCount` y en metas

### Ejemplo:
```
Período 2026-05:
- Técnicos: 10
- Líderes activos: 2 (Luis y María)
- Total colaboradores: 12
- Meta total se calcula: 12 * meta_por_persona
```

```
Período 2026-06:
- Técnicos: 10
- Líderes activos: 0 (ninguno trabaja equipos)
- Total colaboradores: 10
- Meta total se calcula: 10 * meta_por_persona
```

## Próximos pasos

### 1. Resolver error de migración
Antes de ejecutar la migración, debe resolverse el error en:
```
2026_04_24_133500_guardar_solicitud_activa_unica_por_equipo.php
```

El error es por constraint de foreign key al intentar crear columna generada. Opciones:
- Hacer rollback y reparar esa migración
- O ejecutar rollback hasta esa migración, excluirla, y luego migrar

### 2. Ejecutar la nueva migración
```bash
php artisan migrate
```

### 3. Integrar el componente en el Dashboard
En `resources/views/livewire/dashboard/dash-board.blade.php`, agregar:
```blade
<livewire:dashboard.configurar-lideres-tecnicos :periodo="$selectedMonthValue" />
```

### 4. Testing
Verificar que:
- Al marcar un líder como activo en mayo, aparece en métricas de mayo
- Al cambiar de mes, los líderes se desmarcan correctamente
- Las metas se calculan con el nuevo total de colaboradores
- Los puntos técnicos se registran para líderes como para técnicos

## Variables de entorno / Configuración
No requiere configuración especial. Todo está manejado por la tabla `lider_modo_tecnico`.

## Rollback
Si es necesario revertir:
```bash
php artisan migrate:rollback --step=1
```

Esto eliminará la tabla `lider_modo_tecnico` y revierte los cambios en Dashboard.

---

**Nota**: Este sistema es completamente flexible. Puede haber múltiples líderes activos, ninguno, o todos, dependiendo de cada período.
