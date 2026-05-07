# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start full dev environment (server + queue + logs + vite, concurrently)
composer dev

# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=PermisosSeeder

# Lint with Pint
./vendor/bin/pint

# Run tests (PEST)
composer test
# or single test file:
php artisan test --filter=NombreTest

# Static analysis
./vendor/bin/phpstan analyse

# Tinker (DB verification)
php artisan tinker

# Visualize routes
php artisan route:list
```

## Architecture Overview

**Stack:** Laravel 12 + Livewire 3.6 + Tailwind CSS 3 + Alpine.js. DB: MySQL (`tecnobyte_soporte` or `TecnoByte_preparacion`). Timezone: `America/Mexico_City`, locale: `es`.

### Permission & Role System

- Every user has a single `role_id` → `roles` table (slug: `ceo`, `gerente`, `lider`, `tecnico`, `admin_sistema`, `usuario`, `sistemas`, `exhibicion`).
- `ceo` bypasses all permission checks automatically.
- Permissions are checked via `User::tienePermiso(string $slug)` which queries `rol_permiso` and `usuario_permiso` pivot tables.
- Routes are protected with `->middleware('permiso:{slug}')` using a custom middleware.
- User model has a global scope filtering `is_active = true` and uses `SoftDeletes`.

### Equipment Lifecycle

Equipos have two parallel statuses:
- `estatus_ciclo`: `CEDIS → PREPARACION → CALIDAD → VENTAS → APARTADO → VENDIDO / SCRAP`
- `estatus_area` (internal to the area): `SIN_ASIGNAR → ASIGNADO → EN_PROCESO → LISTO / PENDIENTE_PIEZA / PENDIENTE_GARANTIA / …`

A technician's work on a single equipment is recorded in `AsignacionEquipo` with a `camino` field:
- `EN_PROCESO` → `COMPLETADO` (full completion, 100% points)
- `EN_PROCESO` → `PIEZA_PENDIENTE` (40% points — INICIO_PIEZA)
- After a piece is installed: `confirmarInstalacionPieza()` gives 60% points — TERMINO_PIEZA

### Points System (PuntoTecnico)

`PuntoTecnico::registrar($tecnicoId, $asignacionEquipoId, $rol, $puntosBase)` calculates:
- `COMPLETO` = 100%, `INICIO_PIEZA` = 40%, `TERMINO_PIEZA` = 60%, `GARANTIA` = 30%

Period format: `Y-m` (e.g. `2026-03`).

### MiTrabajo: Guardrails de puntuación

- `MiTrabajo::iniciarEquipos()` propaga `clasificacion_puntos_id` desde `LoteModeloRecibido` al equipo (existente o nuevo) cuando falta en `equipos`.
- `MiTrabajo::terminarEquipo()` exige clasificación válida para registrar puntos:
  - intenta usar `equipo.clasificacion_puntos_id`
  - si falta, hace fallback a `lote_modelo.clasificacion_puntos_id` y persiste en el equipo
  - si sigue faltando o la clasificación no existe, lanza `PUNTOS_LOTE_FALTANTES`
- Al detectar `PUNTOS_LOTE_FALTANTES`, no termina el equipo, guarda avance silencioso y muestra toast orientativo:
  - **"No hay puntuación configurada para este equipo. Dile a tu líder/gerente que actualice la puntuación; tu avance ya fue guardado."**

### MiTrabajo: Mapeo de garantía (camino vs estatus_area)

- En garantía, el detalle técnico se guarda en `asignacion_equipos.camino`:
  - `GARANTIA_INTERNA` o `GARANTIA_EXTERNA`
- El estado operativo del equipo se unifica en `equipos.estatus_area = PENDIENTE_GARANTIA`.
- Aunque el enum de `equipos.estatus_area` incluye `GARANTIA_INT` y `GARANTIA_EXT`, en el flujo actual de `MiTrabajo::terminarEquipo()` no se usan; la decisión vigente es mantener `PENDIENTE_GARANTIA` como estado de área.

### Piece Request Flow (SolicitudPieza)

1. Technician creates a `SolicitudPieza` (estatus: `PENDIENTE`)
2. Manager/Líder reviews it in `GestionSolicitudesPiezas` → assigns an inventory piece + reasigns to a technician → estatus becomes `SURTIDA_INVENTARIO`, `InventarioPieza.estatus` = `RESERVADA`
3. Assigned technician sees it in `MiTrabajo` → clicks "Iniciar instalación" (sets `iniciada_instalacion_en = now()`, redirects to list)
4. Technician confirms result (`funciono = true/false`):
   - `funciono = true` → estatus: `CONFIRMADA`, `InventarioPieza.estatus` = `USADA`, equipment → `CALIDAD/LISTO`, registers `TERMINO_PIEZA` points
   - `funciono = false` → estatus: `CONFIRMADA`, `InventarioPieza.estatus` = `DADA_DE_BAJA`, auto-creates a new `PENDIENTE` `SolicitudPieza` for retry

### Key Livewire Components

| Component | Route | Who uses it |
|-----------|-------|-------------|
| `Preparacion\Equipos\MiTrabajo` | `/preparacion/mi-trabajo` | Técnico — ver y terminar sus equipos asignados |
| `Preparacion\Equipos\Asignaciones` | `/preparacion/asignaciones` | Gerente/Líder — ver técnicos y asignar equipos |
| `Inventario\GestionSolicitudesPiezas` | `/inventario/piezas/gestionar` | Gerente/Líder — gestionar solicitudes de piezas |
| `Inventario\SolicitudesPiezas` | `/inventario/piezas/solicitudes` | Técnico — ver sus solicitudes |
| `Preparacion\Inventario\GestionInventario` | `/inventario/gestion` | Gestión de piezas en inventario |
| `Preparacion\Lotes\ListaLotes` | `/lotes/editar` | Gestión de lotes |

`MiTrabajo` is a multi-vista component (internal routing via `$this->vista`):
- `lista` → `equipos` → `caracteristicas` → `terminar` → `confirmar_pieza`

### Lote → Equipment Chain

`Lote` → `LoteModeloRecibido` (batches by model) → `Equipo` (individual units) → `Asignacion` (batch-level assignment) → `AsignacionEquipo` (per-equipment work record).

### InventarioPieza

Statuses: `DISPONIBLE → RESERVADA → USADA / DADA_DE_BAJA`.
Field `equipo_destino_id` (nullable FK) records which equipment the piece ended up in.

### Blade Views Convention

- Route views live in `resources/views/preparacion/`, `resources/views/inventario/`, etc.
- Livewire component views live in `resources/views/livewire/preparacion/`, etc.
- Layout: `resources/views/layouts/app.blade.php` (sidebar + navbar).
- Toast notifications dispatched via `$this->dispatch('toast', type: 'success'|'error', message: '...')`.
