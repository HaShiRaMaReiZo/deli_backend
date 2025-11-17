# How to Add psql to PATH (Windows)

Quick guide to add PostgreSQL's `psql` command to your Windows PATH so you can use it from anywhere.

## Method 1: Using PowerShell (Quick - Current Session Only)

Open PowerShell and run:

```powershell
$env:Path += ";C:\Program Files\PostgreSQL\18\bin"
```

**Note**: This only works for the current PowerShell session. Close and reopen PowerShell to lose it.

## Method 2: Add to PATH Permanently (Recommended)

### Step 1: Open Environment Variables

1. Press `Win + R`
2. Type: `sysdm.cpl`
3. Press Enter
4. Click **"Advanced"** tab
5. Click **"Environment Variables"** button

### Step 2: Edit PATH

1. Under **"System variables"** (bottom section), find **"Path"**
2. Click **"Edit"**
3. Click **"New"**
4. Add this path:
   ```
   C:\Program Files\PostgreSQL\18\bin
   ```
5. Click **"OK"** on all dialogs

### Step 3: Restart PowerShell

1. **Close** all PowerShell/CMD windows
2. **Open a new** PowerShell window
3. Test: `psql --version`

You should now see: `psql (PostgreSQL) 18.x`

## Method 3: Use Full Path (No PATH Changes Needed)

You can always use the full path directly:

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" "your-connection-string"
```

## Verify It Works

After adding to PATH, test with:

```powershell
psql --version
```

You should see the version number.

---

**Note**: If your PostgreSQL version is different (like 16 or 17), replace `18` with your version number in the path.

