# PostgreSQL Connection Script for Render
# Usage: .\connect-postgres.ps1

# Replace these with your actual Render PostgreSQL credentials
$DB_HOST = "dpg-d49foru3jp1c73d2aph0-a"
$DB_PORT = "5432"
$DB_NAME = "deli_db_9m02"
$DB_USER = "deli_db_9m02_user"
$DB_PASSWORD = "4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4"

# Full connection string
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

# Connect to database
psql $CONNECTION_STRING

