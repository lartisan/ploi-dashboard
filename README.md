# Ploi Dashboard

<div align="center" class="filament-hidden">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lartisan/ploi-dashboard.svg)](https://packagist.org/packages/lartisan/ploi-dashboard)
[![GitHub Tests](https://img.shields.io/github/actions/workflow/status/lartisan/ploi-dashboard/tests.yml?label=Tests)](https://github.com/lartisan/ploi-dashboard/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/lartisan/ploi-dashboard.svg)](https://packagist.org/packages/lartisan/ploi-dashboard)
[![License](https://img.shields.io/packagist/l/lartisan/ploi-dashboard.svg)](https://packagist.org/packages/lartisan/ploi-dashboard)

</div>

This is a package that brings the Ploi dashboard to Filament admin panel. Provide the server id and the id of the site you wish to manage and unlock the Ploi features straight in your Filament admin panel.

<img width="1376" height="768" alt="PloiDashboard Poster" src="https://github.com/user-attachments/assets/de4486b2-4698-405d-8c08-2ed7c28d4261" />


## Requirements

- PHP 8.2+
- Laravel 11+
- Filament 4 or 5

## Installation

You can install the package via composer:

```bash
composer require lartisan/ploi-dashboard
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ploi-dashboard-config"
```

This is the contents of the published config file:

```php
return [
    'server_id' => env('PLOI_SERVER_ID'),

    'website_id' => env('PLOI_WEBSITE_ID'),

    'services' => [
        'api_url' => env('PLOI_API_URL', 'https://ploi.io/api'),
        'api_key' => env('PLOI_API_KEY'),
    ],

    'log_requests' => true,

    'polling' => [
        'interval' => env('PLOI_POLLING_INTERVAL', '10s'),
    ],

    'enabled_modules' => [
        'server' => [
            'server'   => true,
            'cronjobs' => true,
            'daemons'  => true,
            'databases' => true,
            'logs'     => true,
            'network'  => true,
            'php'      => true,
            'settings' => true,
            'ssh-keys' => true,
        ],

        'site' => [
            'site'        => true,
            'certificate' => true,
            'queue'       => true,
            'redirects'   => true,
            'repository'  => true,
            'settings'    => true,
        ],
    ],
];
```

## Usage

Add the plugin to your Panel Provider:

```php
use Lartisan\PloiDashboard\PloiDashboardPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugins([
                PloiDashboardPlugin::make(),
            ]);
    }
}
```

Then add the required environment variables to your `.env` file:

```bash
PLOI_API_KEY=your-api-key
PLOI_SERVER_ID=your-server-id
PLOI_WEBSITE_ID=your-website-id
```

### Modules

You can enable or disable individual pages via the `enabled_modules` config key. Each key maps to a Filament page — set it to `false` to hide that page from the panel.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Cristian Iosif](https://github.com/lartisan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
