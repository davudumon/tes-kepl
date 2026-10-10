# =====================================================================
# TAHAP 1: BUILD STAGE (Composer & Dependencies)
# =====================================================================
FROM php:8.3-fpm-alpine AS build

# Install dependensi sistem & ekstensi PHP yang dibutuhkan untuk build
RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" bcmath intl opcache pcntl pdo_mysql zip \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/pear

# Copy Composer binary dari image resmi
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Salin file definisi dependensi terlebih dahulu (Cache Optimization)
COPY composer.json composer.lock ./

# Install dependensi tanpa autoloader dulu
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# Salin seluruh kode aplikasi
COPY . .

# Generate optimized autoloader & package discovery di tahap build
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi


# =====================================================================
# TAHAP 2: RUNTIME STAGE (Production Image)
# =====================================================================
FROM php:8.3-fpm-alpine AS runtime

# Install hanya ekstensi PHP runtime yang dibutuhkan
RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" bcmath intl opcache pcntl pdo_mysql zip \
    && docker-php-ext-enable opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/pear

WORKDIR /var/www/html

# Salin folder vendor DAN kode aplikasi yang sudah diproses dari stage `build`
COPY --from=build /var/www/html /var/www/html

# Siapkan folder storage, bootstrap/cache, serta atur permission
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

# HEALTHCHECK
HEALTHCHECK --interval=10s --timeout=5s --start-period=15s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:8000/up') ? 0 : 1);"

# KEAMANAN: Set User ke www-data TEPAT sebelum EXPOSE & CMD
USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]