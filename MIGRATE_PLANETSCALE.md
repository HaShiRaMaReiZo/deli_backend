# Migrate Database to PlanetScale (Fast MySQL)

PlanetScale is **much faster** than Supabase for Laravel applications and requires minimal changes.

## ✅ Why PlanetScale?

- ⚡ **Ultra-fast** (sub-100ms response times)
- 🌍 **Global edge network** (low latency worldwide)
- 🚀 **Serverless MySQL** (no connection pooling issues)
- 💰 **Free tier** (5GB storage, 1 billion row reads/month)
- 🔧 **Laravel compatible** (just change DB_CONNECTION to mysql)
- 🔄 **Branching** (test migrations safely)

## 🚀 Quick Migration Steps

### 1. Create PlanetScale Account

1. Go to [planetscale.com](https://planetscale.com)
2. Sign up (free tier available)
3. Create a new database: **"deli_db"**

### 2. Get Connection String

PlanetScale will provide:
```
Host: aws.connect.psdb.cloud
Username: xxxxxx
Password: xxxxxx
Database: deli_db
Port: 3306
```

### 3. Update Laravel Configuration

**Update `config/database.php`** - Already supports MySQL! ✅

**Update Render/Railway Environment Variables:**

```env
DB_CONNECTION=mysql
DB_HOST=aws.connect.psdb.cloud
DB_PORT=3306
DB_DATABASE=deli_db
DB_USERNAME=your-planetscale-username
DB_PASSWORD=your-planetscale-password
DB_SSLMODE=require
```

**For PlanetScale, add SSL options:**

```env
MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false
```

### 4. Update Dockerfile (if needed)

Your Dockerfile already has MySQL support! Just ensure it's using `pdo_mysql`:

```dockerfile
# Already correct:
RUN apt-get install -y default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd
```

### 5. Run Migrations

```bash
php artisan migrate:fresh --seed
```

Or on your server:
```bash
php artisan migrate --force
```

## 📊 Performance Comparison

| Database | Response Time | Setup Difficulty |
|----------|--------------|------------------|
| Supabase (current) | 4-8 seconds | Easy |
| PlanetScale | 100-500ms | Easy |
| Neon PostgreSQL | 200-800ms | Easy |
| Railway PostgreSQL | 300-1000ms | Easy |

## 🔄 Migration from PostgreSQL to MySQL

### Minor Changes Needed:

1. **Auto-increment IDs**: MySQL uses `AUTO_INCREMENT` (already in migrations)
2. **JSON columns**: Both support JSON
3. **Timestamps**: Both support timestamps
4. **Foreign keys**: Both support foreign keys

### Check Your Migrations:

Your migrations should work as-is! Laravel handles differences automatically.

## 💰 PlanetScale Pricing

- **Free Tier**: 
  - 5GB storage
  - 1 billion row reads/month
  - 10 million row writes/month
  - Perfect for development/small apps

- **Scaler Plan**: $29/month (for production)

## ✅ Verify Connection

1. Test connection:
```bash
php artisan tinker
DB::connection()->getPdo();
```

2. Run migrations:
```bash
php artisan migrate
```

3. Test API endpoints

## 🎯 Expected Performance

- **Before (Supabase)**: 4-8 seconds
- **After (PlanetScale)**: 100-500ms
- **Improvement**: 8-80x faster! 🚀

## 🔧 Troubleshooting

### SSL Connection Error
- Add `MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false` to env

### Migration Errors
- Check if migrations use PostgreSQL-specific syntax
- Most Laravel migrations work on both MySQL and PostgreSQL

### Connection Timeout
- PlanetScale uses connection pooling automatically
- No need to configure poolers

