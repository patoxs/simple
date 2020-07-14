<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    //'claveunica' => [
        //'client_id' => env('CLAVEUNICA_KEY'),
        //'client_secret' => env('CLAVEUNICA_SECRET'),
        //'redirect' => env('CLAVEUNICA_REDIRECT'),
    //],

    'rollbar' => [
        'access_token' => env('ROLLBAR_ACCESS_TOKEN'),
        'environment' => ENV('APP_ENV', 'production')
    ]

];
