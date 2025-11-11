# Start Laravel Test/Development Server on localhost:8000

Write-Host "Starting Laravel Test/Development Server..." -ForegroundColor Green
Write-Host "Server will run on: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""

# Navigate to backend directory
Set-Location $PSScriptRoot

# Verify test mode
$envContent = Get-Content .env -Raw
if ($envContent -notmatch 'APP_ENV=local') {
    Write-Host "WARNING: Not in test/development mode. Running setup..." -ForegroundColor Yellow
    .\setup-test.ps1
}

# Check if port 8000 is in use
Write-Host "Checking if port 8000 is available..." -ForegroundColor Cyan
$portInUse = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if ($portInUse) {
    Write-Host "WARNING: Port 8000 is already in use!" -ForegroundColor Red
    Write-Host "   Please stop the other process or use a different port." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   To find what's using port 8000:" -ForegroundColor Cyan
    Write-Host "   Get-NetTCPConnection -LocalPort 8000 | Select-Object OwningProcess" -ForegroundColor White
    Write-Host ""
    Write-Host "   To kill the process:" -ForegroundColor Cyan
    Write-Host "   Stop-Process -Id [PID]" -ForegroundColor White
    Write-Host ""
    exit 1
}

# Clear config cache to ensure environment is correct
Write-Host "Clearing config cache..." -ForegroundColor Cyan
php artisan config:clear

# Start server
Write-Host "Starting server on http://127.0.0.1:8000..." -ForegroundColor Green
Write-Host "Environment: TEST/DEVELOPMENT" -ForegroundColor Yellow
Write-Host "Debug Mode: ENABLED" -ForegroundColor Yellow
Write-Host "Caches: DISABLED (for development)" -ForegroundColor Yellow
Write-Host ""
php artisan serve --host=127.0.0.1 --port=8000

