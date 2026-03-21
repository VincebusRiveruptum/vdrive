dev-up:
	@docker compose -f docker-compose.dev.yml up -d --build --force-recreate
dev-down:
	@docker compose -f docker-compose.dev.yml down
dev-migrate:
	@docker exec -i vdrive-app php artisan migrate
dev-start:
	@docker exec -i vdrive-app php artisan migrate --seed
dev-tinker:
	@docker exec -it vdrive-app php artisan tinker

prod-up :
	@docker compose -f docker-compose.prod.yml up -d --build --force-recreate
prod-down:
	@docker compose -f docker-compose.prod.yml down

prod-migrate:
	@docker exec -i vdrive-prod php artisan migrate
prod-start:
	@docker exec -i vdrive-prod php artisan migrate --seed
prod-tinker:
	@docker exec -it vdrive-prod php artisan tinker
