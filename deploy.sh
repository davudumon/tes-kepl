#!/usr/bin/env bash
set -e

cd /var/www/evolusi-pl-540567
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize
php artisan up
