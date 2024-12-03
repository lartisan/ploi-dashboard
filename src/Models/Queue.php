<?php

namespace Lartisan\PloiDashboard\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\QueueResponseData;
use Sushi\Sushi;

class Queue extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'connection' => 'string',
        'queue' => 'string',
        'maximum_seconds' => 'string',
        'maximum_tries' => 'string',
        'enviroment' => 'string',
        'sleep' => 'string',
        'processes' => 'string',
        'backoff' => 'string',
        'system_user' => 'string',
        'php_version' => 'string',
        'status' => 'string',
        'site_id' => 'integer',
        'server_id' => 'integer',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->getQueueWorkers()
                ->map(fn (QueueResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
