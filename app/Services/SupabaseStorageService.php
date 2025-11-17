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
     * @param string|null $errorMessage Reference to store error message
     * @return string|null Public URL or null on failure
     */
    public function upload(string $path, string $content, ?string &$errorMessage = null): ?string
    {
        if (!$this->url || !$this->key) {
            $errorMessage = 'Supabase credentials not configured (missing SUPABASE_URL or SUPABASE_KEY)';
            Log::error('Supabase credentials not configured', [
                'url_set' => !empty($this->url),
                'key_set' => !empty($this->key),
            ]);
            return null;
        }

        try {
            $uploadUrl = "{$this->url}/storage/v1/object/{$this->bucket}/{$path}";
            
            Log::info('Attempting Supabase upload', [
                'url' => $uploadUrl,
                'bucket' => $this->bucket,
                'path' => $path,
                'content_size' => strlen($content),
            ]);
            
            // Try POST first (Supabase Storage prefers POST for uploads)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'image/jpeg',
                'x-upsert' => 'true', // Overwrite if exists
            ])->post($uploadUrl, $content);
            
            // If POST fails with 405, try PUT
            if ($response->status() === 405) {
                Log::info('POST not allowed, trying PUT', ['url' => $uploadUrl]);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->key,
                    'Content-Type' => 'image/jpeg',
                    'x-upsert' => 'true',
                ])->put($uploadUrl, $content);
            }

            if ($response->successful()) {
                // Generate public URL
                $publicUrl = "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
                Log::info('Image uploaded to Supabase successfully', [
                    'path' => $path,
                    'url' => $publicUrl,
                ]);
                return $publicUrl;
            } else {
                $errorBody = $response->body();
                
                // Try to decode JSON, but handle non-JSON responses (binary data, HTML, etc.)
                $errorJson = null;
                $supabaseError = null;
                
                if (!empty($errorBody)) {
                    // Check if response is valid JSON
                    $errorJson = json_decode($errorBody, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($errorJson)) {
                        // Valid JSON response
                        $supabaseError = $errorJson['message'] ?? $errorJson['error'] ?? $errorJson['error_description'] ?? 'Unknown error';
                    } else {
                        // Not JSON - might be binary data, HTML, or plain text
                        // Try to extract readable text (first 200 chars)
                        $readableText = mb_convert_encoding(substr($errorBody, 0, 200), 'UTF-8', 'UTF-8');
                        // Remove non-printable characters
                        $readableText = preg_replace('/[\x00-\x1F\x7F]/', '', $readableText);
                        $supabaseError = !empty($readableText) ? $readableText : 'Non-JSON response received';
                    }
                } else {
                    $supabaseError = 'Empty response from Supabase';
                }
                
                $errorMessage = "HTTP {$response->status()}: {$supabaseError}";
                
                Log::error('Supabase upload failed', [
                    'path' => $path,
                    'bucket' => $this->bucket,
                    'status' => $response->status(),
                    'status_text' => $response->reason(),
                    'response_body_preview' => mb_convert_encoding(substr($errorBody, 0, 500), 'UTF-8', 'UTF-8'),
                    'error_message' => $supabaseError,
                    'upload_url' => $uploadUrl,
                ]);
                return null;
            }
        } catch (\Exception $e) {
            $errorMessage = 'Exception: ' . $e->getMessage();
            Log::error('Supabase upload exception', [
                'path' => $path,
                'bucket' => $this->bucket,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

