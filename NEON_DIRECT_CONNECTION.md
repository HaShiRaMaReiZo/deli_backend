# Neon Direct Connection (For Migrations)

## Your Direct Connection Details

**Direct Connection String (use this for migrations):**
```
postgresql://neondb_owner:npg_EJgS1Ntker2Q@ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech/neondb?sslmode=require
```

**Key difference:** Host is `ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech` (NO `-pooler`)

## Render Environment Variables

Use these in Render → Environment tab:

```env
DB_CONNECTION=pgsql
DB_HOST=ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_EJgS1Ntker2Q
DB_SSLMODE=require
```

## Why Direct Connection?

- ✅ **Works with migrations** (no transaction errors)
- ✅ **Better for Laravel** (handles transactions properly)
- ✅ **Same performance** (for most use cases)

## Pooler vs Direct

| Connection Type | Hostname | Use Case |
|----------------|----------|----------|
| **Direct** (Recommended) | `ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech` | Migrations, Laravel apps |
| Pooler | `ep-still-hall-a1gu8tkn-pooler.ap-southeast-1.aws.neon.tech` | High concurrency (not needed for most apps) |

## Steps to Fix Migration Error

1. **Update Render Environment Variables:**
   - Change `DB_HOST` to direct connection (remove `-pooler`)
   - Save changes

2. **Reset Database in Neon:**
   - Go to Neon Dashboard → SQL Editor
   - Run:
   ```sql
   DROP SCHEMA public CASCADE;
   CREATE SCHEMA public;
   GRANT ALL ON SCHEMA public TO postgres;
   GRANT ALL ON SCHEMA public TO public;
   ```

3. **Redeploy:**
   - Render will auto-redeploy
   - Migrations should succeed now

## Verify Connection

Test in Neon SQL Editor:
```sql
SELECT version();
```

Should return PostgreSQL version.

