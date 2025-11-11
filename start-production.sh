#!/bin/bash

# Start Laravel Production Server on localhost:8000

echo "🚀 Starting Laravel Production Server..."
echo "📍 Server will run on: http://localhost:8000"
echo ""

# Navigate to backend directory
cd "$(dirname "$0")"

# Check if optimized
if [ ! -f bootstrap/cache/config.php ]; then
    echo "⚠️  Configuration not optimized. Running setup..."
    ./setup-production.sh
fi

# Start server
echo "🌐 Starting server on http://127.0.0.1:8000..."
php artisan serve --host=127.0.0.1 --port=8000

