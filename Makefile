up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

bash:
	docker compose exec app bash

migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app php artisan test

queue:
	docker compose exec app php artisan queue:work

cache-clear:
	docker compose exec app php artisan optimize:clear

backup:
	sh scripts/backup.sh
