FROM php:8.3-fpm-alpine

# --- System deps ---
RUN apk add --no-cache bash git unzip icu-data-full icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql intl mbstring zip opcache

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
RUN mkdir -p storage bootstrap/cache
