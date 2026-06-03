FROM php:8.3-cli-alpine

RUN apk add --no-cache git unzip libzip-dev libpng-dev oniguruma-dev && \
    docker-php-ext-install pdo pdo_sqlite zip bcmath mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction && \
    touch database/database.sqlite && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
