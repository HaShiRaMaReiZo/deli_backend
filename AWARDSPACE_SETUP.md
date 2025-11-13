# Laravel Setup for AwardSpace Free Hosting

Guide for deploying Laravel application on AwardSpace free hosting.

## ✅ AwardSpace Compatibility

**Yes, your Laravel project will work on AwardSpace!** ✅

AwardSpace supports:
- ✅ PHP 8.2+ (latest versions)
- ✅ MySQL databases
- ✅ Laravel framework
- ✅ `.htaccess` files (for URL rewriting)
- ✅ Composer (via SSH or manual upload)

## ⚠️ Free Plan Limitations

- **Disk Space**: 1GB
- **Bandwidth**: 5GB/month
- **MySQL Databases**: Limited (check your plan)
- **SSH Access**: May require paid plan

## Step 1: Get Database Credentials from AwardSpace

1. Log in to your **AwardSpace Control Panel**
2. Go to **"MySQL Databases"** or **"Database"** section
3. Create a new database (or use existing)
4. Note down:
   - **Database Name**: Usually format is `username_dbname`
   - **Database Username**: Your database username
   - **Database Password**: Your database password
   - **Database Host**: Usually `localhost` (check in phpMyAdmin)

## Step 2: Configure Laravel `.env` File

Create/Edit the `.env` file in your Laravel root:

```env
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.awardspace.com

# Database Configuration for AwardSpace
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_username_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

# Session & Cache (use database for shared hosting)
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Get APP_KEY:
Run locally:
```bash
php artisan key:generate --show
```
Copy the output and paste in `.env` file.

## Step 3: Upload Files Structure

**Option A: Standard Structure (Recommended)**

Upload your Laravel project so that the `public` folder contents are in your web root:

```
public_html/                    # Your AwardSpace web root
├── .env                        # Your environment file
├── index.php                   # From Laravel public/index.php
├── .htaccess                   # From Laravel public/.htaccess
├── assets/                     # Compiled assets
└── ...
```

And Laravel root outside `public_html`:
```
/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                     # Symlink or copy contents to public_html
├── resources/
├── routes/
├── storage/
└── vendor/
```

**Option B: All in public_html (Simpler for free hosting)**

```
public_html/
├── .env
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                     # Move index.php and .htaccess to root
│   └── (other public files)
├── resources/
├── routes/
├── storage/
└── vendor/
```

Then move `public/index.php` and `public/.htaccess` to `public_html/` root and update paths in `index.php`.

## Step 4: Update `public/index.php` Paths

If you moved files, update `public/index.php` (or the one in your web root):

```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Update these paths if Laravel is outside public_html
require __DIR__.'/../vendor/autoload.php';  // Adjust path if needed

$app = require_once __DIR__.'/../bootstrap/app.php';  // Adjust path if needed

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

## Step 5: Configure `.htaccess` in Web Root

Make sure `.htaccess` exists in your web root (`public_html/`):

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Step 6: Set Permissions

Set proper permissions (via FTP or File Manager):
- `storage/` folder: `755` or `775`
- `bootstrap/cache/` folder: `755` or `775`
- `storage/logs/` folder: `755` or `775`

## Step 7: Install Dependencies

**Option A: Via SSH (if available)**
```bash
cd /path/to/your/laravel
composer install --no-dev --optimize-autoloader
```

**Option B: Upload vendor folder**
- Run `composer install` locally
- Upload the `vendor/` folder to your server

## Step 8: Run Migrations

**Option A: Via SSH**
```bash
php artisan migrate --force
php artisan db:seed --class=OfficeUserSeeder
```

**Option B: Via phpMyAdmin**
- Import your database schema manually
- Or create a migration script (see below)

**Option C: Create Migration Script**

Create `migrate.php` in your Laravel root:

```php
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();
```

Access via browser: `https://yourdomain.awardspace.com/migrate.php`

⚠️ **Delete this file after running migrations!**

## Step 9: Create Storage Link

**Via SSH:**
```bash
php artisan storage:link
```

**Or manually:**
Create a symlink from `public/storage` to `storage/app/public`, or copy files.

## Step 10: Clear Cache

**Via SSH:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Or via browser script:**
Create `clear-cache.php`:
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

Artisan::call('config:cache');
Artisan::call('route:cache');
Artisan::call('view:cache');
echo "Cache cleared!";
```

## Troubleshooting

### Database Connection Error

1. **Check database credentials** in `.env` file
2. **Verify database name format**: AwardSpace usually uses `username_dbname`
3. **Check database host**: Usually `localhost`, but verify in control panel
4. **Test connection** using phpMyAdmin

### 500 Internal Server Error

1. Check `storage/logs/laravel.log` for errors
2. Verify `.env` file exists and has correct values
3. Check file permissions on `storage/` and `bootstrap/cache/`
4. Verify `APP_KEY` is set in `.env`
5. Check AwardSpace error logs in control panel

### Permission Denied

1. Set `storage/` folder permissions to `755` or `775`
2. Set `bootstrap/cache/` folder permissions to `755` or `775`
3. Check `.htaccess` file exists in web root

### Routes Not Working

1. Verify `.htaccess` file exists in web root
2. Check if `mod_rewrite` is enabled (should be on AwardSpace)
3. Verify `APP_URL` in `.env` matches your domain
4. Check if you need to update paths in `index.php`

### Composer Not Available

If you can't use Composer on AwardSpace:
1. Run `composer install` locally
2. Upload the entire `vendor/` folder to your server
3. Make sure `composer.json` and `composer.lock` are uploaded

## AwardSpace Specific Notes

- **PHP Version**: AwardSpace supports PHP 8.2+ ✅
- **MySQL Version**: MySQL 5.7 or 8.0
- **Database Host**: Usually `localhost`
- **Database Name Format**: `username_database_name`
- **phpMyAdmin**: Available in control panel
- **SSH Access**: May require paid plan
- **Disk Space**: 1GB on free plan (monitor usage)
- **Bandwidth**: 5GB/month on free plan

## Security Reminders

1. ⚠️ **Never commit `.env` file** to Git
2. ⚠️ **Delete `migrate.php` and `clear-cache.php`** after use
3. ⚠️ **Set `APP_DEBUG=false`** in production
4. ⚠️ **Change default admin credentials** after first login
5. ⚠️ **Keep Laravel updated** for security patches

## Quick Checklist

- [ ] Database created in AwardSpace control panel
- [ ] `.env` file configured with database credentials
- [ ] `APP_KEY` generated and set
- [ ] Files uploaded to correct structure
- [ ] `.htaccess` file in web root
- [ ] `storage/` and `bootstrap/cache/` permissions set (755)
- [ ] Dependencies installed (`vendor/` folder uploaded)
- [ ] Migrations run
- [ ] Storage link created
- [ ] Cache cleared
- [ ] Test application in browser

## Need Help?

- Check AwardSpace knowledge base: https://www.awardspace.com/kb/
- Check Laravel logs: `storage/logs/laravel.log`
- Check AwardSpace error logs in control panel

