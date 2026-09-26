FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache bash icu-dev libpq-dev libzip-dev oniguruma-dev zip \
    && docker-php-ext-install bcmath intl opcache pdo_pgsql zip

WORKDIR /var/www/html

# OPcache ringan (Keep All) — dev revalidate tiap request, prod bisa override via env
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Cache config/route/view untuk prod (diabaikan jika APP_ENV != production saat runtime)
RUN if [ "$APP_ENV" = "production" ]; then php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache; fi || true

USER www-data

CMD ["sh", "-c", "php artisan migrate --force --quiet; php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]