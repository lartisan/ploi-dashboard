<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lartisan\PloiDashboard\Services\Ploi\Client\ClientConfig;
use Lartisan\PloiDashboard\Services\Ploi\Client\PloiClient;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CertificateResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CronjobResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DaemonResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\LogResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\NetworkRuleResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\QueueResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RedirectData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RepositoryResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SshKeyResponseData;

function makeEndpointsClient(): PloiClient
{
    return new PloiClient(new ClientConfig(
        apiKey: 'test-key',
        apiUrl: 'https://ploi.test/api/',
        serverId: 81941,
        websiteId: 358580,
    ));
}

// Shared fixture data builders — real data from Postman collections

function siteFixture(): array
{
    return [
        'id' => 358580,
        'status' => 'active',
        'server_id' => 81941,
        'domain' => 'loto.scalablecodes.com',
        'tags' => null,
        'test_domain' => null,
        'deploy_script' => "cd /home/ploi/loto.scalablecodes.com\ngit pull origin dev\n\nphp8.4 /usr/local/bin/composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev\n\nphp8.4 artisan migrate --force\n\nyarn install --frozen-lockfile\nyarn build\n\nphp8.4 artisan optimize:clear\nphp8.4 artisan optimize\nphp8.4 artisan filament:optimize-clear\nphp8.4 artisan filament:optimize\nphp8.4 artisan icons:cache\n\necho \"\" | sudo -S service php8.4-fpm reload\n\necho \"Application deployed!\"",
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
    ];
}

function serverFixture(): array
{
    return [
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
    ];
}

// ---------------------------------------------------------------------------
// Server — List / Reboot / PHP Versions
// ---------------------------------------------------------------------------

it('listServers returns a Collection of ServerResponseData', function () {
    // Real Postman data uses php_version values like 8.1 (float) — servers with 'none' are excluded
    // since ServerResponseData::php_version is typed float and cannot accept 'none'.
    Http::fake([
        'ploi.test/api/servers' => Http::response([
            'data' => [
                [
                    'id' => 7957,
                    'status' => 'active',
                    'type' => 'server',
                    'database_type' => 'none',
                    'name' => 'chr-sphinx-www',
                    'tags' => null,
                    'ip_address' => '49.12.12.151',
                    'internal_ip' => null,
                    'ssh_port' => 22,
                    'reboot_required' => false,
                    'php_version' => 8.1,
                    'php_cli_version' => '8.1',
                    'mysql_version' => 5.7,
                    'sites_count' => 6,
                    'monitoring' => true,
                    'opcache' => false,
                    'installed_php_versions' => ['8.1'],
                    'updates' => ['packages' => 277, 'security' => 202],
                    'description' => null,
                    'status_id' => 2,
                    'provider' => ['id' => 2290, 'name' => 'Hetzner'],
                    'created_at' => '2020-04-22 12:08:02',
                    'created_human' => '5 years ago',
                    'uptime_human' => 'Online for 1825 days',
                ],
                serverFixture(),
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->listServers();

    /** @var ServerResponseData $first */
    $first = $result->first();
    /** @var ServerResponseData $last */
    $last = $result->last();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($first)->toBeInstanceOf(ServerResponseData::class)
        ->and($first->id)->toBe(7957)
        ->and($last->id)->toBe(81941);
});

it('listServers sends a GET request to the correct URL', function () {
    Http::fake(['ploi.test/api/servers' => Http::response(['data' => [serverFixture()]], 200)]);

    makeEndpointsClient()->listServers();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://ploi.test/api/servers';
    });
});

it('getPhpVersions returns the versions array', function () {
    Http::fake([
        'ploi.test/api/servers/81941/php/versions' => Http::response([
            'data' => ['versions' => ['8.1', '8.2', '8.3', '8.4']],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getPhpVersions(81941);

    expect($result)->toBe(['8.1', '8.2', '8.3', '8.4']);
});

it('getPhpVersions sends a GET request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/php/versions' => Http::response([
            'data' => ['versions' => ['8.3', '8.4']],
        ], 200),
    ]);

    makeEndpointsClient()->getPhpVersions(81941);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://ploi.test/api/servers/81941/php/versions';
    });
});

it('rebootServer returns the message string', function () {
    Http::fake([
        'ploi.test/api/servers/81941/restart' => Http::response([
            'message' => 'Server is being restarted.',
        ], 200),
    ]);

    $result = makeEndpointsClient()->rebootServer(81941);

    expect($result)->toBe('Server is being restarted.');
});

it('rebootServer sends a POST request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/restart' => Http::response(['message' => 'Server is being restarted.'], 200),
    ]);

    makeEndpointsClient()->rebootServer(81941);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/restart';
    });
});

// ---------------------------------------------------------------------------
// Server — Network Rules
// ---------------------------------------------------------------------------

it('listNetworkRules returns a Collection of NetworkRuleResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules' => Http::response([
            'data' => [
                [
                    'id' => 234955,
                    'name' => 'HTTP',
                    'port' => '80',
                    'from_ip_address' => null,
                    'rule_type' => 'allow',
                    'status' => 'active',
                    'created_at' => '2024-11-03 20:19:37',
                ],
                [
                    'id' => 234956,
                    'name' => 'HTTPS',
                    'port' => '443',
                    'from_ip_address' => null,
                    'rule_type' => 'allow',
                    'status' => 'active',
                    'created_at' => '2024-11-03 20:19:37',
                ],
                [
                    'id' => 234957,
                    'name' => 'SSH',
                    'port' => '22',
                    'from_ip_address' => null,
                    'rule_type' => 'allow',
                    'status' => 'active',
                    'created_at' => '2024-11-03 20:19:37',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->listNetworkRules();

    /** @var NetworkRuleResponseData $first */
    $first = $result->first();
    /** @var NetworkRuleResponseData $last */
    $last = $result->last();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($first)->toBeInstanceOf(NetworkRuleResponseData::class)
        ->and($first->name)->toBe('HTTP')
        ->and($last->name)->toBe('SSH');
});

it('createNetworkRule sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules' => Http::response([
            'data' => [
                'id' => 234958,
                'name' => 'Custom',
                'port' => '8080',
                'from_ip_address' => null,
                'rule_type' => 'allow',
                'status' => 'active',
                'created_at' => '2024-11-03 20:19:38',
            ],
        ], 201),
    ]);

    $payload = ['name' => 'Custom', 'port' => '8080', 'rule_type' => 'allow', 'type' => 'tcp'];
    makeEndpointsClient()->createNetworkRule($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/network-rules'
            && $request->data() === $payload;
    });
});

it('createNetworkRule returns a NetworkRuleResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules' => Http::response([
            'data' => [
                'id' => 234958,
                'name' => 'Custom',
                'port' => '8080',
                'from_ip_address' => null,
                'rule_type' => 'allow',
                'status' => 'active',
                'created_at' => '2024-11-03 20:19:38',
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createNetworkRule([
        'name' => 'Custom', 'port' => '8080', 'rule_type' => 'allow', 'type' => 'tcp',
    ]);

    expect($result)->toBeInstanceOf(NetworkRuleResponseData::class)
        ->and($result->port)->toBe('8080');
});

it('deleteNetworkRule sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules/234957' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteNetworkRule(234957);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/network-rules/234957';
    });
});

// ---------------------------------------------------------------------------
// Server — SSH Keys
// ---------------------------------------------------------------------------

it('listSshKeys returns a Collection of SshKeyResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/ssh-keys' => Http::response([
            'data' => [
                [
                    'id' => 573496,
                    'status' => 'active',
                    'name' => 'GITHUB_DEPLOY_KEY',
                    'key' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIIrr/dkaISvaJqhKqdobPmX8aRvwyStOQYgw3GD+aH4a',
                    'system_user' => 'ploi',
                ],
                [
                    'id' => 569938,
                    'status' => 'active',
                    'name' => 'SSH CIosif',
                    'key' => 'ecdsa-sha2-nistp521 AAAAE2VjZHNhLXNoYTItbmlzdHA1MjEAAAAIbmlzdHA1MjE...',
                    'system_user' => 'ploi',
                ],
                [
                    'id' => 457980,
                    'status' => 'active',
                    'name' => 'Cristian Iosif',
                    'key' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIFGv3CEqdfmrIf2IItatWzCYwP6xZuVYITo2RoAWCDGw',
                    'system_user' => 'ploi',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->listSshKeys();

    /** @var SshKeyResponseData $first */
    $first = $result->first();
    /** @var SshKeyResponseData $last */
    $last = $result->last();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($first)->toBeInstanceOf(SshKeyResponseData::class)
        ->and($first->name)->toBe('GITHUB_DEPLOY_KEY')
        ->and($last->name)->toBe('Cristian Iosif');
});

it('createSshKey sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/ssh-keys' => Http::response([
            'data' => [
                'id' => 573497,
                'status' => 'active',
                'name' => 'NEW_KEY',
                'key' => 'ssh-ed25519 AAAA...',
                'system_user' => 'ploi',
            ],
        ], 201),
    ]);

    $payload = ['name' => 'NEW_KEY', 'key' => 'ssh-ed25519 AAAA...', 'system_user' => 'ploi'];
    makeEndpointsClient()->createSshKey($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/ssh-keys'
            && $request->data() === $payload;
    });
});

it('createSshKey returns a SshKeyResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/ssh-keys' => Http::response([
            'data' => [
                'id' => 573497,
                'status' => 'active',
                'name' => 'NEW_KEY',
                'key' => 'ssh-ed25519 AAAA...',
                'system_user' => 'ploi',
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createSshKey([
        'name' => 'NEW_KEY', 'key' => 'ssh-ed25519 AAAA...', 'system_user' => 'ploi',
    ]);

    expect($result)->toBeInstanceOf(SshKeyResponseData::class)
        ->and($result->system_user)->toBe('ploi');
});

it('deleteSshKey sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/ssh-keys/573496' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteSshKey(573496);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/ssh-keys/573496';
    });
});

// ---------------------------------------------------------------------------
// Server — Daemons
// ---------------------------------------------------------------------------

it('listDaemons returns a Collection of DaemonResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/daemons' => Http::response([
            'data' => [
                [
                    'id' => 207985,
                    'command' => 'php8.4 /home/ploi/architect.filamentcomponents.com/artisan horizon',
                    'processes' => 1,
                    'system_user' => 'ploi',
                    'directory' => null,
                    'status' => 'active',
                ],
                [
                    'id' => 171126,
                    'command' => 'php8.4 /home/ploi/investingstrategy.online/artisan horizon',
                    'processes' => 1,
                    'system_user' => 'ploi',
                    'directory' => null,
                    'status' => 'active',
                ],
                [
                    'id' => 167058,
                    'command' => 'php8.4 /home/ploi/filamentcomponents.com/artisan horizon',
                    'processes' => 1,
                    'system_user' => 'ploi',
                    'directory' => null,
                    'status' => 'active',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->listDaemons();

    /** @var DaemonResponseData $first */
    $first = $result->first();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($first)->toBeInstanceOf(DaemonResponseData::class)
        ->and($first->command)->toBe('php8.4 /home/ploi/architect.filamentcomponents.com/artisan horizon')
        ->and($first->id)->toBe(207985);
});

it('createDaemon sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/daemons' => Http::response([
            'data' => [
                'id' => 208360,
                'command' => 'echo 123',
                'processes' => 1,
                'system_user' => 'ploi',
                'directory' => null,
                'status' => 'created',
            ],
        ], 201),
    ]);

    $payload = ['command' => 'echo 123', 'processes' => 1, 'system_user' => 'ploi'];
    makeEndpointsClient()->createDaemon($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/daemons'
            && $request->data() === $payload;
    });
});

it('createDaemon returns a DaemonResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/daemons' => Http::response([
            'data' => [
                'id' => 208360,
                'command' => 'echo 123',
                'processes' => 1,
                'system_user' => 'ploi',
                'directory' => null,
                'status' => 'created',
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createDaemon([
        'command' => 'echo 123', 'processes' => 1, 'system_user' => 'ploi',
    ]);

    expect($result)->toBeInstanceOf(DaemonResponseData::class)
        ->and($result->id)->toBe(208360)
        ->and($result->processes)->toBe(1);
});

it('pauseDaemon sends a POST request to the toggle-pause endpoint', function () {
    Http::fake([
        'ploi.test/api/servers/81941/daemons/208360/toggle-pause' => Http::response(['message' => 'ok'], 200),
    ]);

    makeEndpointsClient()->pauseDaemon(208360);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/daemons/208360/toggle-pause';
    });
});

it('restartDaemon sends a POST request to the restart endpoint', function () {
    Http::fake([
        'ploi.test/api/servers/81941/daemons/208360/restart' => Http::response([
            'data' => [
                'id' => 208360,
                'command' => 'echo 123',
                'processes' => 1,
                'system_user' => 'ploi',
                'directory' => null,
                'status' => 'restarting',
            ],
        ], 200),
    ]);

    makeEndpointsClient()->restartDaemon(208360);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/daemons/208360/restart';
    });
});

it('deleteDaemon sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/daemons/208360' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteDaemon(208360);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/daemons/208360';
    });
});

// ---------------------------------------------------------------------------
// Server — Logs
// ---------------------------------------------------------------------------

it('listLogs returns a Collection of LogResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/logs' => Http::response([
            'data' => [
                [
                    'id' => 97172478,
                    'description' => 'Deleting Daemon worker',
                    'content' => "Supervisor file not found.\nNo config updates to processes",
                    'type' => null,
                    'site_id' => 358580,
                    'server_id' => 81941,
                    'created_at' => '2026-04-12 22:11:42',
                ],
                [
                    'id' => 97172473,
                    'description' => 'Restarting daemon worker',
                    'content' => "worker-208363: ERROR (no such group)\nworker-208363: ERROR (no such group)",
                    'type' => null,
                    'site_id' => 358580,
                    'server_id' => 81941,
                    'created_at' => '2026-04-12 22:11:26',
                ],
                [
                    'id' => 97172428,
                    'description' => 'Pull git changes',
                    'content' => 'Sun Apr 12 19:08:56 UTC 2026',
                    'type' => 'deploy',
                    'site_id' => 358580,
                    'server_id' => 81941,
                    'created_at' => '2026-04-12 22:09:17',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->listLogs();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(LogResponseData::class)
        ->and($result->first()->description)->toBe('Deleting Daemon worker')
        ->and($result->first()->id)->toBe(97172478);
});

it('listLogs sends a GET request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/logs' => Http::response(['data' => []], 200),
    ]);

    makeEndpointsClient()->listLogs();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://ploi.test/api/servers/81941/logs';
    });
});

// ---------------------------------------------------------------------------
// Server — Settings
// ---------------------------------------------------------------------------

it('updateServer sends a PATCH request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941' => Http::response(['data' => serverFixture()], 200),
    ]);

    $payload = ['name' => 'hostinger-vps-renamed'];
    makeEndpointsClient()->updateServer($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://ploi.test/api/servers/81941'
            && $request->data() === $payload;
    });
});

// ---------------------------------------------------------------------------
// Sites — List
// ---------------------------------------------------------------------------

it('listSites returns a Collection of SiteResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites' => Http::response([
            'data' => [
                [
                    'id' => 253253,
                    'status' => 'active',
                    'server_id' => 81941,
                    'domain' => 'filamentcomponents.com',
                    'tags' => null,
                    'test_domain' => null,
                    'deploy_script' => "cd {RELEASE}\ngit pull origin main",
                    'deploy_webhook_url' => 'https://ploi.io/webhooks/servers/81941/sites/253253/deploy?token=jkJzg5Ui',
                    'web_directory' => '/public',
                    'project_type' => 'laravel',
                    'project_root' => '/',
                    'last_deploy_at' => '2026-04-09 22:48:41',
                    'system_user' => 'ploi',
                    'php_version' => 8.4,
                    'health_url' => null,
                    'disable_robots' => false,
                    'has_repository' => true,
                    'zero_downtime_deployment' => true,
                    'has_staging' => false,
                    'fastcgi_cache' => false,
                    'notes' => null,
                    'suspension' => null,
                    'disk_usage' => ['bytes' => 1034061979, 'human' => '986.16 MB'],
                    'created_at' => '2024-11-03 20:24:41',
                ],
                siteFixture(),
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->listSites();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($result->first())->toBeInstanceOf(SiteResponseData::class)
        ->and($result->first()->domain)->toBe('filamentcomponents.com')
        ->and($result->last()->domain)->toBe('loto.scalablecodes.com');
});

it('listSites sends a GET request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites' => Http::response(['data' => [siteFixture()]], 200),
    ]);

    makeEndpointsClient()->listSites();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites';
    });
});

// ---------------------------------------------------------------------------
// Sites — General (Environment / Deploy / Deploy Script)
// ---------------------------------------------------------------------------

it('getEnvironment returns the raw environment string', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/env' => Http::response([
            'data' => "APP_NAME=\"Lottery Predictor\"\nAPP_ENV=production\nAPP_KEY=base64:03vACWYgwwqUl1C3n6SdEBsz+YtpRauyN/t65ab5Bf4=\nAPP_DEBUG=false\nAPP_URL=https://loto.scalablecodes.com",
        ], 200),
    ]);

    $result = makeEndpointsClient()->getEnvironment();

    expect($result)->toContain('APP_NAME="Lottery Predictor"')
        ->and($result)->toContain('APP_ENV=production');
});

it('getEnvironment sends a GET request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/env' => Http::response(['data' => ''], 200),
    ]);

    makeEndpointsClient()->getEnvironment();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/env';
    });
});

it('updateEnvironment sends a PATCH request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/env' => Http::response(['data' => 'ok'], 200),
    ]);

    $payload = ['content' => "APP_NAME=\"Lottery Predictor\"\nAPP_ENV=production"];
    makeEndpointsClient()->updateEnvironment($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/env'
            && $request->data() === $payload;
    });
});

it('deploySite sends a POST request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/deploy' => Http::response([
            'message' => 'Site loto.scalablecodes.com has been queued for deployment',
        ], 200),
    ]);

    $result = makeEndpointsClient()->deploySite();

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/deploy';
    });

    expect($result)->toHaveKey('message');
});

it('updateDeployScript sends a PATCH request with the deploy_script payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/deploy/script' => Http::response([], 200),
    ]);

    makeEndpointsClient()->updateDeployScript("cd /home/ploi/loto.scalablecodes.com\ngit pull origin dev");

    Http::assertSent(function ($request) {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/deploy/script'
            && $request->data() === ['deploy_script' => "cd /home/ploi/loto.scalablecodes.com\ngit pull origin dev"];
    });
});

// ---------------------------------------------------------------------------
// Queue Workers
// ---------------------------------------------------------------------------

it('getQueueWorkers returns a Collection of QueueResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/queues' => Http::response([
            'data' => [
                [
                    'id' => 208363,
                    'connection' => 'database',
                    'queue' => 'default',
                    'maximum_seconds' => 30,
                    'maximum_tries' => 3,
                    'enviroment' => 'development',
                    'sleep' => 30,
                    'processes' => 2,
                    'backoff' => 0,
                    'system_user' => 'ploi',
                    'php_version' => '8.3',
                    'status' => 'active',
                    'site_id' => 358580,
                    'server_id' => null,
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getQueueWorkers();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(1)
        ->and($result->first())->toBeInstanceOf(QueueResponseData::class)
        ->and($result->first()->queue)->toBe('default')
        ->and($result->first()->id)->toBe(208363);
});

it('createQueueWorker sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/queues' => Http::response([
            'data' => [
                'id' => 208363,
                'connection' => 'database',
                'queue' => 'default',
                'maximum_seconds' => '30',
                'maximum_tries' => '3',
                'enviroment' => 'development',
                'sleep' => '30',
                'processes' => '2',
                'backoff' => 0,
                'system_user' => null,
                'php_version' => '8.3',
                'status' => 'created',
                'site_id' => 358580,
                'server_id' => null,
            ],
        ], 201),
    ]);

    $payload = ['queue' => 'default', 'connection' => 'database'];
    makeEndpointsClient()->createQueueWorker($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/queues'
            && $request->data() === $payload;
    });
});

it('createQueueWorker returns a QueueResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/queues' => Http::response([
            'data' => [
                'id' => 208363,
                'connection' => 'database',
                'queue' => 'default',
                'maximum_seconds' => '30',
                'maximum_tries' => '3',
                'enviroment' => 'development',
                'sleep' => '30',
                'processes' => '2',
                'backoff' => 0,
                'system_user' => null,
                'php_version' => '8.3',
                'status' => 'created',
                'site_id' => 358580,
                'server_id' => null,
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createQueueWorker(['queue' => 'default', 'connection' => 'database']);

    expect($result)->toBeInstanceOf(QueueResponseData::class)
        ->and($result->connection)->toBe('database')
        ->and($result->id)->toBe(208363);
});

it('pauseQueueWorker sends a POST request to the toggle-pause endpoint', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/queues/208363/toggle-pause' => Http::response([
            'data' => [
                'id' => 208363,
                'connection' => 'database',
                'queue' => 'default',
                'maximum_seconds' => 30,
                'maximum_tries' => 3,
                'enviroment' => 'development',
                'sleep' => 30,
                'processes' => 2,
                'backoff' => 0,
                'system_user' => 'ploi',
                'php_version' => '8.3',
                'status' => 'restarting',
                'site_id' => 358580,
                'server_id' => null,
            ],
        ], 200),
    ]);

    makeEndpointsClient()->pauseQueueWorker(208363);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/queues/208363/toggle-pause';
    });
});

it('restartQueueWorker sends a POST request to the restart endpoint', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/queues/208363/restart' => Http::response([], 200),
    ]);

    makeEndpointsClient()->restartQueueWorker(208363);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/queues/208363/restart';
    });
});

it('deleteQueueWorker sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/queues/208363' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteQueueWorker(208363);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/queues/208363';
    });
});

// ---------------------------------------------------------------------------
// Certificates
// ---------------------------------------------------------------------------

it('getCertificates returns a Collection of CertificateResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/certificates' => Http::response([
            'data' => [
                [
                    'id' => 552670,
                    'status' => 'active',
                    'domain' => 'filamentcomponents.com,www.filamentcomponents.com',
                    'type' => 'letsencrypt',
                    'tenant' => false,
                    'site_id' => 253253,
                    'server_id' => 81941,
                    'expires_at' => '2026-06-02T18:07:31.000000Z',
                    'created_at' => '2026-03-04 21:05:12',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getCertificates();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(1)
        ->and($result->first())->toBeInstanceOf(CertificateResponseData::class)
        ->and($result->first()->domain)->toBe('filamentcomponents.com,www.filamentcomponents.com')
        ->and($result->first()->id)->toBe(552670);
});

it('createCertificate sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/certificates' => Http::response([
            'data' => [
                'id' => 552671,
                'status' => 'pending',
                'domain' => 'loto.scalablecodes.com',
                'type' => 'letsencrypt',
                'tenant' => false,
                'site_id' => 358580,
                'server_id' => 81941,
                'expires_at' => null,
                'created_at' => '2026-04-13 00:00:00',
            ],
        ], 201),
    ]);

    $payload = ['type' => 'letsencrypt', 'certificate' => 'loto.scalablecodes.com', 'force' => false];
    makeEndpointsClient()->createCertificate($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/certificates'
            && $request->data() === $payload;
    });
});

it('createCertificate returns a CertificateResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/certificates' => Http::response([
            'data' => [
                'id' => 552671,
                'status' => 'pending',
                'domain' => 'loto.scalablecodes.com',
                'type' => 'letsencrypt',
                'tenant' => false,
                'site_id' => 358580,
                'server_id' => 81941,
                'expires_at' => null,
                'created_at' => '2026-04-13 00:00:00',
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createCertificate([
        'type' => 'letsencrypt', 'certificate' => 'loto.scalablecodes.com', 'force' => false,
    ]);

    expect($result)->toBeInstanceOf(CertificateResponseData::class)
        ->and($result->type)->toBe('letsencrypt')
        ->and($result->id)->toBe(552671);
});

it('deleteCertificate sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/certificates/552670' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteCertificate(552670);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/certificates/552670';
    });
});

// ---------------------------------------------------------------------------
// Cronjobs
// ---------------------------------------------------------------------------

it('createCronjob sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/crontabs' => Http::response([
            'data' => [
                'id' => 294568,
                'status' => 'unknown',
                'command' => 'php8.3 /home/ploi/loto.scalablecodes.com/artisan schedule:run',
                'user' => 'ploi',
                'frequency' => '0 2 * * *',
                'created_at' => '2026-04-12 22:00:01',
            ],
        ], 201),
    ]);

    $payload = ['command' => 'php8.3 /home/ploi/loto.scalablecodes.com/artisan schedule:run', 'frequency' => '0 2 * * *', 'user' => 'ploi'];
    makeEndpointsClient()->createCronjob($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/crontabs'
            && $request->data() === $payload;
    });
});

it('createCronjob returns a CronjobResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/crontabs' => Http::response([
            'data' => [
                'id' => 294568,
                'status' => 'unknown',
                'command' => 'php8.3 /home/ploi/loto.scalablecodes.com/artisan schedule:run',
                'user' => 'ploi',
                'frequency' => '0 2 * * *',
                'created_at' => '2026-04-12 22:00:01',
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createCronjob([
        'command' => 'php8.3 /home/ploi/loto.scalablecodes.com/artisan schedule:run',
        'frequency' => '0 2 * * *',
        'user' => 'ploi',
    ]);

    expect($result)->toBeInstanceOf(CronjobResponseData::class)
        ->and($result->id)->toBe(294568)
        ->and($result->frequency)->toBe('0 2 * * *');
});

it('deleteCronjob sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/crontabs/294568' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteCronjob(294568);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/crontabs/294568';
    });
});

// ---------------------------------------------------------------------------
// Repository
// ---------------------------------------------------------------------------

it('getRepository returns a RepositoryResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/repository' => Http::response([
            'data' => [
                'id' => 358580,
                'domain' => 'loto.scalablecodes.com',
                'web_directory' => '/public',
                'wordpress' => false,
                'laravel' => false,
                'project_root' => '/',
                'last_deploy_at' => '2026-04-12 19:09:17',
                'created_at' => '2026-03-21 14:45:03',
                'repository' => [
                    'branch' => 'dev',
                    'user' => 'lartisan',
                    'name' => 'lottery-generator',
                    'provider' => 'github',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getRepository();

    expect($result)->toBeInstanceOf(RepositoryResponseData::class)
        ->and($result->domain)->toBe('loto.scalablecodes.com')
        ->and($result->web_directory)->toBe('/public');
});

it('getRepository sends a GET request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/repository' => Http::response([
            'data' => [
                'id' => 358580,
                'domain' => 'loto.scalablecodes.com',
                'web_directory' => '/public',
                'project_root' => '/',
                'last_deploy_at' => '2026-04-12 19:09:17',
                'created_at' => '2026-03-21 14:45:03',
            ],
        ], 200),
    ]);

    makeEndpointsClient()->getRepository();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/repository';
    });
});

it('installRepository sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/repository' => Http::response([
            'data' => [
                'id' => 358580,
                'domain' => 'loto.scalablecodes.com',
                'web_directory' => '/public',
                'wordpress' => false,
                'laravel' => false,
                'project_root' => '/',
                'last_deploy_at' => '2026-04-12 19:09:17',
                'created_at' => '2026-03-21 14:45:03',
                'repository' => [
                    'branch' => 'dev',
                    'user' => 'lartisan',
                    'name' => 'lottery-generator',
                    'provider' => 'github',
                ],
            ],
        ], 201),
    ]);

    $payload = ['provider' => 'github', 'branch' => 'dev', 'name' => 'lartisan/lottery-generator'];
    makeEndpointsClient()->installRepository($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/repository'
            && $request->data() === $payload;
    });
});

it('installRepository returns a RepositoryResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/repository' => Http::response([
            'data' => [
                'id' => 358580,
                'domain' => 'loto.scalablecodes.com',
                'web_directory' => '/public',
                'wordpress' => false,
                'laravel' => false,
                'project_root' => '/',
                'last_deploy_at' => '2026-04-12 19:09:17',
                'created_at' => '2026-03-21 14:45:03',
                'repository' => [
                    'branch' => 'dev',
                    'user' => 'lartisan',
                    'name' => 'lottery-generator',
                    'provider' => 'github',
                ],
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->installRepository([
        'provider' => 'github', 'branch' => 'dev', 'name' => 'lartisan/lottery-generator',
    ]);

    expect($result)->toBeInstanceOf(RepositoryResponseData::class)
        ->and($result->web_directory)->toBe('/public');
});

it('toggleQuickDeploy sends a POST request and returns a SiteResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/quick-deploy' => Http::response([
            'data' => siteFixture(),
            'message' => 'Quick deploy has been enabled',
        ], 200),
    ]);

    $result = makeEndpointsClient()->toggleQuickDeploy();

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/quick-deploy';
    });

    expect($result)->toBeInstanceOf(SiteResponseData::class);
});

it('deleteRepository sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/repository' => Http::response(['data' => null], 200),
    ]);

    makeEndpointsClient()->deleteRepository();

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/repository';
    });
});

// ---------------------------------------------------------------------------
// Redirects
// ---------------------------------------------------------------------------

it('getRedirects returns an empty Collection when no redirects exist', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/redirects' => Http::response([
            'data' => [],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getRedirects();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(0);
});

it('getRedirects returns a Collection of RedirectData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/redirects' => Http::response([
            'data' => [
                [
                    'id' => 11,
                    'status' => 'active',
                    'redirect_from' => '/old-page',
                    'redirect_to' => '/new-page',
                    'type' => 'redirect',
                ],
                [
                    'id' => 12,
                    'status' => 'active',
                    'redirect_from' => '/blog',
                    'redirect_to' => '/articles',
                    'type' => 'redirect',
                ],
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getRedirects();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($result->first())->toBeInstanceOf(RedirectData::class)
        ->and($result->first()->redirect_from)->toBe('/old-page');
});

it('createRedirect sends a POST request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/redirects' => Http::response([
            'data' => [
                'id' => 13,
                'status' => 'active',
                'redirect_from' => '/old-page',
                'redirect_to' => '/new-page',
                'type' => 'redirect',
            ],
        ], 201),
    ]);

    $payload = ['redirect_from' => '/old-page', 'redirect_to' => '/new-page', 'type' => 'redirect'];
    makeEndpointsClient()->createRedirect($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/redirects'
            && $request->data() === $payload;
    });
});

it('createRedirect returns a RedirectData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/redirects' => Http::response([
            'data' => [
                'id' => 13,
                'status' => 'active',
                'redirect_from' => '/old-page',
                'redirect_to' => '/new-page',
                'type' => 'redirect',
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->createRedirect([
        'redirect_from' => '/old-page', 'redirect_to' => '/new-page', 'type' => 'redirect',
    ]);

    expect($result)->toBeInstanceOf(RedirectData::class)
        ->and($result->redirect_to)->toBe('/new-page')
        ->and($result->id)->toBe(13);
});

it('deleteRedirect sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/redirects/13' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteRedirect(13);

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/redirects/13';
    });
});

// ---------------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------------

it('getSite returns a SiteResponseData for the configured site', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580' => Http::response([
            'data' => siteFixture(),
        ], 200),
    ]);

    $result = makeEndpointsClient()->getSite();

    expect($result)->toBeInstanceOf(SiteResponseData::class)
        ->and($result->id)->toBe(358580)
        ->and($result->domain)->toBe('loto.scalablecodes.com');
});

it('updateSite sends a PATCH request with the correct payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580' => Http::response([
            'data' => siteFixture(),
        ], 200),
    ]);

    $payload = ['root_domain' => 'loto.scalablecodes.com'];
    makeEndpointsClient()->updateSite($payload);

    Http::assertSent(function ($request) use ($payload) {
        return $request->method() === 'PATCH'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580'
            && $request->data() === $payload;
    });
});

it('updateSite returns a SiteResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580' => Http::response([
            'data' => siteFixture(),
        ], 200),
    ]);

    $result = makeEndpointsClient()->updateSite(['root_domain' => 'loto.scalablecodes.com']);

    expect($result)->toBeInstanceOf(SiteResponseData::class);
});

it('deleteSite sends a DELETE request to the correct URL', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580' => Http::response([], 200),
    ]);

    makeEndpointsClient()->deleteSite();

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580';
    });
});

it('changePhpVersion sends a POST request with the php_version payload', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/php-version' => Http::response([
            'data' => siteFixture(),
        ], 200),
    ]);

    makeEndpointsClient()->changePhpVersion('8.4');

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://ploi.test/api/servers/81941/sites/358580/php-version'
            && $request->data() === ['php_version' => '8.4'];
    });
});

it('changePhpVersion returns a SiteResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/php-version' => Http::response([
            'data' => siteFixture(),
        ], 200),
    ]);

    $result = makeEndpointsClient()->changePhpVersion('8.4');

    expect($result)->toBeInstanceOf(SiteResponseData::class);
});

// ---------------------------------------------------------------------------
// TestDomain bug — always returns null
// ---------------------------------------------------------------------------

it('getTestDomain always returns null because TestDomainData does not extend BaseResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/test-domain' => Http::response([
            'data' => [
                'id' => 358580,
                'domain' => 'loto.scalablecodes.com',
                'test_domain' => null,
                'full_test_domain' => null,
            ],
        ], 200),
    ]);

    $result = makeEndpointsClient()->getTestDomain();

    expect($result)->toBeNull();
});

it('enableTestDomain always returns null because TestDomainData does not extend BaseResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/test-domain' => Http::response([
            'data' => [
                'id' => 358580,
                'domain' => 'loto.scalablecodes.com',
                'test_domain' => null,
                'full_test_domain' => null,
            ],
        ], 201),
    ]);

    $result = makeEndpointsClient()->enableTestDomain();

    expect($result)->toBeNull();
});

it('disableTestDomain always returns null because TestDomainData does not extend BaseResponseData', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/test-domain' => Http::response(['data' => null], 200),
    ]);

    $result = makeEndpointsClient()->disableTestDomain();

    expect($result)->toBeNull();
});

// ---------------------------------------------------------------------------
// Error Handling
// ---------------------------------------------------------------------------

it('throws an Exception with the API message on a 4xx response', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => ['name' => ['The name field is required.']],
        ], 422),
    ]);

    makeEndpointsClient()->createNetworkRule([]);
})->throws(Exception::class);

it('4xx exception message contains the API error message', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => [],
        ], 422),
    ]);

    try {
        makeEndpointsClient()->createNetworkRule([]);
    } catch (Exception $e) {
        $decoded = json_decode($e->getMessage(), true);
        expect($decoded['message'])->toBe('The given data was invalid.');
    }
});

it('throws an Exception with a generic message on a 5xx response', function () {
    Http::fake([
        'ploi.test/api/servers/81941/sites/358580/deploy' => Http::response([], 500),
    ]);

    makeEndpointsClient()->deploySite();
})->throws(Exception::class, 'The server responded with an error.');

it('throws a TypeError when a list endpoint returns malformed (null) data', function () {
    Http::fake([
        'ploi.test/api/servers/81941/network-rules' => Http::response(['data' => null], 200),
    ]);

    makeEndpointsClient()->listNetworkRules();
})->throws(TypeError::class);
