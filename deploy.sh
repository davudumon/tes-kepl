#!/bin/bash
set -e

# deploy.sh – Langkah deploy production (7 tahap)
# Setiap langkah ditulis sebagai echo untuk simulasi.

echo "===== DEPLOY PRODUCTION ====="

echo "[1/7] Pulling latest code from repository..."
# git pull origin main

echo "[2/7] Installing/updating dependencies (composer install --no-dev)..."
# composer install --no-dev --optimize-autoloader

echo "[3/7] Running database migrations..."
# php artisan migrate --force

echo "[4/7] Caching configuration, routes, and views..."
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

echo "[5/7] Installing and building frontend assets..."
# npm ci && npm run build

echo "[6/7] Restarting queue workers..."
# php artisan queue:restart

echo "[7/7] Reloading web server..."
# sudo systemctl reload nginx

echo "===== DEPLOY SELESAI ====="
