#!/bin/sh
# Don't use set -e, handle errors manually to prevent restart loops
# set -e

# Clear any cached config first (in case of errors)
echo "Clearing cached configuration..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Cache Laravel configuration (with error handling)
echo "Caching Laravel configuration..."
php artisan config:cache || {
    echo "ERROR: Failed to cache config. Continuing anyway..."
    php artisan config:clear || true
}
php artisan route:cache || {
    echo "WARNING: Failed to cache routes. Continuing anyway..."
}
php artisan view:cache || {
    echo "WARNING: Failed to cache views. Continuing anyway..."
}

# Create storage directories if they don't exist
# Note: package_images are now stored in Supabase, but keeping delivery_proofs and cod_proofs local for now
mkdir -p storage/app/public/delivery_proofs || true
mkdir -p storage/app/public/cod_proofs || true

# Create storage link (symlink from public/storage to storage/app/public)
php artisan storage:link || true

# Verify symlink exists
if [ ! -L public/storage ]; then
    echo "Warning: Storage symlink not created. Attempting manual creation..."
    ln -sf ../storage/app/public public/storage || true
fi

# Run database migrations (safe on startup in Render free plan)
echo "Running database migrations..."
php artisan migrate --force || {
    echo "WARNING: Migrations failed. This might be normal if already migrated."
}

# Seed database with initial users (super admin, etc.)
echo "Seeding database with initial users..."
php artisan db:seed --class=OfficeUserSeeder --force || {
    echo "WARNING: Seeding failed. This might be normal if already seeded."
}

# Start PHP built-in server using PORT from environment (default to 8000)
# Use php -S instead of artisan serve for better compatibility
PORT=${PORT:-8000}
echo "Starting PHP server on port $PORT..."
echo "Server is ready!"

# Use exec to replace shell process with PHP server
# This ensures proper signal handling and prevents restart loops
exec php -S 0.0.0.0:$PORT -t public

