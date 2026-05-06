# Deploy a producción: `fdc042ca` → `236b1f0` (TecnoByte)

Este documento es un checklist para subir producción desde el commit:

- **Producción (actual):** `fdc042ca8e5b8cb86abdac9f3acca69a4f6f23da`
- **Objetivo (release):** `236b1f0b57e97633e63ad09f7fc1a38eb402742a`

> Recomendación fuerte: ensayar 1:1 en **staging** con un dump reciente de producción antes de tocar prod.

## Cambios críticos a considerar

### Migraciones nuevas desde prod

Estas migraciones **no existen** en `fdc042ca` y se ejecutarán en producción al desplegar:

- `database/migrations/2026_04_21_000001_crear_solicitud_pieza_intentos.php`
- `database/migrations/2026_04_21_000002_add_requiere_reasignacion_to_solicitudes_piezas.php`
- `database/migrations/2026_04_21_000004_agregar_pendiente_a_asignacion_equipos.php`
- `database/migrations/2026_04_22_162346_agregar_en_calidad_pre_asignado_a_asignacion_equipos.php`
- `database/migrations/2026_04_23_120000_add_campos_estructurados_a_solicitudes_piezas.php`
- `database/migrations/2026_04_24_133500_guardar_solicitud_activa_unica_por_equipo.php`

### Puntos de riesgo (DB)

- `2026_04_21_000004_...` y `2026_04_22_...` ejecutan `ALTER TABLE ... MODIFY COLUMN ... ENUM(...)` sobre `asignacion_equipos.camino`.
  - En rollback, “reducir” el enum puede fallar si existen valores nuevos (`PRE_ASIGNADO`, `EN_CALIDAD`, etc.).
- `2026_04_24_133500_...` crea una **columna generada** `active_equipo_id` en `solicitudes_piezas` y un **índice UNIQUE** para garantizar “1 solicitud activa por equipo”.
  - Si producción tiene datos legacy/duplicados, la migración intenta normalizar y auto-cancelar duplicadas, pero aun así puede fallar si hay casos no contemplados.

## Ensayo en staging (obligatorio si quieres cero sorpresas)

1. Restaurar dump de producción en una DB de staging (misma versión MySQL/MariaDB).
2. Desplegar el código a staging (mismo proceso que prod).
3. Ejecutar:
   - `php artisan migrate --force`
   - `php artisan db:seed --class=PermisosSeeder --force` (si tu deploy lo incluye; evita romper menús/permisos)
4. Smoke tests manuales:
   - Asignaciones: crear asignación, verificar `asignacion_equipos` creados.
   - Técnico: `MiTrabajo` → iniciar equipo → terminar → validar caminos.
   - Solicitudes piezas: surtido/reasignación/confirmación.
   - Dashboard y permisos (menú sidebar visible por rol).

Si staging pasa, prod es “procedimental”.

## Despliegue en producción (ventana corta)

### 0) Pre-flight (antes de mantenimiento)

- Confirmar espacio en disco y backups.
- Confirmar versión DB y que soporta:
  - columnas generadas `STORED`
  - índices sobre columnas generadas

### 1) Backup (no negociable)

- Backup de DB (dump completo).
- Backup del release actual (carpeta del proyecto o artefacto).

### 2) Modo mantenimiento

- `php artisan down`

### 3) Actualizar código

- Cambiar el release a `236b1f0...` (pull/checkout/tag según tu flujo).
- Dependencias:
  - `composer install --no-dev --optimize-autoloader`
  - (si aplica) build de assets con tu pipeline habitual

### 4) Migraciones

- `php artisan migrate --force`

> Si falla aquí: NO intentes “arreglar sobre la marcha” sin backup/rollback plan. Revisa el error, restaura DB si fue parcial y repite en staging.

### 5) Seed de permisos (si usas menús por permiso)

- `php artisan db:seed --class=PermisosSeeder --force`

### 6) Cachés (Laravel)

- `php artisan optimize:clear`
- `php artisan config:cache`
- `php artisan route:cache` (solo si no usas closures en rutas)
- `php artisan view:cache`

### 7) Levantar sistema

- `php artisan up`

## Verificación post-deploy (15–30 min)

- Probar 1 flujo completo:
  - líder/gerente crea asignación
  - técnico inicia y termina equipo
  - confirmación de piezas (si aplica)
- Revisar logs:
  - `storage/logs/laravel.log`
- Confirmar que no haya errores 500 en rutas clave.

## Rollback (realista)

- El rollback más seguro es **volver al release anterior + restaurar DB** desde backup si las migraciones dejaron el esquema/datos incompatibles.
- `migrate:rollback` puede no ser suficiente por los `ALTER TABLE ... ENUM(...)` si existen valores nuevos en datos.

