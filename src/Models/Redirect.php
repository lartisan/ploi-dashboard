<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RedirectData;
use Sushi\Sushi;

class Redirect extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'status' => 'string',
        'redirect_from' => 'string',
        'redirect_to' => 'string',
        'type' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->getRedirects()
                ->map(fn(RedirectData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
