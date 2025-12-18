<?php

namespace App\Services\Notifications;


use Exception;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class FirebaseNotificationService
{
    protected $projectId;
    protected $credentials;

    public function __construct()
    {
        $this->credentials = config('firebase.credentials');
    }

    /**
     * @throws \Google\Exception
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function sendNotification(string $token, string $title, string $body): array
    {
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

        if ($response->failed()) {

            Log::info("Erroreeeee" . $response->body());
            throw new Exception($response->body());
        }


        return $response->json();
    }


    /**
     * @throws \Google\Exception
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function sendNotificationNew(string $token, string $title, string $body): array
    {
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
                        'sound' => 'default', // Or use a custom sound uploaded to the app
                    ],
                ],
                'data' => [
                    'type' => 'chat_message',
                    'sender_id' => '123',
                    'conversation_id' => '456',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Required for background tap to work
                ],

            ]
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

        if ($response->failed()) {
            Log::info("Erroreeeee" . $response->body());
            throw new Exception($response->body());
        }

        return $response->json();
    }
}
