<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Billable Model
    |--------------------------------------------------------------------------
    |
    | The model that will be used for subscriptions. This model should use
    | the HasPlanSubscriptions trait.
    |
    */

    'billable_model' => env('FILASAAS_BILLABLE_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Default Subscription Name
    |--------------------------------------------------------------------------
    |
    | The default name for subscriptions when creating new subscriptions.
    |
    */

    'default_subscription_name' => env('FILASAAS_DEFAULT_SUBSCRIPTION_NAME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Configuration for payment gateways. Each gateway can be enabled/disabled
    | and configured with its specific settings.
    |
    */

    'gateways' => [
        'cash' => [
            'enabled' => env('FILASAAS_CASH_ENABLED', true),
            'default_approval_mode' => env('FILASAAS_CASH_DEFAULT_APPROVAL', 'manual'), // 'auto' or 'manual'
        ],

        'stripe' => [
            'enabled' => env('FILASAAS_STRIPE_ENABLED', false),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'usd'),
        ],

        'paypal' => [
            'enabled' => env('FILASAAS_PAYPAL_ENABLED', false),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' or 'live'
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'currency' => env('PAYPAL_CURRENCY', 'USD'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        ],

        'custom' => [
            'enabled' => env('FILASAAS_CUSTOM_GATEWAYS_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for plans when creating new plans.
    |
    */

    'plans' => [
        'default_currency' => env('FILASAAS_DEFAULT_CURRENCY', 'USD'),
        'default_trial_period' => env('FILASAAS_DEFAULT_TRIAL_PERIOD', 0),
        'default_trial_interval' => env('FILASAAS_DEFAULT_TRIAL_INTERVAL', 'day'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Routes
    |--------------------------------------------------------------------------
    |
    | Configuration for webhook routes. These routes will be protected by
    | middleware to ensure only the payment gateways can access them.
    |
    */

    'webhooks' => [
        'prefix' => env('FILASAAS_WEBHOOK_PREFIX', 'webhooks/billing'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all billing-related database tables.
    |
    */

    'table_prefix' => 'filasaas_',

    /*
    |--------------------------------------------------------------------------
    | Feature Usage Tracking
    |--------------------------------------------------------------------------
    |
    | Enable or disable feature usage tracking for subscriptions.
    |
    */

    'track_usage' => env('FILASAAS_TRACK_USAGE', true),
];
