# Supabase Storage Setup Guide

Complete guide for setting up Supabase Storage for package image storage with auto-cleanup.

## 📋 Prerequisites

- Supabase account (free tier available)
- Basic understanding of Supabase dashboard

## 🎁 Supabase Free Tier

- **Storage**: 1GB free (enough for ~1500 images with 90-day cleanup)
- **Bandwidth**: 2GB/month
- **API Requests**: Unlimited
- **Never expires!** ✅

## 🚀 Step 1: Create Supabase Account

1. Go to [supabase.com](https://supabase.com)
2. Click **"Start your project"** or **"Sign up"**
3. Sign up with GitHub, Google, or email
4. Create a new organization (if needed)

## 🔧 Step 2: Create New Project

1. Click **"New Project"**
2. Fill in project details:
   - **Name**: `deli-delivery` (or your preferred name)
   - **Database Password**: Create a strong password (save it!)
   - **Region**: Choose closest to your users
   - **Pricing Plan**: Free (perfect for this use case)
3. Click **"Create new project"**
4. Wait 2-3 minutes for project to initialize

## 📦 Step 3: Create Storage Bucket

1. In your Supabase project dashboard, go to **"Storage"** (left sidebar)
2. Click **"New bucket"**
3. Configure bucket:
   - **Name**: `package-images` (must match `SUPABASE_BUCKET` in `.env`)
   - **Public bucket**: ✅ **Enable** (images need to be publicly accessible)
   - **File size limit**: 5MB (or adjust as needed)
   - **Allowed MIME types**: `image/jpeg, image/png, image/jpg` (optional, for security)
4. Click **"Create bucket"**

## 🔐 Step 4: Configure Bucket Policies (Optional but Recommended)

1. Go to **Storage** → Click on your `package-images` bucket
2. Go to **"Policies"** tab
3. Click **"New Policy"**

### Policy 1: Allow Public Read Access

```sql
-- Policy name: Public Read Access
-- Allowed operation: SELECT
-- Policy definition:
(
  bucket_id = 'package-images'
)
```

### Policy 2: Allow Authenticated Upload (if using service role)

```sql
-- Policy name: Authenticated Upload
-- Allowed operation: INSERT
-- Policy definition:
(
  bucket_id = 'package-images'
)
```

**Note**: Since we're using the anon key for uploads, the bucket should be public. The policies above are optional for additional security.

## 🔑 Step 5: Get API Credentials

1. Go to **"Settings"** (gear icon) → **"API"**
2. Find these values:
   - **Project URL**: `https://xxxxx.supabase.co` (copy this)
   - **anon/public key**: `eyJhbGc...` (copy this - starts with `eyJ`)
3. **Important**: Use the **anon/public key**, NOT the service_role key

## ⚙️ Step 6: Configure Laravel Environment

Add these variables to your `.env` file (or Render Environment Variables):

```env
# Supabase Storage Configuration
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_KEY=your-anon-public-key-here
SUPABASE_BUCKET=package-images
```

### Replace:
- `your-project-id.supabase.co` → Your Project URL from Step 5
- `your-anon-public-key-here` → Your anon/public key from Step 5
- `package-images` → Your bucket name (must match exactly)

### Example:
```env
SUPABASE_URL=https://abcdefghijklmnop.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFiY2RlZmdoaWprbG1ub3AiLCJyb2xlIjoiYW5vbiIsImlhdCI6MTYxNjIzOTAyMiwiZXhwIjoxOTMxODE1MDIyfQ.example
SUPABASE_BUCKET=package-images
```

## ✅ Step 7: Test the Setup

1. Deploy your application
2. Upload a package with an image via the merchant app
3. Check your Supabase Storage:
   - Go to **Storage** → `package-images` bucket
   - You should see the uploaded image in `package_images/` folder
4. Click the image URL in the API response - it should display the image

## 🧹 Step 8: Setup Auto-Cleanup (Already Configured!)

The cleanup command is already scheduled to run daily at 2 AM UTC. It will:
- Delete images older than 90 days
- Keep only ~1500 images (~300MB)
- Fit within Supabase free tier forever

### Manual Cleanup (Optional):

You can run cleanup manually:
```bash
php artisan images:cleanup --days=90
```

### Change Cleanup Days:

Edit `bootstrap/app.php`:
```php
$schedule->command('images:cleanup --days=90')  // Change 90 to your preferred days
```

## 🔍 Troubleshooting

### Images not uploading:

1. **Check Supabase credentials**:
   - Verify `SUPABASE_URL` is correct (no trailing slash)
   - Verify `SUPABASE_KEY` is the anon/public key (not service_role)
   - Verify `SUPABASE_BUCKET` matches bucket name exactly

2. **Check bucket exists**:
   - Go to Supabase dashboard → Storage
   - Verify `package-images` bucket exists
   - Verify bucket is **public**

3. **Check bucket permissions**:
   - Bucket must be public for images to be accessible
   - Go to bucket settings → Ensure "Public bucket" is enabled

4. **Check Laravel logs**:
   ```bash
   # Check logs for Supabase errors
   tail -f storage/logs/laravel.log
   ```

### Images not accessible:

1. **Check bucket is public**: Must be enabled in bucket settings
2. **Check URL format**: Should be `https://xxx.supabase.co/storage/v1/object/public/package-images/package_images/filename.jpg`
3. **Check CORS** (if accessing from web):
   - Go to Supabase → Settings → API
   - Add your domain to allowed origins

### 403 Forbidden errors:

- Verify bucket is public
- Verify you're using anon/public key (not service_role)
- Check bucket policies allow public read

### 401 Unauthorized errors:

- Verify `SUPABASE_KEY` is correct
- Ensure you're using anon/public key
- Check key hasn't been rotated in Supabase dashboard

## 💰 Cost Estimation

### With 90-Day Auto-Cleanup:

**Monthly usage**:
- ~500 images uploaded/month
- ~1500 images stored (90 days × ~500/30)
- Average image size: 200KB
- **Total storage**: ~300MB/month
- **Bandwidth**: ~100MB/month (image views)

**Within Free Tier**:
- ✅ Storage: 300MB < 1GB (FREE)
- ✅ Bandwidth: 100MB < 2GB (FREE)
- **Total: $0/month FOREVER!** 🎉

### If you exceed free tier:

- Storage: $0.021/GB/month (after 1GB)
- Bandwidth: $0.09/GB (after 2GB)
- Very affordable even if you exceed!

## 📊 Storage Calculation

**Without cleanup** (1 year):
- 500 images/month × 12 = 6,000 images
- 6,000 × 200KB = 1.2GB → **Exceeds free tier!**

**With 90-day cleanup**:
- ~1500 images stored (90 days)
- 1500 × 200KB = 300MB → **Fits in free tier!** ✅

## 🔒 Security Best Practices

1. **Never commit credentials** to Git
2. **Use anon/public key** (not service_role) for client-side
3. **Enable bucket policies** for additional security
4. **Set MIME type restrictions** in bucket settings
5. **Monitor storage usage** in Supabase dashboard
6. **Rotate keys** if compromised (in Supabase Settings → API)

## 📝 Notes

- Images are stored permanently until cleanup (90 days)
- URLs are publicly accessible (as needed for web/mobile apps)
- Auto-cleanup runs daily at 2 AM UTC
- Manual cleanup available via `php artisan images:cleanup`
- Storage fits within free tier with 90-day cleanup strategy

## 🎯 Next Steps

After setup:
1. Test image upload from merchant app
2. Verify images appear in Supabase Storage
3. Check image URLs work in web/mobile apps
4. Monitor storage usage in Supabase dashboard
5. Adjust cleanup days if needed (in `bootstrap/app.php`)

## 🔄 Switching from Local/S3 Storage

If you were using local or S3 storage before:
1. Old images will remain in old storage
2. New images will go to Supabase
3. Old images will be accessible until they expire
4. No migration needed - new uploads use Supabase automatically

---

**Need help?** Check [Supabase Storage Docs](https://supabase.com/docs/guides/storage) or Laravel logs.

