<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DaemonResponseData;
use Sushi\Sushi;

class Daemon extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'command' => 'string',
        'processes' => 'integer',
        'system_user' => 'string',
        'directory' => 'string',
        'status' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->listDaemons()
                ->map(fn (DaemonResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
