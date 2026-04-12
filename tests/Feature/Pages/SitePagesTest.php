<?php

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Lartisan\PloiDashboard\Pages\Site\Certificate;
use Lartisan\PloiDashboard\Pages\Site\Queue;
use Lartisan\PloiDashboard\Pages\Site\Redirects;
use Lartisan\PloiDashboard\Pages\Site\Repository;
use Lartisan\PloiDashboard\Pages\Site\Settings;
use Lartisan\PloiDashboard\Pages\Site\Site;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

// ---------------------------------------------------------------------------
// Shared API fixtures for site pages
// ---------------------------------------------------------------------------

function fakeSiteApis(): void
{
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

    $siteFixture = [
        'id' => 358580,
        'status' => 'active',
        'server_id' => 81941,
        'domain' => 'test.example.com',
        'test_domain' => null,
        'deploy_script' => 'cd /home/ploi/test.example.com && git pull',
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

    Http::fake([
        // Sub-resources before '*/servers/*' — first match wins.
        '*/servers/*/sites/*' => Http::response(['data' => $siteFixture], 200),
        '*/servers/*' => Http::response(['data' => $serverFixture], 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

function setSiteConfig(): void
{
    config([
        'ploi-dashboard.server_id' => 81941,
        'ploi-dashboard.website_id' => 358580,
        'ploi-dashboard.services.api_key' => 'test-key',
        'ploi-dashboard.services.api_url' => 'https://ploi.test/api',
        'ploi-dashboard.enabled_modules.site.site' => true,
        'ploi-dashboard.enabled_modules.site.certificate' => true,
        'ploi-dashboard.enabled_modules.site.queue' => true,
        'ploi-dashboard.enabled_modules.site.redirects' => true,
        'ploi-dashboard.enabled_modules.site.repository' => true,
        'ploi-dashboard.enabled_modules.site.settings' => true,
        'ploi-dashboard.polling.interval' => '10s',
    ]);
}

// ---------------------------------------------------------------------------
// canAccess() — config gates
// ---------------------------------------------------------------------------

it('each site page respects its canAccess() config flag', function (string $pageClass, string $configKey) {
    config([$configKey => true]);
    expect($pageClass::canAccess())->toBeTrue();

    config([$configKey => false]);
    expect($pageClass::canAccess())->toBeFalse();
})->with([
    'Site' => [Site::class, 'ploi-dashboard.enabled_modules.site.site'],
    'Certificate' => [Certificate::class, 'ploi-dashboard.enabled_modules.site.certificate'],
    'Queue' => [Queue::class, 'ploi-dashboard.enabled_modules.site.queue'],
    'Redirects' => [Redirects::class, 'ploi-dashboard.enabled_modules.site.redirects'],
    'Repository' => [Repository::class, 'ploi-dashboard.enabled_modules.site.repository'],
    'Settings' => [Settings::class, 'ploi-dashboard.enabled_modules.site.settings'],
]);

// ---------------------------------------------------------------------------
// Static properties — slug
// ---------------------------------------------------------------------------

it('each site page has the expected slug', function (string $pageClass, string $expectedSlug) {
    expect($pageClass::getSlug())->toBe($expectedSlug);
})->with([
    [Site::class, 'site'],
    [Certificate::class, 'site/certificate'],
    [Queue::class, 'site/queue'],
    [Redirects::class, 'site/redirects'],
    [Repository::class, 'site/repository'],
    [Settings::class, 'site/settings'],
]);

// ---------------------------------------------------------------------------
// form() method — uses Filament\Schemas\Schema (Filament v5 requirement)
// ---------------------------------------------------------------------------

it('each site page with a form uses Schema as parameter type', function (string $pageClass) {
    $method = new ReflectionMethod($pageClass, 'form');
    $param = $method->getParameters()[0] ?? null;

    expect($param)->not->toBeNull('form() must accept a parameter')
        ->and($param->getType()->getName())->toBe(Schema::class);
})->with([
    'Site' => Site::class,
    'Certificate' => Certificate::class,
    'Queue' => Queue::class,
    'Redirects' => Redirects::class,
    'Repository' => Repository::class,
    'Settings' => Settings::class,
]);

// ---------------------------------------------------------------------------
// table() method — uses Filament\Tables\Table
// ---------------------------------------------------------------------------

it('each site page with a table uses Table as parameter type', function (string $pageClass) {
    $method = new ReflectionMethod($pageClass, 'table');
    $param = $method->getParameters()[0] ?? null;

    expect($param)->not->toBeNull('table() must accept a parameter')
        ->and($param->getType()->getName())->toBe(Table::class);
})->with([
    'Certificate' => Certificate::class,
    'Queue' => Queue::class,
    'Redirects' => Redirects::class,
]);

// ---------------------------------------------------------------------------
// records() closure — returns plain arrays (Filament v5 Model|array constraint)
// ---------------------------------------------------------------------------

it('Certificate page table records are plain arrays after toLivewire()', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [[
                'id' => 1,
                'status' => 'active',
                'domain' => 'test.example.com',
                'type' => 'letsencrypt',
                'tenant' => false,
                'site_id' => 358580,
                'server_id' => 81941,
                'expires_at' => '2027-01-01 00:00:00',
                'created_at' => '2026-01-01 00:00:00',
            ]],
        ], 200),
    ]);
    setSiteConfig();

    $records = Ploi::make()->getCertificates()
        ->map(fn ($r) => $r->toLivewire());

    expect($records->first())->toBeArray()
        ->and($records->first())->toHaveKey('domain')
        ->and($records->first())->toHaveKey('status');
});

it('Queue page table records are plain arrays after toLivewire()', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [[
                'id' => 1,
                'status' => 'active',
                'queue' => 'default',
                'connection' => 'database',
                'sleep' => 10,
                'processes' => 1,
                'maximum_seconds' => 30,
                'maximum_tries' => 3,
                'enviroment' => 'production',
                'backoff' => 0,
                'system_user' => 'ploi',
                'php_version' => null,
                'site_id' => 358580,
                'server_id' => 81941,
            ]],
        ], 200),
    ]);
    setSiteConfig();

    $records = Ploi::make()->getQueueWorkers()
        ->map(fn ($r) => $r->toLivewire());

    expect($records->first())->toBeArray()
        ->and($records->first())->toHaveKey('queue')
        ->and($records->first())->toHaveKey('status');
});

it('Redirects page table records are plain arrays after toLivewire()', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [[
                'id' => 1,
                'status' => 'active',
                'redirect_from' => '/old',
                'redirect_to' => '/new',
                'type' => 'redirect',
                'created_at' => '2026-01-01 00:00:00',
            ]],
        ], 200),
    ]);
    setSiteConfig();

    $records = Ploi::make()->getRedirects()
        ->map(fn ($r) => $r->toLivewire());

    expect($records->first())->toBeArray()
        ->and($records->first())->toHaveKey('redirect_from')
        ->and($records->first())->toHaveKey('redirect_to');
});

// ---------------------------------------------------------------------------
// mount() — API calls succeed and properties are populated
// ---------------------------------------------------------------------------

it('Site page mount() populates $site and $server from API', function () {
    fakeSiteApis();
    setSiteConfig();

    $page = new Site;
    $page->mount();

    expect($page->site)->not->toBeNull()
        ->and($page->site->domain)->toBe('test.example.com')
        ->and($page->server)->not->toBeNull()
        ->and($page->server->name)->toBe('test-server');
});

it('Site page mount() fills $data with deploy_script', function () {
    fakeSiteApis();
    setSiteConfig();

    $page = new Site;
    $page->mount();

    expect($page->data)->toHaveKey('deploy_script')
        ->and($page->data['deploy_script'])->toContain('test.example.com');
});

it('Certificate page mount() sets default certificate to site domain', function () {
    fakeSiteApis();
    setSiteConfig();

    $page = new Certificate;
    $page->mount();

    expect($page->website)->not->toBeNull()
        ->and($page->data['certificate'])->toBe('test.example.com')
        ->and($page->data['type'])->toBe('letsencrypt');
});

it('Queue page mount() populates $server with installed PHP versions', function () {
    fakeSiteApis();
    setSiteConfig();

    $page = new Queue;
    $page->mount();

    expect($page->server)->not->toBeNull()
        ->and($page->server->installed_php_versions)->toBe(['8.4', '8.3']);
});

it('Queue page has correct default form data after mount()', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);
    setSiteConfig();

    $page = new Queue;
    $page->mount();

    expect($page->data['connection'])->toBe('database')
        ->and($page->data['queue'])->toBe('default')
        ->and($page->data['processes'])->toBe(1)
        ->and($page->data['memory'])->toBe(128);
});

it('Redirects page has correct default form data', function () {
    $page = new Redirects;

    expect($page->data['redirect_from'])->toBe('')
        ->and($page->data['redirect_to'])->toBe('')
        ->and($page->data['type'])->toBe('redirect');
});

it('Settings page mount() populates $server and $data from API', function () {
    fakeSiteApis();
    setSiteConfig();

    $page = new Settings;
    $page->mount();

    expect($page->server)->not->toBeNull()
        ->and($page->data)->toHaveKey('domain')
        ->and($page->data['domain'])->toBe('test.example.com')
        ->and($page->data)->toHaveKey('php_version')
        ->and($page->data['php_version'])->toBe('8.4');
});
