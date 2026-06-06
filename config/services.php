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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'sms' => [
        'kno'        => env('SMS_KNO'),
        'username'   => env('SMS_USERNAME'),
        'password'   => env('SMS_PASSWORD'),
        'originator' => env('SMS_ORIGINATOR', 'MUDAVIM'),
        'notify_phone' => env('SMS_NOTIFY_PHONE'),
    ],

    'google' => [
        'api_key'  => env('GOOGLE_API_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),  // Places API (New) format: "ChIJ..."
    ],

    'instagram' => [
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'user_id'      => env('INSTAGRAM_USER_ID'),
    ],

    'spotify' => [
        'client_id'     => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        'redirect_uri'  => env('SPOTIFY_REDIRECT_URI', 'https://mudavimpalamutbuku.com/yonetim/spotify/callback'),
    ],

];
