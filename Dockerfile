FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache bash icu-dev libpq-dev libzip-dev oniguruma-dev zip \
    && docker-php-ext-install bcmath intl opcache pdo_pgsql zip

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]
