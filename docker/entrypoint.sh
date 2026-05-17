#!/bin/sh
set -e

cd /var/www/html

# Laravel exige .env presente mesmo quando usa variáveis de ambiente Docker
[ -f .env ] || touch .env

# Gera APP_KEY automaticamente se não definida
if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(openssl rand -base64 32)"
    echo "APP_KEY=${APP_KEY}" >> .env
    export APP_KEY
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

# Aguarda o DB estar pronto
attempt=0
until php artisan db:show --json > /dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ $attempt -ge 20 ]; then
        echo "DB nao respondeu apos 20 tentativas. Abortando."
        exit 1
    fi
    echo "Aguardando banco de dados... tentativa $attempt/20"
    sleep 3
done

# Roda migrations — "table already exists" é inofensivo num restart, outros erros abortam
migrate_out=$(php artisan migrate --force --no-interaction 2>&1) || {
    if printf '%s' "$migrate_out" | grep -qi "already exists"; then
        echo "WARNING: Algumas tabelas ja existem, continuando..."
    else
        printf '%s\n' "$migrate_out"
        echo "FATAL: Migration falhou."
        exit 1
    fi
}
printf '%s\n' "$migrate_out"

exec "$@"
