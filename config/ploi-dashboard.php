<?php

// config for Lartisan/PloiDashboard
return [

    /*
    |--------------------------------------------------------------------------
    | Ploi keys
    |--------------------------------------------------------------------------
    |
    | This values should be set in the .env file.
    |
    */
    'server_id' => env('PLOI_SERVER_ID'),

    'website_id' => env('PLOI_WEBSITE_ID'),

    'services' => [
        'api_url' => env('PLOI_API_URL', 'https://ploi.io/api'),
        'api_key' => env('PLOI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable the logging of the requests made to the Ploi API
    |--------------------------------------------------------------------------
    */
    'log_requests' => true,

    /*
    |--------------------------------------------------------------------------
    | Adjust the polling interval
    |--------------------------------------------------------------------------
    */
    'polling' => [
        'interval' => env('PLOI_POLLING_INTERVAL', '10s'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules Visibility
    |--------------------------------------------------------------------------
    |
    | This values determine if a module is visible or not.
    | The visibility is then determined in the Filament page by using the canAccess method.
    |
    */
    'enabled_modules' => [
        'server' => [
            'server' => true,
            'cronjobs' => true,
            'daemons' => true,
            'databases' => true,
            'logs' => true,
            'network' => true,
            'php' => true,
            'settings' => true,
            'ssh-keys' => true,
        ],

        'site' => [
            'site' => true,
            'certificate' => true,
            'queue' => true,
            'redirects' => true,
            'repository' => true,
            'settings' => true,
        ],
    ],

];
