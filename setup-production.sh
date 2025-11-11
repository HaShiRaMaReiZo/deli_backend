#!/bin/bash

# Production Setup Script for Laravel
# This script configures Laravel for production mode on localhost:8000

echo "🚀 Setting up Laravel for Production Mode..."

# Navigate to backend directory
cd "$(dirname "$0")"

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found. Creating from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "❌ .env.example not found. Please create .env manually."
        exit 1
    fi
fi

# Update .env for production
echo "📝 Configuring .env for production mode..."

# Update APP_ENV to production
sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/' .env
grep -q "^APP_ENV=" .env || echo "APP_ENV=production" >> .env

# Update APP_DEBUG to false
sed -i.bak 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
grep -q "^APP_DEBUG=" .env || echo "APP_DEBUG=false" >> .env

# Update APP_URL to localhost:8000
sed -i.bak 's|^APP_URL=.*|APP_URL=http://localhost:8000|' .env
grep -q "^APP_URL=" .env || echo "APP_URL=http://localhost:8000" >> .env

# Ensure BROADCAST_DRIVER is set
grep -q "^BROADCAST_DRIVER=" .env || echo "BROADCAST_DRIVER=log" >> .env

echo "✅ .env configured for production"

# Generate application key if not set
echo "🔑 Checking application key..."
php artisan key:generate --force

# Clear and cache configuration
echo "⚙️  Optimizing configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
echo "🛣️  Optimizing routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
echo "👁️  Optimizing views..."
php artisan view:clear
php artisan view:cache

# Clear and cache events
echo "📡 Optimizing events..."
php artisan event:clear
php artisan event:cache

# Optimize autoloader
echo "📦 Optimizing autoloader..."
composer install --optimize-autoloader --no-dev

# Create storage link if not exists
echo "🔗 Creating storage link..."
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "✅ Production setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Run migrations: php artisan migrate --force"
echo "   2. Start server: php artisan serve --host=127.0.0.1 --port=8000"
echo ""

