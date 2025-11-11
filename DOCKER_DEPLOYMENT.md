# Quick Docker Deployment Guide for Render

Since Render doesn't have PHP as a direct runtime option, we'll use **Docker**.

## Steps:

1. **Select Docker as Language**:
   - In the Render form, select **"Docker"** from the Language dropdown

2. **Leave Build/Start Commands Empty**:
   - **Build Command**: Leave empty (Docker handles it)
   - **Start Command**: Leave empty (Docker handles it)

3. **Set Root Directory**:
   - **Root Directory**: `deli_backend`

4. **Push Dockerfile to Repository**:
   - Make sure the `Dockerfile` is committed and pushed to your repo
   - The Dockerfile is already created in `deli_backend/Dockerfile`

5. **Deploy**:
   - Click "Deploy Web Service"
   - Render will automatically:
     - Build the Docker image
     - Run the container
     - Start your Laravel app

## What the Dockerfile Does:

- Uses PHP 8.2
- Installs PostgreSQL support
- Installs Composer
- Copies your Laravel app
- Sets proper permissions
- Installs dependencies
- Starts Laravel's built-in server

## Important:

- Make sure `Dockerfile` is in the `deli_backend` directory
- The Dockerfile will automatically use Render's `$PORT` environment variable
- All environment variables you set in Render will be available to the container

