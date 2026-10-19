<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'timeout' => env('DEEPSEEK_TIMEOUT', 30),
        'retries' => env('DEEPSEEK_RETRIES', 2),
        'retry_sleep_ms' => env('DEEPSEEK_RETRY_SLEEP_MS', 350),
    ],

    'crm' => [
        'name' => env('CRM_NAME', 'Primary CRM'),
        'timeout' => env('CRM_TIMEOUT', 10),
        'retries' => env('CRM_RETRIES', 1),
        'retry_sleep_ms' => env('CRM_RETRY_SLEEP_MS', 200),
        'default_domain' => env('CONCEPT_DOMAIN'),
        'default_name' => env('CONCEPT_NAME', 'AI Resume Assistant'),
        'api_url' => env('CRM_API_URL'),
        'api_key' => env('CRM_API_KEY'),
        'service_id' => env('CRM_SERVICE_ID'),
        'product_id' => env('CRM_PRODUCT_ID'),
        'support_email' => env('SUPPORT_EMAIL'),
        'support_phone' => env('SUPPORT_PHONE'),
    ],

];
