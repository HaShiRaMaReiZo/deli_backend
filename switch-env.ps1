# Switch between Test and Production environments

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("test", "production", "prod")]
    [string]$Mode
)

Write-Host "🔄 Switching environment..." -ForegroundColor Cyan
Write-Host ""

# Navigate to backend directory
Set-Location $PSScriptRoot

if ($Mode -eq "test" -or $Mode -eq "dev") {
    Write-Host "🧪 Switching to TEST/DEVELOPMENT mode..." -ForegroundColor Yellow
    .\setup-test.ps1
} elseif ($Mode -eq "production" -or $Mode -eq "prod") {
    Write-Host "🚀 Switching to PRODUCTION mode..." -ForegroundColor Green
    .\setup-production.ps1
}

Write-Host ""
Write-Host "✅ Environment switched successfully!" -ForegroundColor Green
Write-Host ""

