<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Sushi\Sushi;

class Server extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'status' => 'string',
        'type' => 'string',
        'database_type' => 'string',
        'name' => 'string',
        'ip_address' => 'string',
        'internal_ip' => 'string',
        'ssh_port' => 'string',
        'reboot_required' => 'boolean',
        'php_version' => 'float',
        'php_cli_version' => 'string',
        'mysql_version' => 'float',
        'sites_count' => 'integer',
        'monitoring' => 'boolean',
        'opcache' => 'boolean',
        'installed_php_versions' => 'json',
        'updates' => 'json',
        'description' => 'string',
        'status_id' => 'integer',
        'provider' => 'json',
        'created_at' => 'string',
        'created_human' => 'string',
        'uptime_human' => 'string',
    ];

    protected $casts = [
        'reboot_required' => 'boolean',
        'installed_php_versions' => 'array',
        'updates' => 'array',
        'monitoring' => 'boolean',
        'opcache' => 'boolean',
        'provider' => 'array',
        'created_at' => 'datetime',
    ];

    public function getRows(): array
    {
        return collect([Ploi::make()->getServer()])
            ->map(fn (ServerResponseData $server) => collect($server->toLivewire())->map(fn ($item) => is_array($item) ? json_encode($item) : $item)->toArray())
            ->toArray();
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
