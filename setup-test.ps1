# Test/Development Setup Script for Laravel
# This script configures Laravel for test/development mode on localhost:8000

Write-Host "🧪 Setting up Laravel for Test/Development Mode..." -ForegroundColor Green

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

# Update .env for test/development
Write-Host "📝 Configuring .env for test/development mode..." -ForegroundColor Cyan

# Read .env content
$envContent = Get-Content .env -Raw

# Update APP_ENV to local (for development)
$envContent = $envContent -replace 'APP_ENV=.*', 'APP_ENV=local'
if ($envContent -notmatch 'APP_ENV=') {
    $envContent += "`nAPP_ENV=local"
}

# Update APP_DEBUG to true (for development)
$envContent = $envContent -replace 'APP_DEBUG=.*', 'APP_DEBUG=true'
if ($envContent -notmatch 'APP_DEBUG=') {
    $envContent += "`nAPP_DEBUG=true"
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

Write-Host "✅ .env configured for test/development" -ForegroundColor Green

# Clear all caches FIRST (before generating key)
Write-Host "🧹 Clearing all caches..." -ForegroundColor Cyan
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan cache:clear

# Generate application key if not set
Write-Host "🔑 Checking application key..." -ForegroundColor Cyan
php artisan key:generate --force

# Create storage link if not exists
Write-Host "🔗 Creating storage link..." -ForegroundColor Cyan
if (-not (Test-Path public/storage)) {
    php artisan storage:link
}

# Run migrations
Write-Host "🗄️  Running migrations..." -ForegroundColor Cyan
php artisan migrate --force

Write-Host "✅ Test/Development setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Server ready to start:" -ForegroundColor Yellow
Write-Host "   Run: php artisan serve --host=127.0.0.1 --port=8000" -ForegroundColor White
Write-Host "   Or use: .\start-test.ps1" -ForegroundColor White
Write-Host ""
Write-Host "ℹ️  Note: Caches are cleared for development. No caching enabled." -ForegroundColor Cyan
Write-Host ""

