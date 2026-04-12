<?php

use Illuminate\Support\Collection;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CronjobResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DatabaseResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;

// ---------------------------------------------------------------------------
// ServerResponseData
// ---------------------------------------------------------------------------

function serverPayload(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

it('constructs ServerResponseData from a clean payload', function () {
    $dto = ServerResponseData::fromLivewire(serverPayload());

    expect($dto)->toBeInstanceOf(ServerResponseData::class)
        ->and($dto->id)->toBe(81941)
        ->and($dto->name)->toBe('hostinger-vps')
        ->and($dto->ip_address)->toBe('147.79.101.115')
        ->and($dto->status)->toBe('active')
        ->and($dto->installed_php_versions)->toBe(['8.3', '8.2', '8.4'])
        ->and($dto->opcache)->toBeTrue();
});

it('ignores extra keys from the API response (e.g. tags, suspension)', function () {
    $payload = array_merge(serverPayload(), ['tags' => null, 'extra_field' => 'ignored']);

    $dto = ServerResponseData::fromLivewire($payload);

    expect($dto)->toBeInstanceOf(ServerResponseData::class)
        ->and($dto->id)->toBe(81941);
});

it('round-trips ServerResponseData through toLivewire/fromLivewire', function () {
    $original = ServerResponseData::fromLivewire(serverPayload());
    $restored = ServerResponseData::fromLivewire($original->toLivewire());

    expect($restored->id)->toBe($original->id)
        ->and($restored->name)->toBe($original->name)
        ->and($restored->installed_php_versions)->toBe($original->installed_php_versions);
});

// ---------------------------------------------------------------------------
// SiteResponseData
// ---------------------------------------------------------------------------

function sitePayload(array $overrides = []): array
{
    return array_merge([
        'id' => 358580,
        'status' => 'active',
        'server_id' => 81941,
        'domain' => 'loto.scalablecodes.com',
        'test_domain' => null,
        'deploy_script' => 'cd /home/ploi/loto.scalablecodes.com && git pull',
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
        'disk_usage' => ['bytes' => 176912715, 'human' => '168.72 MB'],
        'created_at' => '2026-03-21 14:45:03',
    ], $overrides);
}

it('constructs SiteResponseData from a clean payload', function () {
    $dto = SiteResponseData::fromLivewire(sitePayload());

    expect($dto)->toBeInstanceOf(SiteResponseData::class)
        ->and($dto->id)->toBe(358580)
        ->and($dto->domain)->toBe('loto.scalablecodes.com')
        ->and($dto->status)->toBe('active')
        ->and($dto->has_repository)->toBeTrue();
});

it('ignores extra API keys like tags and suspension in SiteResponseData', function () {
    $payload = array_merge(sitePayload(), ['tags' => null, 'suspension' => null]);

    $dto = SiteResponseData::fromLivewire($payload);

    expect($dto)->toBeInstanceOf(SiteResponseData::class)
        ->and($dto->domain)->toBe('loto.scalablecodes.com');
});

// ---------------------------------------------------------------------------
// DatabaseResponseData — toCollection
// ---------------------------------------------------------------------------

function databaseItems(): array
{
    return [
        [
            'id' => 205747,
            'type' => 'mysql',
            'name' => 'fc_dev',
            'server_id' => 81941,
            'status' => 'active',
            'site' => null,
            'created_at' => '2024-11-08 00:09:11',
            'users' => [
                ['id' => 179032, 'user' => 'fc_dev', 'remote' => false, 'remote_ip' => '%', 'readonly' => false, 'created_at' => '2024-11-08 00:09:11'],
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
            'users' => [],
        ],
    ];
}

it('builds a Collection of DatabaseResponseData via toCollection', function () {
    $collection = DatabaseResponseData::toCollection(databaseItems());

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection)->toHaveCount(2)
        ->and($collection->first())->toBeInstanceOf(DatabaseResponseData::class)
        ->and($collection->first()->name)->toBe('fc_dev')
        ->and($collection->first()->users)->toHaveCount(1)
        ->and($collection->last()->site)->toBe(['id' => 253253, 'root_domain' => 'filamentcomponents.com']);
});

it('DatabaseResponseData has correct users array', function () {
    $dto = DatabaseResponseData::fromLivewire(databaseItems()[0]);

    expect($dto->users)->toBeArray()
        ->and($dto->users[0]['user'])->toBe('fc_dev');
});

// ---------------------------------------------------------------------------
// CronjobResponseData — toCollection
// ---------------------------------------------------------------------------

function cronjobItems(): array
{
    return [
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
    ];
}

it('builds a Collection of CronjobResponseData via toCollection', function () {
    $collection = CronjobResponseData::toCollection(cronjobItems());

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection)->toHaveCount(2)
        ->and($collection->first())->toBeInstanceOf(CronjobResponseData::class)
        ->and($collection->first()->id)->toBe(273465)
        ->and($collection->first()->command)->toContain('logs:clear')
        ->and($collection->last()->command)->toContain('schedule:run');
});
