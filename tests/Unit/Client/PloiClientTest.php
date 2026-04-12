<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lartisan\PloiDashboard\Services\Ploi\Client\ClientConfig;
use Lartisan\PloiDashboard\Services\Ploi\Client\PloiClient;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CronjobResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DatabaseResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;

// Helper to build a PloiClient pointing at a fake API URL.
function makeClient(): PloiClient
{
    return new PloiClient(new ClientConfig(
        apiKey: 'test-key',
        apiUrl: 'https://ploi.test/api/',
        serverId: 81941,
        websiteId: 358580,
    ));
}

// ---------------------------------------------------------------------------
// getServer
// ---------------------------------------------------------------------------

it('getServer returns a ServerResponseData when the API response is valid', function () {
    Http::fake([
        'ploi.test/api/servers/81941' => Http::response([
            'data' => [
                'id' => 81941,
                'status' => 'active',
                'type' => 'server',
                'database_type' => 'mysql',
                'name' => 'hostinger-vps',
                'tags' => null,
                'ip_address' => '147.79.101.115',
                'internal_ip' => null,
                'ssh_port' => 22,
                'reboot_required' => true,
                'php_version' => 8.3,
                'php_cli_version' => '8.4',
                'mysql_version' => 8,
                'sites_count' => 6,
                'monitoring' => true,
                'opcache' => true,
                'installed_php_versions' => ['8.3', '8.2', '8.4'],
                'updates' => ['packages' => 40, 'security' => 0],
                'description' => null,
                'status_id' => 2,
                'provider' => null,
                'created_at' => '2024-11-03 20:16:58',
                'created_human' => '1 year ago',
                'uptime_human' => 'Online for 84 days',
            ],
        ], 200),
    ]);

    $result = makeClient()->getServer();

    expect($result)->toBeInstanceOf(ServerResponseData::class)
        ->and($result->id)->toBe(81941)
        ->and($result->name)->toBe('hostinger-vps')
        ->and($result->installed_php_versions)->toBe(['8.3', '8.2', '8.4']);
});

it('getServer returns null when the API response is malformed', function () {
    Http::fake([
        'ploi.test/api/servers/81941' => Http::response(['data' => null], 200),
    ]);

    $result = makeClient()->getServer();

    expect($result)->toBeNull();
});

// ---------------------------------------------------------------------------
// getSite
// ---------------------------------------------------------------------------

it('getSite returns a SiteResponseData when the API response is valid', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580' => Http::response([
            'data' => [
                'id' => 358580,
                'status' => 'active',
                'server_id' => 81941,
                'domain' => 'loto.scalablecodes.com',
                'tags' => null,
                'test_domain' => null,
                'deploy_script' => "cd /home/ploi/loto.scalablecodes.com\ngit pull origin dev\n\nphp8.4 /usr/local/bin/composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev\n\nphp8.4 artisan migrate --force",
                'deploy_webhook_url' => 'https://ploi.io/webhooks/servers/81941/sites/358580/deploy?token=dStLgau10XaWkvDYhj3WCU3HzFbvFYICwmCA18EJ2HUOFHbXFz',
                'web_directory' => '/public',
                'project_type' => 'laravel',
                'project_root' => '/',
                'last_deploy_at' => '2026-04-12 19:09:17',
                'system_user' => 'ploi',
                'php_version' => 8.4,
                'health_url' => null,
                'disable_robots' => true,
                'has_repository' => true,
                'zero_downtime_deployment' => false,
                'has_staging' => false,
                'fastcgi_cache' => false,
                'notes' => null,
                'suspension' => null,
                'disk_usage' => ['bytes' => 176912715, 'human' => '168.72 MB'],
                'created_at' => '2026-03-21 14:45:03',
            ],
        ], 200),
    ]);

    $result = makeClient()->getSite();

    expect($result)->toBeInstanceOf(SiteResponseData::class)
        ->and($result->id)->toBe(358580)
        ->and($result->domain)->toBe('loto.scalablecodes.com')
        ->and($result->has_repository)->toBeTrue();
});

// ---------------------------------------------------------------------------
// getDatabases
// ---------------------------------------------------------------------------

it('getDatabases returns a Collection of DatabaseResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/databases' => Http::response([
            'data' => [
                [
                    'id' => 205747,
                    'type' => 'mysql',
                    'name' => 'fc_dev',
                    'server_id' => 81941,
                    'status' => 'active',
                    'site' => null,
                    'created_at' => '2024-11-08 00:09:11',
                    'users' => [
                        [
                            'id' => 179032,
                            'user' => 'fc_dev',
                            'remote' => false,
                            'remote_ip' => '%',
                            'readonly' => false,
                            'created_at' => '2024-11-08 00:09:11',
                        ],
                    ],
                ],
                [
                    'id' => 205748,
                    'type' => 'mysql',
                    'name' => 'fc_prod',
                    'server_id' => 81941,
                    'status' => 'active',
                    'site' => ['id' => 253253, 'root_domain' => 'filamentcomponents.com'],
                    'created_at' => '2024-11-08 00:48:22',
                    'users' => [
                        [
                            'id' => 179180,
                            'user' => 'cristian_iosif_local',
                            'remote' => true,
                            'remote_ip' => '%',
                            'readonly' => false,
                            'created_at' => '2024-11-09 14:22:34',
                        ],
                        [
                            'id' => 234590,
                            'user' => 'fc_prod',
                            'remote' => false,
                            'remote_ip' => '%',
                            'readonly' => false,
                            'created_at' => '2026-03-04 21:16:06',
                        ],
                    ],
                ],
                [
                    'id' => 207617,
                    'type' => 'mysql',
                    'name' => 'scalablecodes',
                    'server_id' => 81941,
                    'status' => 'active',
                    'site' => null,
                    'created_at' => '2024-11-15 20:45:26',
                    'users' => [],
                ],
            ],
        ], 200),
    ]);

    $result = makeClient()->getDatabases();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(DatabaseResponseData::class)
        ->and($result->first()->name)->toBe('fc_dev')
        ->and($result->last()->site)->toBeNull();
});

// ---------------------------------------------------------------------------
// getCronjobs
// ---------------------------------------------------------------------------

it('getCronjobs returns a Collection of CronjobResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/crontabs' => Http::response([
            'data' => [
                [
                    'id' => 273465,
                    'status' => 'active',
                    'command' => 'php8.4 /home/ploi/filamentcomponents.com/artisan logs:clear',
                    'user' => 'ploi',
                    'frequency' => '* * * * *',
                    'created_at' => '2026-01-12 17:29:00',
                ],
                [
                    'id' => 289838,
                    'status' => 'active',
                    'command' => 'php8.4 /home/ploi/architect.filamentcomponents.com/artisan schedule:run',
                    'user' => 'ploi',
                    'frequency' => '* * * * *',
                    'created_at' => '2026-03-21 17:41:46',
                ],
                [
                    'id' => 291475,
                    'status' => 'active',
                    'command' => 'php8.4 /home/ploi/investingstrategy.online/artisan schedule:run',
                    'user' => 'ploi',
                    'frequency' => '* * * * *',
                    'created_at' => '2026-03-29 01:07:42',
                ],
                [
                    'id' => 291727,
                    'status' => 'active',
                    'command' => 'php8.4 /home/ploi/filamentcomponents.com/artisan schedule:run',
                    'user' => 'ploi',
                    'frequency' => '* * * * *',
                    'created_at' => '2026-03-30 15:40:29',
                ],
            ],
        ], 200),
    ]);

    $result = makeClient()->getCronjobs();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(4)
        ->and($result->first())->toBeInstanceOf(CronjobResponseData::class)
        ->and($result->first()->command)->toBe('php8.4 /home/ploi/filamentcomponents.com/artisan logs:clear')
        ->and($result->first()->frequency)->toBe('* * * * *');
});

// ---------------------------------------------------------------------------
// Error handling
// ---------------------------------------------------------------------------

it('returns an empty Collection when the API returns an empty data array', function () {
    Http::fake([
        'ploi.test/api/servers/81941/crontabs' => Http::response(['data' => []], 200),
    ]);

    $result = makeClient()->getCronjobs();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(0);
});
