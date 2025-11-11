# Fix Port 8000 - Kill process using port 8000

Write-Host "🔍 Checking for processes using port 8000..." -ForegroundColor Cyan
Write-Host ""

$connections = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue

if (-not $connections) {
    Write-Host "✅ Port 8000 is free!" -ForegroundColor Green
    exit 0
}

Write-Host "⚠️  Port 8000 is in use by the following processes:" -ForegroundColor Yellow
Write-Host ""

foreach ($conn in $connections) {
    $process = Get-Process -Id $conn.OwningProcess -ErrorAction SilentlyContinue
    if ($process) {
        Write-Host "   PID: $($conn.OwningProcess) - $($process.ProcessName)" -ForegroundColor White
    }
}

Write-Host ""
$response = Read-Host "Do you want to kill these processes? (y/n)"

if ($response -eq 'y' -or $response -eq 'Y') {
    foreach ($conn in $connections) {
        try {
            Stop-Process -Id $conn.OwningProcess -Force -ErrorAction SilentlyContinue
            Write-Host "✅ Killed process $($conn.OwningProcess)" -ForegroundColor Green
        } catch {
            Write-Host "❌ Could not kill process $($conn.OwningProcess)" -ForegroundColor Red
        }
    }
    Write-Host ""
    Write-Host "✅ Port 8000 should now be free!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "ℹ️  Processes not killed. Please stop them manually." -ForegroundColor Yellow
}

