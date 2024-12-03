<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\LogResponseData;
use Sushi\Sushi;

class Log extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'description' => 'string',
        'content' => 'string',
        'type' => 'string',
        'site_id' => 'integer',
        'server_id' => 'integer',
        'created_at' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->listLogs()
                ->map(fn (LogResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
