# Production Setup Script for Laravel
# This script configures Laravel for production mode on localhost:8000

Write-Host "🚀 Setting up Laravel for Production Mode..." -ForegroundColor Green
Write-Host "⚠️  This will enable production optimizations and disable debug mode" -ForegroundColor Yellow

# Navigate to backend directory
Set-Location $PSScriptRoot

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "❌ .env file not found. Creating from .env.example..." -ForegroundColor Yellow
    if (Test-Path .env.example) {
        Copy-Item .env.example .env
    } else {
        Write-Host "❌ .env.example not found. Please create .env manually." -ForegroundColor Red
        exit 1
    }
}

# Update .env for production
Write-Host "📝 Configuring .env for production mode..." -ForegroundColor Cyan

# Read .env content
$envContent = Get-Content .env -Raw

# Update APP_ENV to production
$envContent = $envContent -replace 'APP_ENV=.*', 'APP_ENV=production'
if ($envContent -notmatch 'APP_ENV=') {
    $envContent += "`nAPP_ENV=production"
}

# Update APP_DEBUG to false
$envContent = $envContent -replace 'APP_DEBUG=.*', 'APP_DEBUG=false'
if ($envContent -notmatch 'APP_DEBUG=') {
    $envContent += "`nAPP_DEBUG=false"
}

# Update APP_URL to localhost:8000
$envContent = $envContent -replace 'APP_URL=.*', 'APP_URL=http://localhost:8000'
if ($envContent -notmatch 'APP_URL=') {
    $envContent += "`nAPP_URL=http://localhost:8000"
}

# Ensure BROADCAST_DRIVER is set (default to log for localhost)
if ($envContent -notmatch 'BROADCAST_DRIVER=') {
    $envContent += "`nBROADCAST_DRIVER=log"
}

# Save updated .env
$envContent | Set-Content .env -NoNewline

Write-Host "✅ .env configured for production" -ForegroundColor Green

# Generate application key if not set
Write-Host "🔑 Checking application key..." -ForegroundColor Cyan
php artisan key:generate --force

# Clear and cache configuration
Write-Host "⚙️  Optimizing configuration..." -ForegroundColor Cyan
php artisan config:clear
php artisan config:cache

# Clear and cache routes
Write-Host "🛣️  Optimizing routes..." -ForegroundColor Cyan
php artisan route:clear
php artisan route:cache

# Clear and cache views
Write-Host "👁️  Optimizing views..." -ForegroundColor Cyan
php artisan view:clear
php artisan view:cache

# Clear and cache events
Write-Host "📡 Optimizing events..." -ForegroundColor Cyan
php artisan event:clear
php artisan event:cache

# Optimize autoloader (keep dev dependencies for now)
Write-Host "📦 Optimizing autoloader..." -ForegroundColor Cyan
composer dump-autoload --optimize

# Create storage link if not exists
Write-Host "🔗 Creating storage link..." -ForegroundColor Cyan
if (-not (Test-Path public/storage)) {
    php artisan storage:link
}

# Run migrations
Write-Host "🗄️  Running migrations..." -ForegroundColor Cyan
php artisan migrate --force

# Clear all caches one more time
Write-Host "🧹 Final cache clear..." -ForegroundColor Cyan
php artisan cache:clear
php artisan optimize:clear

# Rebuild all caches
Write-Host "🔨 Rebuilding caches..." -ForegroundColor Cyan
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

Write-Host "✅ Production setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Server ready to start:" -ForegroundColor Yellow
Write-Host "   Run: php artisan serve --host=127.0.0.1 --port=8000" -ForegroundColor White
Write-Host "   Or use: .\start-production.ps1" -ForegroundColor White
Write-Host ""

