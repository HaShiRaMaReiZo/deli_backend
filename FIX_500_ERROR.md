# Fixing 500 Error on Render

Your deployment succeeded, but you're getting 500 errors. This is usually due to missing environment variables.

## Step 1: Check the Logs

1. Go to your Render dashboard
2. Click on your service (`ok-delivery`)
3. Go to **"Logs"** tab
4. Look for error messages (usually red text)
5. Common errors you might see:
   - "No application encryption key has been specified"
   - "SQLSTATE[HY000] [2002] Connection refused" (database)
   - "The stream or file could not be opened" (storage permissions)

## Step 2: Set Required Environment Variables

Go to your service → **"Environment"** tab and add these:

### Critical Variables (Must Have):

```
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://ok-delivery.onrender.com
```

**To get APP_KEY:**
1. Run locally: `php artisan key:generate --show`
2. Copy the output (starts with `base64:`)
3. Paste it as APP_KEY value

### Database Variables:

```
DB_CONNECTION=pgsql
DB_HOST=your-database-host.onrender.com
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

**To get database credentials:**
1. Go to your PostgreSQL database service in Render
2. Click on it
3. Go to **"Info"** tab
4. Copy the **Internal Database URL**
5. Parse it: `postgresql://user:password@host:port/database`
   - Extract: host, port, database, username, password

### Other Important Variables:

```
LOG_CHANNEL=stack
LOG_LEVEL=error
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## Step 3: Run Migrations

After setting environment variables:

1. Go to your service → **"Shell"** tab
2. Run:
```bash
php artisan migrate --force
```

## Step 4: Check Storage Permissions

If you see storage errors:

1. In Shell, run:
```bash
chmod -R 755 storage bootstrap/cache
```

## Step 5: Clear Cache

In Shell, run:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Then the service will automatically restart.

## Step 6: Verify

1. Visit: https://ok-delivery.onrender.com
2. Check if it loads (should show Laravel welcome page or your app)
3. If still 500, check logs again for new errors

## Common Issues:

1. **APP_KEY missing** → Most common cause of 500 error
2. **Database not connected** → Check DB credentials
3. **Migrations not run** → Run `php artisan migrate --force`
4. **Storage permissions** → Fix with chmod commands

