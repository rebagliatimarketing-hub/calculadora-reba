# REBA Lanzamientos

Sistema interno para planificar, calcular, validar y aprobar lanzamientos academicos de Rebagliati Diplomados.

## Que incluye el MVP

- Login interno con usuario administrador inicial.
- Catalogos base: especialidades, publicos, tipos de evento, modalidades, certificacion, aulas, Zoom y docentes.
- Importacion de calendario desde Excel con corte desde julio 2026 en adelante.
- Creacion de lanzamientos con score comercial ponderado.
- Estructura academica por modulos, clases, frecuencia y talleres.
- Generador automatico de sesiones.
- Motor de conflictos por feriados, dia debil, aula, Zoom, docente y publico similar.
- Dashboard ejecutivo, calendario mensual, panel de conflictos, aprobaciones y reporte mensual.
- Docker, Makefile y scripts de setup, backup, restore y deploy.

## Arranque local sin Docker

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Luego abrir `http://localhost:8000`.

Usuario inicial:

- Correo: `admin@rebagliati.edu.pe`
- Contrasena: `password`

Para produccion, cambia `ADMIN_PASSWORD` antes de ejecutar los seeders.

## Arranque con Docker

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

La app queda en `http://localhost:8000`.

## Comandos utiles

```bash
make up
make fresh
make test
make backup
make cache-clear
```

## Estructura relevante

- `app/Modules`: controladores y servicios por dominio.
- `app/Models`: modelos Eloquent del proceso operativo.
- `database/migrations`: esquema normalizado del sistema.
- `database/seeders`: datos base para probar el MVP.
- `resources/views`: pantallas Blade.
- `scripts`: operaciones basicas para setup, backup, restore y deploy.

## Variables importantes

No se deben commitear credenciales reales. Usa `.env` para base de datos, correo y usuario administrador. El archivo `.env.example` solo contiene valores de desarrollo.

## Carga de eventos Excel

Los archivos `2026.xlsx` y `2027.xlsx` fueron leidos desde calendario visual, celda por celda. El corte aplicado es desde `2026-07-01` en adelante.

Resultado importado:

- 986 celdas/eventos en total.
- 942 registros de julio a diciembre 2026.
- 44 registros de 2027.
- Cada registro conserva el texto original de la celda en `imported_calendar_events.raw_text`.
- Tambien se crea un `academic_event` y una `event_session` por celda para que aparezcan en el calendario operativo.

Archivos relevantes:

- `database/seeders/data/calendar_events_july_2026_onward.json`
- `database/seeders/ImportedCalendarEventSeeder.php`
- `database/supabase/002_seed_calendar_events_july_2026_onward.sql`

## Supabase

Supabase esta bien para este sistema porque usa PostgreSQL y permite tener una sola base central para calendario, eventos, trazabilidad y reportes.

Cuando tengas el proyecto de Supabase listo:

1. Copia `.env.supabase.example` a `.env`.
2. Completa `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` y `APP_KEY`.
3. Ejecuta:

```bash
php artisan migrate --seed
```

Si prefieres usar el SQL Editor de Supabase, ejecuta los archivos en este orden:

```text
database/supabase/001_schema_and_base_catalogs.sql
database/supabase/002_seed_calendar_events_july_2026_onward.sql
```

El archivo `002` solo carga datos. Si se ejecuta antes del `001`, Supabase mostrara errores como `relation "users" does not exist`.
