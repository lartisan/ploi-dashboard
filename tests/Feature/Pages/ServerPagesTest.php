<?php

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Lartisan\PloiDashboard\Pages\Server\Cronjobs;
use Lartisan\PloiDashboard\Pages\Server\Daemons;
use Lartisan\PloiDashboard\Pages\Server\Databases;
use Lartisan\PloiDashboard\Pages\Server\Logs;
use Lartisan\PloiDashboard\Pages\Server\Network;
use Lartisan\PloiDashboard\Pages\Server\Php;
use Lartisan\PloiDashboard\Pages\Server\Server;
use Lartisan\PloiDashboard\Pages\Server\Settings;
use Lartisan\PloiDashboard\Pages\Server\SshKeys;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

// ---------------------------------------------------------------------------
// Shared API fixture for server pages
// ---------------------------------------------------------------------------

function fakeServerApis(): void
{
    $siteFixture = [
        'id' => 358580,
        'status' => 'active',
        'server_id' => 81941,
        'domain' => 'test.example.com',
        'test_domain' => null,
        'deploy_script' => 'git pull',
        'deploy_webhook_url' => 'https://ploi.io/webhooks/deploy',
        'web_directory' => '/public',
        'project_type' => 'laravel',
        'project_root' => '/',
        'last_deploy_at' => '2026-01-01 00:00:00',
        'system_user' => 'ploi',
        'php_version' => '8.4',
        'health_url' => null,
        'disable_robots' => false,
        'has_repository' => true,
        'zero_downtime_deployment' => false,
        'has_staging' => false,
        'fastcgi_cache' => false,
        'notes' => null,
        'disk_usage' => null,
        'created_at' => '2026-01-01 00:00:00',
    ];

    $serverFixture = [
        'id' => 81941,
        'status' => 'active',
        'type' => 'server',
        'database_type' => 'mysql',
        'name' => 'test-server',
        'ip_address' => '1.2.3.4',
        'internal_ip' => null,
        'ssh_port' => '22',
        'reboot_required' => false,
        'php_version' => '8.4',
        'php_cli_version' => '8.4',
        'mysql_version' => '8.0',
        'sites_count' => 1,
        'monitoring' => true,
        'opcache' => true,
        'installed_php_versions' => ['8.4', '8.3'],
        'updates' => ['packages' => 0, 'security' => 0],
        'description' => null,
        'status_id' => 2,
        'provider' => null,
        'created_at' => '2024-01-01 00:00:00',
        'created_human' => '1 year ago',
        'uptime_human' => 'Online for 1 day',
    ];

    Http::fake([
        // Sub-resources must come before '*/servers/*' — first match wins
        // and '*/servers/*' would otherwise swallow all nested URLs.
        '*/servers/*/sites' => Http::response(['data' => [$siteFixture]], 200),
        '*/servers/*/crontabs' => Http::response(['data' => []], 200),
        '*/servers/*/databases' => Http::response(['data' => []], 200),
        '*/servers/*/daemons' => Http::response(['data' => []], 200),
        '*/servers/*/ssh-keys' => Http::response(['data' => []], 200),
        '*/servers/*/firewall-rules' => Http::response(['data' => []], 200),
        '*/servers/*/logs' => Http::response(['data' => []], 200),
        '*/servers/*' => Http::response(['data' => $serverFixture], 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

function setServerConfig(): void
{
    config([
        'ploi-dashboard.server_id' => 81941,
        'ploi-dashboard.website_id' => 358580,
        'ploi-dashboard.services.api_key' => 'test-key',
        'ploi-dashboard.services.api_url' => 'https://ploi.test/api',
        'ploi-dashboard.enabled_modules.server.server' => true,
        'ploi-dashboard.enabled_modules.server.cronjobs' => true,
        'ploi-dashboard.enabled_modules.server.daemons' => true,
        'ploi-dashboard.enabled_modules.server.databases' => true,
        'ploi-dashboard.enabled_modules.server.logs' => true,
        'ploi-dashboard.enabled_modules.server.network' => true,
        'ploi-dashboard.enabled_modules.server.php' => true,
        'ploi-dashboard.enabled_modules.server.settings' => true,
        'ploi-dashboard.enabled_modules.server.ssh-keys' => true,
        'ploi-dashboard.polling.interval' => '10s',
    ]);
}

// ---------------------------------------------------------------------------
// canAccess() — config gates
// ---------------------------------------------------------------------------

it('Server page is accessible when config is enabled', function () {
    config(['ploi-dashboard.enabled_modules.server.server' => true]);
    expect(Server::canAccess())->toBeTrue();
});

it('Server page is inaccessible when config is disabled', function () {
    config(['ploi-dashboard.enabled_modules.server.server' => false]);
    expect(Server::canAccess())->toBeFalse();
});

it('each server page respects its canAccess() config flag', function (string $pageClass, string $configKey) {
    config([$configKey => true]);
    expect($pageClass::canAccess())->toBeTrue();

    config([$configKey => false]);
    expect($pageClass::canAccess())->toBeFalse();
})->with([
    'Server' => [Server::class, 'ploi-dashboard.enabled_modules.server.server'],
    'Cronjobs' => [Cronjobs::class, 'ploi-dashboard.enabled_modules.server.cronjobs'],
    'Daemons' => [Daemons::class, 'ploi-dashboard.enabled_modules.server.daemons'],
    'Databases' => [Databases::class, 'ploi-dashboard.enabled_modules.server.databases'],
    'Logs' => [Logs::class, 'ploi-dashboard.enabled_modules.server.logs'],
    'Network' => [Network::class, 'ploi-dashboard.enabled_modules.server.network'],
    'Php' => [Php::class, 'ploi-dashboard.enabled_modules.server.php'],
    'Settings' => [Settings::class, 'ploi-dashboard.enabled_modules.server.settings'],
    'SshKeys' => [SshKeys::class, 'ploi-dashboard.enabled_modules.server.ssh-keys'],
]);

// ---------------------------------------------------------------------------
// Static properties — slug and navigation group
// ---------------------------------------------------------------------------

it('each server page has the expected slug', function (string $pageClass, string $expectedSlug) {
    expect($pageClass::getSlug())->toBe($expectedSlug);
})->with([
    [Server::class, 'server'],
    [Cronjobs::class, 'server/cronjobs'],
    [Daemons::class, 'server/daemons'],
    [Databases::class, 'server/databases'],
    [Logs::class, 'server/logs'],
    [Network::class, 'server/network'],
    [Php::class, 'server/php'],
    [Settings::class, 'server/settings'],
    [SshKeys::class, 'server/ssh-keys'],
]);

// ---------------------------------------------------------------------------
// form() method — uses Filament\Schemas\Schema (Filament v5 requirement)
// ---------------------------------------------------------------------------

it('each server page with a form uses Schema as parameter type', function (string $pageClass) {
    $method = new ReflectionMethod($pageClass, 'form');
    $param = $method->getParameters()[0] ?? null;

    expect($param)->not->toBeNull('form() must accept a parameter')
        ->and($param->getType()->getName())->toBe(Schema::class);
})->with([
    'Server' => Server::class,
    'Cronjobs' => Cronjobs::class,
    'Daemons' => Daemons::class,
    'Databases' => Databases::class,
    'Network' => Network::class,
    'Php' => Php::class,
    'Settings' => Settings::class,
    'SshKeys' => SshKeys::class,
]);

// ---------------------------------------------------------------------------
// table() method — uses Filament\Tables\Table
// ---------------------------------------------------------------------------

it('each server page with a table uses Table as parameter type', function (string $pageClass) {
    $method = new ReflectionMethod($pageClass, 'table');
    $param = $method->getParameters()[0] ?? null;

    expect($param)->not->toBeNull('table() must accept a parameter')
        ->and($param->getType()->getName())->toBe(Table::class);
})->with([
    'Server' => Server::class,
    'Cronjobs' => Cronjobs::class,
    'Daemons' => Daemons::class,
    'Databases' => Databases::class,
    'Logs' => Logs::class,
    'Network' => Network::class,
    'SshKeys' => SshKeys::class,
]);

// ---------------------------------------------------------------------------
// records() closure — returns plain arrays (Filament v5 Model|array constraint)
// ---------------------------------------------------------------------------

it('Server page table records are plain arrays after toLivewire()', function () {
    fakeServerApis();
    setServerConfig();

    $records = Ploi::make()->listSites()
        ->map(fn ($r) => $r->toLivewire());

    expect($records->first())->toBeArray()
        ->and($records->first())->toHaveKey('domain')
        ->and($records->first())->toHaveKey('status');
});

it('Databases page table records are plain arrays after toLivewire()', function () {
    Http::fake([
        '*/databases' => Http::response([
            'data' => [[
                'id' => 1,
                'type' => 'mysql',
                'name' => 'test_db',
                'server_id' => 81941,
                'status' => 'active',
                'site' => null,
                'created_at' => '2024-01-01 00:00:00',
                'users' => [],
            ]],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);
    setServerConfig();

    $records = Ploi::make()->getDatabases()
        ->map(fn ($r) => $r->toLivewire());

    expect($records->first())->toBeArray()
        ->and($records->first())->toHaveKey('name')
        ->and($records->first())->toHaveKey('status');
});

// ---------------------------------------------------------------------------
// mount() — API calls succeed and properties are populated
// ---------------------------------------------------------------------------

it('Server page mount() populates $record from API', function () {
    fakeServerApis();
    setServerConfig();

    $page = new Server;
    $page->mount();

    expect($page->record)->not->toBeNull()
        ->and($page->record->name)->toBe('test-server')
        ->and($page->record->ip_address)->toBe('1.2.3.4');
});

it('Databases page mount() populates server record and databases count', function () {
    fakeServerApis();
    setServerConfig();

    $page = new Databases;
    $page->mount();

    expect($page->record)->not->toBeNull()
        ->and($page->record->name)->toBe('test-server')
        ->and($page->databasesCount)->toBe(0);
});

it('Settings page mount() populates $record and fills $data array', function () {
    fakeServerApis();
    setServerConfig();

    $page = new Settings;
    $page->mount();

    expect($page->record)->not->toBeNull()
        ->and($page->data)->toHaveKey('name')
        ->and($page->data['name'])->toBe('test-server')
        ->and($page->data)->toHaveKey('ip_address')
        ->and($page->data['ip_address'])->toBe('1.2.3.4');
});

it('Php page mount() populates $record with installed PHP versions', function () {
    fakeServerApis();
    setServerConfig();

    $page = new Php;
    $page->mount();

    expect($page->record)->not->toBeNull()
        ->and($page->record->installed_php_versions)->toBe(['8.4', '8.3']);
});

it('Cronjobs page mount() sets default command with site domain', function () {
    Http::fake([
        '*/sites/*' => Http::response([
            'data' => [
                'id' => 358580, 'status' => 'active', 'server_id' => 81941,
                'domain' => 'my-site.com', 'test_domain' => null,
                'deploy_script' => 'git pull', 'deploy_webhook_url' => 'https://ploi.io/deploy',
                'web_directory' => '/public', 'project_type' => 'laravel',
                'project_root' => '/', 'last_deploy_at' => null, 'system_user' => 'ploi',
                'php_version' => '8.4', 'health_url' => null, 'disable_robots' => false,
                'has_repository' => false, 'zero_downtime_deployment' => false,
                'has_staging' => false, 'fastcgi_cache' => false, 'notes' => null,
                'disk_usage' => null, 'created_at' => '2026-01-01 00:00:00',
            ],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);
    setServerConfig();

    $page = new Cronjobs;
    $page->mount();

    expect($page->data['command'])->toContain('my-site.com');
});

it('Daemons page has default data set without API calls', function () {
    $page = new Daemons;

    expect($page->data)->toHaveKey('processes')
        ->and($page->data['processes'])->toBe(1)
        ->and($page->data)->toHaveKey('system_user')
        ->and($page->data['system_user'])->toBe('ploi');
});

it('Logs page has no public data property and no API calls in mount', function () {
    $page = new Logs;

    expect($page)->not->toHaveProperty('data');
});

it('Network page has default type and rule_type', function () {
    $page = new Network;

    expect($page->data['type'])->toBe('tcp')
        ->and($page->data['rule_type'])->toBe('allow');
});
