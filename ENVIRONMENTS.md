# 🌍 Environment Configuration Guide

This project supports both **TEST/DEVELOPMENT** and **PRODUCTION** environments.

---

## 🧪 Test/Development Mode

### Features
- ✅ Debug mode enabled
- ✅ Detailed error messages
- ✅ No caching (for easier development)
- ✅ Hot reloading support
- ✅ Development tools enabled

### Setup Test Environment
```powershell
cd deli_backend
.\setup-test.ps1
```

### Start Test Server
```powershell
cd deli_backend
.\start-test.ps1
```

Or manually:
```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

### Configuration
- **APP_ENV**: `local`
- **APP_DEBUG**: `true`
- **APP_URL**: `http://localhost:8000`
- **Caching**: Disabled

---

## 🚀 Production Mode

### Features
- ✅ Debug mode disabled
- ✅ All caches enabled
- ✅ Optimized performance
- ✅ Error details hidden
- ✅ Production-ready configuration

### Setup Production Environment
```powershell
cd deli_backend
.\setup-production.ps1
```

### Start Production Server
```powershell
cd deli_backend
.\start-production.ps1
```

Or manually:
```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

### Configuration
- **APP_ENV**: `production`
- **APP_DEBUG**: `false`
- **APP_URL**: `http://localhost:8000`
- **Caching**: Enabled (config, routes, views, events)

---

## 🔄 Quick Switch Between Environments

### Switch to Test Mode
```powershell
cd deli_backend
.\switch-env.ps1 test
```

### Switch to Production Mode
```powershell
cd deli_backend
.\switch-env.ps1 production
```

Or:
```powershell
.\switch-env.ps1 prod
```

---

## 📋 Environment Comparison

| Feature | Test/Development | Production |
|---------|-----------------|-------------|
| **APP_ENV** | `local` | `production` |
| **APP_DEBUG** | `true` | `false` |
| **Error Details** | Full stack trace | Generic messages |
| **Config Cache** | Disabled | Enabled |
| **Route Cache** | Disabled | Enabled |
| **View Cache** | Disabled | Enabled |
| **Event Cache** | Disabled | Enabled |
| **Performance** | Standard | Optimized |
| **Use Case** | Development/Testing | Production/Deployment |

---

## 🛠️ Available Scripts

### Setup Scripts
- `setup-test.ps1` - Configure for test/development
- `setup-production.ps1` - Configure for production
- `switch-env.ps1` - Quick switch between environments

### Start Scripts
- `start-test.ps1` - Start test/development server
- `start-production.ps1` - Start production server

---

## 📝 Manual Configuration

### Test/Development Mode
```powershell
# Update .env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Clear caches
php artisan optimize:clear
```

### Production Mode
```powershell
# Update .env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

# Build caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## ✅ Verification

### Check Current Environment
```powershell
php artisan config:show app.env
php artisan config:show app.debug
```

### Test Environment
- Should show: `local` and `true`
- Caches should be cleared

### Production Environment
- Should show: `production` and `false`
- Caches should be built

---

## 🎯 Recommendations

### For Development
- Use **Test Mode** for active development
- Switch to **Production Mode** only when testing production behavior

### For Testing
- Use **Test Mode** for debugging and development
- Use **Production Mode** for performance testing

### For Deployment
- Always use **Production Mode** in production
- Test in **Production Mode** before deploying

---

## 📞 Quick Commands

### Switch to Test
```powershell
.\switch-env.ps1 test
.\start-test.ps1
```

### Switch to Production
```powershell
.\switch-env.ps1 production
.\start-production.ps1
```

---

**Both environments are configured to run on `localhost:8000`!** 🚀

