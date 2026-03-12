#!/bin/sh
set -e

echo "==> Gerando APP_KEY se não existir..."
php artisan key:generate --no-interaction --force 2>/dev/null || true

echo "==> Rodando migrations..."
php artisan migrate --force --no-interaction

echo "==> Otimizando a aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Criando link de storage..."
php artisan storage:link 2>/dev/null || true

echo "==> Iniciando serviços (nginx + php-fpm + queue)..."
exec supervisord -c /etc/supervisord.conf

