# Auditoria De Vistas Y Permisos

Fecha: 2026-03-30

Estado: auditoria realizada primero y correcciones aplicadas despues en el codigo durante esta misma sesion. Los hallazgos marcados aqui describen lo encontrado originalmente.

Fuentes revisadas:
- `routes/web.php`
- `routes/auth.php`
- `bootstrap/app.php`
- `app/Models/User.php`
- `app/Http/Middleware/*`
- `app/Http/Controllers/*`
- `app/Livewire/*`
- `resources/views/*`
- `DB mas reciente.sql`
- `database/seeders/PermisosSeeder.php`
- `database/seeders/RolesSeeder.php`

## Resumen Ejecutivo

Tu seguridad esta montada como un RBAC propio y en general la base esta bien:
- autenticacion con `auth`
- validacion de rol/departamento con `role_depto`
- autorizacion por slug con `permiso:*`
- resolucion real del permiso con `User::tienePermiso()`

No estas usando `Gate` ni `Policy` como centro, pero eso no es malo por si solo. El problema no es "no usar Gate", sino que hoy hay varias incoherencias entre:
- slugs usados en vistas
- slugs definidos en BD
- slugs asignados por rol
- checks por rol (`admin` vs `admin_sistema`)
- permisos de ruta vs intencion funcional

## Correcciones Aplicadas Y Motivo

Despues de la auditoria se corrigieron las incoherencias detectadas para alinear estas 3 capas:
- ruta
- vista
- backend del componente/controlador

La idea fue evitar estos problemas:
- una vista preguntando por un slug distinto al real
- una ruta protegida con un permiso demasiado amplio o incorrecto
- un componente Livewire ejecutando acciones sensibles aunque la UI ya no las mostrara

### 1. Rutas Y Permisos Reales

Archivo principal:
- `routes/web.php`

Que se corrigio:
- `equipos/registrar` cambio de `prep.equipos.ver` a `prep.equipos.crear`
- `equipos/{equipo}/etiqueta-comando` quedo protegido con `prep.equipos.imprimir`
- `sistema/avisos` cambio a `sistema.admin.configuracion`
- `preparacion/catalogo-piezas` quedo con `prep.inventario.gestion`
- se elimino una ruta duplicada de `preparacion/dashboard`
- se alinearon referencias de vistas hacia la estructura `preparacion.*`

Por que:
- habia rutas que permitian entrar con un permiso mas amplio del que correspondia a la accion real
- eso hacia que el RBAC fuera inconsistente entre intencion funcional y acceso efectivo

### 2. Vista De Transferencias

Archivo:
- `resources/views/livewire/preparacion/inventario/transferencias.blade.php`

Que se corrigio:
- permiso de la vista de `transferencias.crear` a `prep.transferencias.crear`
- ruta del boton de `inventario.transferencias.crear` a `inventario.prep.transferencias.crear`

Por que:
- el boton estaba usando un slug y una ruta que no existian realmente en la aplicacion
- eso podia ocultar el boton incorrectamente o romper el enlace aunque el backend estuviera protegido

### 3. Resumen De Inventario

Archivo:
- `resources/views/livewire/preparacion/inventario/resumen-inventario.blade.php`

Que se corrigio:
- `equipos.editar` se alineo a `prep.equipos.editar`
- los checks quedaron consistentes con `prep.equipos.ver`

Por que:
- la vista estaba revisando slugs que no corresponden con los permisos reales definidos en la base

### 4. Navegacion Y Sidebar

Archivos:
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/sidebar.blade.php`

Que se corrigio:
- `Registrar usuario` ahora depende de `sistema.usuarios.crear`
- el bloque de usuarios muestra opciones segun permiso real
- `Anuncios` dejo de depender de `sistema.usuarios.ver` y paso a `sistema.admin.configuracion`
- `Catalogo piezas` se alineo con `prep.inventario.gestion`

Por que:
- aqui la seguridad es visual, no la definitiva
- pero si la UI muestra acciones incoherentes con el backend, el sistema se vuelve confuso y propenso a errores de autorizacion

### 5. Gestion De Usuarios

Archivo:
- `resources/views/usuarios/index.blade.php`

Que se corrigio:
- boton `Nuevo usuario` solo con `sistema.usuarios.crear`
- acciones de editar y dar de baja solo con `sistema.usuarios.editar`
- se ajusto la presentacion de roles para incluir `admin_sistema`

Por que:
- habia botones visibles sin depender del permiso correcto
- tambien habia inconsistencia entre `admin` y `admin_sistema`

### 6. Admin Vs Admin_Sistema

Archivos:
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Middleware/OnlyAdminCeo.php`
- `app/Models/User.php`
- `app/Traits/TieneRolTransferencias.php`

Que se corrigio:
- se incluyo `admin_sistema` en las validaciones donde antes solo se consideraba `admin` o `ceo`

Por que:
- el respaldo real de base de datos usa `admin_sistema`
- parte del codigo seguia esperando `admin`
- eso podia bloquear acciones validas o provocar comportamientos inconsistentes

### 7. Seeder De Roles

Archivo:
- `database/seeders/RolesSeeder.php`

Que se corrigio:
- los roles quedaron alineados con la base real:
- `ceo`
- `gerente`
- `tecnico`
- `lider`
- `usuario`
- `admin_sistema`
- `sistemas`
- `exhibicion`

Por que:
- el seeder anterior no representaba fielmente la base real
- eso era peligroso si se volvian a sembrar datos en otro entorno

### 8. Seeder De Permisos

Archivo:
- `database/seeders/PermisosSeeder.php`

Que se corrigio:
- se ajusto especialmente la asignacion de `sistema.avisos.ver`
- se redujo su reparto en roles donde ya no era coherente con la nueva proteccion de la ruta de avisos

Por que:
- la pantalla de avisos estaba funcionando mas como gestion administrativa que como simple lectura
- si la ruta exige administracion pero el seeder reparte acceso visual amplio, el modelo queda incoherente

### 9. Refuerzo En Livewire

Aqui estuvo la parte mas importante: se agrego autorizacion dentro de los componentes para que la seguridad no dependiera solo de la ruta o de esconder botones en Blade.

#### Registrar Equipo

Archivo:
- `app/Livewire/Preparacion/Equipos/RegistrarEquipo.php`

Que se corrigio:
- se agrego `autorizarCreacion()` en `mount()` y `guardar()`

Por que:
- la ruta ya protegia, pero faltaba una segunda capa dentro del propio componente

#### Editar Equipo

Archivo:
- `app/Livewire/Preparacion/Equipos/EditarEquipo.php`

Que se corrigio:
- se agrego `autorizarEdicion()` en `mount`, `transferir`, `solicitarGuardar` y `confirmarGuardar`

Por que:
- editar y transferir son acciones sensibles y debian exigir el permiso real tambien desde el backend del componente

#### Avisos

Archivo:
- `app/Livewire/Avisos/Index.php`

Que se corrigio:
- se agrego `autorizarGestionAvisos()` en `mount`, crear, editar, guardar, activar/desactivar, fijar y eliminar

Por que:
- el componente hace CRUD completo y no bastaba con esconder botones

#### Catalogo De Piezas

Archivo:
- `app/Livewire/Preparacion/Inventario/CatalogoPiezas.php`

Que se corrigio:
- se agrego `autorizarGestion()` en `mount()` y en acciones de modificacion

Por que:
- es un modulo de gestion real de inventario y no solo de consulta

#### Gestion De Inventario

Archivo:
- `app/Livewire/Preparacion/Inventario/GestionInventario.php`

Que se corrigio:
- se agrego `autorizarGestion()` en `mount`, exportaciones, eliminacion y cambios masivos

Por que:
- modifica informacion y exporta datos sensibles, por lo que debia depender tambien del permiso de gestion dentro del componente

#### Inventario Listo

Archivo:
- `app/Livewire/Preparacion/Inventario/InventarioListo.php`

Que se corrigio:
- se agrego `autorizarVisualizacion()` en `mount()`

Por que:
- aunque es un modulo mas de lectura, debia respetar el mismo permiso que la ruta

#### Resumen De Inventario

Archivo:
- `app/Livewire/Preparacion/Inventario/ResumenInventario.php`

Que se corrigio:
- se agrego `autorizarResumen()` en `mount`, apertura de resumen, exportaciones y comparacion

Por que:
- expone informacion y exportaciones, por lo que debia respetar permiso desde backend

#### Mi Trabajo

Archivo:
- `app/Livewire/Preparacion/Equipos/MiTrabajo.php`

Que se corrigio:
- se agrego `autorizarTrabajo()` en `mount` y en las acciones principales del flujo del tecnico

Por que:
- es un componente operativo real y no debia depender solo del acceso por URL

#### Asignaciones

Archivo:
- `app/Livewire/Preparacion/Equipos/Asignaciones.php`

Que se corrigio:
- se agrego `autorizarGestionAsignaciones()` en `mount`, guardar asignacion y cancelar asignacion

Por que:
- asignar carga de trabajo a tecnicos es una accion de gestion y debia exigir permiso tambien desde el componente

#### Dashboard

Archivo:
- `app/Livewire/Dashboard/Dashboard.php`

Que se corrigio:
- se ajusto la deteccion de `esAdminCeo` para incluir `admin_sistema`
- se reemplazaron retornos silenciosos por `abort_unless(..., 403)` en acciones de empleado del mes

Por que:
- antes algunas acciones simplemente no hacian nada si el usuario no tenia acceso
- ahora el sistema responde como autorizacion denegada, que es lo correcto

### 10. Conclusion Tecnica

Lo que se corrigio en conjunto fue:
- slugs de permisos mal escritos
- rutas mal nombradas
- checks de vista incoherentes
- diferencias entre `admin` y `admin_sistema`
- falta de autorizacion interna en componentes Livewire

Y por que era necesario:
- porque la vista nunca debe ser la seguridad principal
- la vista solo oculta o muestra opciones
- la seguridad real debe vivir en middleware, en `User::tienePermiso()` y en validaciones dentro del backend

Sobre `Gate` y `Policy`:
- no es incorrecto no usarlos
- el proyecto ya implementa un RBAC propio
- el problema original no era la ausencia de `Gate`, sino las incoherencias entre rutas, vistas, roles y seeders

## Hallazgos Prioritarios

### Critico

1. Registro de equipos protegido con permiso incorrecto.
- Ruta: `routes/web.php:135-137`
- Actual: `permiso:prep.equipos.ver`
- Esperado por intencion: `prep.equipos.crear`
- Riesgo: un usuario con permiso de ver podria entrar a una pantalla de alta.
- Agravante: `app/Livewire/Preparacion/Equipos/RegistrarEquipo.php` no revalida un permiso de creacion en `mount()` ni al guardar.

2. Gestion de avisos expuesta por permiso demasiado amplio.
- Ruta: `routes/web.php:270-272`
- Actual: `permiso:sistema.avisos.ver`
- El componente `app/Livewire/Avisos/Index.php` permite crear, editar, activar, fijar y borrar avisos.
- En la vista se presenta como "Solo Admin / CEO" en `resources/views/livewire/avisos/index.blade.php:27-32`.
- Pero en la matriz real de permisos el rol tecnico recibe `sistema.avisos.ver` y `modulo.sistema` en `database/seeders/PermisosSeeder.php:192-200`.
- Riesgo: por URL directa un tecnico puede administrar avisos del sistema.

3. Endpoint de impresion de etiquetas sin permiso granular.
- Ruta: `routes/web.php:70-95`
- Solo vive bajo `auth` + `role_depto`.
- No exige `prep.equipos.imprimir` ni `prep.equipos.ver`.
- Riesgo: cualquier usuario autenticado con rol/departamento valido puede solicitar el comando de etiqueta de cualquier equipo.

### Alta

4. Slug de permiso inexistente en transferencias.
- Vista: `resources/views/livewire/preparacion/inventario/transferencias.blade.php:113`
- Actual: `tienePermiso('transferencias.crear')`
- En BD y rutas existe `prep.transferencias.crear`, no `transferencias.crear`.
- Efecto: el boton puede no mostrarse aunque el usuario si tenga permiso real.

5. Nombre de ruta inexistente en transferencias.
- Vista: `resources/views/livewire/preparacion/inventario/transferencias.blade.php:114`
- Actual: `route('inventario.transferencias.crear')`
- La ruta registrada es `inventario.prep.transferencias.crear` en `routes/web.php:118-120`.
- Efecto: enlace roto si la condicion del boton llegara a cumplirse.

6. Slugs inexistentes en resumen de inventario.
- Vista: `resources/views/livewire/preparacion/inventario/resumen-inventario.blade.php:706`
- Vista: `resources/views/livewire/preparacion/inventario/resumen-inventario.blade.php:711`
- Actual: `tienePermiso('equipos.editar')`
- En BD y rutas existe `prep.equipos.editar`, no `equipos.editar`.
- Efecto: acciones ocultas de forma incorrecta.

7. Inconsistencia fuerte de roles `admin` vs `admin_sistema`.
- `database/seeders/RolesSeeder.php:12-16` crea `admin`.
- La BD real contiene `admin_sistema`, `gerente`, `lider`, `usuario`, `sistemas`, `exhibicion` en `DB mas reciente.sql:1484`.
- Varias partes del codigo usan `admin`:
  - `app/Http/Middleware/OnlyAdminCeo.php:17`
  - `app/Http/Controllers/ProfileController.php:21` y `:32`
  - `app/Models/User.php:93`
  - `resources/views/layouts/navigation.blade.php:52`
- Otras usan `admin_sistema`:
  - `app/Traits/TieneRolTransferencias.php:17`
  - `app/Http/Controllers/UserController.php:26`
  - `app/Http/Controllers/Auth/RegisteredUserController.php:20`
- Efecto: un `admin_sistema` puede ver y operar distinto segun la pantalla.

### Media

8. Catalogo de piezas usa permiso de equipos, no de inventario.
- Ruta: `routes/web.php:221-223`
- Actual: `permiso:prep.equipos.ver`
- En sidebar el item se filtra con `prep.inventario.ver` en `resources/views/layouts/sidebar.blade.php:488-490`.
- Riesgo: logica semantica incoherente entre menu y backend.

9. Link de avisos en sidebar revisa el permiso incorrecto.
- Vista: `resources/views/layouts/sidebar.blade.php:902-909`
- Actual: muestra "Anuncios" si el usuario tiene `sistema.usuarios.ver`
- Lo coherente seria `sistema.avisos.ver`
- Efecto: usuarios con permiso de avisos pueden no ver el acceso; usuarios con permiso de usuarios si.

10. Menu de usuarios muestra "Crear Usuario" sin filtrar por permiso de crear.
- Vista: `resources/views/layouts/sidebar.blade.php:735-744`
- El bloque completo solo exige `sistema.usuarios.ver`.
- Dentro del menu el enlace a `route('register')` no se filtra por `sistema.usuarios.crear`.
- Efecto: UX inconsistente y posible 403 al entrar.

11. Dashboard duplicado.
- Ruta repetida en `routes/web.php:202-208`
- No rompe permisos por si mismo, pero aumenta riesgo de divergencia futura.

12. Permiso `sistema.dashboard.ver` existe pero no esta siendo usado en rutas.
- Definido en BD y seeder.
- No aparece como middleware de dashboards.
- Parece permiso muerto o incompleto.

## Matriz De Vistas Revisadas

### Publicas / Auth

| Ruta | Vista o componente | Control actual | Estado |
| --- | --- | --- | --- |
| `/` | `auth.login` | publica | Aceptable, pero usuarios autenticados aun pueden caer aqui |
| `/login` | `auth.login` | `guest` | Correcto |
| `/forgot-password` | `auth.forgot-password` | `guest` | Correcto |
| `/reset-password/{token}` | `auth.reset-password` | `guest` | Correcto |
| `/verify-email` | `auth.verify-email` | `auth` | Correcto |
| `/confirm-password` | `auth.confirm-password` | `auth` | Correcto |

### Perfil

| Ruta | Vista | Control actual | Estado |
| --- | --- | --- | --- |
| `/perfil` | `profile.show` | `auth` + `role_depto` | Correcto |
| `/perfil/editar` | `profile.edit` | `auth` + `role_depto` | Correcto, pero con checks `admin` vs `admin_sistema` inconsistentes |

### Dashboards

| Ruta | Vista | Control actual | Estado |
| --- | --- | --- | --- |
| `/dashboard` | `AfterLoginRedirectController` | `auth` + `role_depto` | Correcto |
| `/preparacion/dashboard` | `DashboardController@index` | `permiso:modulo.preparacion` | Correcto, ruta duplicada |
| `/ventas/dashboard` | `ventas.dashboard` | `permiso:modulo.ventas` | Correcto |
| `/soporte/dashboard` | `soporte.dashboard` | `permiso:modulo.soporte` | Correcto |
| `/rrhh/dashboard` | `rrhh.dashboard` | `permiso:modulo.rrhh` | Correcto |
| `/administracion/dashboard` | `administracion.dashboard` | `permiso:modulo.administracion` | Correcto |

### Preparacion > Equipos

| Ruta | Vista o componente | Control actual | Estado |
| --- | --- | --- | --- |
| `/equipos/registrar` | `preparacion.equipos.registrar` -> `preparacion.equipos.registrar-equipo` | `permiso:prep.equipos.ver` | Incorrecto, deberia ser `prep.equipos.crear` |
| `/equipos/caracteristicas` | `preparacion.inventario.resumen` -> `preparacion.inventario.resumen-inventario` | `permiso:prep.equipos.ver` | Ruta correcta, vista con slug interno incorrecto |
| `/equipos/{equipo}/editar` | `preparacion.equipos.editar-equipo` | `permiso:prep.equipos.editar` | Correcto |
| `/preparacion/mi-trabajo` | `Preparacion\\Equipos\\MiTrabajo` | `permiso:prep.equipos.ver` | Aceptable |
| `/preparacion/asignaciones` | `Preparacion\\Equipos\\Asignaciones` | `permiso:prep.inventario.gestion` | Aceptable |
| `/equipos/{equipo}/etiqueta-comando` | respuesta TSPL | `auth` + `role_depto` | Falta permiso granular |

### Preparacion > Inventario

| Ruta | Vista o componente | Control actual | Estado |
| --- | --- | --- | --- |
| `/inventario/listo` | `preparacion.inventario.listo` -> `preparacion.inventario.inventario-listo` | `permiso:prep.inventario.ver` | Correcto |
| `/inventario/gestion` | `preparacion.inventario.gestion-inventario` -> `preparacion.inventario.gestion-inventario` | `permiso:prep.inventario.gestion` | Correcto |
| `/inventario/transferencias` | `preparacion.inventario.transferencias` -> `preparacion.inventario.transferencias` | `permiso:prep.transferencias.ver` + check Livewire | Correcto en backend, incorrecto en boton de vista |
| `/inventario/transferencias/crear` | `preparacion.inventario.transferencias-crear` -> `preparacion.inventario.transferencias-crear` | `permiso:prep.transferencias.crear` + check Livewire | Correcto en backend |
| `/preparacion/catalogo-piezas` | `Preparacion\\Inventario\\CatalogoPiezas` | `permiso:prep.equipos.ver` | Incoherente con el menu y el dominio funcional |
| `/inventario/piezas/solicitudes` | `Inventario\\SolicitudesPiezas` | `permiso:prep.equipos.ver` | Aceptable |
| `/inventario/piezas/gestionar` | `Inventario\\GestionSolicitudesPiezas` | `permiso:prep.inventario.gestion` | Correcto |

### Preparacion > Lotes

| Ruta | Vista o componente | Control actual | Estado |
| --- | --- | --- | --- |
| `/lotes/registrar` | `preparacion.lotes.registrarlote` -> `preparacion.lotes.registrar-lote` | `permiso:prep.lotes.gestion` | Correcto |
| `/lotes/editar` | `preparacion.lotes.listalotes` -> `preparacion.lotes.lista-lotes` | `permiso:prep.lotes.ver` | Correcto |
| `/lotes/{lote}/editar` | `preparacion.lotes.editarlote` -> `preparacion.lotes.editar-lote` | `permiso:prep.lotes.gestion` | Correcto |

### Sistema

| Ruta | Vista o componente | Control actual | Estado |
| --- | --- | --- | --- |
| `/sistema/usuarios` | `usuarios.index` | `permiso:modulo.sistema` + `permiso:sistema.usuarios.ver` | Correcto |
| `/sistema/usuarios/{user}/edit` | `usuarios.edit` | `permiso:modulo.sistema` + `permiso:sistema.usuarios.editar` + scoping en controlador | Correcto |
| `/sistema/usuarios/crear` | `auth.register` | `permiso:modulo.sistema` + `permiso:sistema.usuarios.crear` + scoping en controlador | Correcto |
| `/sistema/avisos` | `Avisos\\Index` | `permiso:modulo.sistema` + `permiso:sistema.avisos.ver` | Funcionalmente riesgoso porque ese "ver" hoy administra |

## Coherencia De La Base De Datos

### Permisos

Los slugs usados por middleware en rutas si existen en `DB mas reciente.sql`:
- `modulo.preparacion`
- `modulo.ventas`
- `modulo.soporte`
- `modulo.rrhh`
- `modulo.administracion`
- `modulo.sistema`
- `prep.equipos.ver`
- `prep.equipos.editar`
- `prep.inventario.ver`
- `prep.inventario.gestion`
- `prep.lotes.ver`
- `prep.lotes.gestion`
- `prep.transferencias.ver`
- `prep.transferencias.crear`
- `sistema.usuarios.ver`
- `sistema.usuarios.crear`
- `sistema.usuarios.editar`
- `sistema.avisos.ver`

Los slugs detectados en vistas que NO existen en la BD:
- `transferencias.crear`
- `equipos.editar`

### Roles

En la BD real existen:
- `ceo`
- `gerente`
- `tecnico`
- `lider`
- `usuario`
- `admin_sistema`
- `sistemas`
- `exhibicion`

En `RolesSeeder.php` solo se crean:
- `ceo`
- `admin`
- `tecnico`

Esto significa que un entorno sembrado desde cero no quedaria alineado con la logica actual del sistema.

### Usuario Permiso

No encontre inserts de `usuario_permiso` en `DB mas reciente.sql`.

Conclusion:
- Hoy el RBAC real esta funcionando por `rol_permiso`.
- `usuario_permiso` existe a nivel modelo (`User::tienePermiso()`), pero no esta siendo usado en tu respaldo actual.

## Respuesta A Tu Pregunta Sobre Gate / Policy

No hay nada "malo" en no usar `Gate` o `Policy` si tu RBAC propio esta bien mantenido.

Ventajas de tu enfoque actual:
- simple de entender
- muy directo para slugs tipo `permiso:prep.inventario.ver`
- encaja bien con una tabla `rol_permiso`

Desventajas que ya se notan en tu proyecto:
- es facil meter slugs mal escritos en vistas
- es facil mezclar checks por permiso con checks por rol
- se repiten reglas en rutas, vistas, controladores y componentes
- el framework ya no te ayuda a centralizar autorizacion por recurso

Mi conclusion:
- tu problema no es no usar `Gate` o `Policy`
- tu problema hoy es falta de consistencia interna del RBAC

Si mantienes tu RBAC, te conviene:
1. unificar todos los checks a slugs `permiso:*`
2. dejar de usar comparaciones sueltas por rol salvo en casos excepcionales
3. usar constantes o enum para slugs
4. separar permisos de "ver" y "gestionar" cuando una pantalla hace CRUD

## Orden Recomendado De Correccion

1. Corregir `/equipos/registrar` a `prep.equipos.crear` y revalidar en `RegistrarEquipo`.
2. Corregir gestion de avisos: o cambias permiso/roles o separas `sistema.avisos.ver` de `sistema.avisos.gestion`.
3. Proteger `equipos.etiqueta.comando` con permiso.
4. Corregir slugs invalidos en `transferencias.blade.php` y `resumen-inventario.blade.php`.
5. Unificar `admin` vs `admin_sistema` en todo el proyecto.
6. Corregir sidebar de avisos y filtros de menu.
7. Alinear `RolesSeeder.php` con la BD real.
