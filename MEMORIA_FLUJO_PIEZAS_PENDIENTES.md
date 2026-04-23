# Memoria - Flujo de Piezas Pendientes

Fecha: 2026-04-22

## Objetivo actual

Dejar documentado el estado real del flujo de `piezas pendientes`, el objetivo funcional deseado por el usuario y qué contexto de otras memorias del proyecto sí sigue siendo útil.

## Decisión funcional deseada

El flujo deseado para `piezas pendientes` es este:

1. El técnico termina el equipo y elige `PIEZA_PENDIENTE`.
2. El técnico no debe decidir si hay stock real ni escoger una pieza exacta del catálogo.
3. El técnico solo debe capturar:
   - `categoria` de la pieza
   - `notas` o especificación
   - `cantidad`
4. El sistema debe mandar el equipo a estado de pieza pendiente y crear la solicitud automáticamente.
5. El gerente o líder revisa la solicitud y decide:
   - surtir con una pieza del inventario
   - marcarla como pendiente de compra
6. Si se surte una pieza:
   - se reserva/descuenta del inventario
   - se reasigna al técnico que la instalará
7. El técnico instala y confirma resultado:
   - si funciona, el equipo debe ir a calidad
   - si falla, la pieza debe ir a baja/malos y el equipo vuelve a pieza pendiente
8. Para alimentar el inventario, se quiere centralizar todo en `catalogo-piezas` con dos entradas:
   - `compra`
   - `deshueso` o `restock desde otros equipos`
9. Se quiere evitar duplicados de catálogo por nombres distintos para la misma pieza.
10. La vista separada de `compras piezas` no es prioritaria y se quiere eliminar o dejar fuera del flujo principal.

Esta intención del usuario coincide especialmente con `solicitud.txt`, que debe tomarse como la referencia funcional principal para este tema.

## Estado real verificado hoy

### Flujo actual en código

- La solicitud nace en `app/Livewire/Preparacion/Equipos/MiTrabajo.php`.
- La administración de solicitudes vive en `app/Livewire/Inventario/GestionSolicitudesPiezas.php`.
- El alta de inventario vive en `app/Livewire/Preparacion/Inventario/CatalogoPiezas.php`.
- También existe un flujo alterno en `app/Livewire/Preparacion/Inventario/ComprasInventario.php`.

### Problemas confirmados

1. El técnico todavía decide demasiado.
   - En `MiTrabajo`, al mandar un equipo a `PIEZA_PENDIENTE`, el técnico hoy puede entrar por modo `stock` o `libre`.
   - En modo `stock` además debe seleccionar una pieza del catálogo.
   - Esto contradice el flujo deseado.

2. El flujo está partido en varias vistas/componentes.
   - La solicitud nace en `MiTrabajo`.
   - El gerente la gestiona en `GestionSolicitudesPiezas`.
   - El stock se alimenta desde `CatalogoPiezas`.
   - Además hay otra vista separada para compras.

3. Hay un desfase de estados.
   - Cuando la pieza sí funciona, `SolicitudPieza` manda el equipo a `CALIDAD / EN_CALIDAD`.
   - Pero `AsignacionEquipo.camino` queda en `PIEZA_PENDIENTE`.
   - Eso infla métricas y deja basura operativa.

4. El catálogo no evita duplicados semánticos.
   - Solo evita duplicado exacto por `LOWER(TRIM(nombre)) + categoria`.
   - Variantes como `ram 8 gb ddr4` y `ram para laptop 8gb` sí podrían coexistir.

5. Las solicitudes libres dependen de texto.
   - Hoy muchas solicitudes usan `descripcion_libre`.
   - El gerente infiere categoría parseando texto como `RAM - ...` o similares.
   - Eso es frágil.

6. Hay dos flujos de compra.
   - `CatalogoPiezas` registra compras y consolida inventario.
   - `ComprasInventario` también registra compras, pero además marca solicitudes `PENDIENTE_COMPRA` como `COMPRADA`.
   - Tener ambos caminos complica el mantenimiento.

7. No hay sincronía fuerte entre equipo y solicitud activa.
   - Hay equipos y asignaciones marcados como `PIEZA_PENDIENTE` sin solicitud operativa activa correspondiente.

### Estado real verificado en la base local

- Tabla `solicitudes_piezas`: 18 registros.
- Estatus actuales:
  - `PENDIENTE`: 2
  - `SURTIDA_INVENTARIO`: 4
  - `CONFIRMADA`: 9
  - `REQUIERE_REASIGNACION`: 2
  - `CANCELADA`: 1
- Solicitudes con catálogo: 5
- Solicitudes libres: 13
- Solicitudes reasignadas a un técnico: 13

- Tabla `catalogo_piezas`: 2 registros activos
  - `ram 8 gb ddr4`
  - `ssd 256 gb`

- Tabla `inventario_piezas`: 4 entradas
  - 3 de `COMPRA`
  - 1 de `DESHUESO`

- `asignacion_equipos` con `camino = PIEZA_PENDIENTE`: 16
- De esas, solo 7 tienen solicitud activa ligada

- `equipos` con `estatus_area = PENDIENTE_PIEZA`: 177
- De esos, solo 7 tienen solicitud activa ligada

Esto confirma que sí existe inconsistencia histórica y operativa entre:

- `equipos.estatus_area`
- `asignacion_equipos.camino`
- `solicitudes_piezas.estatus`

## Qué hay que hacer para mejorar

### Prioridad alta

1. Simplificar la captura del técnico.
   - Reemplazar la lógica actual de `stock/libre`.
   - El técnico solo debe capturar `categoria`, `detalle` y `cantidad`.

2. Normalizar la solicitud.
   - Agregar campos formales tipo:
     - `categoria_solicitada`
     - `detalle_solicitado`
   - No depender de `descripcion_libre` como string mezclado.

3. Corregir sincronía de estados.
   - Cuando la pieza funciona y el equipo pasa a calidad, `AsignacionEquipo` ya no debe quedarse en `PIEZA_PENDIENTE`.

4. Garantizar una sola solicitud activa por equipo/asignación.
   - Debe existir validación lógica y, de ser posible, apoyo en base de datos o limpieza previa.

### Prioridad media

5. Centralizar la alimentación de inventario en `CatalogoPiezas`.
   - Mantener:
     - `compra`
     - `deshueso`
     - `restock`
   - Retirar del flujo principal la vista separada `ComprasInventario`.

6. Fortalecer el catálogo para evitar duplicados.
   - Buscar por similitud o por estándar de nombre.
   - Forzar categoría obligatoria.
   - Considerar `nombre_base` o estandarización adicional si hace falta.

7. Limpiar datos históricos.
   - Hay equipos y asignaciones colgados en `PIEZA_PENDIENTE` sin solicitud activa.
   - Eso debe resolverse antes o durante la migración del flujo.

## Utilidad de otras memorias

### Archivos útiles

- `solicitud.txt`
  - Muy útil.
  - Es la mejor referencia de intención funcional del usuario para este tema.

- `ultimo.txt`
  - Útil como contexto histórico.
  - Explica que `CatalogoPiezas` fue empujado hacia un flujo con vistas de `compra` y `deshueso`.
  - Sirve para entender por qué el componente actual creció tanto.

- `chat-historial-2026-03-24.txt`
  - Útil para contexto de rutas, permisos y creación de vistas de solicitudes.
  - No describe el flujo ideal actual, pero sí da contexto de cómo se acomodó el módulo.

- `AUDITORIA_PERMISOS_VISTAS_2026-03-30.md`
  - Útil para no romper permisos y rutas al modificar inventario o solicitudes.

### Archivos parcialmente útiles pero desactualizados

- `CLAUDE.md`
- `QWEN.md`

Sirven para:

- stack del proyecto
- roles y permisos
- nombres de componentes
- rutas principales

Pero el flujo de `SolicitudPieza` que describen ya no coincide del todo con el código actual. En particular, ya no deben tomarse como verdad estas ideas:

- que `InventarioPieza` use estados `RESERVADA` y `USADA` como enum actual
- que al fallar una pieza el sistema cree automáticamente una nueva solicitud `PENDIENTE`
- que los porcentajes de puntos sigan siendo 40/60 como regla vigente del código actual

Hoy el sistema usa cantidades en inventario:

- `cantidad_disponible`
- `cantidad_reservada`
- `cantidad_usada`
- `cantidad_baja`

y el fallo actual de pieza regresa la misma solicitud a `REQUIERE_REASIGNACION`.

### Archivos poco útiles para este tema

- `reasignacion_santiago.txt`
  - Está más enfocado a reasignación de equipos/asignaciones, no al rediseño de piezas pendientes.

## Nota para trabajo futuro

Si se retoma este tema después, usar esta prioridad:

1. Simplificar captura del técnico
2. Corregir sincronía de estados
3. Unificar entrada de inventario en `CatalogoPiezas`
4. Eliminar flujo duplicado de compras
5. Limpiar datos históricos colgados

