# REBA Lanzamientos

Sistema interno para planificar, calcular, validar y aprobar lanzamientos academicos de Rebagliati Diplomados.

## Que incluye el MVP

- Login interno con usuario administrador inicial.
- Catalogos base: especialidades, publicos, tipos de evento, modalidades, certificacion, aulas, Zoom y docentes.
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
