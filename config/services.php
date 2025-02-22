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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT'),
    ],

    'reset_password_url' => env('RESET_PASSWORD_URL'),
    'frontend_baseurl' => env('FRONTEND_BASEURL'),
    'staging_frontend_baseurl' => env('STAGING_FRONTEND_BASEURL'),
    'baseurl' => env('BASEURL'),

    'agricom_seller_baseurl' => 'https://shopazany.com/en/seller-signup',
    'agricom_staging_seller_baseurl' => 'https://fe-staging.shopazany.com/en/seller-signup',

    'seller_baseurl' => 'https://shopazany.com/en/seller-signup',
    'staging_seller_baseurl' => 'https://fe-staging.shopazany.com/en/seller-signup',

    'b2b_seller_baseurl' => 'https://shopazany.com/en/seller-signup',
    'b2b_staging_seller_baseurl' => 'https://fe-staging.shopazany.com/en/seller-signup',

    'paystack' => [
        'mode' => env('PAYSTACK'),
        'live_sk' => env('LIVE_PAYSTACK_SECRET_KEY'),
        'test_sk' => env('PAYSTACK_SECRET_KEY'),

        'test_pk' => env('PAYSTACK_TEST_PK'),
        'live_pk' => env('PAYSTACK_PK'),
        'bank_base_url' => "https://api.paystack.co/bank",
    ],

    'authorizenet' => [
        'api_login_id' => env('AUTHORIZENET_API_LOGIN_ID'),
        'transaction_key' => env('AUTHORIZENET_TRANSACTION_KEY'),
        'sandbox' => [
            'api_login_id' => env('AUTHORIZENET_SANDBOX_API_LOGIN_ID'),
            'transaction_key' => env('AUTHORIZENET_SANDBOX_TRANSACTION_KEY'),
        ]
    ],

];
