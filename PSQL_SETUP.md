# PostgreSQL Client (psql) Setup Guide for Windows

This guide will help you install and connect to your Render PostgreSQL database using `psql` command-line tool.

## Table of Contents
1. [Installation Methods](#installation-methods)
2. [Connect to Render PostgreSQL](#connect-to-render-postgresql)
3. [Basic psql Commands](#basic-psql-commands)
4. [Check Your Data](#check-your-data)
5. [Alternative Methods](#alternative-methods)

---

## Installation Methods

### Method 1: Install PostgreSQL (Recommended - Full Package)

**Step 1: Download PostgreSQL**
1. Go to https://www.postgresql.org/download/windows/
2. Click **"Download the installer"**
3. Download **PostgreSQL 15** or **16** (latest stable version)
4. Run the installer (e.g., `postgresql-16.x-windows-x64.exe`)

**Step 2: Install PostgreSQL**
1. Click **"Next"** through the setup wizard
2. **Installation Directory**: Keep default (`C:\Program Files\PostgreSQL\16`)
3. **Select Components**: Make sure **"Command Line Tools"** is checked ✅
4. **Data Directory**: Keep default
5. **Password**: Set a password for the `postgres` superuser (remember this!)
6. **Port**: Keep default `5432`
7. **Locale**: Keep default
8. Click **"Next"** → **"Next"** → **"Install"**
9. Wait for installation to complete (5-10 minutes)
10. **Uncheck** "Launch Stack Builder" and click **"Finish"**

**Step 3: Verify Installation**
1. Open **PowerShell** or **Command Prompt**
2. Run:
   ```powershell
   psql --version
   ```
3. You should see: `psql (PostgreSQL) 16.x`

**If `psql` is not found:**
- Add PostgreSQL to PATH:
  1. Press `Win + R`, type `sysdm.cpl`, press Enter
  2. Go to **"Advanced"** tab → **"Environment Variables"**
  3. Under **"System variables"**, find **"Path"** → Click **"Edit"**
  4. Click **"New"** and add:
     ```
     C:\Program Files\PostgreSQL\16\bin
     ```
  5. Click **"OK"** on all dialogs
  6. **Close and reopen** PowerShell/CMD
  7. Run `psql --version` again

---

### Method 2: Install Only psql (Standalone)

**Option A: Using Chocolatey (if you have it)**
```powershell
choco install postgresql --params '/Password:yourpassword'
```

**Option B: Using Scoop (if you have it)**
```powershell
scoop install postgresql
```

**Option C: Download Portable Version**
1. Go to https://www.enterprisedb.com/download-postgresql-binaries
2. Download PostgreSQL binaries for Windows
3. Extract to a folder (e.g., `C:\postgresql`)
4. Add `C:\postgresql\bin` to your PATH

---

## Connect to Render PostgreSQL

### Step 1: Get Your Database Connection String

1. Go to https://dashboard.render.com
2. Click on your **PostgreSQL database** service
3. Find **"Connection Pooling"** or **"Internal Database URL"** section
4. Copy the **Internal Database URL** (format: `postgresql://user:password@host:port/database`)

**Example:**
```
postgresql://deli_db_9m02_user:4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4@dpg-d49foru3jp1c73d2aph0-a/deli_db_9m02
```

### Step 2: Connect Using psql

**Option A: Using Full Connection String**
```powershell
psql "postgresql://deli_db_9m02_user:4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4@dpg-d49foru3jp1c73d2aph0-a:5432/deli_db_9m02"
```

**Option B: Using Individual Parameters**
```powershell
psql -h dpg-d49foru3jp1c73d2aph0-a -p 5432 -U deli_db_9m02_user -d deli_db_9m02
```
(It will prompt for password: `4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4`)

**Option C: Using Environment Variable (Recommended)**
1. Set environment variable:
   ```powershell
   $env:PGPASSWORD="4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4"
   psql -h dpg-d49foru3jp1c73d2aph0-a -p 5432 -U deli_db_9m02_user -d deli_db_9m02
   ```

**Note:** Replace the connection details with your actual Render database credentials!

---

## Basic psql Commands

Once connected, you'll see a prompt like:
```
deli_db_9m02=>
```

### Navigation Commands

```sql
-- List all tables
\dt

-- List all tables with details
\dt+

-- Describe a table structure
\d users
\d packages

-- List all databases
\l

-- Show current database
SELECT current_database();

-- Exit psql
\q
```

### Query Commands

```sql
-- Check if users table exists and count records
SELECT COUNT(*) FROM users;

-- View all users
SELECT id, name, email, role, status FROM users;

-- View all users (full details)
SELECT * FROM users;

-- Check packages
SELECT COUNT(*) FROM packages;

-- View packages
SELECT id, tracking_number, status, created_at FROM packages LIMIT 10;

-- Check specific user
SELECT * FROM users WHERE email = 'erickboyle@superadmin.com';
```

---

## Check Your Data

### 1. Check if Users Were Created

```sql
-- Connect to database first, then run:
SELECT id, name, email, role, status, created_at 
FROM users 
ORDER BY created_at;
```

**Expected Output:**
```
 id |     name      |              email               |     role      | status |      created_at
----+---------------+----------------------------------+---------------+--------+---------------------
  1 | Super Admin   | erickboyle@superadmin.com       | super_admin   | active | 2025-11-11 10:00:00
  2 | Office Manager| manager@delivery.com            | office_manager| active | 2025-11-11 10:00:00
  3 | Office Staff  | staff@delivery.com              | office_staff  | active | 2025-11-11 10:00:00
```

### 2. Check All Tables

```sql
\dt
```

**Expected Output:**
```
                    List of relations
 Schema |           Name            | Type  |  Owner
--------+---------------------------+-------+------------------
 public | migrations                | table | deli_db_9m02_user
 public | users                     | table | deli_db_9m02_user
 public | packages                  | table | deli_db_9m02_user
 public | merchants                 | table | deli_db_9m02_user
 ... (other tables)
```

### 3. Check Package Statuses

```sql
SELECT status, COUNT(*) as count 
FROM packages 
GROUP BY status;
```

---

## Alternative Methods

### Method 1: Use pgAdmin (GUI Tool)

**Install pgAdmin:**
1. During PostgreSQL installation, pgAdmin is included
2. Or download separately: https://www.pgadmin.org/download/
3. Open **pgAdmin 4**
4. Right-click **"Servers"** → **"Create"** → **"Server"**
5. **General** tab:
   - Name: `Render PostgreSQL`
6. **Connection** tab:
   - Host: `dpg-d49foru3jp1c73d2aph0-a`
   - Port: `5432`
   - Database: `deli_db_9m02`
   - Username: `deli_db_9m02_user`
   - Password: `4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4`
7. Click **"Save"**
8. Browse tables and run queries visually

### Method 2: Use DBeaver (Free Universal Database Tool)

1. Download: https://dbeaver.io/download/
2. Install DBeaver Community Edition (free)
3. Open DBeaver → **"New Database Connection"**
4. Select **"PostgreSQL"**
5. Enter connection details:
   - Host: `dpg-d49foru3jp1c73d2aph0-a`
   - Port: `5432`
   - Database: `deli_db_9m02`
   - Username: `deli_db_9m02_user`
   - Password: `4zhr1GtO9dQ9FqTiY50KqvELjnYtUiF4`
6. Click **"Test Connection"** → **"Finish"**
7. Browse and query your database

### Method 3: Use Laravel Tinker (Via Your App)

If you can't install psql, you can check data via your Laravel app:

1. Create a route in `routes/web.php`:
   ```php
   Route::get('/check-db', function() {
       $users = DB::table('users')->get();
       $packages = DB::table('packages')->count();
       return [
           'users_count' => $users->count(),
           'users' => $users->map(function($user) {
               return [
                   'id' => $user->id,
                   'name' => $user->name,
                   'email' => $user->email,
                   'role' => $user->role,
               ];
           }),
           'packages_count' => $packages
       ];
   });
   ```

2. Visit: `https://your-app.onrender.com/check-db`

### Method 4: Use Render Shell (If Available)

1. Go to your PostgreSQL database in Render dashboard
2. Click **"Shell"** tab (if available on your plan)
3. Run:
   ```bash
   psql $DATABASE_URL
   ```
4. Then run SQL queries

---

## Troubleshooting

### "psql: command not found"
- **Solution**: Add PostgreSQL bin directory to PATH (see Method 1, Step 3)

### "Connection refused" or "Timeout"
- **Solution**: 
  - Check if you're using the **Internal Database URL** (only works from Render services)
  - For external access, you may need to use **External Connection String** from Render
  - Check if your IP is whitelisted (if required by Render)

### "Password authentication failed"
- **Solution**: 
  - Double-check your password in Render dashboard
  - Make sure there are no extra spaces in the connection string
  - Try resetting the database password in Render

### "Database does not exist"
- **Solution**: 
  - Verify database name in Render dashboard
  - Check if migrations ran successfully (check Render logs)

---

## Quick Reference

**Connect:**
```powershell
psql "postgresql://user:password@host:port/database"
```

**Check users:**
```sql
SELECT * FROM users;
```

**Check tables:**
```sql
\dt
```

**Exit:**
```sql
\q
```

---

## Next Steps

After verifying your data:
1. ✅ Check if users exist (should be 3 users from seeder)
2. ✅ Check if tables are created (migrations ran)
3. ✅ Try logging in to your app with: `erickboyle@superadmin.com` / `erick2004`
4. ✅ If users don't exist, run the seeder manually (see DEPLOYMENT.md)

