<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DatabaseResponseData;
use Sushi\Sushi;

class Database extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'type' => 'string',
        'name' => 'string',
        'server_id' => 'integer',
        'status' => 'string',
        'site' => 'json',
        'created_at' => 'string',
        'users' => 'json',
    ];

    protected $casts = [
        'site' => 'array',
        'users' => 'array',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->getDatabases()
                ->map(fn (DatabaseResponseData $server) => collect($server->toLivewire())->map(fn ($item) => is_array($item) ? json_encode($item) : $item)->toArray())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
