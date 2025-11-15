# Use a common base image with Nginx and PHP 8.2-FPM
# You can change 8.2 to 8.1 or 8.3 if your project needs it
FROM richarvey/nginx-php-fpm:php8.2

# ---- This is the most important step for MongoDB ----
# Install the PECL extension for MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb
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