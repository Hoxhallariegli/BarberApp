<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected function getFirebaseConfig(): array
    {
        $projectId = env('FIREBASE_PROJECT_ID', Setting::where('key', 'firebase_project_id')->value('value'));
        $credentialsSource = env('FIREBASE_CREDENTIALS', Setting::where('key', 'firebase_credentials')->value('value'));

        $credentials = null;
        if ($credentialsSource && str_ends_with($credentialsSource, '.json')) {
            $path = base_path($credentialsSource);
            if (file_exists($path)) {
                $credentials = json_decode(file_get_contents($path), true);
            }
        } elseif ($credentialsSource) {
            $credentials = json_decode($credentialsSource, true);
        }

        return [
            'project_id' => $projectId,
            'credentials' => $credentials
        ];
    }

    public function sendNotification(string $title, string $body, string $topic = 'all', array $extraData = []): ?string
    {
        // Prioritizohet .env, nese jo merret nga databaza
        $enabled = env('FIREBASE_ENABLED', Setting::where('key', 'firebase_enabled')->value('value') ?? true);
        if (!$enabled) return null;

        $config = $this->getFirebaseConfig();
        $projectId = $config['project_id'];
        $credentials = $config['credentials'];

        if (!$projectId || !$credentials) {
            Log::warning('Firebase config is missing or invalid.');
            return null;
        }

        try {
            $token = $this->getAccessToken($credentials);
            if (!$token) return null;

            $dataPayload = array_merge([
                'title' => $title,
                'body' => $body,
            ], $extraData);

            $message = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $dataPayload),
                'android' => [
                    'priority' => 'high'
                ]
            ];

            // Nëse inputi 'topic' është në fakt një Token pajisjeje (i gjatë), përdor 'token'
            if (strlen($topic) > 50) {
                $message['token'] = $topic;
            } else {
                $message['topic'] = $topic;
            }

            $response = Http::withToken($token)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => $message
            ]);

            if ($response->successful()) {
                $name = $response->json()['name'] ?? '';
                return str_contains($name, 'messages/') ? explode('messages/', $name)[1] : $name;
            }

            Log::error('Firebase API Response: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendData(string $token, array $data): ?string
    {
        $enabled = env('FIREBASE_ENABLED', Setting::where('key', 'firebase_enabled')->value('value') ?? true);
        if (!$enabled) return null;

        $config = $this->getFirebaseConfig();
        $projectId = $config['project_id'];
        $credentials = $config['credentials'];

        if (!$projectId || !$credentials) return null;

        try {
            $accessToken = $this->getAccessToken($credentials);
            if (!$accessToken) return null;

            $response = Http::withToken($accessToken)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'data' => array_map('strval', $data),
                    'android' => [
                        'priority' => 'high'
                    ]
                ]
            ]);

            if ($response->successful()) {
                $name = $response->json()['name'] ?? '';
                // Emri kthehet ne formatin "projects/my-project/messages/0:12345..."
                // Ne na duhet vetem pjesa pas "messages/"
                return str_contains($name, 'messages/') ? explode('messages/', $name)[1] : $name;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Firebase Data Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gjeneron OAuth2 Token manualisht duke përdorur OpenSSL (pa pasur nevojë për librari të rënda).
     */
    protected function getAccessToken(array $credentials): ?string
    {
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], 'SHA256');
        $base64UrlSignature = $this->base64UrlEncode($signature);

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->json()['access_token'] ?? null;
    }

    protected function base64UrlEncode($data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
