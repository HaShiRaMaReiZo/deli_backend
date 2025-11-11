# 🌍 Environment Management

This project supports both **TEST/DEVELOPMENT** and **PRODUCTION** environments.

---

## 🧪 Test/Development Mode

### Setup
```powershell
.\setup-test.ps1
```

### Start Server
```powershell
.\start-test.ps1
```

### Configuration
- **APP_ENV**: `local`
- **APP_DEBUG**: `true`
- **Caching**: Disabled
- **Error Details**: Full stack trace

---

## 🚀 Production Mode

### Setup
```powershell
.\setup-production.ps1
```

### Start Server
```powershell
.\start-production.ps1
```

### Configuration
- **APP_ENV**: `production`
- **APP_DEBUG**: `false`
- **Caching**: Enabled (config, routes, views, events)
- **Error Details**: Generic messages only

---

## 🔄 Quick Switch

```powershell
.\switch-env.ps1 test         # Switch to test
.\switch-env.ps1 production    # Switch to production
```

---

## 📋 Comparison

| Feature | Test | Production |
|---------|------|------------|
| **APP_ENV** | `local` | `production` |
| **APP_DEBUG** | `true` | `false` |
| **Config Cache** | ❌ | ✅ |
| **Route Cache** | ❌ | ✅ |
| **View Cache** | ❌ | ✅ |
| **Event Cache** | ❌ | ✅ |
| **Error Details** | Full | Generic |

---

**Both environments configured for localhost:8000!** 🚀

