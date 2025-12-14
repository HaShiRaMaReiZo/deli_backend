# Fix Supabase Image Upload DNS Error

## Error

```
cURL error 6: Could not resolve host: rpxvqvieukyaijpdindg.supabase.co
```

This is a **DNS resolution error** - Render cannot resolve the Supabase hostname.

## Causes

1. **Missing/Incorrect SUPABASE_URL** in Render environment variables
2. **Supabase project deleted** or URL changed
3. **Network/DNS issues** from Render

## Solution

### Step 1: Check Supabase Configuration in Render

1. Go to **Render Dashboard** → Your service → **Environment** tab
2. Check if these variables exist:
   ```env
   SUPABASE_URL=https://your-project-id.supabase.co
   SUPABASE_KEY=your-anon-or-service-role-key
   SUPABASE_BUCKET=package-images
   ```

### Step 2: Get Correct Supabase URL

1. Go to [Supabase Dashboard](https://app.supabase.com)
2. Select your project
3. Go to **Settings** → **API**
4. Copy:
   - **Project URL**: `https://xxxxx.supabase.co` (this is your `SUPABASE_URL`)
   - **anon public key** or **service_role key** (this is your `SUPABASE_KEY`)

### Step 3: Update Render Environment Variables

**In Render → Environment tab, set:**

```env
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_BUCKET=package-images
```

**Important:**
- ✅ URL should start with `https://`
- ✅ URL should NOT have trailing slash
- ✅ Use `service_role` key for uploads (bypasses RLS) or `anon` key
- ✅ Bucket name must match exactly (case-sensitive)

### Step 4: Verify Supabase Storage Bucket

1. Go to Supabase Dashboard → **Storage**
2. Check if bucket `package-images` exists
3. If not, create it:
   - Name: `package-images`
   - Public: ✅ Yes (for public access)
   - File size limit: 5MB (or your preference)

### Step 5: Test Connection

After updating environment variables:

1. **Save changes** in Render (will auto-redeploy)
2. **Test image upload** from your Flutter app
3. **Check Render logs** for any errors

## Alternative: Disable Image Upload Temporarily

If Supabase is not available, you can temporarily disable image uploads:

**In Render → Environment:**
```env
SUPABASE_URL=
SUPABASE_KEY=
```

The app will still work, but images won't be uploaded (will show error in response but package will be saved).

## Troubleshooting

### Still Getting DNS Error?

1. **Verify URL format:**
   - ✅ `https://xxxxx.supabase.co` (correct)
   - ❌ `https://xxxxx.supabase.co/` (trailing slash - remove it)
   - ❌ `xxxxx.supabase.co` (missing https://)

2. **Check Supabase project status:**
   - Go to Supabase dashboard
   - Verify project is active (not paused/deleted)

3. **Test DNS resolution:**
   - From your local machine: `ping your-project-id.supabase.co`
   - Should resolve to an IP address

4. **Check Render network:**
   - Render free tier might have network restrictions
   - Try upgrading to paid plan if issue persists

### Image Upload Works But Shows Error?

The package is saved successfully, but image upload failed. Check:
- `image_upload_errors` in API response
- Render logs for specific error
- Supabase Storage bucket permissions

## Expected Behavior

**After fixing:**
- ✅ Image uploads successfully
- ✅ Package saved with image URL
- ✅ No DNS errors in logs
- ✅ Image accessible via public URL

## Quick Fix Checklist

- [ ] `SUPABASE_URL` is set in Render (no trailing slash)
- [ ] `SUPABASE_KEY` is set in Render (valid key)
- [ ] `SUPABASE_BUCKET=package-images` is set
- [ ] Supabase project is active
- [ ] Storage bucket `package-images` exists and is public
- [ ] Render service redeployed after changes

