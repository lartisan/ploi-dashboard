<?php

namespace Lartisan\PloiDashboard\Services\Ploi;

use Illuminate\Support\ServiceProvider;
use Lartisan\PloiDashboard\Services\Ploi\Client\ClientConfig;

class PloiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientConfig::class, function () {
            return new ClientConfig(
                config('ploi-dashboard.services.api_key'),
                config('ploi-dashboard.services.api_url'),
                config('ploi-dashboard.server_id'),
                config('ploi-dashboard.website_id'),
            );
        });
    }
}
