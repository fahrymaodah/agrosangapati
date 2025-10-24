.PHONY: help install up down restart logs shell composer artisan migrate fresh test

help: ## Menampilkan bantuan
	@echo "Perintah yang tersedia:"
	@echo "  make install   - Install Laravel project"
	@echo "  make up        - Start semua container"
	@echo "  make down      - Stop semua container"
	@echo "  make restart   - Restart semua container"
	@echo "  make logs      - Lihat logs container"
	@echo "  make shell     - Masuk ke PHP container"
	@echo "  make composer  - Jalankan composer (contoh: make composer CMD='install')"
	@echo "  make artisan   - Jalankan artisan (contoh: make artisan CMD='migrate')"
	@echo "  make migrate   - Jalankan database migration"
	@echo "  make fresh     - Fresh database migration"
	@echo "  make test      - Jalankan test"

install: ## Install Laravel project baru
	docker-compose run --rm php composer create-project laravel/laravel .
	cp src/.env.example src/.env || true
	docker-compose run --rm php php artisan key:generate
	sudo chmod -R 777 src/storage src/bootstrap/cache || true
	@echo "\n✅ Laravel berhasil diinstall!"
	@echo "Jalankan 'make up' untuk memulai container"

up: ## Start semua container
	docker-compose up -d
	@echo "\n✅ Container sedang berjalan!"
	@echo "Akses: http://agrosangapati.local"
	@echo "PhpMyAdmin: http://localhost:8080"

down: ## Stop semua container
	docker-compose down

restart: ## Restart semua container
	docker-compose restart

logs: ## Lihat logs
	docker-compose logs -f

shell: ## Masuk ke PHP container
	docker-compose exec php bash

composer: ## Jalankan composer command
	docker-compose exec php composer $(CMD)

artisan: ## Jalankan artisan command
	docker-compose exec php php artisan $(CMD)

migrate: ## Jalankan database migration
	docker-compose exec php php artisan migrate

fresh: ## Fresh database migration dengan seeder
	docker-compose exec php php artisan migrate:fresh --seed

test: ## Jalankan PHPUnit tests
	docker-compose exec php php artisan test
