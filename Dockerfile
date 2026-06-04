FROM php:8.3-cli-alpine

RUN apk add --no-cache git unzip sqlite-dev libzip-dev && \
    docker-php-ext-install pdo pdo_sqlite zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction && \
    touch database/database.sqlite && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
