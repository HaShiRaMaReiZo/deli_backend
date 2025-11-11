# Quick Deployment Checklist for Render

## ✅ Pre-Deployment Checklist

- [ ] Code is pushed to GitHub/GitLab/Bitbucket
- [ ] All migrations are tested locally
- [ ] Environment variables list is ready
- [ ] APP_KEY is generated
- [ ] Database credentials are ready

## 🚀 Deployment Steps (Quick Reference)

### 1. Create Database
- [ ] Go to Render Dashboard → New → PostgreSQL
- [ ] Name: `deli-database`
- [ ] Copy Internal Database URL

### 2. Create Web Service
- [ ] Go to Render Dashboard → New → Web Service
- [ ] Connect your repository
- [ ] Configure:
  - Name: `deli-backend`
  - Root Directory: `deli_backend` (if repo has multiple folders)
  - Build Command: See RENDER_DEPLOYMENT.md
  - Start Command: `php artisan serve --host=0.0.0.0 --port=$PORT`

### 3. Set Environment Variables
- [ ] APP_NAME=Deli
- [ ] APP_ENV=production
- [ ] APP_KEY=(generate with `php artisan key:generate --show`)
- [ ] APP_DEBUG=false
- [ ] APP_URL=https://your-service-name.onrender.com
- [ ] DB_CONNECTION=pgsql
- [ ] DB_HOST=(from database Internal URL)
- [ ] DB_PORT=5432
- [ ] DB_DATABASE=(from database)
- [ ] DB_USERNAME=(from database)
- [ ] DB_PASSWORD=(from database)
- [ ] Add Pusher/FCM keys if needed

### 4. Run Migrations
- [ ] Go to Web Service → Shell
- [ ] Run: `php artisan migrate --force`

### 5. Set Up Storage
- [ ] Go to Web Service → Settings → Disks
- [ ] Add disk: `/opt/render/project/src/public/storage` (1GB)

### 6. Update Mobile Apps
- [ ] Update API base URL in `merchant_app/lib/core/api_endpoints.dart`
- [ ] Update API base URL in `rider_app/lib/core/api_endpoints.dart`

## 📝 Important Notes

- Free tier services spin down after 15 min inactivity
- First request after spin-down takes ~30 seconds
- Use Internal Database URL (not External) for DB_HOST
- Storage files need persistent disk mount
- SSL is automatic on Render

## 🔗 Files Created

- `RENDER_DEPLOYMENT.md` - Full detailed guide
- `render.yaml` - Render configuration (optional)
- `.render-build.sh` - Build script (optional)

## ⚠️ Common Issues

1. **500 Error**: Check APP_KEY and database connection
2. **Database Error**: Use Internal Database URL
3. **Storage Not Working**: Mount persistent disk
4. **Migration Fails**: Check database permissions

## 📞 Need Help?

Refer to `RENDER_DEPLOYMENT.md` for detailed instructions.

