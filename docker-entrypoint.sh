#!/bin/sh
set -e

# Cache Laravel configuration
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Create storage link
php artisan storage:link || true

# Start PHP built-in server using PORT from environment (default to 8000)
# Use php -S instead of artisan serve for better compatibility
PORT=${PORT:-8000}
exec php -S 0.0.0.0:$PORT -t public

