# Fix Neon Migration Error

## Problem

You're getting this error:
```
SQLSTATE[25P02]: In failed sql transaction: 7 ERROR: current transaction is aborted
```

This happens because the **pooler connection** doesn't handle migrations well.

## Solution 1: Use Direct Connection (Recommended)

### Step 1: Update Render Environment Variables

Go to Render → Environment tab and change:

**FROM (pooler - causes errors):**
```env
DB_HOST=ep-still-hall-a1gu8tkn-pooler.ap-southeast-1.aws.neon.tech
```

**TO (direct - works for migrations):**
```env
DB_HOST=ep-still-hall-a1gu8tkn.ap-southeast-1.aws.neon.tech
```

(Just remove `-pooler` from the hostname)

### Step 2: Reset Database in Neon (if needed)

If migrations partially ran, you need to clean up:

1. Go to **Neon Dashboard** → Your project
2. Click **"SQL Editor"** (left sidebar)
3. Run this to drop all tables:

```sql
-- Drop all tables (be careful!)
DROP SCHEMA public CASCADE;
CREATE SCHEMA public;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO public;
```

4. Click **"Run"**

### Step 3: Redeploy on Render

1. Save changes in Render
2. Render will auto-redeploy
3. Migrations should run successfully now

## Solution 2: Manual Migration via Neon SQL Editor

If automatic migrations still fail:

1. Go to **Neon Dashboard** → **SQL Editor**
2. Copy your migration SQL and run it manually
3. Or use Laravel's migration SQL output

## Why This Happens

- **Pooler connection** (`-pooler`): Good for many connections, but has transaction limitations
- **Direct connection** (no `-pooler`): Better for migrations and transactions

## After Migrations Succeed

Once migrations work, you can:
- Keep using direct connection (simpler, works great)
- Or switch back to pooler if you have many concurrent connections

## Verify It Works

1. Check Render logs - should see "Migrations completed successfully"
2. Test API endpoints
3. Check Neon dashboard → Tables - should see all your tables

