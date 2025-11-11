# Fix All Issues - Clear caches and check port

Write-Host "🔧 Fixing All Issues..." -ForegroundColor Cyan
Write-Host ""

# Navigate to backend directory
Set-Location $PSScriptRoot

# 1. Clear all caches
Write-Host "1️⃣  Clearing all caches..." -ForegroundColor Yellow
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan cache:clear

Write-Host "✅ Caches cleared!" -ForegroundColor Green
Write-Host ""

# 2. Check port 8000
Write-Host "2️⃣  Checking port 8000..." -ForegroundColor Yellow
$portInUse = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue

if ($portInUse) {
    Write-Host "⚠️  Port 8000 is in use!" -ForegroundColor Red
    Write-Host ""
    Write-Host "   Processes using port 8000:" -ForegroundColor Cyan
    foreach ($conn in $portInUse) {
        $process = Get-Process -Id $conn.OwningProcess -ErrorAction SilentlyContinue
        if ($process) {
            Write-Host "   - PID: $($conn.OwningProcess) - $($process.ProcessName)" -ForegroundColor White
        }
    }
    Write-Host ""
    Write-Host "   Run: .\fix-port.ps1" -ForegroundColor Yellow
    Write-Host "   Or manually: Stop-Process -Id [PID] -Force" -ForegroundColor Yellow
} else {
    Write-Host "✅ Port 8000 is free!" -ForegroundColor Green
}

Write-Host ""

# 3. Verify environment
Write-Host "3️⃣  Verifying environment..." -ForegroundColor Yellow
$env = php artisan config:show app.env 2>&1 | Select-String -Pattern "app.env" | ForEach-Object { $_.Line.Split()[-1] }
$debug = php artisan config:show app.debug 2>&1 | Select-String -Pattern "app.debug" | ForEach-Object { $_.Line.Split()[-1] }

Write-Host "   Environment: $env" -ForegroundColor White
Write-Host "   Debug: $debug" -ForegroundColor White

if ($env -eq "local" -and $debug -eq "true") {
    Write-Host "✅ Environment is set to TEST/DEVELOPMENT" -ForegroundColor Green
} elseif ($env -eq "production" -and $debug -eq "false") {
    Write-Host "✅ Environment is set to PRODUCTION" -ForegroundColor Green
} else {
    Write-Host "⚠️  Environment may need to be reconfigured" -ForegroundColor Yellow
    Write-Host "   Run: .\setup-test.ps1 or .\setup-production.ps1" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "✅ Fix complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Cyan
Write-Host "   If port 8000 is in use: .\fix-port.ps1" -ForegroundColor White
Write-Host "   To start server: .\start-test.ps1 or .\start-production.ps1" -ForegroundColor White
Write-Host ""

