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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Profile Enrichment Service
    |--------------------------------------------------------------------------
    |
    | Configurações para o serviço de enriquecimento automático de perfis
    | de usuários através de consulta a endpoints externos.
    |
    */
    'user_enrichment' => [
        'enabled' => env('USER_ENRICHMENT_ENABLED', false),
        'url' => env('USER_ENRICHMENT_URL'),
        'api_key' => env('USER_ENRICHMENT_API_KEY'),
        'timeout' => env('USER_ENRICHMENT_TIMEOUT', 10),
        'retry_attempts' => env('USER_ENRICHMENT_RETRY_ATTEMPTS', 2),
        'cache_ttl' => env('USER_ENRICHMENT_CACHE_TTL', 3600), // 1 hora
    ],

];
