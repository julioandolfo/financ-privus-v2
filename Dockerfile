# ─── Stage 1: Node (build frontend assets) ────────────────────────────────────
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --prefer-offline

COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/

RUN npm run build

# ─── Stage 2: Composer (install PHP deps) ─────────────────────────────────────
FROM composer:2 AS composer-builder

WORKDIR /app

COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    && composer dump-autoload --optimize

# ─── Stage 3: Final production image ──────────────────────────────────────────
FROM php:8.4-fpm-alpine AS production

# System deps
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        pcntl

WORKDIR /var/www/html

# Copy built assets from previous stages
COPY --from=composer-builder /app /var/www/html
COPY --from=node-builder /app/public/build /var/www/html/public/build

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor (manages nginx + php-fpm)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
