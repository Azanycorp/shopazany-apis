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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'reset_password_url' => env('RESET_PASSWORD_URL'),
    'frontend_baseurl' => env('FRONTEND_BASEURL'),
    'staging_frontend_baseurl' => env('STAGING_FRONTEND_BASEURL'),
    'baseurl' => env('BASEURL'),

    'paystack' => [
        'mode' => env('PAYSTACK'),
        'live_sk' => env('LIVE_PAYSTACK_SECRET_KEY'),
        'test_sk' => env('PAYSTACK_SECRET_KEY'),

        'test_pk' => env('PAYSTACK_TEST_PK'),
        'live_pk' => env('PAYSTACK_PK'),
    ],

    'authorizenet' => [
        'api_login_id' => env('AUTHORIZENET_API_LOGIN_ID'),
        'transaction_key' => env('AUTHORIZENET_TRANSACTION_KEY'),
    ],

];
