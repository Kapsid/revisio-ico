# Registry Service - Makefile
# Convenience commands for development

.PHONY: help build up down restart logs shell migrate seed test fresh

# Default target
help:
	@echo "Registry Service - Available commands:"
	@echo ""
	@echo "  make build    - Build Docker containers"
	@echo "  make up       - Start all containers"
	@echo "  make down     - Stop all containers"
	@echo "  make restart  - Restart all containers"
	@echo "  make logs     - Show container logs"
	@echo "  make shell    - Open PHP container shell"
	@echo "  make migrate  - Run database migrations"
	@echo "  make seed     - Seed the database"
	@echo "  make test     - Run tests"
	@echo "  make fresh    - Fresh install (migrate + seed)"
	@echo "  make install  - Install composer dependencies"
	@echo ""

# Docker commands
build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

restart: down up

logs:
	docker-compose logs -f

shell:
	docker-compose exec php sh

# Laravel commands
install:
	docker-compose exec php composer install

migrate:
	docker-compose exec php php artisan migrate

seed:
	docker-compose exec php php artisan db:seed

test:
	docker-compose exec php php artisan test

fresh:
	docker-compose exec php php artisan migrate:fresh --seed

# Generate application key
key:
	docker-compose exec php php artisan key:generate

# Full setup
setup: build up install key migrate seed
	@echo "Setup complete! Access the API at http://localhost:8080"
