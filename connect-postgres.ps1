# PostgreSQL Connection Script for Render
# Usage: .\connect-postgres.ps1

# Render PostgreSQL External Connection (for external access)
$DB_HOST = "dpg-d49foru3jp1c73d2aph0-a.oregon-postgres.render.com"
$DB_PORT = "5432"
$DB_NAME = "deli_db_9m02"
$DB_USER = "deli_db_9m02_user"
$DB_PASSWORD = "4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4"

# Full connection string (External URL)
$CONNECTION_STRING = "postgresql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}"

Write-Host "Connecting to PostgreSQL database..." -ForegroundColor Green
Write-Host "Host: $DB_HOST" -ForegroundColor Cyan
Write-Host "Database: $DB_NAME" -ForegroundColor Cyan
Write-Host ""
Write-Host "Once connected, try these commands:" -ForegroundColor Yellow
Write-Host "  \dt                    - List all tables" -ForegroundColor Gray
Write-Host "  SELECT * FROM users;   - View all users" -ForegroundColor Gray
Write-Host "  \q                     - Exit psql" -ForegroundColor Gray
Write-Host ""

# Use full path to psql (adjust version number if different)
$PSQL_PATH = "C:\Program Files\PostgreSQL\18\bin\psql.exe"

# Check if psql exists
if (Test-Path $PSQL_PATH) {
    Write-Host "Using: $PSQL_PATH" -ForegroundColor Green
    & $PSQL_PATH $CONNECTION_STRING
} else {
    Write-Host "Error: psql.exe not found at $PSQL_PATH" -ForegroundColor Red
    Write-Host "Please update PSQL_PATH in this script with the correct path." -ForegroundColor Yellow
    Write-Host "Or add PostgreSQL bin folder to your PATH environment variable." -ForegroundColor Yellow
}

