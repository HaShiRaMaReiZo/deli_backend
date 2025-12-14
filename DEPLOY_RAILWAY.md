# Deploy Laravel Backend to Railway

Railway is **faster and more reliable** than Render for Laravel applications.

## ✅ Why Railway?

- ⚡ **Faster cold starts** (no 30-60s wake-up time)
- 🚀 **Better performance** (lower latency)
- 🐳 **Native Docker support**
- 💰 **Generous free tier** ($5/month credit)
- 🔧 **Easier setup** than Render

## 🚀 Quick Deployment Steps

### 1. Create Railway Account

1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub
3. Click **"New Project"**

### 2. Deploy from GitHub

1. Click **"New"** → **"GitHub Repo"**
2. Select your `deli_backend` repository
3. Railway will auto-detect Dockerfile

### 3. Add Environment Variables

Go to your service → **Variables** tab, add:

```env
APP_NAME=Deli
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-service-name.up.railway.app

# Database (Supabase PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-northeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.vcicqszowctcfpcesaqz
DB_PASSWORD=your-password-here
DB_SSLMODE=require

# Location Tracker
LOCATION_TRACKER_URL=https://location-tracker-js.onrender.com
```

### 4. Configure Port

Railway uses `PORT` environment variable. Update `docker-entrypoint.sh` if needed:

```bash
# Railway automatically sets PORT env variable
# Your entrypoint should use: php artisan serve --host=0.0.0.0 --port=$PORT
```

### 5. Deploy

Railway will automatically:
- Build Docker image
- Deploy your service
- Provide a public URL

## 🔧 Railway-Specific Configuration

### Update Dockerfile (if needed)

Railway works with your existing Dockerfile, but you can optimize:

```dockerfile
# Railway automatically handles PORT, but expose it anyway
EXPOSE $PORT
```

### Update docker-entrypoint.sh

Make sure it uses Railway's PORT:

```bash
#!/bin/bash
set -e

# Use Railway's PORT or default to 8000
PORT=${PORT:-8000}

# Run migrations
php artisan migrate --force

# Start server
php artisan serve --host=0.0.0.0 --port=$PORT
```

## 📊 Performance Comparison

| Feature | Render | Railway |
|---------|--------|---------|
| Cold Start | 30-60s | 5-10s |
| Response Time | 3-4s | 500ms-1s |
| Free Tier | Limited | $5/month credit |
| Setup | Medium | Easy |

## 💰 Pricing

- **Free Tier**: $5/month credit (enough for small apps)
- **Hobby**: $5/month + usage
- **Pro**: $20/month + usage

## ✅ Verify Deployment

1. **Health Check**: `https://your-service.up.railway.app/up`
2. **API Test**: `https://your-service.up.railway.app/api/auth/login`
3. **Check Logs**: Railway dashboard → Logs tab

## 🐛 Troubleshooting

### Build Fails
- Check Dockerfile exists
- Verify all dependencies in `composer.json`
- Check Railway logs

### Database Connection Error
- Verify Supabase credentials
- Check `DB_HOST` and `DB_PORT` (use Transaction pooler: port 6543)
- Ensure `DB_SSLMODE=require`

### Port Issues
- Railway sets `PORT` automatically
- Make sure entrypoint uses `$PORT` variable

## 🎯 Next Steps

1. Deploy to Railway
2. Test API endpoints
3. Update Flutter app to use new Railway URL
4. Monitor performance (should be much faster!)

