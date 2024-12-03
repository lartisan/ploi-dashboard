<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SshKeyResponseData;
use Sushi\Sushi;

class SshKey extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'status' => 'string',
        'name' => 'string',
        'key' => 'string',
        'system_user' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->listSshKeys()
                ->map(fn (SshKeyResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
