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

# Disable post-install scripts to avoid artisan errors
RUN composer install --no-scripts --no-dev --optimize-autoloader --ignore-platform-reqs

# Expose port 10000 for Render
EXPOSE 10000

# Start Laravel app
CMD php artisan key:generate && php artisan serve --host=0.0.0.0 --port=10000
