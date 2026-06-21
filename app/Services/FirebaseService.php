<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $credentialsPath;
    protected ?string $projectId = null;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-service-account.json');
        
        if (file_exists($this->credentialsPath)) {
            $json = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $json['project_id'] ?? null;
        }
    }

    /**
     * Get OAuth2 Access Token for Firebase Messaging.
     */
    protected function getAccessToken(): ?string
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error("Firebase credentials file not found at: {$this->credentialsPath}");
            return null;
        }

        try {
            $client = new Client();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $token = $client->getAccessToken();
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error("Failed to generate Firebase Access Token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send push notification using FCM HTTP v1 API.
     */
    public function sendPush(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!$this->projectId) {
            Log::error("Firebase Project ID is missing.");
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // Convert all data array values to strings as required by FCM
        $formattedData = [];
        foreach ($data as $key => $value) {
            $formattedData[$key] = (string) $value;
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $formattedData,
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("FCM push notification sent successfully to token: {$fcmToken}");
                return true;
            } else {
                Log::error("FCM API error response: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send FCM push notification: " . $e->getMessage());
            return false;
        }
    }
}
