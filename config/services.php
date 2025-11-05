<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'disks' => [
    'public' => [
        'driver' => 'local',
        'root'   => storage_path('app/public'),
        'url'    => env('FILESYSTEM_PUBLIC_URL'), // <<— tambah/bariskan ini
        'visibility' => 'public',
        'throw'  => false,
    ],
    // ...
],


    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    // config/services.php
'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
],
'twitter' => [
    'consumer_key' => env('TWITTER_CONSUMER_KEY'),
    'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
],



];
