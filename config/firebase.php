<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com Firebase
    |
    */

    'credentials' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/firebase-credentials.json')),
    
    'project_id' => env('FIREBASE_PROJECT_ID', ''),
    
    'database_url' => env('FIREBASE_DATABASE_URL', ''),
    
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Firebase Auth Settings
    |--------------------------------------------------------------------------
    */
    
    'auth' => [
        'verify_email' => env('FIREBASE_VERIFY_EMAIL', true),
        'auto_create_users' => env('FIREBASE_AUTO_CREATE_USERS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    
    'cache_ttl' => env('FIREBASE_CACHE_TTL', 3600), // 1 hora
];