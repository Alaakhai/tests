# Dockerfile for Laravel app on Render

FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install dependencies (ignore scripts + platform requirements)
RUN composer install --no-scripts --no-dev --optimize-autoloader --ignore-platform-reqs

# Generate key and clear caches when container starts
CMD php artisan key:generate --force && php artisan config:clear && php artisan serve --host=0.0.0.0 --port=10000

# Expose port 10000 for Render
EXPOSE 10000
