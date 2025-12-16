<?php

namespace App\Http\Controllers;

use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    public function redirect()
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->addScope('https://www.googleapis.com/auth/gmail.send');
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();
        Log::info('Google Auth URL: '.$authUrl);

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));

        $code = $request->query('code');

        if (! $code) {
            return response()->json(['error' => 'Authorization code not found.'], 400);
        }

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return response()->json(['error' => $token['error_description'] ?? 'Unknown error'], 500);
        }

        file_put_contents(storage_path('app/google-token.json'), json_encode($token));
        Log::info('Google Token saved:', $token);

        return response()->json([
            'access_token' => $token['access_token'] ?? null,
            'expires_in' => $token['expires_in'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
        ]);
    }
}
