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
    | API OVHcloud — token créé sur https://api.ovh.com/createToken/
    | Endpoints : ovh-eu https://eu.api.ovh.com/1.0 | ovh-ca https://ca.api.ovh.com/1.0
    */
    'ovh' => [
        'endpoint' => env('OVH_API_ENDPOINT', 'https://eu.api.ovh.com/1.0'),
        'application_key' => env('OVH_APPLICATION_KEY'),
        'application_secret' => env('OVH_APPLICATION_SECRET'),
        'consumer_key' => env('OVH_CONSUMER_KEY'),
        'timeout' => env('OVH_API_TIMEOUT', 15),
    ],

];
