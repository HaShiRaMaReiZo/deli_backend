<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_KEY');
        $this->bucket = env('SUPABASE_BUCKET', 'package-images');
    }

    /**
     * Upload image to Supabase Storage
     *
     * @param string $path File path (e.g., 'package_images/filename.jpg')
     * @param string $content Image content (binary data)
     * @return string|null Public URL or null on failure
     */
    public function upload(string $path, string $content): ?string
    {
        if (!$this->url || !$this->key) {
            Log::error('Supabase credentials not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'image/jpeg',
                'x-upsert' => 'true', // Overwrite if exists
            ])->put(
                "{$this->url}/storage/v1/object/{$this->bucket}/{$path}",
                $content
            );

            if ($response->successful()) {
                // Generate public URL
                $publicUrl = "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
                Log::info('Image uploaded to Supabase', [
                    'path' => $path,
                    'url' => $publicUrl,
                ]);
                return $publicUrl;
            } else {
                Log::error('Supabase upload failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Supabase upload exception', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete image from Supabase Storage
     *
     * @param string $path File path
     * @return bool Success status
     */
    public function delete(string $path): bool
    {
        if (!$this->url || !$this->key) {
            Log::error('Supabase credentials not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
            ])->delete(
                "{$this->url}/storage/v1/object/{$this->bucket}/{$path}"
            );

            if ($response->successful()) {
                Log::info('Image deleted from Supabase', ['path' => $path]);
                return true;
            } else {
                Log::warning('Supabase delete failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Supabase delete exception', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Extract path from Supabase URL
     *
     * @param string $url Full Supabase URL
     * @return string|null Path or null if invalid
     */
    public function extractPathFromUrl(string $url): ?string
    {
        // Extract path from URL like: https://xxx.supabase.co/storage/v1/object/public/bucket/path
        // Or: https://xxx.supabase.co/storage/v1/object/public/package-images/package_images/filename.jpg
        $pattern = '/\/storage\/v1\/object\/public\/' . preg_quote($this->bucket, '/') . '\/(.+)$/';
        if (preg_match($pattern, $url, $matches)) {
            return urldecode($matches[1]); // Decode URL-encoded characters
        }
        
        // Try alternative pattern without bucket name (if bucket is in path)
        $pattern2 = '/\/storage\/v1\/object\/public\/[^\/]+\/(.+)$/';
        if (preg_match($pattern2, $url, $matches)) {
            return urldecode($matches[1]);
        }
        
        return null;
    }
}

