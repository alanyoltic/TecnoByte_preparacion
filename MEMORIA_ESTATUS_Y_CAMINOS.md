# MEMORIA COMPLETA: estatus, caminos y hallazgos (chat + repo)

Contexto: TecnoByte (Laravel + Livewire + MariaDB/MySQL). Esta nota guarda los hallazgos del análisis del repo y la conversación para evitar confusiones futuras.

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
