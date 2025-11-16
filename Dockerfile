# Multi-stage Dockerfile for Laravel on Render

# Stage 1: Build frontend assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node.js dependencies
RUN npm ci

# Copy frontend source files
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build assets
RUN npm run build

# Stage 2: PHP application
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure PHP for production
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/uploads.ini

# Configure OPcache for production
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy built assets from node-builder stage
COPY --from=node-builder /app/public/build ./public/build

# Install PHP dependencies (production only)
# Try install first, if lock file is incompatible, update it then install
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts 2>&1 || \
    (echo "Lock file incompatible, updating..." && \
     composer update --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts && \
     composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts)

# Run composer scripts (package discovery, etc.)
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi || true

# Create storage directories if they don't exist and set proper permissions
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage/logs

# Configure Apache to use public directory
RUN echo '<VirtualHost *:8080>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Update ports.conf to listen on 8080 by default
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Create a startup script that handles PORT and runs migrations
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Get PORT from environment or use default 8080\n\
PORT=${PORT:-8080}\n\
\n\
# Ensure storage directories exist and have correct permissions\n\
# This must run as root (which it does by default in Docker)\n\
mkdir -p /var/www/html/storage/framework/sessions\n\
mkdir -p /var/www/html/storage/framework/views\n\
mkdir -p /var/www/html/storage/framework/cache\n\
mkdir -p /var/www/html/storage/logs\n\
mkdir -p /var/www/html/bootstrap/cache\n\
\n\
# Fix permissions (important for runtime - must be done as root)\n\
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true\n\
chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true\n\
chmod -R 775 /var/www/html/storage 2>/dev/null || true\n\
chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true\n\
chmod -R 777 /var/www/html/storage/logs 2>/dev/null || true\n\
\n\
# Update Apache ports.conf to listen on the specified PORT\n\
sed -i "s/Listen 8080/Listen $PORT/" /etc/apache2/ports.conf\n\
sed -i "s/:8080/:$PORT/" /etc/apache2/sites-available/000-default.conf\n\
\n\
# Run migrations (with || true to not fail if migrations already ran)\n\
php artisan migrate --force || true\n\
\n\
# Cache configuration and routes for production\n\
php artisan config:cache || true\n\
php artisan route:cache || true\n\
php artisan view:cache || true\n\
\n\
# Start Apache (this will run as www-data)\n\
exec apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Expose port (Render will override with PORT env var)
EXPOSE 8080

# Use the startup script
CMD ["/usr/local/bin/start.sh"]
