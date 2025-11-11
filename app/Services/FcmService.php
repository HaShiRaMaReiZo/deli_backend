<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private ?string $credentialsPath = null;
    private ?array $credentials = null;
    private ?string $accessToken = null;
    private ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $this->credentialsPath = config('services.firebase.credentials_path');
        if ($this->credentialsPath && file_exists($this->credentialsPath)) {
            $this->credentials = json_decode(file_get_contents($this->credentialsPath), true);
        }
    }

    /**
     * Get access token for FCM API
     */
    private function getAccessToken(): ?string
    {
        if (!$this->credentials) {
            Log::error('Firebase credentials not found');
            return null;
        }

        // Check if we have a valid cached token
        if ($this->accessToken && $this->tokenExpiresAt && time() < $this->tokenExpiresAt - 60) {
            return $this->accessToken;
        }

        try {
            // Request access token from Google OAuth2
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->createJWT(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                $this->tokenExpiresAt = time() + $data['expires_in'];
                return $this->accessToken;
            }

            Log::error('Failed to get FCM access token', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting FCM access token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create JWT for service account authentication
     */
    private function createJWT(): string
    {
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;

        $privateKey = openssl_pkey_get_private($this->credentials['private_key']);
        openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        $base64UrlSignature = $this->base64UrlEncode($signature);
        return $signatureInput . '.' . $base64UrlSignature;
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send notification to a single FCM token
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $projectId = $this->credentials['project_id'] ?? config('services.firebase.project_id');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post($url, $message);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to send FCM notification', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error sending FCM notification', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send notification to a user (all their devices)
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $tokens = DB::table('fcm_tokens')
            ->where('user_id', $userId)
            ->pluck('fcm_token')
            ->toArray();

        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $tokens = DB::table('fcm_tokens')
            ->whereIn('user_id', $userIds)
            ->pluck('fcm_token')
            ->toArray();

        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }
}

