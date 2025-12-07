# Quick Deploy Laravel Backend to Render

## ✅ Prerequisites

1. **GitHub Repository**: Your `deli_backend` code is on GitHub
2. **Location Tracker JS**: Already deployed at `https://location-tracker-js.onrender.com`
3. **Database**: MySQL database (Render managed or external)

## 🚀 Quick Deployment Steps

### 1. Create Web Service on Render

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +"** → **"Web Service"**
3. Connect your GitHub repository
4. Configure:
   - **Name**: `deli-backend` (or any name)
   - **Root Directory**: `deli_backend` (if repo has multiple folders)
   - **Environment**: `Docker` (PHP not directly available on Render)
   - **Build Command**: (Leave empty - Docker handles it)
   - **Start Command**: (Leave empty - Docker handles it)
   - **Plan**: Free

### 2. Add Environment Variables

Go to your Web Service → **Environment** tab, add:

#### Required:
```env
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-service-name.onrender.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-mysql-host.onrender.com
DB_PORT=3306
DB_DATABASE=deli_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Location Tracker (IMPORTANT!)
LOCATION_TRACKER_URL=https://location-tracker-js.onrender.com
```

#### Get APP_KEY:
```bash
cd deli_backend
php artisan key:generate --show
```
Copy the output and use as `APP_KEY` value.

### 3. Add Persistent Storage (for file uploads)

1. Web Service → **Settings** → **Disks**
2. Add Disk:
   - **Mount Path**: `/opt/render/project/src/public/storage`
   - **Size**: 1GB

### 4. Deploy

1. Click **"Save Changes"**
2. Render will automatically build and deploy
3. Check logs for any errors

## ✅ Verify Deployment

1. **Health Check**: `https://your-service-name.onrender.com/health` (if you have a health route)
2. **API Test**: `https://your-service-name.onrender.com/api/auth/login`
3. **Check Logs**: Render dashboard → Logs tab

## 🔧 Important Notes

- **Free Plan**: Service sleeps after 15 min inactivity
- **First Request**: Takes 30-60 seconds to wake up
- **Database**: Make sure MySQL database is accessible
- **Storage**: Files uploaded will be stored in persistent disk

## 🐛 Troubleshooting

### Build Fails
- Check Dockerfile exists
- Check logs for specific error
- Verify all dependencies in `composer.json`

### Database Connection Error
- Verify database credentials
- Check database is accessible from Render
- Use Internal Database URL if using Render managed MySQL

### Location Tracker Not Working
- Verify `LOCATION_TRACKER_URL` is set correctly
- Check Location Tracker JS service is running
- Check Laravel logs for connection errors

