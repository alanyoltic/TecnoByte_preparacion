# MEMORIA COMPLETA: estatus, caminos y hallazgos (chat + repo)

Contexto: TecnoByte (Laravel + Livewire + MariaDB/MySQL). Esta nota guarda los hallazgos del análisis del repo y la conversación para evitar confusiones futuras.

---

## ⚠️ TERMINOLOGÍA CORRECTA — LEER PRIMERO (definida por el usuario)

### Flujo real de un equipo en TecnoByte

1. **Llega a la empresa → se registra en Lote** (`lote_modelos_recibidos`).
   - Se captura la `cantidad_recibida` por modelo/catálogo.
   - En este momento el equipo puede o no tener número de serie.
   - Si llega SIN serie: el registro existe en `lote_modelos_recibidos` pero NO en `equipos`.
   - Si llega CON serie: el registro existe en ambas tablas.
   - **IMPORTANTE:** estos equipos son 100% reales, ya llegaron. NO son "prometidos", NO son "aire". Son equipos físicos en bodega.

2. **Disponibles** = equipos en lote que aún NO han sido asignados a ningún técnico.
   - Equivale a: `cantidad_recibida (lote) - equipos ya en flujo con número de serie`.
   - También incluye equipos que ya tienen serie escaneada pero están en `estatus_area = SIN_ASIGNAR` o `EN_ESPERA`.

3. **Asignados** → se le notifica al técnico qué equipos va a preparar (`estatus_area = ASIGNADO`).

4. **En Proceso** → el técnico ya lo tomó y empezó a trabajarlo (`estatus_area = EN_PROCESO`).

5. **En Calidad** → el técnico terminó, pasa a revisión (`estatus_area = EN_CALIDAD`).

6. **Aprobados** → pasó calidad, listo (`estatus_area = FINALIZADO`).

7. **Ramas de bloqueo** (pueden ocurrir durante el proceso):
   - **Falta Pieza** → `PENDIENTE_PIEZA`
   - **Garantía** → `PENDIENTE_GARANTIA`, `GARANTIA_INT`, `GARANTIA_EXT`
   - **Desarme** → `PENDIENTE_DESARME`

8. **Transferidos** → `TRANSFERIDO` (flujo futuro, ya definido en BD).

### Términos PROHIBIDOS en el código y comentarios

| ❌ NO usar | ✅ Usar en su lugar |
|---|---|
| `total_prometido` | `total_recibido` |
| "prometidos" | "recibidos" |
| "aire" | "disponibles" o "sin escanear" |
| "Sin Registrar (Aire)" | "Disponibles" |
| "equipos prometidos en lotes" | "equipos registrados en lote" |

---

Referencias (timeline):

- `67f2304` (2026-04-07): cambios de `equipos.estatus_area` (renombres y nuevos valores).
- `36785c2` (2026-04-23): refactor de solicitudes + intentos, y ampliación de `asignacion_equipos.camino`.
- `bfe77cd` (2026-04-27): “1 solicitud activa por equipo” (constraint/columna generada).
- `236b1f0` (2026-04-29): se empezó a usar `ALTA_MANUAL` como tipo de `equipo_movimientos` (alta desde UI).

> “ANTES” se refiere al estado del repo en el commit de producción `fdc042c...`.
> “AHORA” se refiere al estado actual del repo (post cambios de abril 2026).

---

## 0) Hallazgos clave (lo que se descubrió en el chat)

1) La descripción inicial “ANTES/ahora” que se planteó (que `asignacion_equipos` era nueva y que la columna se llamaba `estado`) **no coincide** con el repo:
   - La tabla ya existía en dumps antiguos y en el código; el campo funcional es `camino` (no `estado`).
2) El sistema actual está diseñado como:
   - `asignaciones` = asignación “macro” por técnico + `lote_modelo_id` + `cantidad`
   - `asignacion_equipos` = detalle por equipo individual (trazabilidad y estados por equipo)
3) El error real que apareció en producción/dev (`SQLSTATE[01000] 1265 Data truncated for column 'tipo'`) no era de “solicitudes”:
   - Era de `equipo_movimientos.tipo` por usar `ALTA_MANUAL` sin estar permitido en el `ENUM` de DB.
4) **Cálculo de estadísticas (Dashboard vs Inventario Físico):**
   - **Vistas de Inventario:** (`GestionInventario` e `InventarioListo`) Muestran exclusivamente equipos **físicos** registrados. Para evitar agrupaciones fantasmas (equipos dobles o ignorados por falta de técnico), las tarjetas ahora leen de forma **estricta** el `estatus_area` (ej: Disponibles=`SIN_ASIGNAR`+`EN_ESPERA`, Finalizado=`FINALIZADO`+`TRANSFERIDO`).
   - **Dashboard Global:** Originalmente, el Dashboard ignoraba equipos y lotes si su `catalogo_equipo_id` era NULL. Se refactorizó la arquitectura para basarse en una "columna vertebral" dinámica (`UNION` de `marca` y `modelo` entre `lote_modelos_recibidos` y `equipos`), eliminando la ceguera del catálogo y garantizando que los totales empaten a la perfección con la suma de equipos físicos.

---

## 1) `asignacion_equipos.camino` (trabajo por equipo)

### Valores “ANTES”
- `EN_PROCESO`
- `COMPLETADO`
- `PIEZA_PENDIENTE`
- `GARANTIA_INTERNA`
- `GARANTIA_EXTERNA`
- `DESPIECE`

### Valores “AHORA” (se agregaron)
- `PENDIENTE` (por iniciar)
- `PRE_ASIGNADO` (pre-asignación por gerente)
- `EN_CALIDAD` (pieza instalada / esperando revisión)

### Notas de conteo (típico)
- Antes: “activo” solía ser solo `EN_PROCESO`.
- Ahora: “activo” puede incluir `PENDIENTE`, `PRE_ASIGNADO`, `EN_PROCESO`.

---

## 2) `equipos.estatus_area` (estado interno del área)

### Valores “ANTES”
- `SIN_ASIGNAR`
- `ASIGNADO`
- `EN_PROCESO`
- `LISTO`
- `TRANSFERIDO`
- `PENDIENTE_PIEZA`
- `PENDIENTE_GARANTIA`
- `PENDIENTE_DESHUESO`
- `GARANTIA_INT`
- `GARANTIA_EXT`

### Valores “AHORA”
- `EN_ESPERA`
- `SIN_ASIGNAR` (permanece por compatibilidad)
- `ASIGNADO`
- `EN_PROCESO`
- `EN_CALIDAD`
- `FINALIZADO`
- `TRANSFERIDO`
- `PENDIENTE_PIEZA`
- `PENDIENTE_GARANTIA`
- `PENDIENTE_DESARME`
- `GARANTIA_INT`
- `GARANTIA_EXT`

### Mapeos aplicados en migración (cuando se ejecutó)
- `LISTO` → `EN_CALIDAD`
- `PENDIENTE_DESHUESO` → `PENDIENTE_DESARME`
- `SIN_ASIGNAR` (si `estatus_ciclo = CEDIS`) → `EN_ESPERA`
- `TRANSFERIDO` → `FINALIZADO` (cuando el ciclo NO es `VENTAS/APARTADO/VENDIDO/SCRAP`)

---

## 3) `solicitudes_piezas.estatus` (solicitudes de piezas)

### Valores “ANTES”
- `PENDIENTE`
- `SURTIDA_INVENTARIO`
- `PENDIENTE_COMPRA`
- `COMPRADA`
- `CANCELADA`
- `CONFIRMADA`

### Valores “AHORA” (se agregó)
- `REQUIERE_REASIGNACION`

### Regla nueva importante (“AHORA”)
- Se considera “activa” una solicitud en: `PENDIENTE`, `PENDIENTE_COMPRA`, `COMPRADA`, `SURTIDA_INVENTARIO`, `REQUIERE_REASIGNACION`.
- El sistema evita (por BD) que existan 2 solicitudes activas del mismo `equipo_id` al mismo tiempo.

---

## 4) `equipo_movimientos.tipo` (historial de movimientos)

### Valores del ENUM original (creación de tabla)
- `ALTA_LOTE`
- `MOVER_ALMACEN`
- `ASIGNAR_TECNICO`
- `FINALIZAR_TECNICO`
- `VENTA`
- `BAJA`
- `AJUSTE`

### Valor adicional usado por la UI “AHORA”
- `ALTA_MANUAL` (alta/registro manual desde `MiTrabajo` / registro manual)

---

## 5) ¿Qué cambió cuando el técnico “iniciaba” su equipo desde MiTrabajo?

### “ANTES”
Al iniciar equipo desde `MiTrabajo`, normalmente:
- `equipos.estatus_ciclo` se ponía en `PREPARACION`
- `equipos.estatus_area` se ponía en `EN_PROCESO`
- se creaba `asignacion_equipos` con `camino = EN_PROCESO`

### “AHORA”
Eso sigue siendo cierto (el arranque sigue marcando `EN_PROCESO`), pero:
- puede existir “pre-asignación” (`PRE_ASIGNADO`) previa al inicio
- al terminar instalación de pieza, puede pasar a `EN_CALIDAD` en vez de brincar directo a “completado”
- al dar de alta manual, se registra movimiento con `tipo = ALTA_MANUAL`

---

## 6) Preguntas del usuario (y respuestas/resolución)

### P: “¿Las solicitudes ya existentes se van a romper al cambiar de estatus?”
R: No deberían romperse solo por el cambio. El mayor cambio es una regla nueva: **máx 1 solicitud activa por equipo**.
- Si intentas crear/activar otra solicitud “activa” para el mismo `equipo_id`, la BD puede rechazarlo (eso es deseado para consistencia).

### P: “¿Qué pasó con mis asignaciones/solicitudes/movimientos viejos?”
R:
- Asignaciones: los valores viejos de `camino` siguen siendo válidos; se agregaron más estados.
- Solicitudes: se agregó `REQUIERE_REASIGNACION` y la unicidad por equipo (no rompe histórico; evita duplicados activos).
- Movimientos: histórico viejo no se daña; el problema fue que el código nuevo escribió un `tipo` nuevo no permitido.

### P: “Me tronó con `Data truncated for column 'tipo'`”
R: Causa raíz confirmada con error real:
- Insert intentaba: `equipo_movimientos.tipo = ALTA_MANUAL`.
- El `ENUM` original de `equipo_movimientos.tipo` NO incluía `ALTA_MANUAL`.
Resultado: MariaDB/MySQL lanza warning 1265 (truncamiento por valor inválido en enum).

---

## 7) Timeline detallado de cambios (abril 2026)

- `67f2304` (2026-04-07): refactor de `equipos.estatus_area` (renombres + nuevos estados; migración `2026_04_07_132743...`).
- `36785c2` (2026-04-23): refactor grande:
  - agrega `SolicitudPiezaIntento`
  - agrega `REQUIERE_REASIGNACION` a `solicitudes_piezas.estatus`
  - amplía `asignacion_equipos.camino` con `PENDIENTE`, `PRE_ASIGNADO`, `EN_CALIDAD`
- `75eb48c` (2026-04-24): ajustes de estabilidad al flujo de solicitudes (piezas sin stock, etc.).
- `bfe77cd` (2026-04-27): agrega regla DB de “1 solicitud activa por equipo” (migración `2026_04_24_133500...`).
- `236b1f0` (2026-04-29): se introduce uso de `ALTA_MANUAL` desde Livewire (`MiTrabajo` y `RegistrarEquipo`).

---

## 8) Nota importante: intento de “guardar mapeos en DB” (revertido)

Durante el chat se propuso guardar estos mapeos en tablas nuevas en la BD.
El usuario pidió explícitamente **no modificar la BD** con ese propósito, así que:
- Se eliminaron los archivos que creaban tabla/seeder de mapeos.
- Se conserva esta memoria `.md` como fuente oficial de documentación.

---

## 9) Comandos útiles de verificación (manual)

### Verificar duplicados de solicitudes activas por equipo
```sql
SELECT equipo_id, COUNT(*) total
FROM solicitudes_piezas
WHERE estatus IN ('PENDIENTE','PENDIENTE_COMPRA','COMPRADA','SURTIDA_INVENTARIO','REQUIERE_REASIGNACION')
  AND equipo_id IS NOT NULL
GROUP BY equipo_id
HAVING COUNT(*) > 1;
```

### Verificar solicitudes activas sin equipo_id
```sql
SELECT COUNT(*) activos_sin_equipo
FROM solicitudes_piezas
WHERE estatus IN ('PENDIENTE','PENDIENTE_COMPRA','COMPRADA','SURTIDA_INVENTARIO','REQUIERE_REASIGNACION')
  AND equipo_id IS NULL;
```

### Verificar ENUM permitido para movimientos
```sql
SHOW COLUMNS FROM equipo_movimientos LIKE 'tipo';
```

---

## 10) Dashboard de Estadísticas de Equipos — Cómo se calculan los conteos (Junio 2026)

### Archivo: `app/Livewire/Preparacion/Dashboard/EstadisticasEquipos.php`

### ⚠️ Regla crítica: `equipos.catalogo_equipo_id` frecuentemente es NULL

Cuando un técnico inicia un equipo desde `MiTrabajo` (escaneando un número de serie), el sistema crea el registro en la tabla `equipos` con `lote_modelo_id` pero **NO asigna `catalogo_equipo_id`**. Por lo tanto, **NUNCA agrupar directamente por `equipos.catalogo_equipo_id`**. En su lugar, siempre hacer JOIN con `lote_modelos_recibidos` para resolver el `catalogo_equipo_id`:

```php
// ✅ CORRECTO
DB::table('equipos as eq')
    ->join('lote_modelos_recibidos as lmr', 'eq.lote_modelo_id', '=', 'lmr.id')
    ->groupBy('lmr.catalogo_equipo_id');

// ❌ INCORRECTO — muchos equipos quedarán fuera del conteo
DB::table('equipos')
    ->groupBy('catalogo_equipo_id');
```

### Definición de cada tarjeta del dashboard

| Tarjeta | Qué cuenta | Cómo se calcula |
|---|---|---|
| **Total Preparación** | Todo lo recibido en lote. Son equipos reales, físicos, que llegaron a la empresa. | `SUM(lote_modelos_recibidos.cantidad_recibida)` agrupado por `catalogo_equipo_id` |
| **Disponibles** | Equipos en bodega listos para ser asignados a un técnico. | `(equipos_sin_serie_en_bodega - cupos_ya_asignados) + equipos_con_serie_sin_asignar` |
| **Asignados** | Equipos que el gerente ya asignó a un técnico pero el técnico aún NO ha iniciado. | `equipos con estatus_area = ASIGNADO` + `cupos_asignados_sin_serie (promesas del gerente)` |
| **En Proceso** | Equipos que el técnico ya inició (le dio "Iniciar" y/o escaneó número de serie). | `equipos con estatus_area = EN_PROCESO` |
| **Piezas** | Equipos bloqueados esperando una pieza. | `equipos con estatus_area = PENDIENTE_PIEZA` |
| **Garantía** | Equipos en garantía (interna o externa). | `equipos con estatus_area IN (PENDIENTE_GARANTIA, GARANTIA_INT, GARANTIA_EXT)` |
| **Desarme** | Equipos pendientes de desarme. | `equipos con estatus_area = PENDIENTE_DESARME` |
| **Calidad** | Equipos en revisión de calidad. | `equipos con estatus_area = EN_CALIDAD` |
| **Aprobados** | Equipos que pasaron calidad. | `equipos con estatus_area = FINALIZADO` |
| **Transferidos** | Equipos transferidos fuera de preparación. | `equipos con estatus_area = TRANSFERIDO` |

### Cálculo detallado de "Disponibles"

```
equipos_sin_serie = total_recibido (lote) - total_fisicos (equipos con serie en DB)
cupos_asignados_sin_serie = SUM de cupos prometidos por el gerente que el técnico aún no escaneó
                          = asignaciones.cantidad - COUNT(asignacion_equipos) por cada asignación activa
disponibles_sin_serie = equipos_sin_serie - cupos_asignados_sin_serie
disponibles_con_serie = equipos con estatus_area IN (SIN_ASIGNAR, EN_ESPERA)

DISPONIBLES = disponibles_sin_serie + disponibles_con_serie
```

**Desglose:**
- `equipos_sin_serie`: Son equipos que llegaron en el lote y aún no tienen número de serie en la tabla `equipos`. Están físicamente en bodega.
- `cupos_asignados_sin_serie`: Son "promesas" del gerente. Cuando el gerente crea una asignación de 10 equipos para un técnico, pero el técnico solo ha escaneado 3, los otros 7 son cupos reservados. Estos se **restan** de los disponibles y se **suman** a los asignados.
- `disponibles_con_serie`: Son equipos que SÍ tienen número de serie (ya sea porque llegaron con serie desde el lote o porque alguien los escaneó) pero aún están en bodega esperando asignación.

### Cálculo detallado de "Asignados"

```
asignados_fisicos = equipos con estatus_area = ASIGNADO (pre-asignados por gerente con serie)
asignados_promesa = cupos_asignados_sin_serie (calculado arriba)

ASIGNADOS = asignados_fisicos + asignados_promesa
```

**Clave:** "Asignado" ≠ "En Proceso". El gerente asigna → el técnico ve la asignación → cuando el técnico da "Iniciar" e ingresa el número de serie (o inicia un equipo pre-asignado), ENTONCES pasa a "En Proceso".

### Flujo resumido de un equipo en el dashboard

```
Llega en Lote (Total Preparación)
    ↓
En Bodega sin serie (Disponible) ──o── En Bodega con serie (Disponible)
    ↓                                      ↓
Gerente asigna cupos (Asignado)     Gerente pre-asigna (Asignado)
    ↓                                      ↓
Técnico escanea serie (En Proceso)  Técnico da Iniciar (En Proceso)
    ↓
    ├── Termina OK → Calidad → Aprobado → Transferido
    ├── Falta pieza → Piezas
    ├── Garantía → Garantía
    └── Desarme → Desarme
```

### Subconsulta de cupos asignados sin serie (`cupos_asignados_sin_serie`)

Se calcula dentro de `$lotesSub` con una subconsulta correlacionada:

```sql
SUM(
    COALESCE((
        SELECT SUM(GREATEST(a.cantidad - (
            SELECT COUNT(*) FROM asignacion_equipos ae WHERE ae.asignacion_id = a.id
        ), 0))
        FROM asignaciones a
        WHERE a.lote_modelo_id = lmr.id
        AND a.estatus IN ('PENDIENTE', 'EN_PROCESO')
        AND a.deleted_at IS NULL
    ), 0)
) as cupos_asignados_sin_serie
```

Esto dice: para cada `lote_modelo`, suma la diferencia entre `asignaciones.cantidad` (lo que el gerente prometió) y `COUNT(asignacion_equipos)` (lo que el técnico ya escaneó), solo para asignaciones activas.

---

## 11. Dashboard: Comportamiento de Filtros y Gráficas (Reactividad)

### Filtros en el Dashboard
El componente `EstadisticasEquipos` cuenta con filtros por:
- **Estatus:** Se activa haciendo clic en las tarjetas superiores (ej. Disponibles, Asignados, Calidad).
- **Marca / Modelo / Tipo:** A través de los selectores (dropdowns).
- **Búsqueda:** Campo de texto.

### Lógica de Filtrado y Renderizado de Gráficas
Para mantener la congruencia entre la información solicitada por el usuario al filtrar y las gráficas mostradas, se aplica la siguiente lógica en `getDatosGraficasProperty()`:

1. **La tabla sigue mostrando la fila completa:** Cuando se aplica un filtro (ej. Estatus "Calidad"), la tabla muestra únicamente los modelos que tienen al menos 1 equipo en dicho estatus. Sin embargo, para ese modelo, se muestran todas sus columnas (cuántos tiene disponibles, en proceso, etc.). Esto es por diseño, para que la tabla funcione como un desglose completo del modelo filtrado.
2. **Las gráficas se enfocan ("Focus Mode"):** A diferencia de la tabla, las gráficas (Donut, Barras Top 15, y Apiladas) **ponen en cero** automáticamente todos los valores de los demás estatus cuando se hace clic en una tarjeta de estatus.
   - Si el usuario filtra por "Calidad", el `Donut` será 100% Calidad (no sumará los equipos en proceso de esos mismos modelos).
   - El gráfico de `Barras Top 15` mostrará únicamente barras de "Calidad" y reordenará el Top 15 usando **exclusivamente** el conteo de Calidad.
   - El gráfico de `Apiladas por Marca` mostrará solo la fracción de "Calidad" para cada marca.
3. **Reactividad con `wire:key`:** Dado que Livewire destruye y recrea el DOM de ApexCharts, se inyecta un `wire:key` dinámico generado por un hash de los datos (`md5(json_encode($graficas))`). Esto garantiza que cuando el usuario hace clic en un filtro (cambiando los datos), Livewire destruya el contenedor anterior y Alpine.js (`x-data`) vuelva a instanciar la gráfica desde cero, previniendo que se quede "atrapada" por el `wire:ignore` clásico de Alpine+Livewire.
4. **Preservación de Scroll:** Se implementó un hook `Livewire.hook('commit', ...)` en el wrapper principal del dashboard para guardar el `window.scrollY` antes de la actualización del componente y restaurarlo inmediatamente después usando `requestAnimationFrame`, evitando el salto del navegador hacia la parte superior al cambiar vistas o filtros.
