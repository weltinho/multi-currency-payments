# Laravel API — PHP 8.4-FPM image (matches Symfony 8.1 in composer.lock)
#
# Used by both the `backend` and `scheduler` services.
# PHP-FPM listens on port 9000; Nginx forwards requests via FastCGI.
#
# Build context is the repo root (see docker-compose.yml).

FROM php:8.4-fpm-bookworm

# System packages and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer — used by entrypoint.sh to install vendor/ on first start
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Entrypoint: bootstrap (composer, migrate, seed) when APP_BOOTSTRAP=true; otherwise waits for vendor/
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
