# How to Update Package Status in Render PostgreSQL

Quick guide to change package status from `ready_for_delivery` back to `registered` in Render's PostgreSQL database.

## Method 1: Using psql (Command Line) - Recommended

### Step 1: Get Database Connection Info

1. Go to Render Dashboard → Your PostgreSQL Database
2. Click on **"Info"** tab
3. You'll see:
   - **Hostname**: `dpg-d49foru3jp1c73d2aph0-a`
   - **Port**: `5432`
   - **Database**: `deli_db_9m02`
   - **Username**: `deli_db_9m02_user`
   - **Password**: (click eye icon to reveal)
   - **Internal Database URL**: (use this if connecting from Render)

### Step 2: Connect Using psql

**If you have psql installed locally:**

```bash
psql "postgresql://deli_db_9m02_user:YOUR_PASSWORD@dpg-d49foru3jp1c73d2aph0-a:5432/deli_db_9m02"
```

**Or use the Internal Database URL from Render:**

```bash
psql "postgresql://deli_db_9m02_user:YOUR_PASSWORD@dpg-d49foru3jp1c73d2aph0-a/deli_db_9m02"
```

### Step 3: Update Package Status

Once connected, run these SQL commands:

```sql
-- First, find your package (replace TRACKING_CODE with actual tracking code)
SELECT id, tracking_code, status FROM packages WHERE tracking_code = 'DELI-20251113-PZIND3';

-- Update status to 'registered'
UPDATE packages 
SET status = 'registered', 
    current_rider_id = NULL,
    assigned_at = NULL
WHERE tracking_code = 'DELI-20251113-PZIND3';

-- Verify the change
SELECT id, tracking_code, status FROM packages WHERE tracking_code = 'DELI-20251113-PZIND3';

-- Exit psql
\q
```

## Method 2: Using Web-Based PostgreSQL Client

### Option A: DBeaver (Free, Easy)

1. **Download DBeaver**: https://dbeaver.io/download/
2. **Install** and open DBeaver
3. **Create New Connection**:
   - Click "New Database Connection"
   - Select **PostgreSQL**
   - Fill in:
     - **Host**: `dpg-d49foru3jp1c73d2aph0-a`
     - **Port**: `5432`
     - **Database**: `deli_db_9m02`
     - **Username**: `deli_db_9m02_user`
     - **Password**: (your password)
   - Click **"Test Connection"** → **"Finish"**
4. **Update Package**:
   - Navigate to: `deli_db_9m02` → `Schemas` → `public` → `Tables` → `packages`
   - Right-click → **"Edit Data"**
   - Find your package by `tracking_code`
   - Change `status` to `registered`
   - Change `current_rider_id` to `NULL`
   - Click **"Save"**

### Option B: pgAdmin (Official PostgreSQL Tool)

1. **Download pgAdmin**: https://www.pgadmin.org/download/
2. **Install** and open pgAdmin
3. **Add Server**:
   - Right-click "Servers" → "Register" → "Server"
   - **General** tab:
     - Name: `Render Deli Database`
   - **Connection** tab:
     - Host: `dpg-d49foru3jp1c73d2aph0-a`
     - Port: `5432`
     - Database: `deli_db_9m02`
     - Username: `deli_db_9m02_user`
     - Password: (your password)
   - Click **"Save"**
4. **Update Package**:
   - Navigate to: `Render Deli Database` → `Databases` → `deli_db_9m02` → `Schemas` → `public` → `Tables` → `packages`
   - Right-click → **"View/Edit Data"** → **"All Rows"**
   - Find your package and edit the `status` field
   - Click **"Save"**

## Method 3: Using API Endpoint (If Available)

If you have access to the web app with admin privileges:

1. Go to your web app: `https://ok-delivery.onrender.com/office/packages`
2. Find the package
3. Use the status update feature (if available)

**Note**: The office API endpoint might not allow changing to `registered` status. In that case, use Method 1 or 2.

## Method 4: Create Temporary Admin Route (Quick Fix)

Add this temporary route to update status via browser:

**File**: `deli_backend/routes/web.php`

```php
// TEMPORARY: Update package status (REMOVE AFTER USE)
Route::get('/admin/update-package-status/{trackingCode}/{status}', function ($trackingCode, $status) {
    $package = \App\Models\Package::where('tracking_code', $trackingCode)->first();
    
    if (!$package) {
        return "Package not found!";
    }
    
    $package->status = $status;
    if ($status === 'registered') {
        $package->current_rider_id = null;
        $package->assigned_at = null;
    }
    $package->save();
    
    return "Package {$trackingCode} status updated to: {$status}";
})->middleware('auth');
```

**Usage**:
1. Log in to your web app
2. Visit: `https://ok-delivery.onrender.com/admin/update-package-status/DELI-20251113-PZIND3/registered`
3. **Remove this route after use!**

## Quick SQL Commands Reference

```sql
-- Find package by tracking code
SELECT * FROM packages WHERE tracking_code = 'DELI-20251113-PZIND3';

-- Update to registered
UPDATE packages 
SET status = 'registered', 
    current_rider_id = NULL,
    assigned_at = NULL
WHERE tracking_code = 'DELI-20251113-PZIND3';

-- Update by ID (if you know the ID)
UPDATE packages 
SET status = 'registered', 
    current_rider_id = NULL,
    assigned_at = NULL
WHERE id = 3;

-- Check all statuses
SELECT DISTINCT status FROM packages;

-- See all packages with their status
SELECT id, tracking_code, status, current_rider_id FROM packages ORDER BY created_at DESC;
```

## Important Notes

1. **Always backup** before making changes (Render has automatic backups)
2. **Clear rider assignment**: When changing to `registered`, also set `current_rider_id = NULL`
3. **Check status history**: The change will be logged in `package_status_histories` table
4. **Valid statuses**: `registered`, `arrived_at_office`, `assigned_to_rider`, `ready_for_delivery`, `on_the_way`, `delivered`, etc.

## Troubleshooting

### Can't connect to database:
- Check if database is running in Render dashboard
- Verify credentials are correct
- Try using Internal Database URL if connecting from Render service

### Permission denied:
- Make sure you're using the correct username/password
- Check if your IP is allowed (Render free tier allows all IPs)

### Package not found:
- Double-check the tracking code spelling
- Use `SELECT * FROM packages;` to see all packages

---

**Recommended**: Use **Method 1 (psql)** or **Method 2 (DBeaver)** for the most reliable way to update data.

