#!/usr/bin/env bash
# Build script for Render deployment

set -o errexit

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Creating storage link..."
php artisan storage:link || true

echo "Running migrations..."
php artisan migrate --force || echo "Migration failed or already run"

echo "Build complete!"

