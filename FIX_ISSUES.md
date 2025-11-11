# 🔧 Fix Current Issues

## Issue 1: Port 8000 Already in Use

### Quick Fix
```powershell
cd deli_backend
.\fix-port.ps1
```

This will:
- Show what's using port 8000
- Ask if you want to kill those processes
- Free up the port

### Manual Fix
```powershell
# Find what's using port 8000
Get-NetTCPConnection -LocalPort 8000 | Select-Object OwningProcess

# Kill the process (replace <PID> with the actual process ID)
Stop-Process -Id <PID> -Force
```

---

## Issue 2: Config Cache Still Shows Production

### Quick Fix
```powershell
cd deli_backend
php artisan config:clear
php artisan optimize:clear
```

Then verify:
```powershell
php artisan config:show app.env
php artisan config:show app.debug
```

Should show:
- `app.env`: `local`
- `app.debug`: `true`

---

## Complete Fix (Do This Now)

```powershell
cd deli_backend

# 1. Clear all caches
php artisan optimize:clear
php artisan config:clear

# 2. Fix port 8000 (if needed)
.\fix-port.ps1

# 3. Switch to test mode again
.\setup-test.ps1

# 4. Start server
.\start-test.ps1
```

---

## Why This Happened

1. **Port 8000**: A previous server instance is still running
2. **Config Cache**: Laravel caches configuration for performance. When you switch environments, you need to clear the cache first.

---

## Prevention

The updated scripts now:
- ✅ Check port availability before starting
- ✅ Clear config cache when switching environments
- ✅ Clear caches first in setup scripts

---

**Run the complete fix above and you should be good!** ✅

