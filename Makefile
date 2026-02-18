.PHONY: setup up down fresh test lint lint-fix queue logs shell verify build

# ============================================================
# One-command setup for evaluators
# ============================================================
setup:
	@echo "Setting up Coldplay Mumbai..."
	cp .env.docker .env
	docker compose build
	docker compose up -d
	@echo "Installing dependencies..."
	docker compose exec app composer install --no-interaction
	docker compose exec app npm ci
	docker compose exec app npm run build
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	@echo ""
	@echo "Ready at http://localhost:8080"

# ============================================================
# Pre-commit verification — run before every commit
# ============================================================
verify:
	@echo "Verifying Docker build..."
	cp .env .env.local.bak
	cp .env.docker .env
	docker compose down
	docker compose up -d --build
	@echo "Installing dependencies..."
	docker compose exec app composer install --no-interaction
	docker compose exec app npm ci
	docker compose exec app npm run build
	docker compose exec app php artisan migrate --force
	@echo "Running tests..."
	docker compose exec app php artisan test --compact
	@echo "Checking code style..."
	docker compose exec app ./vendor/bin/pint --test
	@echo "Restoring local .env..."
	cp .env.local.bak .env
	rm .env.local.bak
	@echo ""
	@echo "All checks passed. Safe to commit."

# ============================================================
# Day-to-day commands
# ============================================================
up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose exec app npm run build

fresh:
	docker compose down -v
	docker compose build --no-cache
	$(MAKE) setup

pest:
	docker compose exec app php artisan test --compact

pint:
	docker compose exec app ./vendor/bin/pint --test

pint-fix:
	docker compose exec app ./vendor/bin/pint

queue:
	docker compose logs -f queue

logs:
	docker compose logs -f

shell:
	docker compose exec app bash

artisan:
	docker compose exec app php artisan $(cmd)
