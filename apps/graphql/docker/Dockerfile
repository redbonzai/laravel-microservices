# Use the official PHP 8.3 FPM image as the base
FROM php:8.3-fpm

# Install system dependencies for Laravel and common PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Avoid running Composer as root to mitigate risks
RUN chown -R www-data:www-data /var/www

# Switch to www-data user to avoid permissions issues
USER www-data

# Copy existing application directory contents to the container
COPY --chown=www-data:www-data . /var/www

RUN composer require rebing/graphql-laravel

# Install Composer dependencies
RUN composer install --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts

# Switch back to root to configure system-wide settings
USER root

# Install Supervisor for managing multiple processes inside the container
RUN apt-get update && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /var/log/supervisor

# Copy Supervisor configuration files
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# Copy and configure the PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
