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
    
        $this->projectId = '/etc/secrets/firebase.project_id';
       
        $this->credentials = '/etc/secrets/firebase_credentials.json' ;
        Log::info('Firebase credentials path: ' . $this->credentials);

        if (!file_exists($this->credentials)) {
            Log::error('Firebase credential file not found: ' . $this->credentials);
            throw new Exception('Firebase credential file not found: ' . $this->credentials);
        }

        $json = json_decode(file_get_contents($this->credentials), true);

       
    }

    public function sendNotification(string $token, string $title, string $body): array
    {
        Log::info("Preparing to send notification to token: $token");

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentials);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();

            $accessToken = $client->getAccessToken()['access_token'];

            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                        ],
                    ],
                    'data' => [
                        'type' => 'chat_message',
                        'sender_id' => 'system',
                        'conversation_id' => '456',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ]
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

            if ($response->failed()) {
                Log::error('Firebase sendNotification failed: ' . $response->body());
                throw new Exception($response->body());
            }

            Log::info('Firebase notification sent successfully', ['response' => $response->json()]);
            return $response->json();

        } catch (Exception $e) {
            Log::error('Firebase sendNotification exception: ' . $e->getMessage());
            throw $e;
        }
    }





}
