SHELL := /bin/bash
.DEFAULT_GOAL := help
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c

PHP        ?= php
COMPOSER   ?= composer
NPM        ?= npm
ARTISAN    := $(PHP) artisan
SQLITE_DB  := database/database.sqlite

.PHONY: help install setup env key db migrate migrate-fresh seed rollback \
        dev serve queue logs vite build build-ssr \
        test test-filter test-coverage \
        lint lint-check format format-check types-check \
        pint pint-check \
        ci optimize cache-clear route-list tinker storage-link \
        update upgrade fresh-install clean reset \
        deploy \
        repomix

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage: make \033[36m<target>\033[0m\n\nTargets:\n"} \
	     /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2 } \
	     /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) }' $(MAKEFILE_LIST)

##@ Setup

install: env key db migrate ## Поставить зависимости PHP + Node, инициализировать .env, ключ, БД, миграции
	$(COMPOSER) install --no-interaction --prefer-dist
	$(NPM) ci

setup: install build ## Полная установка с production-сборкой ассетов

env: ## Создать .env из .env.example при отсутствии
	@test -f .env || cp .env.example .env

key: ## Сгенерировать APP_KEY если пустой
	@grep -q '^APP_KEY=base64:' .env || $(ARTISAN) key:generate

db: ## Создать sqlite-файл если отсутствует
	@test -f $(SQLITE_DB) || (mkdir -p $(dir $(SQLITE_DB)) && touch $(SQLITE_DB))

storage-link: ## Слинковать storage в public
	$(ARTISAN) storage:link

##@ Database

migrate: ## Прогнать миграции
	$(ARTISAN) migrate --graceful

migrate-fresh: ## Сбросить и пересоздать БД
	$(ARTISAN) migrate:fresh

seed: ## Запустить сидеры
	$(ARTISAN) db:seed

rollback: ## Откатить последнюю миграцию
	$(ARTISAN) migrate:rollback

##@ Development

dev: ## Запустить server + queue + logs + vite одной командой
	$(COMPOSER) run dev

serve: ## Только PHP-сервер
	$(ARTISAN) serve

queue: ## Воркер очередей
	$(ARTISAN) queue:listen --tries=1 --timeout=0

logs: ## Стрим логов через pail
	$(ARTISAN) pail --timeout=0

vite: ## Vite dev-сервер
	$(NPM) run dev

##@ Build

build: ## Собрать фронт для production
	$(NPM) run build

build-ssr: ## Собрать SSR-бандл
	$(NPM) run build:ssr

##@ Tests

test: ## Запустить тесты
	$(ARTISAN) config:clear
	$(ARTISAN) test

test-filter: ## make test-filter name=Foo — отфильтровать тесты
	$(ARTISAN) test --filter=$(name)

test-coverage: ## Тесты с покрытием
	$(ARTISAN) test --coverage

##@ Quality

lint: pint format ## Прогнать все автофиксы (PHP + JS/TS + Prettier)
	$(NPM) run lint

lint-check: pint-check ## Только проверка без правок
	$(NPM) run lint:check
	$(NPM) run format:check
	$(NPM) run types:check

format: ## Prettier для resources/
	$(NPM) run format

format-check: ## Проверка форматирования
	$(NPM) run format:check

types-check: ## TypeScript проверка типов
	$(NPM) run types:check

pint: ## Laravel Pint автоформат
	$(COMPOSER) run lint

pint-check: ## Pint в режиме проверки
	$(COMPOSER) run lint:check

ci: lint-check test ## Полный CI-пайплайн локально

##@ Maintenance

optimize: ## Прогрев кэша конфига/роутов/вьюх
	$(ARTISAN) optimize

cache-clear: ## Сбросить все кэши Laravel
	$(ARTISAN) optimize:clear

route-list: ## Показать все маршруты
	$(ARTISAN) route:list

##@ Deploy

deploy: ## Деплой на VPS (rsync + composer + migrate + cache)
	./bin/deploy.sh

tinker: ## Интерактивный REPL
	$(ARTISAN) tinker

update: ## Обновить зависимости (composer + npm)
	$(COMPOSER) update
	$(NPM) update

upgrade: ## Композер upgrade с разрешением конфликтов
	$(COMPOSER) update -W

fresh-install: clean install ## Удалить артефакты и переустановить с нуля

clean: ## Удалить vendor, node_modules, build артефакты
	rm -rf vendor node_modules public/build public/hot bootstrap/cache/*.php
	$(ARTISAN) cache:clear || true

reset: ## Полный сброс: clean + удалить .env и БД
	rm -rf vendor node_modules public/build public/hot bootstrap/cache/*.php
	rm -f .env $(SQLITE_DB)

##@ Export

REPOMIX_HEADER := Это банк вопросов для подготовки к собеседованию PHP/Laravel-разработчика middle/senior уровня. Каждый вопрос лежит в поле 'question', эталонный ответ — в поле 'answer', опционально есть 'code_example'. Обсуждай вопросы по существу, как ведущие технического подкаста для разработчиков. Не комментируй структуру файла, формат хранения, PHP-массивы, сидеры или способ упаковки — это всё технические детали хранилища, а не предмет обсуждения.

repomix: ## Перепаковать сидеры в repomix-<category>.md по 5 категориям (для NotebookLM)
	for pair in php=Php oop=Oop laravel=Laravel database=Database system-design=SystemDesign; do
	  out=$${pair%=*}
	  src=$${pair#*=}
	  echo "→ repomix-$$out.md ($$src)"
	  npx -y repomix \
	    --include "database/seeders/Data/Categories/$$src/**/*.php" \
	    --style markdown \
	    --no-file-summary \
	    --no-directory-structure \
	    --header-text "$(REPOMIX_HEADER)" \
	    -o repomix-$$out.md \
	    --no-security-check >/dev/null
	done
	ls -lh repomix-*.md
