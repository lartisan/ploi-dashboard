<?php

// config for Lartisan/PloiDashboard
return [

    'server_id' => env('PLOI_SERVER_ID'),

    'website_id' => env('PLOI_WEBSITE_ID'),

    'services' => [
        'api_url' => env('PLOI_API_URL', 'https://ploi.io/api'),
        'api_key' => env('PLOI_API_KEY'),
    ],

    'polling' => [
        'interval' => env('PLOI_POLLING_INTERVAL', '10s'),
    ],

    'routes' => [
        'site' => [
            'index' => 'site',
            'general' => 'site/general',
            'queue' => 'site/queue',
            'certificate' => 'site/certificate',
            'cronjobs' => 'site/cronjobs',
            'repository' => 'site/repository',
            'redirects' => 'site/redirects',
            'settings' => 'site/settings',
        ],
        'server' => [
            'index' => 'server',
        ],
    ],

];