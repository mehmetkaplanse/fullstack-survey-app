FROM php:8.3-fpm

# Bağımlılıklar
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libonig-dev libxml2-dev zip libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction

# Storage izinleri
RUN chmod -R 775 storage bootstrap/cache

# Port
EXPOSE 8080

# Container başlarken çalışacak komut
CMD php artisan config:clear && \
    php artisan optimize:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=8080
