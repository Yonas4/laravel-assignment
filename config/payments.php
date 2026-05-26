<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */
    'default' => env('PAYMENT_GATEWAY', 'moyasar'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Rules
    |--------------------------------------------------------------------------
    |
    | Defines which gateways are available based on criteria like city and module.
    |
    */
    'rules' => [
        'moyasar' => [
            'cities' => ['Riyadh', 'Jeddah', 'Dammam'],
            'modules' => ['booking', 'cart', 'subscription'],
        ],
        'tap' => [
            'cities' => ['Riyadh', 'Jeddah'],
            'modules' => ['booking', 'cart'],
        ],
        'stripe' => [
            'cities' => ['*'], // All cities
            'modules' => ['subscription', 'cart'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configurations
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'moyasar' => [
            'api_key' => env('MOYASAR_API_KEY'),
        ],
        'tap' => [
            'api_key' => env('TAP_API_KEY'),
        ],
        'stripe' => [
            'api_key' => env('STRIPE_API_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
    ],
];
