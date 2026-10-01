# 1. Base Image PHP FPM Alpine
FROM php:8.3-fpm-alpine

# 2. Install dependensi sistem & ekstensi PHP
RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" bcmath intl opcache pcntl pdo_mysql zip \
    && docker-php-ext-enable opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/pear

# 3. Copy Composer binary
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 4. SALIN COMPOSER DULUAN (Docker Cache)
COPY composer.json composer.lock ./

# 5. INSTALL DEPENDENSI (Gunakan --no-autoloader agar tidak mencari class source code yang belum di-COPY)
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# 6. SALIN KODE APLIKASI
COPY . .

# 7. GENERATE AUTOLOAD & ARTISAN DISCOVER
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]