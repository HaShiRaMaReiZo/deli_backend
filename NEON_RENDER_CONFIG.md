# Neon → Render Configuration

## Your Neon Connection Details

From your connection string, here are the values:

```
Host: ep-still-hall-a1gu8tkn-pooler.ap-southeast-1.aws.neon.tech
Database: neondb
Username: neondb_owner
Password: npg_EJgS1Ntker2Q
Port: 5432 (default PostgreSQL port)
SSL Mode: require
```

## Render Environment Variables

**⚠️ IMPORTANT: Use DIRECT connection (not pooler) for migrations!**

Copy and paste these into Render → Environment tab:

```env
DB_CONNECTION=pgsql
DB_HOST=ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_EJgS1Ntker2Q
DB_SSLMODE=require
```

**Note:** Hostname is `ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech` (NO `-pooler`)

**Direct Connection String (for reference):**
```
postgresql://neondb_owner:npg_EJgS1Ntker2Q@ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech/neondb?sslmode=require
```

## ⚡ IMPORTANT: Use Direct Connection for Migrations

**The pooler connection causes transaction errors during migrations!**

**Use Direct Connection (required for migrations):**
```env
DB_HOST=ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech
```
(Remove `-pooler` from the hostname)

**Why?** The pooler endpoint doesn't handle transactions well during migrations. Use the direct connection.

**After migrations succeed**, you can optionally switch back to pooler for better connection management:
```env
DB_HOST=ep-still-hall-a1gu8tkn-pooler.ap-southeast-1.aws.neon.tech
```

## Steps to Update Render

1. Go to [dashboard.render.com](https://dashboard.render.com)
2. Click your **deli-backend** service
3. Go to **"Environment"** tab
4. Add/Update these variables (copy from above)
5. Click **"Save Changes"**
6. Render will auto-redeploy
7. Check **"Logs"** tab for migration success

## Expected Results

- ✅ Connection successful
- ✅ Migrations run automatically
- ✅ API response time: **200-800ms** (vs 4-8 seconds before!)
- ✅ 5-40x faster than Supabase

