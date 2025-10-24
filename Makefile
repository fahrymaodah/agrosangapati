.PHONY: help install up down restart logs shell composer artisan migrate fresh test

help: ## Display available commands
	@echo "Available commands:"
	@echo "  make install   - Install Laravel project"
	@echo "  make up        - Start all containers"
	@echo "  make down      - Stop all containers"
	@echo "  make restart   - Restart all containers"
	@echo "  make logs      - View container logs"
	@echo "  make shell     - Access PHP container"
	@echo "  make composer  - Run composer (example: make composer CMD='install')"
	@echo "  make artisan   - Run artisan (example: make artisan CMD='migrate')"
	@echo "  make migrate   - Run database migration"
	@echo "  make fresh     - Fresh database migration"
	@echo "  make test      - Run tests"

install: ## Install new Laravel project
	docker-compose run --rm php composer create-project laravel/laravel .
	cp src/.env.example src/.env || true
	docker-compose run --rm php php artisan key:generate
	sudo chmod -R 777 src/storage src/bootstrap/cache || true
	@echo "\n✅ Laravel installed successfully!"
	@echo "Run 'make up' to start containers"

up: ## Start all containers
	docker-compose up -d
	@echo "\n✅ Containers are running!"
	@echo "Application: http://agrosangapati.local"
	@echo "PhpMyAdmin: http://localhost:8080"

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## View logs
	docker-compose logs -f

shell: ## Access PHP container
	docker-compose exec php bash

composer: ## Run composer command
	docker-compose exec php composer $(CMD)

artisan: ## Run artisan command
	docker-compose exec php php artisan $(CMD)

migrate: ## Run database migration
	docker-compose exec php php artisan migrate

fresh: ## Fresh database migration with seeders
	docker-compose exec php php artisan migrate:fresh --seed

test: ## Run PHPUnit tests
	docker-compose exec php php artisan test
