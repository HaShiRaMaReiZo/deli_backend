# Deli Backend - Deployment Guide

Complete guide for deploying the Deli backend to Render with PostgreSQL.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Local Testing](#local-testing)
3. [Database Setup](#database-setup)
4. [Deploy to Render](#deploy-to-render)
5. [Environment Variables](#environment-variables)
6. [Troubleshooting](#troubleshooting)
7. [Check Database Data](#check-database-data)

---

## Prerequisites

1. **Render Account**: Sign up at https://render.com
2. **Git Repository**: Push your code to GitHub/GitLab/Bitbucket
3. **Database**: MySQL (Render managed or external)

---

## Local Testing

Before deploying, test your Laravel app locally:

**Option 1: Using localhost (same PC access)**
```bash
php -S localhost:8000 -t public
# Access: http://localhost:8000
```

**Option 2: Using your IP (network access)**
```bash
php -S 172.16.0.205:8000 -t public
# Access: http://172.16.0.205:8000
```

**Note**: `0.0.0.0` is only for Docker/container environments. For local testing, use `localhost` or your actual IP address.

---

## Database Setup

### Option A: Render Managed MySQL (Recommended)

1. Go to https://dashboard.render.com
2. Click **"New +"** → **"Database"** → Select **"MySQL"**
3. Configure:
   - **Name**: `deli-mysql-database`
   - **Database**: `deli_db`
   - **User**: (auto-generated or custom)
   - **Region**: Choose closest to your users
   - **MySQL Version**: 8.0 (or latest)
   - **Plan**: Free tier or paid
4. Click **"Create Database"**
5. Copy the **Internal Database URL** (format: `mysql://user:password@host:3306/database`)

### Option B: External MySQL (AWS RDS, Railway, etc.)

#### AWS RDS MySQL (Free Tier - 12 months)

1. Sign up at https://aws.amazon.com
2. Go to AWS Console → RDS → Create database
3. Configure:
   - **Engine**: MySQL
   - **Template**: Free tier
   - **DB Instance**: `deli-mysql`
   - **Master Username**: `admin`
   - **Master Password**: (create strong password)
   - **Instance Class**: `db.t3.micro` (free tier)
   - **Public Access**: Yes
4. Wait 5-10 minutes, then get the endpoint
5. Connect MySQL Workbench and create database:
   ```sql
   CREATE DATABASE deli_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'deli_user'@'%' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON deli_db.* TO 'deli_user'@'%';
   FLUSH PRIVILEGES;
   ```

#### Railway MySQL (Free Tier)

1. Go to https://railway.app
2. Sign up with GitHub
3. Click "New Project" → "Database" → "MySQL"
4. Get connection details from Variables tab

### Connect MySQL Workbench

1. Open MySQL Workbench
2. Click **"+"** to add new connection
3. Enter connection details:
   - **Hostname**: From your database provider
   - **Port**: `3306`
   - **Username**: Your database username
   - **Password**: Your database password
   - **Default Schema**: `deli_db`
4. Click **"Test Connection"** → **"OK"**

---

## Deploy to Render

### Step 1: Prepare Repository

```bash
cd deli_backend
git init  # if not already a git repo
git add .
git commit -m "Initial commit"
git remote add origin <your-repo-url>
git push -u origin main
```

### Step 2: Create Web Service

1. In Render dashboard, click **"New +"** → **"Web Service"**
2. Connect your repository:
   - Select your Git provider (GitHub/GitLab/Bitbucket)
   - Authorize Render to access your repositories
   - Select the repository containing `deli_backend`
   - Click **"Connect"**

3. Configure the service:
   - **Name**: `deli-backend`
   - **Region**: Same as your database
   - **Branch**: `main` (or your default branch)
   - **Root Directory**: `deli_backend` (if your repo has multiple folders)
   - **Runtime**: `Docker` ⚠️ (PHP is not directly available, use Docker)
   - **Build Command**: (Leave empty - Docker handles it)
   - **Start Command**: (Leave empty - Docker handles it via docker-entrypoint.sh)
   - **Plan**: Free tier or paid

### Step 3: Configure Environment Variables

Go to your Web Service → **Environment** tab, add these variables:

#### Required Variables:

```env
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-service-name.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# MySQL Database Connection
DB_CONNECTION=mysql
DB_HOST=your-mysql-host.onrender.com
DB_PORT=3306
DB_DATABASE=deli_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# OR use Internal Database URL (recommended for Render managed DB):
# DB_URL=mysql://username:password@hostname:3306/database_name
```

#### Get APP_KEY:

Run this locally:
```bash
php artisan key:generate --show
```
Copy the output and use it as `APP_KEY` value.

#### Database Connection:

**For Render Managed MySQL:**
- Use the **Internal Database URL** from your MySQL service
- Format: `mysql://user:password@host:3306/database`
- Either paste to `DB_URL` or split into individual fields

**For External MySQL:**
- Use the connection details from your provider
- Make sure the database is publicly accessible
- Use the host, port, database, username, and password

### Step 4: Persistent Storage

1. In Render dashboard → Your Web Service → **Settings**
2. Add a **Disk**:
   - **Mount Path**: `/opt/render/project/src/public/storage`
   - **Size**: 1GB (or as needed)

### Step 5: Deploy

1. Click **"Save Changes"** in your Web Service settings
2. Render will automatically:
   - Clone your repository
   - Build the Docker image
   - Run migrations (via `docker-entrypoint.sh`)
   - Seed database (via `docker-entrypoint.sh`)
   - Start your application
3. Your app will be available at your Render URL

---

## Environment Variables

### Complete Environment Variable List

```env
# Application
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-service-name.onrender.com

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=your-host
DB_PORT=3306
DB_DATABASE=deli_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session & Cache (use database for free tier)
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Supabase Storage (for package images)
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_KEY=your-anon-public-key-here
SUPABASE_BUCKET=package-images

# Mail (configure if needed)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@deli.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Troubleshooting

### 500 Server Error

**Causes:**
- Missing `APP_KEY`
- Incorrect database credentials
- Missing environment variables

**Solutions:**
1. Check Render **Logs** tab for specific error messages
2. Verify all environment variables are set correctly
3. Ensure `APP_KEY` is generated and set
4. Verify database connection details

### Migration Errors

**Causes:**
- Database connection issues
- Syntax errors in migrations

**Solutions:**
1. Check database credentials in environment variables
2. Verify database exists and is accessible
3. Check migration logs in Render
4. Migrations run automatically via `docker-entrypoint.sh` on startup

### Storage Errors

**Causes:**
- Disk not mounted
- Incorrect mount path

**Solutions:**
1. Ensure disk is mounted at `/opt/render/project/src/public/storage`
2. Check disk size is sufficient
3. Verify storage link is created (handled by `docker-entrypoint.sh`)

### Slow First Request (Free Tier)

**Expected Behavior:**
- Free tier services spin down after 15 minutes of inactivity
- First request after spin-down takes 30-60 seconds to wake up
- Subsequent requests are fast

**Solution:**
- Upgrade to paid plan for always-on service
- Or use a service like UptimeRobot to ping your app every 10 minutes

### Connection Timeout

**Causes:**
- Database not publicly accessible
- Firewall blocking connections
- Incorrect host/port

**Solutions:**
1. For external databases, ensure public access is enabled
2. Check firewall rules allow connections from Render's IPs
3. Verify host and port are correct

---

## Docker Configuration

The project uses Docker for deployment. Key files:

- **Dockerfile**: Defines the container image
- **docker-entrypoint.sh**: Runs migrations and seeds on startup

The entrypoint script automatically:
- Caches Laravel configuration
- Creates storage link
- Runs migrations (`php artisan migrate --force`)
- Seeds database (`php artisan db:seed --class=OfficeUserSeeder`)
- Starts the PHP server

---

## Default Admin Credentials

After deployment, the database is seeded with a super admin:

- **Email**: `erickboyle@superadmin.com`
- **Password**: `erick2004`

**⚠️ Important**: Change these credentials after first login in production!

---

## Check Database Data

After deployment, you may want to verify that:
- ✅ Migrations ran successfully
- ✅ Users were created by the seeder
- ✅ Tables exist and have data

### Quick Methods:

**Method 1: Using psql (Recommended)**
- See **[PSQL_SETUP.md](./PSQL_SETUP.md)** for detailed installation and connection guide
- Quick connect: `psql "postgresql://user:password@host:port/database"`
- Or use the provided script: `.\connect-postgres.ps1`

**Method 2: Using pgAdmin (GUI)**
- Install pgAdmin from https://www.pgadmin.org/
- Connect using your Render database credentials
- Browse tables and run queries visually

**Method 3: Using DBeaver (Universal Tool)**
- Download from https://dbeaver.io/download/
- Create PostgreSQL connection with Render credentials
- Browse and query your database

**Method 4: Via Laravel Route (Temporary)**
- Add a `/check-db` route to view data (see PSQL_SETUP.md for code)
- Visit: `https://your-app.onrender.com/check-db`

### Expected Users After Seeding:
- **Super Admin**: `erickboyle@superadmin.com` / `erick2004`
- **Office Manager**: `manager@delivery.com` / `manager123`
- **Office Staff**: `staff@delivery.com` / `staff123`

---

## Support

For issues:
1. Check Render **Logs** tab
2. Verify environment variables
3. Check database connection using psql (see PSQL_SETUP.md)
4. Review this guide's troubleshooting section

