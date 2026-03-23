#!/usr/bin/env bash

set -euo pipefail

APP_DIR="${1:-$(pwd)}"

echo "==> Deploy production in: $APP_DIR"
cd "$APP_DIR"

if [[ ! -f artisan ]]; then
  echo "Errore: artisan non trovato in $APP_DIR"
  exit 1
fi

echo "==> PHP / Laravel info"
php -v | head -n 1
php artisan --version

echo "==> Maintenance mode"
php artisan down --render="errors::minimal" || true

echo "==> Composer install"
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction
else
  echo "Attenzione: composer non trovato, salto install."
fi

echo "==> Migrations"
php artisan migrate --force

echo "==> Clear old cache"
php artisan optimize:clear

echo "==> Rebuild cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "==> Queue restart"
php artisan queue:restart || true

echo "==> Storage link"
php artisan storage:link || true

echo "==> Health checks"
php artisan about
php artisan migrate:status

echo "==> App up"
php artisan up

echo "Deploy completato."
