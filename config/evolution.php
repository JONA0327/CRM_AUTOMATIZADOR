<?php
// config/evolution.php

return [
    /*
    |--------------------------------------------------------------------------
    | Evolution API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL for the Evolution API endpoints. This should be
    | the URL of your Evolution API server.
    |
    */
    'base_url' => env('EVOLUTION_API_URL', 'http://localhost:8080'),

    /*
    |--------------------------------------------------------------------------
    | Evolution API Key
    |--------------------------------------------------------------------------
    |
    | This is your API key which is used to authenticate with the Evolution API.
    | You can get this from your Evolution API configuration.
    |
    */
    'api_key' => env('EVOLUTION_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Instance Name
    |--------------------------------------------------------------------------
    |
    | The default instance name to use when none is provided.
    |
    */
    'default_instance' => env('EVOLUTION_DEFAULT_INSTANCE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of seconds to wait for a response
    | from the Evolution API server.
    |
    */
    'timeout' => env('EVOLUTION_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | The URL where Evolution API will send webhook events.
    |
    */
    'webhook_url' => env('EVOLUTION_WEBHOOK_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Webhook Events
    |--------------------------------------------------------------------------
    |
    | The events that should trigger the webhook.
    |
    */
    'webhook_events' => [
        'message',
        'message.ack',
        'status.instance',
        // Add more events as needed
    ],
];
