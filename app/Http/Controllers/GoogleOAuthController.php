<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount;
use Google\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleOAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $client = $this->getGoogleClient();

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();

        return redirect($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('filament.admin.resources.google-accounts.index')
                ->with('error', 'Google authentication cancelled or failed');
        }

        $client = $this->getGoogleClient();

        $code = $request->get('code');
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return redirect()->route('filament.admin.resources.google-accounts.index')
                ->with('error', 'Failed to authenticate: ' . ($token['error_description'] ?? $token['error']));
        }

        $client->setAccessToken($token);

        $oauth2 = new \Google\Service\Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        GoogleAccount::updateOrCreate(
            [
                'google_user_id' => $userInfo->id,
            ],
            [
                'user_id' => auth()->id(),
                'email' => $userInfo->email,
                'display_name' => $userInfo->name,
                'avatar_url' => $userInfo->picture,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => isset($token['expires_in'])
                    ? now()->addSeconds($token['expires_in'])
                    : null,
                'scopes' => implode(' ', $token['scope'] ?? []),
                'status' => 'active',
                'last_synced_at' => now(),
            ]
        );

        return redirect()->route('filament.admin.resources.google-accounts.index')
            ->with('success', 'Google account connected successfully!');
    }

    private function getGoogleClient(): Client
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->addScope(\Google\Service\Drive::DRIVE_READONLY);
        $client->addScope(\Google\Service\Drive::DRIVE_METADATA_READONLY);
        $client->addScope(\Google\Service\Oauth2::USERINFO_EMAIL);
        $client->addScope(\Google\Service\Oauth2::USERINFO_PROFILE);

        return $client;
    }
}
