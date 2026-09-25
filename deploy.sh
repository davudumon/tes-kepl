#!/bin/bash
set -e # Script akan langsung berhenti jika ada perintah yang error

echo "Deploying application..."

# 1. Maintenance mode
php artisan down || true

# 2. Tarik kode terbaru
git pull origin main

# 3. Install/update dependensi
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 4. Jalankan migrasi
php artisan migrate --force

# 5. Clear Cache
php artisan optimize:clear

# 6. Restart queue (jika pakai supervisor/queue)
php artisan queue:restart

# 7. Matikan maintenance mode
php artisan up

echo "Deployment finished!"