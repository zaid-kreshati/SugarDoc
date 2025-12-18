<?php

namespace App\Services;

use Exception;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $credentials;
    protected $projectId;

    public function __construct()
{
    $this->credentials = '/etc/secrets/firebase_credentials.json';

    if (!file_exists($this->credentials)) {
        throw new Exception('Firebase credential file not found');
    }

    $json = json_decode(file_get_contents($this->credentials), true);
    $this->projectId = $json['project_id'];

    Log::info('Firebase project ID: ' . $this->projectId);
}


    public function sendNotification(string $token, string $title, string $body): bool
    {
        Log::info("Preparing to send notification to token: $token");

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentials);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();

            $accessToken = $client->getAccessToken()['access_token'];


            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'default', // Or use a custom sound uploaded to the app
                            ],
                        ],
                        'data' => [
                            'type' => 'chat_message',
                            'sender_id' => '123',
                            'conversation_id' => '456',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Required for background tap to work
                        ]
                    ]
                ]);


            Log::info('Firebase notification sent successfully', ['response' => $response->json()]);
            return $response->json();
        } catch (Exception $e) {
            Log::error('Firebase sendNotification exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
