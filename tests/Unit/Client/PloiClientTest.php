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
                'ip_address' => '147.79.101.115',
                'internal_ip' => null,
                'ssh_port' => '22',
                'reboot_required' => 'true',
                'php_version' => 8.3,
                'php_cli_version' => '8.4',
                'mysql_version' => 8.0,
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
                // Extra key the real API sends — should be ignored:
                'tags' => null,
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
                'test_domain' => null,
                'deploy_script' => 'git pull',
                'deploy_webhook_url' => 'https://ploi.io/webhooks/deploy',
                'web_directory' => '/public',
                'project_type' => 'laravel',
                'project_root' => '/',
                'last_deploy_at' => '2026-04-12 19:09:17',
                'system_user' => 'ploi',
                'php_version' => '8.4',
                'health_url' => null,
                'disable_robots' => true,
                'has_repository' => true,
                'zero_downtime_deployment' => false,
                'has_staging' => false,
                'fastcgi_cache' => false,
                'notes' => null,
                'disk_usage' => null,
                'created_at' => '2026-03-21 14:45:03',
                // Extra keys the real API sends:
                'tags' => null,
                'suspension' => null,
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
                    'users' => [],
                ],
                [
                    'id' => 205748,
                    'type' => 'mysql',
                    'name' => 'fc_prod',
                    'server_id' => 81941,
                    'status' => 'active',
                    'site' => ['id' => 253253, 'root_domain' => 'filamentcomponents.com'],
                    'created_at' => '2024-11-08 00:48:22',
                    'users' => [],
                ],
            ],
        ], 200),
    ]);

    $result = makeClient()->getDatabases();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($result->first())->toBeInstanceOf(DatabaseResponseData::class)
        ->and($result->first()->name)->toBe('fc_dev')
        ->and($result->last()->site)->toBe(['id' => 253253, 'root_domain' => 'filamentcomponents.com']);
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
                    'command' => 'php8.4 /home/ploi/site.com/artisan schedule:run',
                    'user' => 'ploi',
                    'frequency' => '* * * * *',
                    'created_at' => '2026-01-12 17:29:00',
                ],
                [
                    'id' => 289838,
                    'status' => 'active',
                    'command' => 'php8.4 /home/ploi/other.com/artisan logs:clear',
                    'user' => 'ploi',
                    'frequency' => '0 * * * *',
                    'created_at' => '2026-03-21 17:41:46',
                ],
            ],
        ], 200),
    ]);

    $result = makeClient()->getCronjobs();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($result->first())->toBeInstanceOf(CronjobResponseData::class)
        ->and($result->first()->frequency)->toBe('* * * * *')
        ->and($result->last()->frequency)->toBe('0 * * * *');
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
