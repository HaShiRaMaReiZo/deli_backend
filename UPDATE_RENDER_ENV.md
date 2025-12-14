# Update Render Environment Variables for Neon

## Step-by-Step Instructions

### 1. Go to Render Dashboard

1. Open [dashboard.render.com](https://dashboard.render.com)
2. Click on your **deli-backend** service

### 2. Open Environment Variables

1. Click on **"Environment"** tab (left sidebar)
2. You'll see all your current environment variables

### 3. Update Database Variables

Find and update these variables (or add them if they don't exist):

```env
DB_CONNECTION=pgsql
DB_HOST=ep-xxxxx.region.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=your-neon-username
DB_PASSWORD=your-neon-password
DB_SSLMODE=require
```

**Replace with your actual Neon values:**
- `DB_HOST` = Your Neon host (e.g., `ep-xxxxx.us-east-2.aws.neon.tech`)
- `DB_DATABASE` = Your database name (usually `neondb`)
- `DB_USERNAME` = Your Neon username
- `DB_PASSWORD` = Your Neon password (copy from Neon dashboard)

### 4. Remove Old Supabase Variables (if any)

If you have these old Supabase variables, you can remove them:
- Old `DB_HOST` (if it was Supabase)
- Any Supabase-specific variables

### 5. Save Changes

1. Click **"Save Changes"** button
2. Render will automatically redeploy your service

### 6. Check Deployment Logs

1. Go to **"Logs"** tab
2. Watch for:
   - ✅ "Running database migrations..."
   - ✅ "Migrations completed successfully"
   - ❌ Any connection errors

### 7. Test Your API

Once deployed, test:
```
https://your-app.onrender.com/api/auth/login
```

Response time should be **200-800ms** (much faster than before!)

## 🔧 Troubleshooting

### Connection Error in Logs
- Double-check `DB_HOST` format (should be `ep-xxxxx.region.aws.neon.tech`)
- Verify `DB_PASSWORD` is correct (copy from Neon)
- Ensure `DB_SSLMODE=require`

### Still Using Old Database
- Make sure you saved changes in Render
- Check that `DB_CONNECTION=pgsql` is set
- Verify all variables are correct

### Migration Errors
- Check Neon dashboard to ensure database is active
- Verify connection details are correct
- Check Render logs for specific error messages

