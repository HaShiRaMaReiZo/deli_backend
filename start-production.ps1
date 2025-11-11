# Start Laravel Production Server on localhost:8000

Write-Host "Starting Laravel Production Server..." -ForegroundColor Green
Write-Host "Server will run on: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""

# Navigate to backend directory
Set-Location $PSScriptRoot

# Check if optimized
if (-not (Test-Path bootstrap/cache/config.php)) {
    Write-Host "WARNING: Configuration not optimized. Running setup..." -ForegroundColor Yellow
    .\setup-production.ps1
}

# Verify production mode
$envContent = Get-Content .env -Raw
if ($envContent -notmatch 'APP_ENV=production') {
    Write-Host "WARNING: Not in production mode. Running setup..." -ForegroundColor Yellow
    .\setup-production.ps1
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

# Start server
Write-Host "Starting server on http://127.0.0.1:8000..." -ForegroundColor Green
Write-Host "Production Mode: ENABLED" -ForegroundColor Green
Write-Host "Debug Mode: DISABLED" -ForegroundColor Green
Write-Host ""
php artisan serve --host=127.0.0.1 --port=8000

