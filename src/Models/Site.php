<?php

namespace Lartisan\PloiDashboard\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;
use Sushi\Sushi;

class Site extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'status' => 'string',
        'server_id' => 'integer',
        'domain' => 'string',
        'test_domain' => 'string',
        'deploy_script' => 'string',
        'deploy_webhook_url' => 'string',
        'web_directory' => 'string',
        'project_type' => 'string',
        'project_root' => 'string',
        'last_deploy_at' => 'string',
        'system_user' => 'string',
        'php_version' => 'string',
        'health_url' => 'string',
        'disable_robots' => 'boolean',
        'has_repository' => 'boolean',
        'zero_downtime_deployment' => 'boolean',
        'has_staging' => 'boolean',
        'fastcgi_cache' => 'boolean',
        'notes' => 'string',
        'disk_usage' => 'integer',
        'created_at' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->listSites()
                ->map(fn(SiteResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
