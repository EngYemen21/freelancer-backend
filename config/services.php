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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

'google' => [
        'client_id' => '333004555960-u8k5tr4oegn5kjdor23ubo99i8n6g0lg.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect' => 'http://localhost:3000/auth/google/callback',
    ],
    'recaptcha' => [
        'secret' => env('NOCAPTCHA_SECRET'),
    ],

];
