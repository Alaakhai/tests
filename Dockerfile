# Dockerfile for Laravel app on Render

FROM php:8.2-fpm

# Install system dependencies + libzip
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Start Laravel
CMD php artisan key:generate --force \
 && php artisan config:clear \
 && php artisan serve --host=0.0.0.0 --port=10000

EXPOSE 10000
