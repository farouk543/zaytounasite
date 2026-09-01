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

    /*
    | Server-side IP → country lookup, used by DetectCurrency when no proxy
    | geolocation header (CF-IPCountry, …) is present. Free, keyless provider
    | by default; results are cached per IP. Set GEOIP_LOOKUP_ENABLED=false
    | to disable and rely on the proxy header + manual currency switch only.
    */
    'geoip' => [
        'enabled' => env('GEOIP_LOOKUP_ENABLED', true),
        'endpoint' => env('GEOIP_LOOKUP_ENDPOINT', 'https://ipwho.is/{ip}'),
        'country_path' => env('GEOIP_LOOKUP_COUNTRY_PATH', 'country_code'),
        'timeout' => (float) env('GEOIP_LOOKUP_TIMEOUT', 1.5),
        'cache_days' => (int) env('GEOIP_LOOKUP_CACHE_DAYS', 30),
    ],

];
