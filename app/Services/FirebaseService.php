<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    protected function getAccessToken(): string
    {

    //     $firebase = (new \Kreait\Firebase\Factory)
    // ->withServiceAccount(config('app.firebase_credentials'))
    // ->create();


        $client = new GoogleClient();
        $client->setAuthConfig('/etc/secrets/firebase_credentials.json');


        // $client->setAuthConfig(storage_path('app/firebase/firebase_credentials.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        return $token['access_token'];
    }

    public function sendNotification(string $firebaseToken, string $title, string $body): void
    {
        $accessToken = $this->getAccessToken();

        Http::withToken($accessToken)
            ->post(
                'https://fcm.googleapis.com/v1/projects/' . config('services.firebase.project_id') . '/messages:send',
                [
                    'message' => [
                        'token' => $firebaseToken,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                    ],
                ]
            );
    }
}
