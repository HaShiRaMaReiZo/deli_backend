# Deploy Laravel Backend to Render - Step by Step Guide

## Prerequisites
1. A Render account (sign up at https://render.com)
2. Your code pushed to a Git repository (GitHub, GitLab, or Bitbucket)
3. A database (PostgreSQL recommended for Render)

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
   - **Runtime**: `PHP`
   - **Build Command**: 
     ```bash
     composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
     ```
   - **Start Command**: 
     ```bash
     php artisan serve --host=0.0.0.0 --port=$PORT
     ```
   - **Plan**: Free tier or paid

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
DB_HOST=your-database-host.onrender.com
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
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
- Render will provide this in your database dashboard

### Additional Variables (if needed):
```
BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster

FCM_SERVER_KEY=your_fcm_server_key
FCM_SENDER_ID=your_fcm_sender_id
```

## Step 5: Set Up Storage Link

Add this to your **Build Command** (or create a post-deploy script):
```bash
php artisan storage:link
```

## Step 6: Run Migrations

### Option 1: Manual Migration (Recommended for first deployment)
1. Go to your Web Service → **Shell** tab
2. Run:
```bash
php artisan migrate --force
```

### Option 2: Auto Migration (Add to Build Command)
Add to build command:
```bash
php artisan migrate --force
```

⚠️ **Warning**: Only use `--force` in production if you're sure about migrations.

## Step 7: Configure Public Storage

1. In Render dashboard → Your Web Service → **Settings**
2. Add a **Disk**:
   - **Mount Path**: `/opt/render/project/src/public/storage`
   - **Size**: 1GB (or as needed)
   - This allows file uploads to persist

## Step 8: Deploy

1. Click **"Save Changes"** in your Web Service settings
2. Render will automatically:
   - Clone your repository
   - Run the build command
   - Start your application
3. Wait for deployment to complete (usually 2-5 minutes)
4. Your app will be available at: `https://your-service-name.onrender.com`

## Step 9: Verify Deployment

1. Check the **Logs** tab for any errors
2. Visit your app URL
3. Test API endpoints:
   ```bash
   curl https://your-service-name.onrender.com/api/health
   ```

## Step 10: Update Your Mobile Apps

Update the API base URL in your Flutter apps:
- `merchant_app/lib/core/api_endpoints.dart`
- `rider_app/lib/core/api_endpoints.dart`

Change from:
```dart
static const String baseUrl = 'http://localhost:8000';
```

To:
```dart
static const String baseUrl = 'https://your-service-name.onrender.com';
```

## Troubleshooting

### Common Issues:

1. **500 Error**: Check logs, usually missing APP_KEY or database connection
2. **Database Connection Failed**: Verify DB credentials and use Internal Database URL
3. **Storage Not Working**: Ensure storage disk is mounted
4. **Migration Errors**: Check database permissions and connection
5. **Slow First Request**: Normal on free tier (spins down after inactivity)

### Useful Commands (via Shell):
```bash
# Check environment variables
php artisan tinker
>>> config('app.env')

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Free Tier Limitations

- Services spin down after 15 minutes of inactivity
- First request after spin-down takes ~30 seconds
- 750 hours/month free (enough for 1 service running 24/7)
- Consider paid tier for production use

## Next Steps

1. Set up custom domain (optional)
2. Configure SSL (automatic on Render)
3. Set up monitoring and alerts
4. Configure backups for database
5. Set up CI/CD for automatic deployments

