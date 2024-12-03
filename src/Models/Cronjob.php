<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CronjobResponseData;
use Sushi\Sushi;

class Cronjob extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'status' => 'string',
        'command' => 'string',
        'user' => 'string',
        'frequency' => 'string',
        'created_at' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->getCronjobs()
                ->map(fn (CronjobResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
