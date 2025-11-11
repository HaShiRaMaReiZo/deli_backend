# Deploy Laravel Backend to Render - Step by Step Guide

## Prerequisites
1. A Render account (sign up at https://render.com)
2. Your code pushed to a Git repository (GitHub, GitLab, or Bitbucket)
3. A database (PostgreSQL recommended for Render)

## Local Testing (Before Deployment)

To test your Laravel app locally before deploying:

**Option 1: Using localhost (for same PC access)**
```bash
php -S localhost:8000 -t public
# Then access: http://localhost:8000
```

**Option 2: Using your IP (for network access)**
```bash
php -S 172.16.0.205:8000 -t public
# Then access: http://172.16.0.205:8000
```

**Note**: `0.0.0.0` is only for Docker/container environments. For local testing, use `localhost` or your actual IP address.

## Step 1: Prepare Your Repository

### 1.1 Push your code to GitHub/GitLab/Bitbucket
```bash
cd deli_backend
git init  # if not already a git repo
git add .
git commit -m "Initial commit"
git remote add origin <your-repo-url>
git push -u origin main
```

## Step 2: Create PostgreSQL Database on Render

1. Go to https://dashboard.render.com
2. Click **"New +"** → **"PostgreSQL"**
3. Configure:
   - **Name**: `deli-database` (or your preferred name)
   - **Database**: `deli_db`
   - **User**: (auto-generated)
   - **Region**: Choose closest to your users
   - **PostgreSQL Version**: 16 (or latest)
   - **Plan**: Free tier or paid
4. Click **"Create Database"**
5. **IMPORTANT**: Copy the **Internal Database URL** (you'll need this later)

## Step 3: Create Web Service on Render

1. In Render dashboard, click **"New +"** → **"Web Service"**
2. Connect your repository:
   - Select your Git provider (GitHub/GitLab/Bitbucket)
   - Authorize Render to access your repositories
   - Select the repository containing `deli_backend`
   - Click **"Connect"**

3. Configure the service:
   - **Name**: `deli-backend` (or your preferred name)
   - **Region**: Same as your database
   - **Branch**: `main` (or your default branch)
   - **Root Directory**: `deli_backend` (if your repo has multiple folders)
   - **Runtime**: `Docker` (PHP is not directly available, use Docker instead)
   - **Build Command**: (Leave empty - Docker will handle it)
   - **Start Command**: (Leave empty - Docker will handle it via docker-entrypoint.sh)
   - **Plan**: Free tier or paid
   
   **Note**: Since PHP is not available as a direct runtime, we'll use Docker. Make sure you have a `Dockerfile` in your `deli_backend` directory (see Dockerfile in the repo).

## Step 4: Configure Environment Variables

In the Render dashboard, go to your Web Service → **Environment** tab, add these variables:

### Required Variables:
```
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-service-name.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
# Either set DB_URL or the individual fields
DB_URL=postgresql://user:password@host:5432/database
# Or:
DB_HOST=host
DB_PORT=5432
DB_DATABASE=database
DB_USERNAME=user
DB_PASSWORD=password
```

### Get APP_KEY:
Run this locally first:
```bash
php artisan key:generate --show
```
Copy the output and use it as `APP_KEY` value.

### Database Connection:
Use the **Internal Database URL** from your PostgreSQL service:
- Format: `postgresql://user:password@host:port/database`
- Either paste to `DB_URL` or split into the individual fields above

## Step 5: Migrations on Free Plan (no Shell)

The provided `docker-entrypoint.sh` automatically runs:
```bash
php artisan migrate --force
```
Every time the service starts. This allows you to use the free plan without Shell access.

## Step 6: Persistent Storage

1. In Render dashboard → Your Web Service → **Settings**
2. Add a **Disk**:
   - **Mount Path**: `/opt/render/project/src/public/storage`
   - **Size**: 1GB (or as needed)

## Step 7: Deploy

1. Click **"Save Changes"** in your Web Service settings
2. Render will automatically:
   - Clone your repository
   - Build the Docker image
   - Start your application
3. Your app will be available at your Render URL

## Troubleshooting

- 500 error? Check **Logs** and verify env vars (APP_KEY, DB settings). The entrypoint auto-runs migrations.
- Storage errors? Ensure the disk is mounted at `/opt/render/project/src/public/storage`.
- Slow first request on free tier is expected (service spins down when idle).

