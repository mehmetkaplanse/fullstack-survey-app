FROM php:8.3-fpm

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer kur
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction
RUN php artisan key:generate

# Laravel storage link
RUN php artisan storage:link || true

# Port ayarı
EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=8080
