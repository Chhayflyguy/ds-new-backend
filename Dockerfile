# Use the "latest" tag (which is Alpine-based)
FROM richarvey/nginx-php-fpm:latest

# ---- This is the most important step for MongoDB ----
# Install build dependencies, including autoconf, compile the extension, then remove them
RUN apk update \
    && apk add --no-cache \
        build-base \
        pkgconfig \
        autoconf \
    && pecl channel-update pecl.php.net \
    && pecl install mongodb \
    && apk del build-base pkgconfig autoconf \
    && docker-php-ext-enable mongodb
# ----------------------------------------------------

# Set the working directory
WORKDIR /var/www/html

# Copy your entire Laravel project into the container
COPY . /var/www/html

# Install Composer dependencies for production
RUN composer install --no-interaction --no-dev --prefer-dist

# Set the correct file permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache