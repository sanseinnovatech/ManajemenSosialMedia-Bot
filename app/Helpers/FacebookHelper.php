<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class FacebookHelper
{
    public static function validateAccessToken(string $accessToken): array
    {
        $url = "https://graph.facebook.com/me?access_token={$accessToken}";

        $response = Http::get($url);

        if ($response->successful()) {
            return [
                'valid' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'valid' => false,
            'error' => $response->json('error.message') ?? 'Unknown error',
        ];
    }
}
