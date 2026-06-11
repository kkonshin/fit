SHELL := /bin/bash

.ONESHELL: ;
.NOTPARALLEL: ;
default: help;

MAKEFLAGS += --no-print-directory

FRMT_NORM=\033[0m
FRMT_INVRS=\033[7m
BRANCH=$(shell git rev-parse --abbrev-ref HEAD)

.PHONY: help
help: ## Информация о доступных командах
	@egrep -h '\s##\s' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: install
install: ## Установка проекта
	@echo -e "${FRMT_INVRS} Установка проекта ${FRMT_NORM}"
	touch .npmrc
	@cp -n .env.example .env || true
	@while [ -z "$$CONTINUE" ]; do \
		read -r -p "Вы настроили файл .env? [y/N]: " CONTINUE; \
	done; [ $$CONTINUE = "y" ] || [ $$CONTINUE = "Y" ] || (echo "Отменено!"; exit 1;)
	make metrics_network
	docker compose run --rm nodejs npm ci && make frontend-build
	make restore
	make build && docker-compose up -d --wait


.PHONY: up
up: ## Запустить сервер
	@echo -e "${FRMT_INVRS} Запуск сервера... ${FRMT_NORM}"
	docker compose up -d


.PHONY: down
down: ## Остановить сервер
	@echo -e "${FRMT_INVRS} Остановка сервера... ${FRMT_NORM}"
	docker compose down


.PHONY: console
console: ## Открыть консоль сервера
	@echo -e "${FRMT_INVRS} Открытие консоли сервера... ${FRMT_NORM}"
	docker compose exec app bash


.PHONY: build
build: ## Собрать проект
	@echo -e "${FRMT_INVRS} Сборка проекта... ${FRMT_NORM}"
	docker compose build


.PHONY: update
update: ## Обновить проект
	@echo -e "${FRMT_INVRS} Обновление проекта... ${FRMT_NORM}"
	git pull
	make build
	make frontend-build
	make up
	rm -fv ./bootstrap/cache/*.php
	docker compose exec app bash -c 'composer install'
	docker compose exec app bash -c 'php artisan optimize:clear'
	docker compose exec app bash -c 'php artisan migrate'
	docker compose exec app bash -c 'php artisan queue:restart'


.PHONY: deploy
deploy: ## Деплой проекта
	@echo -e "${FRMT_INVRS} Деплой проекта... ${FRMT_NORM}"
	git pull
	make env
	make build
	make frontend-build
	make up
	rm -fv ./bootstrap/cache/*.php
	docker compose exec app bash -c 'composer install --no-interaction --no-dev'
	docker compose exec app bash -c 'php artisan optimize:clear'
	docker compose exec app bash -c 'php artisan migrate --force'
	docker compose exec app bash -c 'php artisan optimize'
	docker compose exec app bash -c 'php artisan queue:restart'

.PHONY: test
test: ## Протестировать проект
	@echo -e "${FRMT_INVRS} Тестирование проекта... ${FRMT_NORM}"
	docker compose exec app php artisan test

.PHONY: frontend-watch
frontend-watch: ## Сборка frontend для локальной разработки
	docker compose run --rm nodejs bash -c 'npx mix watch'

.PHONY: frontend-build
frontend-build: ## Сборка frontend
	docker compose run --rm nodejs bash -c 'npx mix build'

.PHONY: rector-dry
rector-dry: ## План рефакторинга без внесения изменений в код
	docker compose exec app vendor/bin/rector process --dry-run --clear-cache

.PHONY: rector
rector: ## Рефакторинг с изменениями в коде
	docker compose exec app vendor/bin/rector process --clear-cache

.PHONY: stan
stan: ## Запуск статического анализатора
	docker compose exec app vendor/bin/phpstan clear-result-cache \
	&& vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=3G | tee phpstan.log

.PHONY: coverage
coverage: ## Статистика покрытия тестами
	docker compose exec -e XDEBUG_MODE=coverage app php artisan test --coverage
