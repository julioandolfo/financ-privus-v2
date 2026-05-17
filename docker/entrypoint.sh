#!/bin/sh
set -e

cd /var/www/html

# Laravel exige .env presente mesmo quando usa variáveis de ambiente Docker
[ -f .env ] || touch .env

# Gera APP_KEY automaticamente se não definida
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction
fi

# Storage link (idempotente)
php artisan storage:link --force 2>/dev/null || true

# Cache em produção
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache  || true
    php artisan route:cache   || true
    php artisan view:cache    || true
    php artisan event:cache   || true
fi

# Migrations (aguarda o DB estar pronto)
attempt=0
until php artisan migrate --force --no-interaction 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge 10 ]; then
        echo "DB nao respondeu apos 10 tentativas. Abortando."
        exit 1
    fi
    echo "Aguardando banco de dados... tentativa $attempt/10"
    sleep 3
done

exec "$@"
