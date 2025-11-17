#!/bin/sh
set -e

# Clear any cached config first (in case of errors)
php artisan config:clear || true

# Cache Laravel configuration
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Create storage directories if they don't exist
mkdir -p storage/app/public/package_images || true
mkdir -p storage/app/public/delivery_proofs || true
mkdir -p storage/app/public/cod_proofs || true

# Create storage link (symlink from public/storage to storage/app/public)
php artisan storage:link || true

# Verify symlink exists
if [ ! -L public/storage ]; then
    echo "Warning: Storage symlink not created. Attempting manual creation..."
    ln -sf ../storage/app/public public/storage || true
fi

# Clear database if CLEAR_DB environment variable is set to "yes"
if [ "$CLEAR_DB" = "yes" ]; then
    echo "⚠️  CLEAR_DB=yes detected. Clearing database..."
    php artisan db:clear --force --reseed || true
    echo "Database cleared and re-seeded."
fi

# Run database migrations (safe on startup in Render free plan)
echo "Running database migrations..."
php artisan migrate --force || true
echo "Migrations completed."

# Seed database with initial users (super admin, etc.)
# Only seed if database was NOT just cleared (to avoid duplicate seeding)
if [ "$CLEAR_DB" != "yes" ]; then
    echo "Seeding database with initial users..."
    php artisan db:seed --class=OfficeUserSeeder --force || true
    echo "Database seeding completed."
fi

# Start PHP built-in server using PORT from environment (default to 8000)
# Use php -S instead of artisan serve for better compatibility
PORT=${PORT:-8000}
exec php -S 0.0.0.0:$PORT -t public

