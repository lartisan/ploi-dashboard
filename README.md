# Ploi Dashboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lartisan/ploi-dashboard.svg?style=flat-square)](https://packagist.org/packages/lartisan/ploi-dashboard)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lartisan/ploi-dashboard/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lartisan/ploi-dashboard/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lartisan/ploi-dashboard/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lartisan/ploi-dashboard/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lartisan/ploi-dashboard.svg?style=flat-square)](https://packagist.org/packages/lartisan/ploi-dashboard)

![ploi-dashboard-og](https://filamentcomponents.com/plugins/ploi-dashboard.png)

This is as package that brings the Ploi dashboard to Filament admin panel. Provide the server id and the id of the site you wish to manage and unlock the Ploi features straight in your Filament admin panel.

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

    'polling' => [
        'interval' => env('PLOI_POLLING_INTERVAL', '10s'),
    ],
];
```

## Usage

Add the plugin to you Panel Provide, example:

```php
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ...
            ->plugins([
                new \Lartisan\PloiDashboard\PloiDashboardPlugin,
            ])
        ;
    }
}
```

and provide the necessary environment variables in your `.env` file:

```bash
PLOI_API_KEY=your-api-key
PLOI_SERVER_ID=your-server-id
PLOI_WEBSITE_ID=your-website-id
```

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
